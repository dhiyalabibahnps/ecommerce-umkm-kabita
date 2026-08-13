<?php

namespace App\Console\Commands;

use App\Models\DailyProductSales;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AggregateDailySalesCommand extends Command
{
    protected $signature = 'analytics:aggregate-daily-sales';

    protected $description = 'Aggregate daily product sales from delivered orders';

    public function handle(): int
    {
        $this->info('Starting daily sales aggregation...');

        // Get all dates that have delivered orders
        $dates = Order::where('status', 'delivered')
            ->selectRaw('DATE(created_at) as sale_date')
            ->distinct()
            ->pluck('sale_date');

        $totalUpdated = 0;

        foreach ($dates as $date) {
            // Aggregate from order_items joined with products for category info
            $salesData = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->where('orders.status', 'delivered')
                ->whereRaw('DATE(orders.created_at) = ?', [$date])
                ->select(
                    'products.id as product_id',
                    'products.shop_id',
                    'products.category_id',
                    DB::raw('SUM(order_items.quantity) as total_qty'),
                    DB::raw('SUM(order_items.quantity * order_items.price_snapshot) as total_revenue'),
                    DB::raw('SUM(order_items.quantity * (order_items.price_snapshot - order_items.cost_snapshot)) as total_profit')
                )
                ->groupBy('products.id', 'products.shop_id', 'products.category_id')
                ->get();

            foreach ($salesData as $row) {
                DailyProductSales::updateOrCreate(
                    [
                        'date' => $date,
                        'product_id' => $row->product_id,
                        'shop_id' => $row->shop_id,
                        'category_id' => $row->category_id,
                    ],
                    [
                        'total_qty_sold' => $row->total_qty,
                        'total_revenue' => $row->total_revenue,
                        'total_profit' => $row->total_profit,
                    ]
                );
                $totalUpdated++;
            }
        }

        $this->info("Aggregation complete. Updated {$totalUpdated} records.");

        return Command::SUCCESS;
    }
}
