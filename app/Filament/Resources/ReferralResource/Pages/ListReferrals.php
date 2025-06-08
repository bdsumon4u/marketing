<?php

namespace App\Filament\Resources\ReferralResource\Pages;

use App\Filament\Resources\ReferralResource;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListReferrals extends ListRecords
{
    protected static string $resource = ReferralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
            Actions\Action::make('copy-link')
                ->label('Copy Link')
                ->icon('heroicon-o-link')
                ->color('success')
                ->action(fn () => $this->copyLink()),
        ];
    }

    private function copyLink()
    {
        $this->js("
            const input = Object.assign(document.createElement('input'), {
                value: '{$this->getReferralLink()}',
                style: 'position:absolute;left:-9999px;top:-9999px;pointer-events:none;z-index:-9999;overflow:hidden'
            });
            document.body.appendChild(input);
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(input.value).then(() => {
                    \$wire.notifyCopied();
                });
            } else {
                input.select();
                document.execCommand('copy');
                \$wire.notifyCopied();
            }
            input.remove();
        ");
    }

    public function getReferralLink(): string
    {
        return Filament::getPanel()->getRegistrationUrl([
            'ref' => Filament::auth()->user()->username,
        ]);
    }

    public function notifyCopied()
    {
        Notification::make()
            ->title('Link copied to clipboard!')
            ->success()
            ->send();
    }
}
