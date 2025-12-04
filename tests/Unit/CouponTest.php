<?php

namespace Tests\Unit;

use App\Models\Coupon;
use Carbon\Carbon;
use Tests\RefreshMongoDB;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use RefreshMongoDB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRefreshMongoDB();
    }

    // ==========================================
    // TEST SUITE: Coupon Validity
    // ==========================================

    public function test_coupon_is_valid_when_not_expired_and_has_uses_left(): void
    {
        $coupon = Coupon::create([
            'code' => 'VALID10',
            'type' => 'percent',
            'value' => 10,
            'expires_at' => Carbon::now()->addDays(30),
            'usage_limit' => 100,
            'usage_count' => 50,
        ]);

        $this->assertTrue($coupon->isValid());
    }

    public function test_coupon_is_invalid_when_expired(): void
    {
        $coupon = Coupon::create([
            'code' => 'EXPIRED10',
            'type' => 'percent',
            'value' => 10,
            'expires_at' => Carbon::now()->subDays(1),
            'usage_limit' => 100,
            'usage_count' => 0,
        ]);

        $this->assertFalse($coupon->isValid());
    }

    public function test_coupon_is_invalid_when_usage_limit_reached(): void
    {
        $coupon = Coupon::create([
            'code' => 'LIMIT_REACHED',
            'type' => 'fixed',
            'value' => 50000,
            'expires_at' => Carbon::now()->addDays(30),
            'usage_limit' => 10,
            'usage_count' => 10,
        ]);

        $this->assertFalse($coupon->isValid());
    }

    public function test_coupon_is_valid_when_no_expiry_date(): void
    {
        $coupon = Coupon::create([
            'code' => 'NO_EXPIRY',
            'type' => 'percent',
            'value' => 15,
            'expires_at' => null,
            'usage_limit' => 100,
            'usage_count' => 0,
        ]);

        $this->assertTrue($coupon->isValid());
    }

    public function test_coupon_is_valid_when_no_usage_limit(): void
    {
        $coupon = Coupon::create([
            'code' => 'UNLIMITED',
            'type' => 'fixed',
            'value' => 20000,
            'expires_at' => Carbon::now()->addDays(7),
            'usage_limit' => null,
            'usage_count' => 9999,
        ]);

        $this->assertTrue($coupon->isValid());
    }

    // ==========================================
    // TEST SUITE: Coupon Types
    // ==========================================

    public function test_fixed_type_coupon_has_correct_value(): void
    {
        $coupon = Coupon::create([
            'code' => 'FIXED50K',
            'type' => 'fixed',
            'value' => 50000,
            'expires_at' => Carbon::now()->addDays(30),
        ]);

        $this->assertEquals('fixed', $coupon->type);
        $this->assertEquals(50000, $coupon->value);
    }

    public function test_percent_type_coupon_has_correct_value(): void
    {
        $coupon = Coupon::create([
            'code' => 'PERCENT20',
            'type' => 'percent',
            'value' => 20,
            'expires_at' => Carbon::now()->addDays(30),
        ]);

        $this->assertEquals('percent', $coupon->type);
        $this->assertEquals(20, $coupon->value);
    }

    // ==========================================
    // TEST SUITE: Value Accessor (Decimal128 conversion)
    // ==========================================

    public function test_value_accessor_converts_float_correctly(): void
    {
        $coupon = Coupon::create([
            'code' => 'FLOAT_TEST',
            'type' => 'percent',
            'value' => 15.5,
            'expires_at' => Carbon::now()->addDays(30),
        ]);

        $coupon->refresh();

        $this->assertIsFloat($coupon->value);
        $this->assertEquals(15.5, $coupon->value);
    }

    public function test_value_accessor_handles_null(): void
    {
        $coupon = Coupon::create([
            'code' => 'NULL_VALUE',
            'type' => 'fixed',
            'value' => null,
            'expires_at' => Carbon::now()->addDays(30),
        ]);

        $this->assertNull($coupon->value);
    }
}
