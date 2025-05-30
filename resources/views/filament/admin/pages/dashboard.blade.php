<x-filament-panels::page class="fi-dashboard-page">
    @if (method_exists($this, 'filtersForm'))
    {{ $this->filtersForm }}
    @endif

    <x-filament-widgets::widgets :columns="$this->getColumns()" :data="
            [
                ...(property_exists($this, 'filters') ? ['filters' => $this->filters] : []),
                ...$this->getWidgetData(),
            ]
        " :widgets="$this->getVisibleWidgets()" />

    @php
    $colorMap = [
        'primary' => ['bg' => 'bg-blue-50 dark:bg-blue-900', 'icon' => 'text-blue-500 dark:text-blue-300', 'amount' => 'text-blue-600 dark:text-blue-200'],
        'success' => ['bg' => 'bg-green-50 dark:bg-green-900', 'icon' => 'text-green-500 dark:text-green-300', 'amount' => 'text-green-600 dark:text-green-200'],
        'info' => ['bg' => 'bg-cyan-50 dark:bg-cyan-900', 'icon' => 'text-cyan-500 dark:text-cyan-300', 'amount' => 'text-cyan-600 dark:text-cyan-200'],
        'warning' => ['bg' => 'bg-yellow-50 dark:bg-yellow-900', 'icon' => 'text-yellow-500 dark:text-yellow-300', 'amount' => 'text-yellow-600 dark:text-yellow-200'],
        'danger' => ['bg' => 'bg-red-50 dark:bg-red-900', 'icon' => 'text-red-500 dark:text-red-300', 'amount' => 'text-red-600 dark:text-red-200'],
    ];
    $rows = [
        [
            // Row 1: 3 columns
            [
                'icon' => 'heroicon-o-banknotes',
                'label' => 'Total Deposit',
                'value' => $this->totalDeposit,
                'color' => 'primary'
            ],
            [
                'icon' => 'heroicon-o-currency-dollar',
                'label' => 'Total Withdraw',
                'value' => $this->totalWithdraw,
                'color' => 'danger'
            ],
            [
                'icon' => 'heroicon-o-chart-bar',
                'label' => 'Company Balance',
                'value' => $this->companyBalance,
                'color' => 'success'
            ],
        ],
        [
            // Row 2: 4 columns
            ...array_filter(array_map(function ($case) {
                if ($case->value === 'company') {
                    return null;
                }
                return [
                    'icon' => 'heroicon-o-chart-bar',
                    'label' => $case->name(),
                    'value' => \App\Models\Wallet::company()->getWallet($case->value)->balanceFloat,
                    'color' => $case->getColor()
                ];
            }, App\Enums\CompanyWalletType::cases())),
        ],
        [
            // Row 3: 4 columns
            [
                'icon' => 'heroicon-o-clock',
                'label' => 'Pending Deposit',
                'value' => $this->pendingDeposit,
                'color' => 'warning'
            ],
            [
                'icon' => 'heroicon-o-x-circle',
                'label' => 'Rejected Deposit',
                'value' => $this->rejectedDeposit,
                'color' => 'danger'
            ],
            [
                'icon' => 'heroicon-o-clock',
                'label' => 'Pending Withdraw',
                'value' => $this->pendingWithdraw,
                'color' => 'warning'
            ],
            [
                'icon' => 'heroicon-o-x-circle',
                'label' => 'Rejected Withdraw',
                'value' => $this->rejectedWithdraw,
                'color' => 'danger'
            ],
        ],
    ];
    @endphp

    @foreach ($rows as $i => $row)
    <div class="grid gap-4 mb-4
        @if(count($row) === 2) grid-cols-1 md:grid-cols-2 @endif
        @if(count($row) === 3) grid-cols-1 md:grid-cols-3 @endif
        @if(count($row) === 4) grid-cols-1 md:grid-cols-4 @endif
        @if(count($row) === 5) grid-cols-1 md:grid-cols-5 @endif
        @if(count($row) === 6) grid-cols-1 md:grid-cols-6 @endif
    ">
        @foreach ($row as $stat)
        <div @class([ 'rounded-xl shadow p-4 flex items-center min-h-[90px]' , $colorMap[$stat['color']]['bg'], ])>
            <x-dynamic-component :component="$stat['icon']" @class([ 'w-12 h-12 flex-shrink-0' ,
                $colorMap[$stat['color']]['icon'], ]) />
            <div class="flex flex-col items-end justify-between flex-1 h-full ml-4">
                <div class="text-base font-bold text-right">{{ $stat['label'] }}</div>
                <div @class([ 'text-lg font-extrabold text-right' , $colorMap[$stat['color']]['amount'], ])>
                    {{ is_numeric($stat['value']) ? number_format($stat['value'], 2) : $stat['value'] }}
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endforeach
</x-filament-panels::page>