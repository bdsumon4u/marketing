<section x-if="!is_null($user)">
    @if($user)
    <div class="tree-node">
        <div
            class="user-card"
            data-node-id="{{ $user->id }}"
            data-parent-id="{{ $user->parentId() }}"
            @mouseenter="highlightNode({{ $user->id }}, true)"
            @mouseleave="highlightNode({{ $user->id }}, false)"
            @unless($user->is_active) style="border-color: red;" @endunless
        >
            <div class="avatar">
                <svg width="40" height="40" fill="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="8" r="4" />
                    <ellipse cx="12" cy="17" rx="7" ry="5" />
                </svg>
            </div>
            <div class="user-info">
                <a
                    wire:navigate
                    class="user-id"
                    @if($panelId === 'admin')
                    href="{{ url()->current() }}?base_id={{ $user->id }}"
                    @endif
                >ID: {{ $user->id }}</a>
                <div class="user-name">{{ $user->name }}</div>
                <div class="user-username">{{ '@' . $user->username }}</div>
            </div>
            @unless ($expanded)
            <x-filament::button wire:click="expand">Next</x-filament::button>
            @endunless
        </div>
        @if($expanded)
        <div class="tree-level">
            <livewire:tree-node :node-id="$user->leftId()" />
            <livewire:tree-node :node-id="$user->rightId()" />
        </div>
        @endif
    </div>
    @endif
</section>