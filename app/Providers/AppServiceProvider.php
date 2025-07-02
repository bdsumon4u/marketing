<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput\Actions\HidePasswordAction;
use Filament\Forms\Components\TextInput\Actions\ShowPasswordAction;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint\Operators\IsRelatedToOperator;
use Filament\Tables\Filters\QueryBuilder\Constraints\SelectConstraint;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Number;
use App\View\ThemedViewFinder;
use Illuminate\Support\Facades\View;
use Illuminate\View\Factory as ViewFactory;
use Illuminate\Support\ServiceProvider;
use Illuminate\Filesystem\Filesystem;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('view.finder', function ($app) {
            $paths = $app['config']['view.paths'];
            $filesystem = new Filesystem;

            $finder = new ThemedViewFinder($filesystem, $paths);
            $theme = config('view.theme', 'defaultx'); // Set default theme or pull from DB/session
            $finder->setTheme($theme);

            return $finder;
        });
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
        Table::configureUsing(fn (Table $table) => $table->defaultCurrency(Number::defaultCurrency()));
        Table::configureUsing(fn (Table $table) => $table->defaultDateDisplayFormat('d-M-Y'));
        Table::configureUsing(fn (Table $table) => $table->defaultTimeDisplayFormat('h:i:s A'));
        Table::configureUsing(fn (Table $table) => $table->defaultDateTimeDisplayFormat('d-M-Y h:i:s A'));

        // CreateAction::configureUsing(fn (CreateAction $action) => $action->icon('heroicon-o-plus'));
        DeleteAction::configureUsing(fn (DeleteAction $action) => $action->icon('heroicon-o-trash'));

        ShowPasswordAction::configureUsing(function (ShowPasswordAction $action) {
            return $action->extraAttributes(['tabindex' => '-1']);
        });
        // HidePasswordAction::configureUsing(function (HidePasswordAction $action) {
        //     return $action->extraAttributes(['tabindex' => '-1']);
        // });

        FilamentAsset::register([
            Js::make('global', __DIR__.'/../../resources/js/global.js'),
        ]);

        $isMobile = preg_match('/Mobile|Android|iPhone/', (string) request()->header('User-Agent'));

        foreach ([Select::class, MorphToSelect::class, DateTimePicker::class, SelectFilter::class, IsRelatedToOperator::class, SelectConstraint::class] as $class) {
            $class::configureUsing(fn ($component) => $component->native($isMobile));
        }

        IconColumn::configureUsing(function (IconColumn $column) {
            return $column->alignCenter();
        });
    }
}
