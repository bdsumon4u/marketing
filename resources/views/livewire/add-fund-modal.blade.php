<x-filament::modal
    id="add-fund-modal"
    width="md"
>
    <x-slot name="header">
        <h2 class="text-xl font-bold tracking-tight">Add Fund</h2>
    </x-slot>

    @unless(auth()->user()->is_active)
        <div class="p-4 mb-4 text-sm text-yellow-800 rounded-lg bg-yellow-50 dark:bg-gray-800 dark:text-yellow-400" role="alert">
            <div class="font-medium">Account Not Verified</div>
            <p class="mt-1">Please choose a package to verify your account:</p>
            <ul class="mt-2 list-disc list-inside">
                <li>With Product: Tk. 1,000</li>
                <li>Without Product: Tk. 500</li>
            </ul>
        </div>
    @endunless

    <div class="p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400" role="alert">
        <div class="font-medium">Payment Method</div>
        <p class="mt-1">Please send money via bKash personal:</p>
        <ul class="mt-2 list-disc list-inside">
            <li>Account: <span class="font-mono">01789009870</span></li>
            <li>Reference: <span class="font-mono">{{ auth()->user()->username }}</span></li>
        </ul>
    </div>

    <form wire:submit="submit">
        {{ $this->form }}

        <div class="flex justify-end mt-4">
            <x-filament::button
                type="submit"
                color="primary"
            >
                Submit
            </x-filament::button>
        </div>
    </form>
</x-filament::modal>
