<?php

namespace App\Filament\Admin\Resources;

use App\Enums\UserRank;
use App\Filament\Admin\Resources\UserResource\Pages;
use App\Filament\Admin\Resources\UserResource\Pages\ListUsers;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                return $query->with(['wallets' => function ($q) {
                    $q->whereIn('slug', ['earning', 'product']);
                }]);
            })
            ->defaultSort('users.id', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->tooltip(fn (User $record): string => $record->created_at->format(
                        config('app.datetime_format'),
                    )),
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->tooltip(fn (User $record): string => $record->username),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('with_product')
                    ->label('Product')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('rank')
                    ->label('Rank')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->tooltip(fn (User $record): string => $record->rank_updated_at->format(
                        config('app.datetime_format'),
                    )),
                TextColumn::make('total_deposit')
                    ->label('Total Deposit')
                    ->money()
                    ->sortable()
                    ->hidden(),
                TextColumn::make('total_withdraw')
                    ->label('Total Withdraw')
                    ->money()
                    ->sortable()
                    ->hidden(),
                TextColumn::make('total_income')
                    ->label('Total Income')
                    ->money()
                    ->sortable()
                    ->hidden(),
                TextColumn::make('earnings')
                    ->label('Earning Balance')
                    ->money()
                    ->sortable(query: function (Builder $query, string $direction) {
                        return $query
                            ->leftJoin('wallets as earning_wallet', function ($join) {
                                $join->on('users.id', '=', 'earning_wallet.holder_id')
                                    ->where('earning_wallet.holder_type', '=', \App\Models\User::class)
                                    ->where('earning_wallet.slug', '=', 'earning');
                            })
                            ->orderBy('earning_wallet.balance', $direction ?? 'desc')
                            ->select('users.*');
                    })
                    ->getStateUsing(function (User $record) {
                        return optional($record->wallets->first(function ($wallet) {
                            return $wallet->slug === 'earning';
                        }))->balanceFloat ?? 0;
                    }),
                TextColumn::make('product_balance')
                    ->label('Product Balance')
                    ->money()
                    ->sortable(query: function (Builder $query, string $direction) {
                        return $query
                            ->leftJoin('wallets as product_wallet', function ($join) {
                                $join->on('users.id', '=', 'product_wallet.holder_id')
                                    ->where('product_wallet.holder_type', '=', \App\Models\User::class)
                                    ->where('product_wallet.slug', '=', 'product');
                            })
                            ->orderBy('product_wallet.balance', $direction ?? 'desc')
                            ->select('users.*');
                    })
                    ->getStateUsing(function (User $record) {
                        return optional($record->wallets->first(function ($wallet) {
                            return $wallet->slug === 'product';
                        }))->balanceFloat ?? 0;
                    }),
            ])
            ->filters([
                SelectFilter::make('rank')
                    ->options(UserRank::class)
                    ->label('Rank'),
                TernaryFilter::make('is_active')
                    ->trueLabel('Active')
                    ->falseLabel('Inactive')
                    ->label('Status'),
                TernaryFilter::make('with_product')
                    ->trueLabel('With Product')
                    ->falseLabel('Without Product')
                    ->label('Product'),
            ])
            ->recordActions([
                // Tables\Actions\EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
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
            'index' => ListUsers::route('/'),
            // 'create' => Pages\CreateUser::route('/create'),
            // 'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
