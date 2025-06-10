<?php

namespace App\Filament\Common\Resources;

use App\Filament\Common\Resources\DepositResource\Pages;
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

class DepositResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $modelLabel = 'Deposit';

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

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
                    ->when(Filament::getCurrentPanel()->getId() === 'app', function ($query) {
                        $query->where('payable_id', Filament::auth()->id());
                    })
                    ->where('meta->action', 'deposit')
                    ->whereRelation('wallet', 'slug', 'default')
                    ->with(['payable', 'wallet']);
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
                Tables\Columns\TextColumn::make('wallet.name')
                    ->label('Wallet')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amountFloat')
                    ->label('Amount')
                    ->money()
                    ->tooltip(fn (Transaction $record): string => $record->meta['transaction_id'] ?? ''),
                Tables\Columns\IconColumn::make('confirmed')
                    ->boolean()
                    ->alignCenter()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('confirmed')
                    ->trueLabel('Confirmed')
                    ->falseLabel('Pending')
                    ->label('Status'),
            ])
            ->actions([
                Tables\Actions\Action::make('review')
                    ->slideOver()
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->visible(fn (Transaction $record): bool => Filament::getCurrentPanel()->getId() === 'admin' && ! $record->confirmed)
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->label('Amount')
                            ->formatStateUsing(fn (Transaction $record): string => $record->amountFloat)
                            ->disabled(),
                        Forms\Components\TextInput::make('reference')
                            ->label('Reference')
                            ->formatStateUsing(fn (Transaction $record): string => $record->meta['reference'] ?? '')
                            ->disabled(),
                        Forms\Components\TextInput::make('transaction_id')
                            ->label('Transaction ID')
                            ->formatStateUsing(fn (Transaction $record): string => $record->meta['transaction_id'] ?? '')
                            ->disabled(),
                    ])
                    ->extraModalFooterActions([
                        Action::make('reject')
                            ->label('Reject')
                            ->color('danger')
                            ->action(function (Transaction $record): void {
                                $user = value(fn (): User => $record->payable);
                                $user->decrement('pending_deposit', $record->amount);
                                $user->increment('rejected_deposit', $record->amount);

                                Notification::make()
                                    ->title('Deposit rejected')
                                    ->body('The deposit has been rejected.')
                                    ->warning()
                                    ->send();

                                // send notification to user
                                Notification::make()
                                    ->title('Deposit rejected')
                                    ->body('The deposit #'.($record->meta['transaction_id'] ?? '').' of '.Number::currency($record->amountFloat).' has been rejected.')
                                    ->danger()
                                    ->sendToDatabase($user);

                                $record->delete();
                            })
                            ->modalWidth('md')
                            ->modalHeading('Reject Deposit')
                            ->modalDescription('Are you sure you want to reject this deposit?')
                            ->modalSubmitActionLabel('Yes, reject')
                            ->cancelParentActions(),
                    ])
                    ->action(function (Transaction $record): void {
                        $user = value(fn (): User => $record->payable);
                        $user->confirm($record);
                        $user->decrement('pending_deposit', $record->amount);
                        $user->increment('total_deposit', $record->amount);

                        Notification::make()
                            ->title('Deposit confirmed')
                            ->body('The deposit has been confirmed.')
                            ->success()
                            ->send();

                        // send notification to user
                        Notification::make()
                            ->title('Deposit confirmed')
                            ->body('The deposit of '.Number::currency($record->amountFloat).' has been confirmed.')
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
            'index' => Pages\ListDeposits::route('/'),
            // 'create' => Pages\CreateDeposit::route('/create'),
            // 'edit' => Pages\EditDeposit::route('/{record}/edit'),
        ];
    }
}
