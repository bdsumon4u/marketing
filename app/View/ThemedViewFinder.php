<?php

namespace App\View;

use Illuminate\View\FileViewFinder;
use Illuminate\Filesystem\Filesystem;

class ThemedViewFinder extends FileViewFinder
{
    protected string $theme;

    public function setTheme(string $theme)
    {
        $this->theme = $theme;
    }

    public function find($view)
    {
        $themedView = $this->prependTheme($view);

        if ($this->viewExists($themedView)) {
            return parent::find($themedView);
        }

        // Fallback to default (non-themed) view
        return parent::find($view);
    }

    protected function prependTheme($view)
    {
        return $this->theme . '.' . $view;
    }

    protected function viewExists($view)
    {
        try {
            parent::find($view);
            return true;
        } catch (\InvalidArgumentException $e) {
            return false;
        }
    }
}
