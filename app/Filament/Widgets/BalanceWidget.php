<?php

namespace App\Filament\Widgets;

use App\Filament\Common\Resources\WithdrawResource;
use App\Filament\Pages\ProductPage;
use App\Filament\Resources\TransferResource;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Number;

class BalanceWidget extends BaseWidget
{
    // protected static ?string $pollingInterval = null;

    protected function getListeners(): array
    {
        return [
            'refresh-balance' => '$refresh',
        ];
    }

    protected function user(): User
    {
        return Filament::auth()->user();
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Deposit Balance', Number::currency($this->user()->balanceFloat))
                ->description(
                    new HtmlString('
                        <button wire:click="$dispatch(\'open-modal\', { id: \'add-fund-modal\' })" class="filament-button filament-button-size-sm inline-flex items-center justify-center py-1 gap-1 font-medium rounded-lg border transition-colors outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset min-h-[2rem] px-3 text-sm text-white shadow focus:ring-white border-transparent bg-primary-600 hover:bg-primary-500 focus:bg-primary-700 focus:ring-offset-primary-700">
                            Add Fund
                        </button>
                        <a href="'.TransferResource::getUrl().'" class="filament-button filament-button-size-sm inline-flex items-center justify-center py-1 gap-1 font-medium rounded-lg border transition-colors outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset min-h-[2rem] px-3 text-sm text-white shadow focus:ring-white border-transparent bg-primary-600 hover:bg-primary-500 focus:bg-primary-700 focus:ring-offset-primary-700">
                            Transfer
                        </a>
                    ')
                ),
            Stat::make('Earning Balance', Number::currency($this->user()->getOrCreateWallet('earning')->balanceFloat))
                ->description(
                    new HtmlString('
                        <button class="filament-button filament-button-size-sm inline-flex items-center justify-center py-1 gap-1 font-medium rounded-lg border transition-colors outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset min-h-[2rem] px-3 text-sm text-white shadow focus:ring-white border-transparent bg-primary-600 hover:bg-primary-500 focus:bg-primary-700 focus:ring-offset-primary-700">
                            Withdraws
                        </button>
                    ')
                )
                ->url(WithdrawResource::getUrl())
                ->descriptionIcon('heroicon-o-arrow-top-right-on-square'),
            Stat::make('Product Balance', Number::currency($this->user()->getOrCreateWallet('product')->balanceFloat))
                ->description(
                    new HtmlString('<button class="filament-button filament-button-size-sm inline-flex items-center justify-center py-1 gap-1 font-medium rounded-lg border transition-colors outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset min-h-[2rem] px-3 text-sm text-white shadow focus:ring-white border-transparent bg-primary-600 hover:bg-primary-500 focus:bg-primary-700 focus:ring-offset-primary-700">
                        History
                    </button>')
                )
                ->url(ProductPage::getUrl())
                ->descriptionIcon('heroicon-o-arrow-top-right-on-square'),
        ];
    }
}
