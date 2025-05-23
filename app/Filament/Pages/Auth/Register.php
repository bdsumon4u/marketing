<?php

namespace App\Filament\Pages\Auth;

use App\Jobs\ProcessMLMIncentives;
use App\Models\User;
use App\Models\Wallet;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
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
                TextInput::make('referrer')
                    ->string()
                    ->exists('users', 'username')
                    ->validationAttribute('referrer')
                    ->helperText('Enter the username of the person who referred you')
                    ->default(fn () => request()->cookie('referrer')),
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
                'password' => Hash::make($data['password']),
                'referrer_id' => $referrer->id,
            ]);

            // Get registration fee from config
            $registrationFee = config('mlm.registration_fee');

            // Distribute registration fee to company wallets
            foreach (Wallet::company()->wallets as $wallet) {
                $percentageShare = $wallet->meta['percentage_share'] ?? 0;
                $amount = ($registrationFee * $percentageShare) / 100;
                $wallet->deposit($amount, [
                    'description' => 'Registration fee distribution',
                    'user_id' => $user->id,
                ]);
            }

            // Dispatch job to process MLM incentives
            ProcessMLMIncentives::dispatch($user, $referrer);

            Log::info('New referral registered', [
                'referrer_id' => $referrer->id,
                'referred_user_id' => $user->id,
                'username' => $data['username'],
                'registration_fee' => $registrationFee,
            ]);

            return $user;
        });
    }
}
