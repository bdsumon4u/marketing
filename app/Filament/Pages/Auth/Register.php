<?php

namespace App\Filament\Pages\Auth;

use App\Jobs\ProcessMLMIncentives;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class Register extends BaseRegister
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                Forms\Components\Grid::make(2)
                    ->schema([
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ]),
                TextInput::make('referral_code')
                    ->label('Referral Code')
                    ->required()
                    ->string()
                    ->exists('users', 'referral_code')
                    ->validationAttribute('referral code')
                    ->helperText('Enter the referral code of the person who referred you')
                    ->default(fn () => request()->cookie('referral_code'))
                    ->afterStateHydrated(function (TextInput $component, ?string $state) {
                        if ($state) {
                            $referrer = User::where('referral_code', $state)->first();
                            if ($referrer && $referrer->hasReachedReferralLimit()) {
                                $component->state(null);
                                Notification::make()
                                    ->title('This referrer has reached their maximum referral limit')
                                    ->warning()
                                    ->send();
                            }
                        }
                    })
                    ->rules([
                        function () {
                            return function (string $attribute, $value, \Closure $fail) {
                                $referrer = User::where('referral_code', $value)->first();
                                if ($referrer && $referrer->hasReachedReferralLimit()) {
                                    $fail('This referrer has reached their maximum referral limit.');
                                }
                            };
                        },
                    ]),
            ]);
    }

    protected function handleRegistration(array $data): User
    {
        return DB::transaction(function () use ($data) {
            // Get referrer once
            $referrer = User::where('referral_code', $data['referral_code'])->first();

            // Create user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'referred_by' => $referrer->id,
            ]);

            // Dispatch job to process MLM incentives
            ProcessMLMIncentives::dispatch($user, $referrer);

            Log::info('New referral registered', [
                'referrer_id' => $referrer->id,
                'referred_user_id' => $user->id,
                'referral_code' => $data['referral_code'],
            ]);

            return $user;
        });
    }
}
