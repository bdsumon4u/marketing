<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WithdrawResource\Pages;
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
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class WithdrawResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $modelLabel = 'Withdraw';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('amount')
                    ->label('Amount')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->formatStateUsing(fn ($state) => abs($state))
                    ->prefix(Number::defaultCurrency()),
                PhoneInput::make('bkash_number')
                    ->label('bKash Number')
                    ->required()
                    ->disallowDropdown()
                    ->defaultCountry('BD')
                    ->initialCountry('BD'),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->modifyQueryUsing(function (Builder $query) {
                return $query
                    ->where('type', Transaction::TYPE_WITHDRAW)
                    ->where('payable_type', User::class)
                    ->where('payable_id', Filament::auth()->user()->id)
                    ->where('meta->action', 'withdraw')
                    ->whereRelation('wallet', 'slug', 'earning')
                    ->with('wallet');
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

                Tables\Columns\IconColumn::make('confirmed')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn (Transaction $record) => $record->confirmed)
                    ->action(function (Transaction $record) {
                        $user = value(fn (): User => Filament::auth()->user());
                        $user->decrement('pending_withdraw', $record->amountFloat);
                        $record->delete();

                        return $record;
                    }),
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
            'index' => Pages\ListWithdraws::route('/'),
            // 'create' => Pages\CreateWithdraw::route('/create'),
            // 'edit' => Pages\EditWithdraw::route('/{record}/edit'),
        ];
    }
}
