<?php

namespace App\Filament\Common\Pages;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Pages\Page;

class BinaryTreePage extends Page
{
    protected string $view = 'filament.pages.binary-tree-page';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-share';

    protected static ?string $navigationLabel = 'Binary Tree';

    protected static ?string $title = 'Binary Tree Visualization';

    protected static string|\UnitEnum|null $navigationGroup = 'Visualization';

    protected ?string $heading = '';

    protected static ?int $navigationSort = 1;

    public function getViewData(): array
    {
        $baseId = request('base_id') ?? User::baseId();
        if (Filament::getCurrentOrDefaultPanel()->getId() === 'app') {
            $baseId = Filament::auth()->id();
        }

        return [
            'baseId' => $baseId,
        ];
    }
}
