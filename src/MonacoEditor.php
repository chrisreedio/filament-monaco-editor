<?php

namespace ChrisReedIO\MonacoEditor;

use ChrisReedIO\MonacoEditor\Forms\Components\MonacoCodeEditor;

class MonacoEditor
{
    public static function make(string $name): MonacoCodeEditor
    {
        return MonacoCodeEditor::make($name);
    }

    public static function blade(string $name): MonacoCodeEditor
    {
        return static::make($name)->language('blade');
    }

    public static function php(string $name): MonacoCodeEditor
    {
        return static::make($name)->language('php');
    }
}
