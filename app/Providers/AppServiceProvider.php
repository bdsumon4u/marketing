<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput\Actions\HidePasswordAction;
use Filament\Forms\Components\TextInput\Actions\ShowPasswordAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::useAggressivePrefetching();
        Date::use(CarbonImmutable::class);
        DB::prohibitDestructiveCommands(app()->isProduction());
        Model::shouldBeStrict(! app()->isProduction());
        URL::forceHttps(app()->isProduction());
        Model::unguard();
        Table::$defaultCurrency = 'BDT';
        Table::$defaultDateDisplayFormat = 'd-M-Y';
        Table::$defaultTimeDisplayFormat = 'h:i:s A';
        Table::$defaultDateTimeDisplayFormat = 'd-M-Y h:i:s A';
        Number::useCurrency(Table::$defaultCurrency);

        // CreateAction::configureUsing(fn (CreateAction $action) => $action->icon('heroicon-o-plus'));
        DeleteAction::configureUsing(fn (DeleteAction $action) => $action->icon('heroicon-o-trash'));

        ShowPasswordAction::configureUsing(function (ShowPasswordAction $action) {
            return $action->extraAttributes(['tabindex' => '-1']);
        });
        HidePasswordAction::configureUsing(function (HidePasswordAction $action) {
            return $action->extraAttributes(['tabindex' => '-1']);
        });
    }
}
