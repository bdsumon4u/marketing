<?php

namespace App\Filament\Common\Resources;

use App\Filament\Common\Resources\WithdrawResource\Pages;
use App\Models\User;
use Bavix\Wallet\Models\Transaction;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
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
                    ->where('meta->action', 'withdraw')
                    ->whereRelation('wallet', 'slug', 'earning')
                    ->with('wallet');
            })
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->date()
                    ->tooltip(function (Transaction $record): string {
                        return $record->created_at->format(
                            Table::$defaultTimeDisplayFormat,
                        );
                    }),
                Tables\Columns\TextColumn::make('payable.username')
                    ->tooltip(fn (Transaction $record): string => $record->payable->name)
                    ->label('User')
                    ->searchable()
                    ->sortable()
                    ->visible(fn () => Filament::getCurrentPanel()->getId() === 'admin'),
                Tables\Columns\TextColumn::make('amountFloat')
                    ->label('Amount')
                    ->formatStateUsing(function (Transaction $record): string {
                        return Number::currency(abs($record->amountFloat));
                    })
                    ->tooltip(fn (Transaction $record): string => $record->meta['transaction_id'] ?? ''),
                Tables\Columns\TextColumn::make('meta.message')
                    ->label('Message')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('confirmed')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('review')
                    ->slideOver()
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->visible(fn (Transaction $record): bool => Filament::getCurrentPanel()->getId() === 'admin' && ! $record->confirmed)
                    ->form([
                        Forms\Components\TextInput::make('amountFloat')
                            ->label('Amount')
                            ->formatStateUsing(fn (Transaction $record): string => Number::currency(abs($record->amountFloat)))
                            ->disabled(),
                        Forms\Components\TextInput::make('bkash_number')
                            ->label('bKash Number')
                            ->formatStateUsing(fn (Transaction $record): string => $record->meta['bkash_number'] ?? '')
                            ->disabled(),
                        Forms\Components\TextInput::make('transaction_id')
                            ->label('Transaction ID')
                            ->formatStateUsing(fn (Transaction $record): string => $record->meta['transaction_id'] ?? '')
                            ->required(),
                    ])
                    ->extraModalFooterActions([
                        Action::make('reject')
                            ->label('Reject')
                            ->color('danger')
                            ->action(function (Transaction $record, array $data) {
                                $user = value(fn (): User => $record->payable);
                                $user->decrement('pending_withdraw', abs($record->amount));
                                $user->increment('rejected_withdraw', abs($record->amount));

                                Notification::make()
                                    ->title('Withdraw rejected')
                                    ->body('The withdraw has been rejected.')
                                    ->warning()
                                    ->send();

                                // send notification to user
                                Notification::make()
                                    ->title('Withdraw rejected')
                                    ->body('The withdraw of '.Number::currency($record->amountFloat).' has been rejected.')
                                    ->danger()
                                    ->sendToDatabase($user);

                                $record->delete();
                            })
                            ->modalWidth('md')
                            ->modalHeading('Reject Withdraw')
                            ->modalDescription('Are you sure you want to reject this withdraw?')
                            ->modalSubmitActionLabel('Yes, reject')
                            ->cancelParentActions(),
                    ])
                    ->action(function (Transaction $record, array $data) {
                        $user = value(fn (): User => $record->payable);
                        $wallet = $user->getOrCreateWallet('earning');
                        $wallet->confirm($record);
                        $meta = $record->meta;
                        $meta['transaction_id'] = $data['transaction_id'];
                        $record->meta = $meta;
                        $record->save();
                        $user->decrement('pending_withdraw', abs($record->amount));
                        $user->increment('total_withdraw', abs($record->amount));

                        Notification::make()
                            ->title('Withdraw confirmed')
                            ->body('The withdraw has been confirmed.')
                            ->success()
                            ->send();

                        // send notification to user
                        Notification::make()
                            ->title('Withdraw confirmed')
                            ->body('The withdraw of '.Number::currency($record->amountFloat).' has been confirmed.')
                            ->success()
                            ->sendToDatabase($user);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Confirm Transaction')
                    ->modalDescription('Are you sure you want to confirm this transaction?')
                    ->modalSubmitActionLabel('Yes, confirm'),
                Tables\Actions\ViewAction::make()
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->infolist([
                        TextEntry::make('payable.name')
                            ->label('User')
                            ->helperText(fn (Transaction $record): string => $record->payable->username),
                        TextEntry::make('amountFloat')
                            ->label('Amount')
                            ->formatStateUsing(fn (Transaction $record): string => Number::currency(abs($record->amountFloat))),
                        TextEntry::make('created_at')
                            ->label('Date')
                            ->date()
                            ->helperText(fn (Transaction $record): string => $record->created_at->format(
                                Table::$defaultTimeDisplayFormat,
                            )),
                        TextEntry::make('meta.transaction_id')
                            ->label('Transaction ID'),
                        TextEntry::make('meta.bkash_number')
                            ->label('bKash Number'),
                    ]),
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
            'index' => Pages\ListWithdraws::route('/'),
            // 'create' => Pages\CreateWithdraw::route('/create'),
            // 'edit' => Pages\EditWithdraw::route('/{record}/edit'),
        ];
    }
}
