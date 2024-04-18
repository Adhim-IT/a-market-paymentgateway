<?php

namespace App\Filament\Resources\UserResource\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserStats extends BaseWidget
{
    protected function getStats(): array
    {
        $userCount = User::whereHas('roles', function ($query) {
            $query->where('name', 'user');
        })->count();
        $adminCount = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->count();
        $sellerCount = User::whereHas('roles', function ($query) {
            $query->where('name', 'seller');
        })->count();
        return [
            Stat::make('User' , $userCount),
            Stat::make('Admin' , $adminCount),
            Stat::make('Seller' , $sellerCount),
        ];
    }
}
