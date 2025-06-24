<?php

namespace App\Filament\Common\Pages;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Pages\Page;

class ReferralTreePage extends Page
{
    protected string $view = 'filament.pages.referral-tree-page';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Referral Tree';

    protected static ?string $title = 'Referral Tree Visualization';

    protected static string|\UnitEnum|null $navigationGroup = 'Visualization';

    protected ?string $heading = '';

    protected static ?int $navigationSort = 2;

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
