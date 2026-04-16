<?php

namespace ChrisReedIO\MonacoEditor;

use ChrisReedIO\MonacoEditor\Commands\MonacoEditorCommand;
use ChrisReedIO\MonacoEditor\Testing\TestsMonacoEditor;
use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Filesystem\Filesystem;
use Livewire\Features\SupportTesting\Testable;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class MonacoEditorServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-monaco-editor';

    public static string $viewNamespace = 'filament-monaco-editor';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasCommands($this->getCommands())
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('chrisreedio/filament-monaco-editor');
            });

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageRegistered(): void {}

    public function packageBooted(): void
    {
        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        FilamentAsset::registerScriptData(
            $this->getScriptData(),
            $this->getAssetPackageName()
        );

        // Icon Registration
        FilamentIcon::register($this->getIcons());

        // Handle Stubs
        if (app()->runningInConsole()) {
            foreach (app(Filesystem::class)->files(__DIR__ . '/../stubs/') as $file) {
                $this->publishes([
                    $file->getRealPath() => base_path("stubs/filament-monaco-editor/{$file->getFilename()}"),
                ], 'filament-monaco-editor-stubs');
            }
        }

        // Testing
        Testable::mixin(new TestsMonacoEditor);
    }

    protected function getAssetPackageName(): ?string
    {
        return 'chrisreedio/filament-monaco-editor';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            AlpineComponent::make(
                'monaco-code-editor',
                __DIR__ . '/../resources/dist/components/monaco-code-editor.js',
            ),
            Css::make(
                'monaco-code-editor-styles',
                __DIR__ . '/../resources/dist/monaco-code-editor.css',
            ),
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            MonacoEditorCommand::class,
        ];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getRoutes(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getScriptData(): array
    {
        $bladeConfig = config('monaco-editor.blade', []);
        $bladeDirectives = $bladeConfig['directives'] ?? ['if', 'elseif', 'else', 'endif', 'foreach', 'endforeach', 'for', 'endfor', 'while', 'endwhile', 'isset', 'empty', 'include', 'extends', 'section', 'endsection', 'yield', 'stack', 'push', 'endpush', 'endphp', 'php', 'csrf', 'method', 'can', 'endcan', 'auth', 'guest', 'unless', 'endunless'];

        return [
            'monacoEditor' => [
                'defaultTheme' => config('monaco-editor.default_theme', 'vs-dark'),
                'defaultLanguage' => config('monaco-editor.default_language', 'blade'),
                'defaultMinHeight' => config('monaco-editor.default_min_height', '16rem'),
                'defaults' => (array) config('monaco-editor.defaults', []),
                'blade' => [
                    'keywords' => (array) data_get($bladeConfig, 'keywords', $bladeDirectives),
                    'directives' => (array) $bladeDirectives,
                    'patterns' => (array) data_get($bladeConfig, 'patterns', []),
                    'delimiters' => [
                        'output' => data_get($bladeConfig, 'delimiters.output', ['{{', '}}']),
                        'raw' => data_get($bladeConfig, 'delimiters.raw', ['{!!', '!!}']),
                        'comment' => data_get($bladeConfig, 'delimiters.comment', ['{{--', '--}}']),
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_filament-monaco-editor_table',
        ];
    }
}
