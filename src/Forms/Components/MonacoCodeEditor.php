<?php

namespace ChrisReedIO\MonacoEditor\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;

class MonacoCodeEditor extends Field
{
    protected string $view = 'filament-monaco-editor::forms.components.monaco-code-editor';

    protected string | Closure | null $language = null;

    protected string | Closure | null $theme = null;

    protected string | int | Closure | null $minHeight = null;

    protected bool | Closure | null $readonly = null;

    protected array $options = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->language(config('monaco-editor.default_language', 'blade'));
        $this->theme(config('monaco-editor.default_theme', 'vs-dark'));
        $this->minHeight(config('monaco-editor.default_min_height', '16rem'));
    }

    public function language(string | Closure | null $language): static
    {
        $this->language = $language;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->evaluate($this->language);
    }

    public function theme(string | Closure | null $theme): static
    {
        $this->theme = $theme;

        return $this;
    }

    public function getTheme(): ?string
    {
        return $this->evaluate($this->theme);
    }

    public function minHeight(string | int | Closure | null $height): static
    {
        $this->minHeight = $height;

        return $this;
    }

    public function height(string | int | Closure | null $height): static
    {
        return $this->minHeight($height);
    }

    public function getMinHeight(): string | int | null
    {
        return $this->evaluate($this->minHeight);
    }

    public function readonly(bool | Closure | null $condition = true): static
    {
        $this->readonly = $condition;

        return $this;
    }

    public function isReadonly(): bool
    {
        return (bool) $this->evaluate($this->readonly);
    }

    public function options(array $options = []): static
    {
        $this->options = $options;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getMonacoConfig(): array
    {
        $height = $this->getMinHeight() ?? '16rem';

        if (is_int($height)) {
            $height = "{$height}px";
        }

        return array_merge([
            'language' => $this->getLanguage(),
            'theme' => $this->getTheme(),
            'height' => $height,
            'readonly' => $this->isReadonly(),
            'options' => array_merge((array) config('monaco-editor.defaults', []), (array) $this->getOptions()),
        ]);
    }

    public function getConfig(): array
    {
        return $this->getMonacoConfig();
    }
}
