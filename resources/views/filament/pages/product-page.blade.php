<x-filament-panels::page>
    <x-filament-panels::form wire:submit="create">
        {{ $this->form }}

        <x-filament::button type="submit" class="mt-4">
            Create Transaction
        </x-filament::button>
    </x-filament-panels::form>

    {{ $this->table }}
</x-filament-panels::page>
