<?php

namespace Duke\CrudGenerator;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application as LumenApplication;
use Duke\CrudGenerator\Commands\ExportMakeCommand;
use Duke\CrudGenerator\Commands\ImportMakeCommand;
use Duke\CrudGenerator\Excel;
use Duke\CrudGenerator\Exporter;
use Duke\CrudGenerator\Files\Filesystem;
use Duke\CrudGenerator\Files\TemporaryFileFactory;
use Duke\CrudGenerator\Importer;
use Duke\CrudGenerator\Mixins\DownloadCollection;
use Duke\CrudGenerator\Mixins\StoreCollection;
use Duke\CrudGenerator\QueuedWriter;
use Duke\CrudGenerator\Reader;
use Duke\CrudGenerator\Transactions\TransactionHandler;
use Duke\CrudGenerator\Transactions\TransactionManager;
use Duke\CrudGenerator\Writer;


class   CrudGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/crudgenerator.php' => config_path('crudgenerator.php'),
        ]);

        $this->publishes([
            __DIR__ . '/../publish/views/' => base_path('resources/views/'),
        ]);

        $this->publishes([
            __DIR__ . '/../publish/css/app.css' => public_path('css/app.css'),
        ]);

        if (\App::VERSION() <= '5.2') {
            $this->publishes([
                __DIR__ . '/../publish/css/app.css' => public_path('css/app.css'),
            ]);
        }

        $this->publishes([
            __DIR__ . '/stubs/' => base_path('resources/crud-generator/'),
        ]);

        $this->publishes([
            __DIR__ . '/../config/excel.php' => config_path('excel.php'),
        ]);

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->commands(
            'Duke\CrudGenerator\Commands\CrudCommand',
            'Duke\CrudGenerator\Commands\CrudControllerCommand',
            'Duke\CrudGenerator\Commands\CrudModelCommand',
            'Duke\CrudGenerator\Commands\CrudMigrationCommand',
            'Duke\CrudGenerator\Commands\CrudViewCommand',
            'Duke\CrudGenerator\Commands\CrudLangCommand',
            'Duke\CrudGenerator\Commands\CrudApiCommand',
            'Duke\CrudGenerator\Commands\CrudApiControllerCommand',
            'Duke\CrudGenerator\Commands\ExportMakeCommand',
            'Duke\CrudGenerator\Commands\ImportMakeCommand'
        );
        $this->mergeConfigFrom(
            $this->getConfigFile(),
            'excel'
        );

        $this->app->bind(TransactionManager::class, function () {
            return new TransactionManager($this->app);
        });

        $this->app->bind(TransactionHandler::class, function () {
            return $this->app->make(TransactionManager::class)->driver();
        });

        $this->app->bind(TemporaryFileFactory::class, function () {
            return new TemporaryFileFactory(
                config('excel.temporary_files.local_path', config('excel.exports.temp_path', storage_path('framework/laravel-excel'))),
                config('excel.temporary_files.remote_disk')

            );
        });

        $this->app->bind(Filesystem::class, function () {
            return new Filesystem($this->app->make('filesystem'));
        });

        $this->app->bind('excel', function () {
            return new Excel(
                $this->app->make(Writer::class),
                $this->app->make(QueuedWriter::class),
                $this->app->make(Reader::class),
                $this->app->make(Filesystem::class)
            );
        });

        $this->app->alias('excel', Excel::class);
        $this->app->alias('excel', Exporter::class);
        $this->app->alias('excel', Importer::class);

        Collection::mixin(new DownloadCollection);
        Collection::mixin(new StoreCollection);

        $this->commands([
            ExportMakeCommand::class,
            ImportMakeCommand::class,
        ]);
    }
    /**
     * @return string
     */
    protected function getConfigFile(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'excel.php';
    }
}