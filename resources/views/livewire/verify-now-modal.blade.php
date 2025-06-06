<x-filament::modal
    id="verify-now-modal"
    width="md"
    display-classes="block"
>
    <x-slot name="header">
        <h2 class="text-xl font-bold tracking-tight">Verify Account</h2>
    </x-slot>

    <div class="p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400" role="alert">
        <div class="font-medium">Verification Process</div>
        <p class="mt-1">Please select a package to verify your account:</p>
    </div>

    <form wire:submit="submit">
        {{ $this->form }}

        <div class="flex justify-end mt-4">
            <x-filament::button
                type="submit"
                color="primary"
            >
                Request Verification
            </x-filament::button>
        </div>
    </form>
</x-filament::modal>
