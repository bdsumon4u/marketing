<?php

namespace App\Filament\Admin\Resources;

use App\Enums\UserRank;
use App\Filament\Admin\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use STS\FilamentImpersonate\Tables\Actions\Impersonate;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->tooltip(fn (User $record): string => $record->username),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean(),
                Tables\Columns\IconColumn::make('with_product')
                    ->label('Product')
                    ->boolean(),
                Tables\Columns\TextColumn::make('rank')
                    ->label('Rank')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('total_deposit')
                    ->label('Total Deposit')
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_withdraw')
                    ->label('Total Withdraw')
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_income')
                    ->label('Total Income')
                    ->money()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('rank')
                    ->options(UserRank::class)
                    ->label('Rank'),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->trueLabel('Active')
                    ->falseLabel('Inactive')
                    ->label('Status'),
                Tables\Filters\TernaryFilter::make('with_product')
                    ->trueLabel('With Product')
                    ->falseLabel('Without Product')
                    ->label('Product'),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Impersonate::make()
                    ->guard('web')
                    ->redirectTo(Filament::getDefaultPanel()->getUrl()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
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
            'index' => Pages\ListUsers::route('/'),
            // 'create' => Pages\CreateUser::route('/create'),
            // 'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
