<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransferResource\Pages;
use App\Models\User;
use Bavix\Wallet\Models\Transaction;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

class TransferResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $modelLabel = 'Transfer';

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
            ->defaultSort('id', 'desc')
            ->modifyQueryUsing(function (Builder $query) {
                return $query
                    ->where('payable_type', User::class)
                    ->where('payable_id', Filament::auth()->user()->id)
                    ->where('meta->action', 'transfer')
                    ->whereRelation('wallet', 'slug', config('wallet.wallet.default.slug'))
                    ->with('payable', 'wallet');
            })
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Transaction::TYPE_DEPOSIT => 'success',
                        Transaction::TYPE_WITHDRAW => 'danger',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Transaction::TYPE_DEPOSIT => 'Credit',
                        Transaction::TYPE_WITHDRAW => 'Debit',
                        default => 'Unknown',
                    }),
                Tables\Columns\TextColumn::make('payable.username')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amountFloat')
                    ->label('Amount')
                    ->formatStateUsing(function (Transaction $record): string {
                        return Number::currency(abs($record->amountFloat));
                    }),
                Tables\Columns\TextColumn::make('meta.message')
                    ->label('Message')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->date()
                    ->tooltip(function (Transaction $record): string {
                        return $record->created_at->format(
                            Table::$defaultTimeDisplayFormat,
                        );
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListTransfers::route('/'),
            // 'create' => Pages\CreateTransfer::route('/create'),
            // 'edit' => Pages\EditTransfer::route('/{record}/edit'),
        ];
    }
}
