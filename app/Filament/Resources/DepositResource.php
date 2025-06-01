<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepositResource\Pages;
use App\Models\User;
use Bavix\Wallet\Models\Transaction;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DepositResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $modelLabel = 'Deposit';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
            ->defaultSort('id', 'desc')
            ->modifyQueryUsing(function (Builder $query) {
                return $query
                    ->where('type', Transaction::TYPE_DEPOSIT)
                    ->where('payable_type', User::class)
                    ->where('payable_id', Filament::auth()->user()->id)
                    ->where('meta->action', 'deposit')
                    ->whereRelation('wallet', 'slug', 'default')
                    ->with('wallet');
            })
            ->columns([
                Tables\Columns\TextColumn::make('payable.username')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amountFloat')
                    ->label('Amount')
                    ->money(),
                Tables\Columns\TextColumn::make('wallet.name')
                    ->label('Wallet')
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
                Tables\Columns\IconColumn::make('confirmed')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListDeposits::route('/'),
            'create' => Pages\CreateDeposit::route('/create'),
            'edit' => Pages\EditDeposit::route('/{record}/edit'),
        ];
    }
}
