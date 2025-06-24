<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\TextInput;

/**
 * @property Form $form
 */
class EditProfile extends \Filament\Auth\Pages\EditProfile
{
    /**
     * @return array<int | string, string | Form>
     */
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->components([
                        $this->getNameFormComponent(),
                        TextInput::make('username')
                            ->label(__('Username'))
                            ->required()
                            ->maxLength(255)
                            ->autofocus(),
                        $this->getEmailFormComponent(),
                        TextInput::make('phone')
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
