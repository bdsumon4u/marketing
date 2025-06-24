<?php

namespace App\Filament\Common\Resources;

use App\Filament\Common\Resources\WithdrawResource\Pages;
use App\Filament\Common\Resources\WithdrawResource\Pages\ListWithdraws;
use App\Models\User;
use Bavix\Wallet\Models\Transaction;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

class WithdrawResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $modelLabel = 'Withdraw';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('amount')
                    ->label('Amount')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->formatStateUsing(fn ($state) => abs($state))
                    ->prefix(Number::defaultCurrency()),
                TextInput::make('bkash_number')
                    ->label('bKash Number')
                    ->required()
                    // ->disallowDropdown()
                    // ->defaultCountry('BD')
                    // ->initialCountry('BD')
                    ->prefixIcon('heroicon-o-phone')
                    ->placeholder('01XXXXXXXXX')
                    ->numeric(),
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
                    ->when(Filament::getCurrentOrDefaultPanel()->getId() === 'app', function ($query) {
                        $query->where('payable_id', Filament::auth()->id());
                    })
                    ->where('meta->action', 'withdraw')
                    ->whereRelation('wallet', 'slug', 'earning')
                    ->with('wallet');
            })
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->date()
                    ->tooltip(function (Transaction $record): string {
                        return $record->created_at->format(
                            config('app.time_format'),
                        );
                    }),
                TextColumn::make('payable.username')
                    ->tooltip(fn (Transaction $record): string => $record->payable->name)
                    ->label('User')
                    ->searchable()
                    ->visible(fn () => Filament::getCurrentOrDefaultPanel()->getId() === 'admin'),
                TextColumn::make('amountFloat')
                    ->label('Amount')
                    ->formatStateUsing(function (Transaction $record): string {
                        return Number::currency(abs($record->amountFloat));
                    })
                    ->tooltip(fn (Transaction $record): string => $record->meta['transaction_id'] ?? ''),
                TextColumn::make('meta.message')
                    ->label('Message')
                    ->searchable(),
                IconColumn::make('confirmed')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('confirmed')
                    ->trueLabel('Confirmed')
                    ->falseLabel('Pending')
                    ->label('Status'),
            ])
            ->recordActions([
                Action::make('review')
                    ->slideOver()
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->visible(fn (Transaction $record): bool => Filament::getCurrentOrDefaultPanel()->getId() === 'admin' && ! $record->confirmed)
                    ->schema([
                        TextInput::make('amountFloat')
                            ->label('Amount')
                            ->formatStateUsing(fn (Transaction $record): string => Number::currency(abs($record->amountFloat)))
                            ->disabled(),
                        TextInput::make('bkash_number')
                            ->label('bKash Number')
                            ->formatStateUsing(fn (Transaction $record): string => $record->meta['bkash_number'] ?? '')
                            ->disabled(),
                        TextInput::make('transaction_id')
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
                ViewAction::make()
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->schema([
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
                                config('app.time_format'),
                            )),
                        TextEntry::make('meta.transaction_id')
                            ->label('Transaction ID'),
                        TextEntry::make('meta.bkash_number')
                            ->label('bKash Number'),
                    ]),
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
            'index' => ListWithdraws::route('/'),
            // 'create' => Pages\CreateWithdraw::route('/create'),
            // 'edit' => Pages\EditWithdraw::route('/{record}/edit'),
        ];
    }
}
