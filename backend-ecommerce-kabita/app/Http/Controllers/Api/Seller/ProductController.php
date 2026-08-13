<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Product
 * @tag Product - Product management for sellers
 */
class ProductController extends Controller
{
    /**
     * List products for the seller's shop
     *
     * @authenticated
     * @queryParam search string "Search by product name" example=sepatu
     * @queryParam category_id integer "Filter by category ID" example=1
     * @queryParam status string "Filter by product status" example=active
     * @queryParam sort string "Sort: newest|oldest|price_asc|price_desc" default=newest
     * @query_param per_page integer "Items per page" default=15
     * @response 200 body="{"success":true,"data":[{}],"meta":{"current_page":1,"per_page":15,"total":50,"last_page":4}}"
     * @response 404 body="{"success":false,"message":"Anda belum memiliki toko."}"
     */
    public function index(Request $request)
    {
        //
    }

    /**
     * Create a new product for the seller's shop
     *
     * @authenticated
     * @requestBody required
     * @bodyParam name string required "Product name" example=Sepatu Sneakers
     * @bodyParam description string "Product description" example=Sepatu sneakers casual
     * @bodyParam price number required "Product price" example=250000
     * @bodyParam cost_price number "Product cost price" example=150000
     * @bodyParam stock integer required "Product stock" example=100
     * @bodyParam category_id integer required "Category ID" example=1
     * @bodyParam status string "Product status: pending|active|rejected" example=pending
     * @response 201 body="{"success":true,"message":"Produk berhasil dibuat. Menunggu verifikasi admin.","data":{}}"
     * @response 404 body="{"success":false,"message":"Anda belum memiliki toko."}"
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Get product details for the seller's shop
     *
     * @authenticated
     * @param string $slug Product slug
     * @response 200 body="{"success":true,"data":{}}"
     * @response 403 body="{"success":false,"message":"Akses ditolak. Produk ini bukan milik toko Anda."}"
     * @response 404 body="{"success":false,"message":"Produk tidak ditemukan."}"
     */
    public function show(string $slug)
    {
        //
    }

    /**
     * Update a product for the seller's shop
     *
     * @authenticated
     * @requestBody required
     * @bodyParam name string "Product name" example=Sepatu Sneakers
     * @bodyParam description string "Product description" example=Sepatu sneakers casual
     * @bodyParam price number "Product price" example=250000
     * @bodyParam cost_price number "Product cost price" example=150000
     * @bodyParam stock integer "Product stock" example=100
     * @bodyParam category_id integer "Category ID" example=1
     * @bodyParam status string "Product status: pending|active|rejected" example=active
     * @response 200 body="{"success":true,"message":"Produk berhasil diperbarui.","data":{}}"
     * @response 403 body="{"success":false,"message":"Akses ditolak. Produk ini bukan milik toko Anda."}"
     * @response 404 body="{"success":false,"message":"Produk tidak ditemukan."}"
     */
    public function update(Request $request, string $slug)
    {
        //
    }

    /**
     * Delete a product from the seller's shop
     *
     * @authenticated
     * @response 200 body="{"success":true,"message":"Produk berhasil dihapus."}"
     * @response 403 body="{"success":false,"message":"Akses ditolak. Produk ini bukan milik toko Anda."}"
     * @response 404 body="{"success":false,"message":"Produk tidak ditemukan."}"
     */
    public function destroy(string $slug)
    {
        //
    }
}
