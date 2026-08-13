<?php

namespace Tests\Unit;

use App\Models\Shop;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\TestCase;

class ShopModelTest extends TestCase
{
  public function test_shop_relationships_can_be_resolved(): void
  {
    $shop = new Shop();

    $this->assertInstanceOf(HasMany::class, $shop->products());
    $this->assertInstanceOf(HasMany::class, $shop->orders());
    $this->assertInstanceOf(HasMany::class, $shop->dailyProductSales());
  }
}
