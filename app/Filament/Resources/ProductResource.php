<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationGroup = 'Shop';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Group::make()
                    ->schema([
                        Section::make('Product Information')
                            ->schema([

                                TextInput::make('name')
                                    ->required()
                                    ->label('Product Name')
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn(string $operation, $state, Set $set) => $operation
                                        === 'create' ? $set('slug', Str::slug($state)) : null),

                                TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->disabled()
                                    ->dehydrated()
                                    ->unique(Product::class, 'slug', ignoreRecord: true),

                                MarkdownEditor::make('description')
                                    ->required()
                                    ->columnSpanFull()
                                    ->fileAttachmentsDirectory('products')
                                    ->label('Description'),

                            ])->columns(2),

                        Section::make('Images')
                            ->schema([
                                FileUpload::make('images')
                                    ->multiple()
                                    ->maxFiles(5)
                                    ->reorderable()
                                    ->directory('products'),
                            ]),
                    ])->columnSpan(2),

                Group::make()
                    ->schema([
                        Section::make('Price & Inventory')
                            ->schema([
                                TextInput::make('price')
                                    ->required()
                                    ->numeric()
                                    ->prefix('INR'),

                            ]),

                        Section::make('Associations')
                            ->schema([
                                Select::make('category_id')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->required()
                                    ->preload(),

                                Select::make('brand_id')
                                    ->relationship('brand', 'name')
                                    ->searchable()
                                    ->required()
                                    ->preload(),
                            ]),

                        Section::make('Status')
                            ->schema([
                                Toggle::make('in_stock')
                                    ->required()
                                    ->default(true),

                                Toggle::make('is_active')
                                    ->required()
                                    ->default(true),

                                Toggle::make('is_featured')
                                    ->required()
                                    ->default(false),

                                Toggle::make('on_sale')
                                    ->required()
                                    ->default(false),
                            ]),

                    ])->columnSpan(1),

            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category.name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('brand.name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('price')
                    ->money('INR'),

                IconColumn::make('in_stock')
                    ->boolean(),

                IconColumn::make('is_active')
                    ->boolean(),

                IconColumn::make('is_featured')
                    ->boolean(),

                IconColumn::make('on_sale')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
                SelectFilter::make('category')
                    ->relationship('category', 'name'),

                SelectFilter::make('brand')
                    ->relationship('brand', 'name'),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
