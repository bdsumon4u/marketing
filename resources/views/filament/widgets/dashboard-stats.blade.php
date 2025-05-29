<x-filament::widget>
    <div
        @if ($pollingInterval = $this->getPollingInterval())
            wire:poll.{{ $pollingInterval }}
        @endif
    >
        @if(!auth()->user()->is_active)
            <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
                <svg class="flex-shrink-0 inline w-4 h-4 mr-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                    viewBox="0 0 20 20">
                    <path
                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                </svg>
                <span class="font-medium">This account is not verified.</span>
                @if(auth()->user()->balanceFloat < config('mlm.registration_fee'))
                    <button
                        wire:click="$dispatch('open-modal', { id: 'add-fund-modal' })"
                        class="ml-1 font-medium underline hover:no-underline"
                    >
                        DEPOSIT NOW
                    </button>
                    <span class="ml-1">
                        at least
                        <strong>{{ config('mlm.registration_fee') - auth()->user()->balanceFloat }} BDT</strong>
                        to verify your account
                    </span>
                @else
                <button wire:click="verifyAccount" class="ml-1 font-medium underline hover:no-underline">VERIFY NOW</button>
                @endif
            </div>
        @endif

        @php
            $colorMap = [
                'primary' => ['bg' => 'bg-blue-50 dark:bg-blue-900', 'icon' => 'text-blue-500 dark:text-blue-300', 'amount' => 'text-blue-600 dark:text-blue-200'],
                'success' => ['bg' => 'bg-green-50 dark:bg-green-900', 'icon' => 'text-green-500 dark:text-green-300', 'amount' => 'text-green-600 dark:text-green-200'],
                'info'    => ['bg' => 'bg-cyan-50 dark:bg-cyan-900', 'icon' => 'text-cyan-500 dark:text-cyan-300', 'amount' => 'text-cyan-600 dark:text-cyan-200'],
                'warning' => ['bg' => 'bg-yellow-50 dark:bg-yellow-900', 'icon' => 'text-yellow-500 dark:text-yellow-300', 'amount' => 'text-yellow-600 dark:text-yellow-200'],
                'danger'  => ['bg' => 'bg-red-50 dark:bg-red-900', 'icon' => 'text-red-500 dark:text-red-300', 'amount' => 'text-red-600 dark:text-red-200'],
            ];
            $rows = [
                [
                    // Row 1: 3 columns
                    ['icon' => 'heroicon-o-banknotes', 'label' => 'Total Deposit', 'value' => auth()->user()->total_deposit ?? 0, 'color' => 'primary'],
                    ['icon' => 'heroicon-o-chart-bar', 'label' => 'Total Income', 'value' => auth()->user()->total_income ?? 0, 'color' => 'success'],
                    ['icon' => 'heroicon-o-currency-dollar', 'label' => 'Total Withdraw', 'value' => auth()->user()->total_withdraw ?? 0, 'color' => 'danger'],
                ],
                [
                    // Row 2: 4 columns
                    ['icon' => 'heroicon-o-user-group', 'label' => 'Referral Income', 'value' => auth()->user()->referral_income ?? 0, 'color' => 'info'],
                    ['icon' => 'heroicon-o-user-group', 'label' => 'Generation Income', 'value' => auth()->user()->generation_income ?? 0, 'color' => 'info'],
                    ['icon' => 'heroicon-o-chart-bar', 'label' => 'Rank Income', 'value' => auth()->user()->rank_income ?? 0, 'color' => 'success'],
                    ['icon' => 'heroicon-o-star', 'label' => 'Magic Income', 'value' => auth()->user()->magic_income ?? 0, 'color' => 'primary'],
                ],
                [
                    // Row 3: 4 columns
                    ['icon' => 'heroicon-o-clock', 'label' => 'Pending Deposit', 'value' => auth()->user()->pending_deposit ?? 0, 'color' => 'warning'],
                    ['icon' => 'heroicon-o-x-circle', 'label' => 'Rejected Deposit', 'value' => auth()->user()->rejected_deposit ?? 0, 'color' => 'danger'],
                    ['icon' => 'heroicon-o-clock', 'label' => 'Pending Withdraw', 'value' => auth()->user()->pending_withdraw ?? 0, 'color' => 'warning'],
                    ['icon' => 'heroicon-o-x-circle', 'label' => 'Rejected Withdraw', 'value' => auth()->user()->rejected_withdraw ?? 0, 'color' => 'danger'],
                ],
                [
                    // Row 4: 2 columns
                    ['icon' => 'heroicon-o-arrow-up-tray', 'label' => 'Total Send', 'value' => auth()->user()->total_send ?? 0, 'color' => 'info'],
                    ['icon' => 'heroicon-o-arrow-down-tray', 'label' => 'Total Receive', 'value' => auth()->user()->total_receive ?? 0, 'color' => 'info'],
                ],
            ];
        @endphp

        @foreach ($rows as $i => $row)
            <div class="grid gap-4 mb-4
                @if(count($row) === 3) grid-cols-1 md:grid-cols-3 @endif
                @if(count($row) === 4) grid-cols-1 md:grid-cols-4 @endif
                @if(count($row) === 2) grid-cols-1 md:grid-cols-2 @endif
            ">
                @foreach ($row as $stat)
                    <div @class([
                        'rounded-xl shadow p-4 flex items-center min-h-[90px]',
                        $colorMap[$stat['color']]['bg'],
                    ])>
                        <x-dynamic-component :component="$stat['icon']"
                            @class([
                                'w-12 h-12 flex-shrink-0',
                                $colorMap[$stat['color']]['icon'],
                            ]) />
                        <div class="flex flex-col items-end justify-between flex-1 h-full ml-4">
                            <div class="text-base font-bold text-right">{{ $stat['label'] }}</div>
                            <div @class([
                                'text-2xl font-extrabold text-right',
                                $colorMap[$stat['color']]['amount'],
                            ])>
                                {{ is_numeric($stat['value']) ? number_format($stat['value'], 2) : $stat['value'] }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</x-filament::widget>
