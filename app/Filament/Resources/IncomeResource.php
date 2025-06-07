<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IncomeResource\Pages;
use App\Models\User;
use Bavix\Wallet\Models\Transaction;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

class IncomeResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $modelLabel = 'Income';

    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-up';

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
                    ->where('meta->action', 'income')
                    ->whereRelation('wallet', 'slug', 'earning')
                    ->with(['payable', 'wallet']);
            })
            ->columns([
                Tables\Columns\TextColumn::make('meta.message')
                    ->label('Message')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amountFloat')
                    ->label('Amount')
                    ->formatStateUsing(function (Transaction $record): string {
                        return Number::currency(abs($record->amountFloat));
                    }),
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
            'index' => Pages\ListIncomes::route('/'),
            // 'create' => Pages\CreateIncome::route('/create'),
            // 'edit' => Pages\EditIncome::route('/{record}/edit'),
        ];
    }
}
