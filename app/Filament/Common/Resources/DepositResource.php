<?php

namespace App\Filament\Common\Resources;

use App\Filament\Common\Resources\DepositResource\Pages;
use App\Filament\Common\Resources\DepositResource\Pages\ListDeposits;
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

class DepositResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $modelLabel = 'Deposit';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                    ->when(Filament::getCurrentOrDefaultPanel()->getId() === 'app', function ($query) {
                        $query->where('payable_id', Filament::auth()->id());
                    })
                    ->where('meta->action', 'deposit')
                    ->whereRelation('wallet', 'slug', 'default')
                    ->with(['payable', 'wallet']);
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
                    ->sortable()
                    ->visible(fn () => Filament::getCurrentOrDefaultPanel()->getId() === 'admin'),
                TextColumn::make('wallet.name')
                    ->label('Wallet')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amountFloat')
                    ->label('Amount')
                    ->money()
                    ->tooltip(fn (Transaction $record): string => $record->meta['transaction_id'] ?? ''),
                IconColumn::make('confirmed')
                    ->boolean()
                    ->alignCenter()
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
                        TextInput::make('amount')
                            ->label('Amount')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->formatStateUsing(fn (Transaction $record): string => $record->amountFloat)
                            ->prefix(Number::defaultCurrency())
                            ->disabled(),
                        TextInput::make('reference')
                            ->label('Reference')
                            ->formatStateUsing(fn (Transaction $record): string => $record->meta['reference'] ?? '')
                            ->disabled(),
                        TextInput::make('transaction_id')
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
            'index' => ListDeposits::route('/'),
            // 'create' => Pages\CreateDeposit::route('/create'),
            // 'edit' => Pages\EditDeposit::route('/{record}/edit'),
        ];
    }
}
