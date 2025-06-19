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
            ->columns([
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
                    ->alignCenter(),
                IconColumn::make('with_product')
                    ->label('Product')
                    ->boolean()
                    ->alignCenter(),
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
                    ->sortable(),
                TextColumn::make('total_withdraw')
                    ->label('Total Withdraw')
                    ->money()
                    ->sortable(),
                TextColumn::make('total_income')
                    ->label('Total Income')
                    ->money()
                    ->sortable(),
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
