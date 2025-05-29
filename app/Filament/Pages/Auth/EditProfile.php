<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
/**
 * @property Form $form
 */
class EditProfile extends BaseEditProfile
{
    /**
     * @return array<int | string, string | Form>
     */
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getNameFormComponent(),
                        Forms\Components\TextInput::make('username')
                            ->label(__('Username'))
                            ->required()
                            ->maxLength(255)
                            ->autofocus(),
                        $this->getEmailFormComponent(),
                        Forms\Components\TextInput::make('phone')
                            ->label(__('Phone'))
                            ->required()
                            ->maxLength(255),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])
                    ->operation('edit')
                    ->model($this->getUser())
                    ->statePath('data')
                    ->inlineLabel(! static::isSimple()),
            ),
        ];
    }
}
