<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class OrderStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('New Orders', Order::where('status', 'new')->count()),
            Stat::make('Processing Orders', Order::where('status', 'processing')->count()),
            Stat::make('Shipped Orders', Order::where('status', 'shipped')->count()),
            Stat::make('Average Price', Number::currency(Order::avg('grand_total'), 'INR')),
            Stat::make('Total Sales', Number::currency(Order::sum('grand_total'), 'INR')),
        ];
    }
}
