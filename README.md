# Filament Monaco Code Editor

Filament v5 form field integration for [Monaco Editor](https://github.com/microsoft/monaco-editor), including dedicated Blade syntax highlighting.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/chrisreedio/filament-monaco-editor.svg?style=flat-square)](https://packagist.org/packages/chrisreedio/filament-monaco-editor)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/chrisreedio/filament-monaco-editor/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/chrisreedio/filament-monaco-editor/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/chrisreedio/filament-monaco-editor.svg?style=flat-square)](https://packagist.org/packages/chrisreedio/filament-monaco-editor)

## Compatibility

- PHP ^8.2
- Filament 5.x

## Installation

```bash
composer require chrisreedio/filament-monaco-editor
```

Run the package build to generate frontend assets:

```bash
cd vendor/chrisreedio/filament-monaco-editor
npm install
npm run build
```

Publish package config and views (optional):

```bash
php artisan vendor:publish --tag="filament-monaco-editor-config"
php artisan vendor:publish --tag="filament-monaco-editor-views"
php artisan filament:assets
```

## Usage

Use the field directly in your form schema:

```php
use ChrisReedIO\MonacoEditor\Forms\Components\MonacoCodeEditor;

MonacoCodeEditor::make('template')
    ->language('blade')
    ->theme('vs-dark')
    ->height('24rem')
    ->options([
        'tabSize' => 2,
        'insertSpaces' => true,
    ]);
```

Blade, PHP, and custom heights:

```php
MonacoCodeEditor::make('blade_view')
    ->language('blade')
    ->theme('vs-dark');

MonacoCodeEditor::make('php_code')
    ->language('php')
    ->theme('vs-light')
    ->minHeight(320)
    ->readonly();
```

You can also use the package entrypoint facade for concise creation:

```php
use MonacoEditor;

MonacoEditor::blade('blade_view')->height('20rem');
MonacoEditor::php('php_payload')->readonly();
```

### Fluent API

- `language(string $language)`
- `theme(string $theme)`
- `minHeight(string|int $height)` / `height(string|int $height)`
- `readonly(bool $condition = true)`
- `options(array $options)` (merged into the final Monaco options)
- `getConfig()` / `getMonacoConfig()` for Alpine payload

## Configuration

Publish and customize `config/monaco-editor.php`:

```php
return [
    'default_language' => 'blade',
    'default_theme' => 'vs-dark',
    'default_min_height' => '16rem',
    'defaults' => [
        'fontSize' => 14,
        'lineNumbers' => 'on',
        'scrollBeyondLastLine' => false,
        'minimap' => ['enabled' => false],
    ],
    'blade' => [
        'keywords' => [
            'if', 'elseif', 'else', 'endif', 'foreach', 'endforeach',
            'for', 'endfor', 'while', 'endwhile', 'php', 'endphp', 'csrf',
            'method', 'can', 'endcan', 'auth', 'guest', 'unless', 'endunless',
        ],
        'directives' => [
            'if',
            'elseif',
            'else',
            'endif',
            'foreach',
            'endforeach',
            'section',
            'endsection',
        ],
        'patterns' => [
            ['/\$[A-Za-z_][A-Za-z0-9_]*/', 'variable'],
        ],
        'delimiters' => [
            'output' => ['{{', '}}'],
            'raw' => ['{!!', '!!}'],
            'comment' => ['{{--', '--}}'],
        ],
    ],
];
```

## Blade highlighting

- `blade` language is registered only when `language('blade')` is used.
- Tokens include output/raw/comment delimiters and directives (`@if`, `@foreach`, etc.).
- You can add custom syntax patterns via `blade.patterns`.

## Build artifacts consumed by Filament

- `resources/dist/components/monaco-code-editor.js`
- `resources/dist/monaco-code-editor.css`

The package service provider wires these assets through Filament `Asset` registration and script data.

## Development

```bash
npm run build
composer test
```

## Credits

- [Chris Reed](https://github.com/chrisreedio)
- Inspired by the broader Monaco ecosystem

## License

This package is released under the [MIT license](LICENSE.md).
