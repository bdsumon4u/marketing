<?php

use App\Enums\UserRank;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id()->startingValue(User::baseId());
            $table->foreignId('referrer_id')->nullable();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->unsignedTinyInteger('rank')->default(UserRank::_M);
            $table->timestamp('rank_updated_at')->useCurrent();
            $table->boolean('is_active')->default(false);
            $table->boolean('with_product')->default(false);

            $table->integer('total_deposit')->default(0);
            $table->integer('total_income')->default(0);
            $table->integer('total_withdraw')->default(0);
            $table->integer('referral_income')->default(0);
            $table->integer('generation_income')->default(0);
            $table->integer('rank_income')->default(0);
            $table->integer('magic_income')->default(0);
            $table->integer('pending_deposit')->default(0);
            $table->integer('rejected_deposit')->default(0);
            $table->integer('pending_withdraw')->default(0);
            $table->integer('rejected_withdraw')->default(0);
            $table->integer('total_send')->default(0);
            $table->integer('total_receive')->default(0);

            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
