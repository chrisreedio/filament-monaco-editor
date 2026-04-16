<?php

namespace ChrisReedIO\MonacoEditor\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \ChrisReedIO\MonacoEditor\MonacoEditor
 */
class MonacoEditor extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \ChrisReedIO\MonacoEditor\MonacoEditor::class;
    }
}
