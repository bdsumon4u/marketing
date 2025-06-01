<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransferResource\Pages;
use App\Filament\Resources\TransferResource\RelationManagers;
use App\Models\User;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Number;

class TransferResource extends Resource
{
    protected static ?string $model = Transfer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('transfer_to')
                    ->validationAttribute('username')
                    ->label('Transfer To (username)')
                    ->exists('users', 'username')
                    ->default('alexhari')
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->prefix(Number::defaultCurrency())
                    ->default(45)
                    ->required(),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(function (Builder $query) {
                return $query
                    ->whereHas('from.holder', function (Builder $query) {
                        $query->where('holder_type', User::class);
                    })
                    ->whereHas('to.holder', function (Builder $query) {
                        $query->where('holder_type', User::class);
                    })
                    ->with(['from.holder', 'to.holder', 'deposit.wallet', 'withdraw.wallet']);
            })
            ->columns([
                Tables\Columns\TextColumn::make('from.holder.name')
                    ->description(fn (Model $record) => $record->from->name)
                    ->label('From')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('to.holder.name')
                    ->description(fn (Model $record) => $record->to->name)
                    ->label('To')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deposit.amountFloat')
                    ->label('Amount')
                    ->searchable()
                    ->sortable(),
                // message
                Tables\Columns\TextColumn::make('withdraw.meta.message')
                    ->label('Message')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->date()
                    ->description(fn (Model $record) => $record->created_at->format(
                        Table::$defaultTimeDisplayFormat,
                    ))
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListTransfers::route('/'),
            // 'create' => Pages\CreateTransfer::route('/create'),
            // 'edit' => Pages\EditTransfer::route('/{record}/edit'),
        ];
    }
}
