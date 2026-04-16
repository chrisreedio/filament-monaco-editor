<?php

use ChrisReedIO\MonacoEditor\Forms\Components\MonacoCodeEditor;
use ChrisReedIO\MonacoEditor\MonacoEditorServiceProvider;

it('stores configuration for editor language, theme, size, and readonly flag', function () {
    config(['monaco-editor.default_language' => 'blade']);
    config(['monaco-editor.default_theme' => 'vs-dark']);
    config(['monaco-editor.default_min_height' => '14rem']);
    config(['monaco-editor.defaults.fontSize' => 12]);
    
    $field = MonacoCodeEditor::make('template')
        ->language('php')
        ->theme('vs-light')
        ->height('24rem')
        ->readonly()
        ->options([
            'tabSize' => 2,
        ]);

    expect($field->getLanguage())->toBe('php')
        ->and($field->getTheme())->toBe('vs-light')
        ->and($field->getMinHeight())->toBe('24rem')
        ->and($field->isReadonly())->toBeTrue()
        ->and($field->getOptions())->toMatchArray(['tabSize' => 2]);

    expect($field->getConfig())->toMatchArray([
        'language' => 'php',
        'theme' => 'vs-light',
        'height' => '24rem',
        'readonly' => true,
    ]);

    expect($field->getConfig()['options'])->toMatchArray([
        'fontSize' => 12,
        'lineNumbers' => 'on',
        'scrollBeyondLastLine' => false,
        'minimap' => ['enabled' => false],
        'tabSize' => 2,
    ]);
});

it('uses package defaults for blade and exposes blade payload aliases', function () {
    config(['monaco-editor.blade.directives' => ['custom', 'if']]);

    $field = MonacoCodeEditor::make('blade_template');

    expect($field->getLanguage())->toBe('blade')
        ->and($field->getTheme())->toBe('vs-dark')
        ->and($field->getMinHeight())->toBe('16rem')
        ->and($field->getMonacoConfig())->toBe($field->getConfig());
});

it('registers package assets and script data in the service provider', function () {
    $provider = new MonacoEditorServiceProvider(app());

    $scriptDataMethod = new \ReflectionMethod(MonacoEditorServiceProvider::class, 'getScriptData');
    $assetsMethod = new \ReflectionMethod(MonacoEditorServiceProvider::class, 'getAssets');
    $scriptDataMethod->setAccessible(true);
    $assetsMethod->setAccessible(true);

    $scriptData = $scriptDataMethod->invoke($provider);
    $assets = $assetsMethod->invoke($provider);

    expect($scriptData)->toBeArray()
        ->and($scriptData)->toHaveKey('monacoEditor')
        ->and($scriptData['monacoEditor'])->toHaveKey('defaults')
        ->and($scriptData['monacoEditor'])->toHaveKey('blade')
        ->and($assets)->toHaveCount(2);
});
