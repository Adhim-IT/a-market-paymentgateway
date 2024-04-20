<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\Order;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class OrderStats extends BaseWidget
{
    protected function getStats(): array
    {
        $averagePrice = Order::query()->avg('grand_total');
        $averagePrice = $averagePrice ?? 0;
        $userCount = User::whereHas('roles', function ($query) {
            $query->where('name', 'user');
        })->count();
        return [
           
            Stat::make('Average Price', Number::currency($averagePrice, 'USD ')),
            Stat::make('New Orders', Order::query()->where('status', 'new')->count()),
            Stat::make('Order Processing', Order::query()->where('status', 'processing')->count()),
            // Stat::make('Shipped Orders', Order::query()->where('status', 'shipped')->count()),
        ];
    }
}
