<x-filament::modal
    id="withdraw-modal"
    width="md"
>
    <x-slot name="header">
        Withdraw Funds
    </x-slot>

    <form wire:submit="submit">
        {{ $this->form }}

        <div class="mt-4 flex justify-end">
            <x-filament::button
                type="submit"
                color="primary"
            >
                Withdraw
            </x-filament::button>
        </div>
    </form>
</x-filament::modal>
