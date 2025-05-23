<x-filament::modal
    id="add-fund-modal"
    width="md"
>
    <x-slot name="header">
        Add Fund
    </x-slot>

    <form wire:submit="submit">
        {{ $this->form }}

        <div class="mt-4 flex justify-end">
            <x-filament::button
                type="submit"
                color="primary"
            >
                Add Fund
            </x-filament::button>
        </div>
    </form>
</x-filament::modal>
