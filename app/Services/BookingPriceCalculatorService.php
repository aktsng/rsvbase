<?php

namespace App\Services;

use App\DTOs\PriceBreakdown;
use App\Models\Room;
use App\Models\SpecialPrice;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class BookingPriceCalculatorService
{
    /**
     * 日本の祝日（年度ごとにキャッシュ）
     * @var array<int, array<string>>
     */
    private array $holidayCache = [];

    /**
     * 部屋の料金を計算する
     *
     * 3段階の優先順位:
     * 1. 特定日料金（special_prices テーブル）
     * 2. 週末料金（金・土・祝前日）
     * 3. 基本単価（base_price_per_night）
     *
     * 室単価の場合: 人数は料金計算に影響しない（dayTotal = price）
     * 人数単価の場合: dayTotal = price * adults + childA*add_child_a_fee + childB*add_child_b_fee
     *
     * @param Room   $room     対象部屋
     * @param Carbon $checkIn  チェックイン日
     * @param Carbon $checkOut チェックアウト日
     * @param int    $adults   大人人数（デフォルト1）
     * @param int    $childA   子供A人数（デフォルト0）
     * @param int    $childB   子供B人数（デフォルト0）
     * @return PriceBreakdown 料金内訳
     */
    public function calculate(
        Room $room,
        Carbon $checkIn,
        Carbon $checkOut,
        int $adults = 1,
        int $childA = 0,
        int $childB = 0,
    ): PriceBreakdown {
        // 特定日料金を事前に取得
        $specialPrices = $this->getSpecialPricesForRange($room, $checkIn, $checkOut);

        $dailyPrices = [];
        $isPersonPricing = $room->isPersonPricing();
        $addChildAFee = $room->add_child_a_fee ?? 0;
        $addChildBFee = $room->add_child_b_fee ?? 0;

        // チェックイン日からチェックアウト前日までを1日ずつ計算
        $period = CarbonPeriod::create($checkIn, '1 day', $checkOut->copy()->subDay());

        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $priceInfo = $this->resolvePrice($room, $date, $specialPrices);

            if ($isPersonPricing) {
                // 人数単価: base_price × 大人人数 + 子供A + 子供B
                $dayTotal = $priceInfo['price'] * $adults
                    + $childA * $addChildAFee
                    + $childB * $addChildBFee;
            } else {
                // 室単価: 部屋料金のみ
                $dayTotal = $priceInfo['price'];
            }

            $dailyPrices[$dateStr] = [
                'price' => $priceInfo['price'],
                'label' => $priceInfo['label'],
                'dayTotal' => $dayTotal,
                'addChildAFee' => $addChildAFee,
                'addChildBFee' => $addChildBFee,
            ];
        }

        $subtotal = collect($dailyPrices)->sum('dayTotal');
        $cleaningFee = $room->cleaning_fee;
        $totalAmount = $subtotal + $cleaningFee;

        return new PriceBreakdown(
            dailyPrices: $dailyPrices,
            cleaningFee: $cleaningFee,
            totalAmount: $totalAmount,
            pricingType: $room->pricing_type ?? 'room',
            adults: $adults,
            childA: $childA,
            childB: $childB,
            addChildAFee: $addChildAFee,
            addChildBFee: $addChildBFee,
        );
    }

    /**
     * 1日分の適用料金を決定する（3段階優先順位）
     *
     * @param Room       $room          対象部屋
     * @param Carbon     $date          対象日
     * @param Collection $specialPrices 期間に該当する特定料金
     * @return array{price: int, label: string}
     */
    private function resolvePrice(Room $room, Carbon $date, Collection $specialPrices): array
    {
        // 優先順位1: 特定日料金
        $specialPrice = $this->findSpecialPrice($date, $specialPrices);
        if ($specialPrice !== null) {
            $label = $specialPrice->label ?: '特別料金';
            return [
                'price' => $specialPrice->price_per_night,
                'label' => $label,
            ];
        }

        // 優先順位2: 週末料金（金・土・祝前日）
        if ($this->isWeekendOrPreHoliday($date) && $room->weekend_price_per_night !== null) {
            return [
                'price' => $room->weekend_price_per_night,
                'label' => '週末料金',
            ];
        }

        // 優先順位3: 基本単価
        return [
            'price' => $room->base_price_per_night,
            'label' => '基本料金',
        ];
    }

    /**
     * 指定範囲に該当する特定日料金を一括取得
     */
    private function getSpecialPricesForRange(Room $room, Carbon $checkIn, Carbon $checkOut): Collection
    {
        $lastNight = $checkOut->copy()->subDay()->startOfDay();
        $firstNight = $checkIn->copy()->startOfDay();

        return SpecialPrice::where('room_id', $room->id)
            ->whereDate('start_date', '<=', $lastNight)
            ->whereDate('end_date', '>=', $firstNight)
            ->get();
    }

    /**
     * 指定日に該当する特定日料金を探す
     */
    private function findSpecialPrice(Carbon $date, Collection $specialPrices): ?SpecialPrice
    {
        $dateStr = $date->format('Y-m-d');

        return $specialPrices->first(function (SpecialPrice $sp) use ($dateStr) {
            return $sp->start_date->format('Y-m-d') <= $dateStr
                && $sp->end_date->format('Y-m-d') >= $dateStr;
        });
    }

    /**
     * 週末または祝前日かどうかを判定
     * - 金曜日・土曜日 = 週末
     * - 翌日が祝日 = 祝前日
     */
    public function isWeekendOrPreHoliday(Carbon $date): bool
    {
        // 金曜 (5) または 土曜 (6)
        if ($date->isFriday() || $date->isSaturday()) {
            return true;
        }

        // 翌日が祝日であれば「祝前日」
        $nextDay = $date->copy()->addDay();
        return $this->isJapaneseHoliday($nextDay);
    }

    /**
     * 日本の祝日かどうかを判定
     * ※ 簡易実装: 外部ライブラリ (yasumi) を使用する場合はここを差し替え
     */
    public function isJapaneseHoliday(Carbon $date): bool
    {
        $year = $date->year;

        if (!isset($this->holidayCache[$year])) {
            $this->holidayCache[$year] = $this->getJapaneseHolidays($year);
        }

        return in_array($date->format('Y-m-d'), $this->holidayCache[$year], true);
    }

    /**
     * 指定年の日本の祝日一覧を返す
     * ※ Yasumiライブラリ導入時は差し替え可能
     */
    private function getJapaneseHolidays(int $year): array
    {
        // 固定祝日一覧（内閣府準拠）
        $holidays = [
            sprintf('%d-01-01', $year), // 元日
            sprintf('%d-02-11', $year), // 建国記念の日
            sprintf('%d-02-23', $year), // 天皇誕生日
            sprintf('%d-04-29', $year), // 昭和の日
            sprintf('%d-05-03', $year), // 憲法記念日
            sprintf('%d-05-04', $year), // みどりの日
            sprintf('%d-05-05', $year), // こどもの日
            sprintf('%d-08-11', $year), // 山の日
            sprintf('%d-11-03', $year), // 文化の日
            sprintf('%d-11-23', $year), // 勤労感謝の日
        ];

        // 移動祝日
        $holidays[] = $this->getNthWeekdayOfMonth($year, 1, Carbon::MONDAY, 2); // 成人の日(1月第2月曜)
        $holidays[] = sprintf('%d-03-%02d', $year, $this->vernalEquinoxDay($year)); // 春分の日
        $holidays[] = $this->getNthWeekdayOfMonth($year, 7, Carbon::MONDAY, 3); // 海の日(7月第3月曜)
        $holidays[] = $this->getNthWeekdayOfMonth($year, 9, Carbon::MONDAY, 3); // 敬老の日(9月第3月曜)
        $holidays[] = sprintf('%d-09-%02d', $year, $this->autumnalEquinoxDay($year)); // 秋分の日
        $holidays[] = $this->getNthWeekdayOfMonth($year, 10, Carbon::MONDAY, 2); // スポーツの日(10月第2月曜)

        return $holidays;
    }

    /**
     * 第N番目の特定曜日の日付を取得
     */
    private function getNthWeekdayOfMonth(int $year, int $month, int $dayOfWeek, int $nth): string
    {
        $date = Carbon::create($year, $month, 1);
        $count = 0;

        while ($date->month === $month) {
            if ($date->dayOfWeek === $dayOfWeek) {
                $count++;
                if ($count === $nth) {
                    return $date->format('Y-m-d');
                }
            }
            $date->addDay();
        }

        return '';
    }

    /**
     * 春分の日を計算（略算式）
     */
    private function vernalEquinoxDay(int $year): int
    {
        return (int) floor(20.8431 + 0.242194 * ($year - 1980) - floor(($year - 1980) / 4));
    }

    /**
     * 秋分の日を計算（略算式）
     */
    private function autumnalEquinoxDay(int $year): int
    {
        return (int) floor(23.2488 + 0.242194 * ($year - 1980) - floor(($year - 1980) / 4));
    }
}
