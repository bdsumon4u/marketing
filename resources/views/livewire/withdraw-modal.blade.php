<x-filament::modal
    id="withdraw-modal"
    width="md"
>
    <x-slot name="header">
        <h2 class="text-xl font-bold tracking-tight">Withdraw Funds</h2>
    </x-slot>

    <form wire:submit="submit">
        {{ $this->form }}

        <div class="flex justify-end mt-4">
            <x-filament::button
                type="submit"
                color="primary"
            >
                Withdraw
            </x-filament::button>
        </div>
    </form>
</x-filament::modal>
