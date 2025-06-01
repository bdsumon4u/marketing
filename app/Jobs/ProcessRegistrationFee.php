<?php

namespace App\Jobs;

use App\Enums\CompanyWalletType;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Arr;

class ProcessRegistrationFee implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected User $user,
        protected string $package,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->user->balanceFloat < Arr::get(config('mlm.registration_fee'), $this->package)) {
            throw new \Exception('Insufficient balance');
        }

        $registrationFee = config('mlm.registration_fee.without_product');
        $companyWallet = Wallet::company()->getWallet(CompanyWalletType::COMPANY->value);
        $this->user->transferFloat($companyWallet, $registrationFee, [
            'action' => 'registration',
            'message' => 'Registration fee',
            'user_id' => $this->user->id,
        ]);

        if ($this->package === 'with_product') {
            $productFund = config('mlm.registration_fee.with_product') - $registrationFee;
            $this->user->transferFloat($this->user->getOrCreateWallet('product'), $productFund, [
                'action' => 'registration',
                'message' => 'Product fund',
                'user_id' => $this->user->id,
            ]);
        }
    }
}
