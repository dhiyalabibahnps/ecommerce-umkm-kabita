<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Buyer\ShippingCalculateRequest;
use Illuminate\Http\JsonResponse;

/**
 * @group Shipping
 * @tag Shipping - Shipping options & calculation
 */
class ShippingController extends Controller
{
  /**
   * @authenticated
   * Get available shipping options
   *
   * @response 200 body="{"success":true,"data":[{"id":"cod","name":"Cash On Delivery (COD)","cost":0},{"id":"kurir_reguler","name":"Kurir Reguler (3-5 Hari)","base_cost":10000},{"id":"kurir_express","name":"Kurir Express (1-2 Hari)","base_cost":20000}]}"
   */
  public function options(): JsonResponse
  {
    $options = [
      [
        'id' => 'cod',
        'name' => 'Cash On Delivery (COD)',
        'cost' => 0,
        'estimated_days' => null,
      ],
      [
        'id' => 'kurir_reguler',
        'name' => 'Kurir Reguler (3-5 Hari)',
        'base_cost' => 10000,
        'estimated_days' => '3-5 hari kerja',
      ],
      [
        'id' => 'kurir_express',
        'name' => 'Kurir Express (1-2 Hari)',
        'base_cost' => 20000,
        'estimated_days' => '1-2 hari kerja',
      ],
    ];

    return response()->json([
      'success' => true,
      'data' => $options,
    ]);
  }

  /**
   * Calculate internal shipping costs
   *
   * @authenticated
   * @requestBody required
   * @bodyParam shipping_method string required "Shipping method: cod or kurir" example=kurir
   * @bodyParam courier_type string "Courier type: reguler or express" example=reguler
   * @bodyParam weight number required "Weight in grams" example=1000
   * @response 200 body="{"success":true,"data":{"shipping_method":"string","courier_type":"reguler|express","estimated_cost":15000,"estimated_days":"3-5 hari kerja"}}"
   */
  public function calculate(ShippingCalculateRequest $request): JsonResponse
  {
    $weight = (float) $request->input('weight'); // in grams
    $shippingMethod = $request->input('shipping_method');
    $courierType = $request->input('courier_type', 'reguler');

    // Config constants
    $baseRates = [
      'reguler' => 10000,
      'express' => 20000,
    ];
    $pricePerKg = 5000;
    $estimatedDays = [
      'reguler' => '3-5 hari kerja',
      'express' => '1-2 hari kerja',
    ];

    // Calculate shipping cost
    if ($shippingMethod === 'cod') {
      $estimatedCost = 0;
    } else {
      // Courier shipping: base_rate + (weight_grams / 1000 * price_per_kg)
      $weightInKg = $weight / 1000;
      $estimatedCost = $baseRates[$courierType] + ($weightInKg * $pricePerKg);
    }

    return response()->json([
      'success' => true,
      'data' => [
        'shipping_method' => $shippingMethod,
        'courier_type' => $courierType,
        'estimated_cost' => $estimatedCost,
        'estimated_days' => $shippingMethod === 'kurir' ? $estimatedDays[$courierType] : null,
      ],
    ]);
  }
}
