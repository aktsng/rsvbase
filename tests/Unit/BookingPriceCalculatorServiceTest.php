<?php

namespace Tests\Unit;

use App\DTOs\PriceBreakdown;
use App\Models\Room;
use App\Models\SpecialPrice;
use App\Services\BookingPriceCalculatorService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingPriceCalculatorServiceTest extends TestCase
{
    use RefreshDatabase;

    private BookingPriceCalculatorService $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new BookingPriceCalculatorService();
    }

    /**
     * テスト用の部屋を作成
     */
    private function createRoom(array $attributes = []): Room
    {
        return Room::factory()->create(array_merge([
            'base_price_per_night' => 10000,
            'weekend_price_per_night' => 13000,
            'cleaning_fee' => 3000,
        ], $attributes));
    }

    // ─── 基本料金テスト ──────────────────────────────

    /** @test */
    public function 平日1泊の基本料金が正しく計算される(): void
    {
        $room = $this->createRoom();

        // 2025年の水曜→木曜（平日）
        $checkIn = Carbon::parse('2025-07-09'); // 水曜
        $checkOut = Carbon::parse('2025-07-10'); // 木曜

        $result = $this->calculator->calculate($room, $checkIn, $checkOut);

        $this->assertInstanceOf(PriceBreakdown::class, $result);
        $this->assertCount(1, $result->dailyPrices);
        $this->assertEquals(10000, $result->dailyPrices['2025-07-09']['price']);
        $this->assertEquals('基本料金', $result->dailyPrices['2025-07-09']['label']);
        $this->assertEquals(3000, $result->cleaningFee);
        $this->assertEquals(13000, $result->totalAmount); // 10000 + 3000
    }

    /** @test */
    public function 平日2泊の基本料金が正しく計算される(): void
    {
        $room = $this->createRoom();

        $checkIn = Carbon::parse('2025-07-08'); // 火曜
        $checkOut = Carbon::parse('2025-07-10'); // 木曜

        $result = $this->calculator->calculate($room, $checkIn, $checkOut);

        $this->assertCount(2, $result->dailyPrices);
        $this->assertEquals(10000, $result->dailyPrices['2025-07-08']['price']);
        $this->assertEquals(10000, $result->dailyPrices['2025-07-09']['price']);
        $this->assertEquals(3000, $result->cleaningFee);
        $this->assertEquals(23000, $result->totalAmount); // 10000*2 + 3000
    }

    // ─── 週末料金テスト ──────────────────────────────

    /** @test */
    public function 金曜日は週末料金が適用される(): void
    {
        $room = $this->createRoom();

        $checkIn = Carbon::parse('2025-07-11'); // 金曜
        $checkOut = Carbon::parse('2025-07-12'); // 土曜

        $result = $this->calculator->calculate($room, $checkIn, $checkOut);

        $this->assertEquals(13000, $result->dailyPrices['2025-07-11']['price']);
        $this->assertEquals('週末料金', $result->dailyPrices['2025-07-11']['label']);
        $this->assertEquals(16000, $result->totalAmount); // 13000 + 3000
    }

    /** @test */
    public function 土曜日は週末料金が適用される(): void
    {
        $room = $this->createRoom();

        $checkIn = Carbon::parse('2025-07-12'); // 土曜
        $checkOut = Carbon::parse('2025-07-13'); // 日曜

        $result = $this->calculator->calculate($room, $checkIn, $checkOut);

        $this->assertEquals(13000, $result->dailyPrices['2025-07-12']['price']);
        $this->assertEquals('週末料金', $result->dailyPrices['2025-07-12']['label']);
    }

    /** @test */
    public function 週末料金が未設定の場合は基本料金が適用される(): void
    {
        $room = $this->createRoom(['weekend_price_per_night' => null]);

        $checkIn = Carbon::parse('2025-07-11'); // 金曜
        $checkOut = Carbon::parse('2025-07-12'); // 土曜

        $result = $this->calculator->calculate($room, $checkIn, $checkOut);

        $this->assertEquals(10000, $result->dailyPrices['2025-07-11']['price']);
        $this->assertEquals('基本料金', $result->dailyPrices['2025-07-11']['label']);
    }

    // ─── 特定日料金テスト ─────────────────────────────

    /** @test */
    public function 特定日料金が最優先で適用される(): void
    {
        $room = $this->createRoom();

        // 特定日料金を設定（年末年始: 12/31〜1/3）
        SpecialPrice::factory()->create([
            'room_id' => $room->id,
            'start_date' => '2025-12-31',
            'end_date' => '2026-01-03',
            'price_per_night' => 25000,
            'label' => '年末年始',
        ]);

        $checkIn = Carbon::parse('2025-12-31'); // 水曜
        $checkOut = Carbon::parse('2026-01-01'); // 木曜

        $result = $this->calculator->calculate($room, $checkIn, $checkOut);

        $this->assertEquals(25000, $result->dailyPrices['2025-12-31']['price']);
        $this->assertEquals('年末年始', $result->dailyPrices['2025-12-31']['label']);
        $this->assertEquals(28000, $result->totalAmount); // 25000 + 3000
    }

    /** @test */
    public function 特定日料金は週末料金より優先される(): void
    {
        $room = $this->createRoom();

        // 金曜日に特定日料金を設定
        SpecialPrice::factory()->create([
            'room_id' => $room->id,
            'start_date' => '2025-07-11',
            'end_date' => '2025-07-11',
            'price_per_night' => 20000,
            'label' => '特別イベント',
        ]);

        $checkIn = Carbon::parse('2025-07-11'); // 金曜（通常は週末料金13000）
        $checkOut = Carbon::parse('2025-07-12');

        $result = $this->calculator->calculate($room, $checkIn, $checkOut);

        // 特定日料金が優先（13000ではなく20000）
        $this->assertEquals(20000, $result->dailyPrices['2025-07-11']['price']);
        $this->assertEquals('特別イベント', $result->dailyPrices['2025-07-11']['label']);
    }

    // ─── 複合ケーステスト ─────────────────────────────

    /** @test */
    public function 基本料金と週末料金と特定日料金が混在するケース(): void
    {
        $room = $this->createRoom();

        // 木金土の3泊で、金曜だけ特定日料金を設定
        SpecialPrice::factory()->create([
            'room_id' => $room->id,
            'start_date' => '2025-07-11',
            'end_date' => '2025-07-11',
            'price_per_night' => 20000,
            'label' => '特別日',
        ]);

        $checkIn = Carbon::parse('2025-07-10');  // 木曜 → 基本料金
        $checkOut = Carbon::parse('2025-07-13'); // 日曜チェックアウト

        $result = $this->calculator->calculate($room, $checkIn, $checkOut);

        $this->assertCount(3, $result->dailyPrices);

        // 木曜: 基本料金
        $this->assertEquals(10000, $result->dailyPrices['2025-07-10']['price']);
        $this->assertEquals('基本料金', $result->dailyPrices['2025-07-10']['label']);

        // 金曜: 特定日料金（週末料金より優先）
        $this->assertEquals(20000, $result->dailyPrices['2025-07-11']['price']);
        $this->assertEquals('特別日', $result->dailyPrices['2025-07-11']['label']);

        // 土曜: 週末料金
        $this->assertEquals(13000, $result->dailyPrices['2025-07-12']['price']);
        $this->assertEquals('週末料金', $result->dailyPrices['2025-07-12']['label']);

        // 合計: 10000 + 20000 + 13000 + 3000(清掃) = 46000
        $this->assertEquals(46000, $result->totalAmount);
    }

    // ─── PriceBreakdown DTO テスト ────────────────────

    /** @test */
    public function toBookedPriceDetailsでJSON保存用配列に変換できる(): void
    {
        $room = $this->createRoom();

        $checkIn = Carbon::parse('2025-07-10');
        $checkOut = Carbon::parse('2025-07-12');

        $result = $this->calculator->calculate($room, $checkIn, $checkOut);
        $details = $result->toBookedPriceDetails();

        $this->assertCount(2, $details);
        $this->assertEquals('2025-07-10', $details[0]['date']);
        $this->assertArrayHasKey('price', $details[0]);
        $this->assertArrayHasKey('label', $details[0]);
    }

    /** @test */
    public function subtotalで清掃費を除いた宿泊料金合計を取得できる(): void
    {
        $room = $this->createRoom();

        $checkIn = Carbon::parse('2025-07-08'); // 火
        $checkOut = Carbon::parse('2025-07-10'); // 木

        $result = $this->calculator->calculate($room, $checkIn, $checkOut);

        $this->assertEquals(20000, $result->subtotal()); // 10000 * 2
        $this->assertEquals(23000, $result->totalAmount); // 20000 + 3000
    }

    // ─── 祝前日テスト ────────────────────────────────

    /** @test */
    public function 祝前日は週末料金が適用される(): void
    {
        $room = $this->createRoom();

        // 2025/11/02（日曜）→ 翌日11/03は文化の日（祝日）なので祝前日扱い
        $checkIn = Carbon::parse('2025-11-02'); // 日曜（通常は基本料金だが祝前日）
        $checkOut = Carbon::parse('2025-11-03');

        $result = $this->calculator->calculate($room, $checkIn, $checkOut);

        $this->assertEquals(13000, $result->dailyPrices['2025-11-02']['price']);
        $this->assertEquals('週末料金', $result->dailyPrices['2025-11-02']['label']);
    }
}
