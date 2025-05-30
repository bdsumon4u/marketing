<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Support\Facades\DB;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class Register extends BaseRegister
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                PhoneInput::make('phone')
                    ->label('Phone Number')
                    ->required()
                    ->disallowDropdown()
                    ->defaultCountry('BD')
                    ->initialCountry('BD'),
                Forms\Components\Grid::make(2)
                    ->schema([
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ]),
                TextInput::make('referrer')
                    ->string()
                    ->exists('users', 'username')
                    ->validationAttribute('referrer')
                    ->helperText('Enter the username of the person who referred you')
                    ->default(fn () => request('ref') ?? request()->cookie('referral_code')),
            ]);
    }

    protected function handleRegistration(array $data): User
    {
        return DB::transaction(function () use ($data) {
            // Get referrer once
            $referrer = User::where('username', $data['referrer'])->first();

            // Create user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => $data['password'],
                'referrer_id' => $referrer?->id,
            ]);

            return $user;
        });
    }
}
