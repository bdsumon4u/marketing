<x-filament::widget>
    @php
        $user = $this->getUser();
        $stats = [
            ['icon' => 'heroicon-o-banknotes', 'label' => 'Main Balance', 'value' => $user->main_balance ?? 0, 'color' => 'primary'],
            ['icon' => 'heroicon-o-hand-raised', 'label' => 'Fund', 'value' => $user->fund ?? 0, 'color' => 'success'],
            ['icon' => 'heroicon-o-chart-bar', 'label' => 'Total Income', 'value' => $user->total_income ?? 0, 'color' => 'info'],
            ['icon' => 'heroicon-o-user-group', 'label' => 'Refer Income', 'value' => $user->refer_income ?? 0, 'color' => 'info'],
            ['icon' => 'heroicon-o-user-group', 'label' => 'Generation Income', 'value' => $user->generation_income ?? 0, 'color' => 'info'],
            ['icon' => 'heroicon-o-users', 'label' => 'Team Income', 'value' => $user->team_income ?? 0, 'color' => 'info'],
            ['icon' => 'heroicon-o-building-office', 'label' => 'Club Income', 'value' => $user->club_income ?? 0, 'color' => 'info'],
            ['icon' => 'heroicon-o-globe-alt', 'label' => 'Global Income', 'value' => $user->global_income ?? 0, 'color' => 'info'],
            ['icon' => 'heroicon-o-chart-bar', 'label' => 'Rank Incentive', 'value' => $user->rank_incentive ?? 0, 'color' => 'info'],
            ['icon' => 'heroicon-o-star', 'label' => 'Magic Income', 'value' => $user->magic_income ?? 0, 'color' => 'info'],
            ['icon' => 'heroicon-o-user-circle', 'label' => 'Country Director', 'value' => $user->country_director_income ?? 0, 'color' => 'info'],
            ['icon' => 'heroicon-o-arrow-path', 'label' => 'Transfer Amount', 'value' => $user->transfer_amount ?? 0, 'color' => 'info'],
            ['icon' => 'heroicon-o-arrow-down-tray', 'label' => 'Receive Amount', 'value' => $user->receive_amount ?? 0, 'color' => 'info'],
            ['icon' => 'heroicon-o-currency-dollar', 'label' => 'Paid Withdraw', 'value' => $user->paid_withdraw ?? 0, 'color' => 'success'],
            ['icon' => 'heroicon-o-clock', 'label' => 'Pending Withdraw', 'value' => $user->pending_withdraw ?? 0, 'color' => 'warning'],
            ['icon' => 'heroicon-o-x-circle', 'label' => 'Rejected Withdraw', 'value' => $user->rejected_withdraw ?? 0, 'color' => 'danger'],
            ['icon' => 'heroicon-o-trophy', 'label' => 'Millionaire', 'value' => $user->millionaire ?? 0, 'color' => 'primary'],
        ];
        $colorMap = [
            'primary' => ['bg' => 'bg-blue-50 dark:bg-blue-900', 'icon' => 'text-blue-500 dark:text-blue-300', 'amount' => 'text-blue-600 dark:text-blue-200'],
            'success' => ['bg' => 'bg-green-50 dark:bg-green-900', 'icon' => 'text-green-500 dark:text-green-300', 'amount' => 'text-green-600 dark:text-green-200'],
            'info'    => ['bg' => 'bg-cyan-50 dark:bg-cyan-900', 'icon' => 'text-cyan-500 dark:text-cyan-300', 'amount' => 'text-cyan-600 dark:text-cyan-200'],
            'warning' => ['bg' => 'bg-yellow-50 dark:bg-yellow-900', 'icon' => 'text-yellow-500 dark:text-yellow-300', 'amount' => 'text-yellow-600 dark:text-yellow-200'],
            'danger'  => ['bg' => 'bg-red-50 dark:bg-red-900', 'icon' => 'text-red-500 dark:text-red-300', 'amount' => 'text-red-600 dark:text-red-200'],
        ];
    @endphp
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
        @foreach ($stats as $stat)
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

    <div class="flex flex-col items-center justify-between p-4 mt-8 bg-blue-100 dark:bg-blue-900 rounded-xl md:flex-row">
        <div class="text-lg font-semibold">Refer Link :</div>
        <div class="flex items-center w-full mt-2 md:mt-0 md:w-auto">
            <input type="text" readonly value="{{ $this->getReferralLink() }}"
                class="w-full px-2 py-1 mr-2 bg-white border border-gray-300 rounded dark:bg-gray-800 dark:border-gray-700 md:w-80" id="referral-link">
            <button onclick="navigator.clipboard.writeText(document.getElementById('referral-link').value)"
                class="flex items-center px-3 py-1 text-white bg-blue-500 rounded hover:bg-blue-600">
                <x-heroicon-o-clipboard-document class="w-5 h-5 mr-1" />
                Copy
            </button>
        </div>
    </div>
</x-filament::widget>
