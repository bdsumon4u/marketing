<x-filament::modal
    id="verify-now-modal"
    width="md"
>
    <x-slot name="header">
        <h2 class="text-xl font-bold tracking-tight">Verify Account</h2>
    </x-slot>

    <div class="p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400" role="alert">
        <div class="font-medium">Verification Process</div>
        <p class="mt-1">Please select a package to verify your account:</p>
        <ul class="mt-2 list-disc list-inside">
            <li>With Product: Tk. 1,000</li>
            <li>Without Product: Tk. 500</li>
        </ul>
    </div>

    <form wire:submit="submit">
        {{ $this->form }}

        <div class="flex justify-end mt-4">
            <x-filament::button
                type="submit"
                color="primary"
            >
                Verify Now
            </x-filament::button>
        </div>
    </form>
</x-filament::modal>
