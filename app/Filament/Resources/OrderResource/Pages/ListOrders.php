<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Filament\Resources\OrderResource\Widgets\OrderStats;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            OrderStats::class,
        ];
    }

    // protected function getFooterWidgets(): array
    // {
    //     return [
    //         // Add your footer widgets here
    //     ];
    // }


    public function getTabs(): array
    {
        return [
            null => Tab::make('All'),
            'new' => Tab::make()->modifyQueryUsing(fn($query) => $query->where('status', 'new')),
            'processing' => Tab::make()->modifyQueryUsing(fn($query) => $query->where('status', 'processing')),
            'shipped' => Tab::make()->modifyQueryUsing(fn($query) => $query->where('status', 'shipped')),
            'delivered' => Tab::make()->modifyQueryUsing(fn($query) => $query->where('status', 'delivered')),
            'canceled' => Tab::make()->modifyQueryUsing(fn($query) => $query->where('status', 'canceled')),

        ];
    }
}
