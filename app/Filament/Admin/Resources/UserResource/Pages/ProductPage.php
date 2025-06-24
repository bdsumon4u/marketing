<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

class ProductPage extends Page implements HasForms, HasTable
{
    use InteractsWithRecord;
    use InteractsWithForms;
    use InteractsWithTable;

    protected static bool $shouldRegisterNavigation = false;

    protected static string $resource = UserResource::class;

    public ?array $data = [];

    protected string $view = 'filament.admin.resources.user-resource.pages.product-page';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('amount')
                    ->label('Amount')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->prefix(Number::defaultCurrency()),
                TextInput::make('message')
                    ->required(),
                TextInput::make('reference')
                    ->required(),
            ])
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                $wallet = $this->record->getOrCreateWallet('product');

                return $wallet->walletTransactions()->getQuery()->with('wallet');
            })
            ->columns([
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'deposit' => 'success',
                        'withdraw' => 'danger',
                    })
                    ->sortable(),
                TextColumn::make('amountFloat')
                    ->label('Amount')
                    ->money()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => Number::currency(abs($state))),
                TextColumn::make('meta.message')
                    ->label('Message')
                    ->searchable(),
                TextColumn::make('meta.reference')
                    ->label('Reference')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->date()
                    ->tooltip(fn ($state) => $state->format(
                        config('app.time_format'),
                    ))
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Action::make('create')
                    ->label('Add Transaction')
                    ->icon('heroicon-m-plus')
                    ->slideOver()
                    ->modalWidth('sm')
                    ->schema([
                        TextInput::make('amount')
                            ->label('Amount')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->prefix(Number::defaultCurrency()),
                        TextInput::make('message')
                            ->required(),
                        TextInput::make('reference')
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        $wallet = $this->record->getOrCreateWallet('product');

                        if ($wallet->balanceFloat < $data['amount']) {
                            Notification::make()
                                ->title('Insufficient balance')
                                ->danger()
                                ->send();

                            return;
                        }

                        $wallet->withdrawFloat($data['amount'], [
                            'message' => $data['message'],
                            'reference' => $data['reference'],
                        ]);

                        Notification::make()
                            ->title('Transaction added successfully')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
