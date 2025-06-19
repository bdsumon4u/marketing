<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransferResource\Pages;
use App\Filament\Resources\TransferResource\Pages\ListTransfers;
use App\Models\User;
use Bavix\Wallet\Models\Transaction;
use Filament\Actions\BulkActionGroup;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

class TransferResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $modelLabel = 'Transfer';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrows-right-left';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('transfer_to')
                    ->validationAttribute('username')
                    ->label('Transfer To (username)')
                    ->exists('users', 'username')
                    ->required(),
                TextInput::make('amount')
                    ->label('Amount')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->prefix(Number::defaultCurrency()),
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
                TextColumn::make('type')
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
                TextColumn::make('payable.username')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amountFloat')
                    ->label('Amount')
                    ->formatStateUsing(function (Transaction $record): string {
                        return Number::currency(abs($record->amountFloat));
                    }),
                TextColumn::make('meta.message')
                    ->label('Message')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->date()
                    ->tooltip(function (Transaction $record): string {
                        return $record->created_at->format(
                            config('app.time_format'),
                        );
                    }),
            ])
            ->filters([
                //
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
            'index' => ListTransfers::route('/'),
            // 'create' => Pages\CreateTransfer::route('/create'),
            // 'edit' => Pages\EditTransfer::route('/{record}/edit'),
        ];
    }
}
