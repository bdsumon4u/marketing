@php
    $node = app('livewire')->current()->getNode($nodeId);
@endphp
@if($node)
    <div class="tree-node">
        <div class="user-card"
            data-node-id="{{ $node['id'] }}"
            @mouseenter="highlightNode({{ $node['id'] }}, true)"
            @mouseleave="highlightNode({{ $node['id'] }}, false)"
        >
            <div class="avatar">
                <svg width="40" height="40" fill="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="8" r="4" />
                    <ellipse cx="12" cy="17" rx="7" ry="5" />
                </svg>
            </div>
            <div class="user-info">
                <div class="user-id">ID: {{ $node['id'] }}</div>
                <div class="user-name">{{ $node['name'] }}</div>
                <div class="user-username">{{ '@' . $node['username'] }}</div>
            </div>
            @if(($node['children'][0] || $node['children'][1]) && !in_array($node['id'], $expandedNodes))
                <x-filament::button wire:click="expandNode({{ $node['id'] }})">Next</x-filament::button>
            @endif
        </div>
        @if(in_array($node['id'], $expandedNodes))
            <div class="tree-level">
                @foreach($node['children'] as $childId)
                    @if($childId)
                        @include('filament.pages.partials.binary-tree-node', ['nodeId' => $childId, 'expandedNodes' => $expandedNodes])
                    @endif
                @endforeach
            </div>
        @endif
    </div>
@endif
