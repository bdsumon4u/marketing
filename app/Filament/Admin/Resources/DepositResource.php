<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DepositResource\Pages;
use App\Models\User;
use Bavix\Wallet\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
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
                    ->where('meta->action', 'deposit')
                    ->whereRelation('wallet', 'slug', 'default')
                    ->with(['payable', 'wallet']);
            })
            ->columns([
                Tables\Columns\TextColumn::make('payable.name'),
                Tables\Columns\TextColumn::make('wallet.name'),
                Tables\Columns\TextColumn::make('amountFloat')
                    ->label('Amount')
                    ->money(),
                Tables\Columns\IconColumn::make('confirmed')
                    ->boolean()
                    ->label('Confirmed')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('created_at')
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
                Tables\Actions\Action::make('review')
                    ->slideOver()
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->visible(fn (Transaction $record): bool => ! $record->confirmed)
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
            // 'create' => Pages\CreateDeposit::route('/create'),
            // 'edit' => Pages\EditDeposit::route('/{record}/edit'),
        ];
    }
}
