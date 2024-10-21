<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Filament\Resources\OrderResource\RelationManagers\AddressRelationManager;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Number;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationGroup = 'Shop';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make('Order Information')
                        ->schema([
                            Select::make('user_id')
                                ->label('Customer')
                                ->relationship('user', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),

                            Select::make('payment_method')
                                ->label('Payment Method')
                                ->options([
                                    'cod' => 'Cash on Delivery',
                                    'stripe' => 'Stripe',
                                    'paypal' => 'Paypal',
                                ])
                                ->required(),

                            Select::make('payment_status')
                                ->label('Payment Status')
                                ->options([
                                    'pending' => 'Pending',
                                    'paid' => 'Paid',
                                    'failed' => 'Failed',
                                    // 'refunded' => 'Refunded',
                                ])
                                ->default('pending')
                                ->required(),

                            ToggleButtons::make('status')
                                ->label('Status')
                                ->inline()
                                ->default('new')
                                ->options([
                                    'new' => 'New',
                                    'processing' => 'Processing',
                                    'shipped' => 'Shipped',
                                    'delivered' => 'Delivered',
                                    'cancelled' => 'Cancelled',
                                ])
                                ->colors([
                                    'new' => 'info',
                                    'processing' => 'warning',
                                    'shipped' => 'success',
                                    'delivered' => 'success',
                                    'cancelled' => 'danger',
                                ])
                                ->icons([
                                    'new' => 'heroicon-m-sparkles',
                                    'processing' => 'heroicon-m-arrow-path',
                                    'shipped' => 'heroicon-m-truck',
                                    'delivered' => 'heroicon-m-check-badge',
                                    'cancelled' => 'heroicon-m-x-circle',
                                ])
                                ->required(),


                            Select::make('currency')
                                ->label('Currency')
                                ->options([
                                    'inr' => 'INR',
                                    'usd' => 'USD',
                                    'eur' => 'EUR',
                                    'gbp' => 'GBP',
                                ])
                                ->default('inr')
                                ->required(),

                            Select::make('shipping_method')
                                ->label('Shipping Method')
                                ->options([
                                    'fedex' => 'Fedex',
                                    'ups' => 'UPS',
                                    'dhl' => 'DHL',
                                    'usps' => 'USPS',
                                ])
                                ->default('fedex'),

                            TextInput::make('notes')
                                ->label('Notes')
                                ->columnSpanFull(),

                        ])->columns(2),

                    Section::make('Order Items')
                        ->schema([
                            Repeater::make('items')
                                ->relationship()
                                ->schema([

                                    Select::make('product_id')
                                        ->relationship('product', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->distinct()
                                        ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                        ->columnSpan(4)
                                        ->reactive()
                                        ->afterStateUpdated(fn($state, Set $set) => $set('unit_amount', Product::find($state)?->price ?? 0))
                                        ->afterStateUpdated(fn($state, Set $set) => $set('total_amount', Product::find($state)?->price  ?? 0)),


                                    TextInput::make('quantity')
                                        ->numeric()
                                        ->default(1)
                                        ->minValue(1)
                                        ->required()
                                        ->columnSpan(2)
                                        ->reactive()
                                        ->afterStateUpdated(fn($state, set $set, Get $get) => $set('total_amount', $state * $get('unit_amount'))),

                                    TextInput::make('unit_amount')
                                        ->numeric()
                                        ->required()
                                        ->disabled()
                                        ->dehydrated()
                                        ->columnSpan(3),

                                    TextInput::make('total_amount')
                                        ->numeric()
                                        ->required()
                                        ->disabled()
                                        ->dehydrated()
                                        ->columnSpan(3),

                                ])->columns(12),

                            Placeholder::make('grand_total_placeholder')
                                ->label('Grand Total')
                                ->content(function (Get $get, Set $set) {
                                    $total = 0;
                                    if (!$repeaters = $get('items')) return $total;

                                    foreach ($repeaters as $key => $repeater) {
                                        $total += $get("items.{$key}.total_amount");
                                    }
                                    $set('grand_total', $total);
                                    return Number::currency($total, 'INR');
                                }),

                            Hidden::make('grand_total')
                                ->default(0),

                        ])
                ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('grand_total')
                    ->label('Grand Total')
                    ->numeric()
                    ->money('inr')
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label('Payment Method')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->color(fn($state) => match ($state) {
                        'stripe' => 'info',
                        'cod' => 'success',
                        'paypal' => 'gray',
                    })
                    ->icon(fn($state) => match ($state) {
                        'stripe' => 'heroicon-m-sparkles',
                        'cod' => 'heroicon-m-truck',
                        'paypal' => 'heroicon-m-check-badge',
                    }),

                TextColumn::make('payment_status')
                    ->label('Payment Status')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->color(fn($state) => match ($state) {
                        'pending' => 'info',
                        'paid' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'gray',
                    })
                    ->icon(fn($state) => match ($state) {
                        'pending' => 'heroicon-m-sparkles',
                        'paid' => 'heroicon-m-check-badge',
                        'failed' => 'heroicon-m-x-circle',
                        'refunded' => 'heroicon-m-x-circle',
                    }),


                SelectColumn::make('status')
                    ->label('Status')
                    ->sortable()
                    ->options([
                        'new' => 'New',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ]),

                TextColumn::make('currency')
                    ->label('Currency')
                    ->badge()
                    ->sortable(),

                TextColumn::make('shipping_method')
                    ->label('Shipping Method')
                    ->badge()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Order Date')
                    ->dateTime('d/m/Y H:i:s')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->date('d/m/Y H:i:s')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            AddressRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return Order::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return Order::count() > 10 ? 'success' : 'danger';
    }
}
