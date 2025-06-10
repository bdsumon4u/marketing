<?php

namespace App\Filament\Pages;

use App\Models\User;
use Bavix\Wallet\Models\Transaction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ProductPage extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.product-page';

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('amount')
                    ->numeric()
                    ->required()
                    ->minValue(1),
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
                $user = value(fn (): User => Filament::auth()->user());
                $wallet = $user->getOrCreateWallet('product');

                return $wallet->walletTransactions()->getQuery();
            })
            ->columns([
                TextColumn::make('id'),
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
                    ->sortable(),
                TextColumn::make('meta.message')
                    ->label('Message')
                    ->searchable(),
                TextColumn::make('meta.reference')
                    ->label('Reference')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->date()
                    ->tooltip(fn ($state) => $state->format(
                        Table::$defaultTimeDisplayFormat,
                    ))
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public function create(): void
    {
        $data = $this->form->getState();
        $user = value(fn (): User => Filament::auth()->user());
        $wallet = $user->getOrCreateWallet('product');

        if ($wallet->balanceFloat < $data['amount']) {
            Notification::make()
                ->title('Insufficient balance')
                ->danger()
                ->send();

            return;
        }

        $wallet->withdrawFloat($data['amount'], [
            'description' => $data['description'],
            'reference' => $data['reference'],
        ]);

        Notification::make()
            ->title('Transaction added successfully')
            ->success()
            ->send();

        $this->form->fill();
    }
}
