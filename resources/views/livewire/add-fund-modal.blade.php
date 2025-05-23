<x-filament::modal
    id="add-fund-modal"
    width="md"
>
    <x-slot name="header">
        <h2 class="text-xl font-bold tracking-tight">Add Fund</h2>
    </x-slot>

    <form wire:submit="submit">
        {{ $this->form }}

        <div class="flex justify-end mt-4">
            <x-filament::button
                type="submit"
                color="primary"
            >
                Add Fund
            </x-filament::button>
        </div>
    </form>
</x-filament::modal>
