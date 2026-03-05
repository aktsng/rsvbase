<?php

namespace App\DTOs;

class PriceBreakdown
{
    /**
     * @param array<string, array{price: int, label: string, dayTotal: int}> $dailyPrices 日付 => {price, label, dayTotal}
     * @param int $cleaningFee 清掃費（1滞在1回）
     * @param int $totalAmount 合計金額
     * @param string $pricingType 'room' or 'person'
     * @param int $adults 大人人数
     * @param int $childA 子供A人数
     * @param int $childB 子供B人数
     * @param int $addAdultFee 大人追加料金（1名あたり）
     * @param int $addChildAFee 子供A追加料金（1名あたり）
     * @param int $addChildBFee 子供B追加料金（1名あたり）
     */
    public function __construct(
        public readonly array $dailyPrices,
        public readonly int $cleaningFee,
        public readonly int $totalAmount,
        public readonly string $pricingType = 'room',
        public readonly int $adults = 1,
        public readonly int $childA = 0,
        public readonly int $childB = 0,
        public readonly int $addChildAFee = 0,
        public readonly int $addChildBFee = 0,
    ) {
    }

    /**
     * 宿泊料金のみの合計（清掃費除く）
     */
    public function subtotal(): int
    {
        return collect($this->dailyPrices)->sum('dayTotal');
    }

    /**
     * 予約テーブルに保存する用のJSON配列に変換
     */
    public function toBookedPriceDetails(): array
    {
        return collect($this->dailyPrices)->map(function ($detail, $date) {
            return [
                'date' => $date,
                'price' => $detail['price'],
                'label' => $detail['label'],
                'dayTotal' => $detail['dayTotal'],
                'addChildAFee' => $detail['addChildAFee'] ?? 0,
                'addChildBFee' => $detail['addChildBFee'] ?? 0,
            ];
        })->values()->all();
    }
}
