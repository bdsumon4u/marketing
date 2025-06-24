<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

class ProductPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.pages.product-page';

    protected static bool $shouldRegisterNavigation = false;

    public function table(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                $user = value(fn (): User => Filament::auth()->user());
                $wallet = $user->getOrCreateWallet('product');

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
            ->defaultSort('created_at', 'desc');
    }
}
