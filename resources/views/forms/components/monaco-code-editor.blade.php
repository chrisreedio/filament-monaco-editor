<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    @php
        $monacoConfig = $getMonacoConfig();
    @endphp

    <div
        x-load
        x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('monaco-code-editor') }}"
        x-data="monacoCodeEditor({
            state: $wire.$entangle('{{ $getStatePath() }}'),
            updateState: (value) => $wire.$entangle('{{ $getStatePath() }}').set(value),
            config: @js($monacoConfig),
        })"
        class="monaco-code-editor-wrapper"
        wire:ignore
    >
        <div
            x-ref="editor"
            class="monaco-code-editor"
            style="min-height: {{ $monacoConfig['height'] ?? '16rem' }};"
        ></div>
    </div>
</x-dynamic-component>
