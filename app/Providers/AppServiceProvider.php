<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput\Actions\HidePasswordAction;
use Filament\Forms\Components\TextInput\Actions\ShowPasswordAction;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
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
        Number::useCurrency('BDT');
        Table::configureUsing(fn(Table $table) => $table->defaultCurrency(Number::defaultCurrency()));
        Table::configureUsing(fn(Table $table) => $table->defaultDateDisplayFormat('d-M-Y'));
        Table::configureUsing(fn(Table $table) => $table->defaultTimeDisplayFormat('h:i:s A'));
        Table::configureUsing(fn(Table $table) => $table->defaultDateTimeDisplayFormat('d-M-Y h:i:s A'));

        // CreateAction::configureUsing(fn (CreateAction $action) => $action->icon('heroicon-o-plus'));
        DeleteAction::configureUsing(fn (DeleteAction $action) => $action->icon('heroicon-o-trash'));

        ShowPasswordAction::configureUsing(function (ShowPasswordAction $action) {
            return $action->extraAttributes(['tabindex' => '-1']);
        });
        HidePasswordAction::configureUsing(function (HidePasswordAction $action) {
            return $action->extraAttributes(['tabindex' => '-1']);
        });

        FilamentAsset::register([
            Js::make('global', __DIR__ . '/../../resources/js/global.js'),
        ]);
    }
}
