<x-filament-widgets::widget>
    <div class="flex flex-col items-center justify-between p-4 mt-8 bg-blue-100 dark:bg-blue-900 rounded-xl md:flex-row">
        <div class="text-lg font-semibold">Refer Link :</div>
        <div class="flex items-center w-full mt-2 md:mt-0 md:w-auto">
            <input type="text" readonly value="{{ $this->getReferralLink() }}"
                class="w-full px-2 py-1 mr-2 bg-white border border-gray-300 rounded dark:bg-gray-800 dark:border-gray-700 md:w-[48rem]"
                id="referral-link">
            <div x-data>
                <button @click="
                    const input = document.getElementById('referral-link');
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(input.value).then(() => {
                            $wire.notifyCopied();
                        });
                    } else {
                        input.select();
                        document.execCommand('copy');
                        $wire.notifyCopied();
                    }
                " class="flex items-center px-3 py-1 text-white bg-blue-500 rounded hover:bg-blue-600">
                    <x-heroicon-o-clipboard-document class="w-5 h-5 mr-1" />
                    Copy
                </button>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
