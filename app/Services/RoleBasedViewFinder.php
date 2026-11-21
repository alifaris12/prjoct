<?php

namespace App\Services;

use Illuminate\View\FileViewFinder;

class RoleBasedViewFinder extends FileViewFinder
{
    /**
     * Get the fully qualified location of the view.
     *
     * @param  string  $name
     * @return string
     */
    public function find($name)
    {
        if (auth()->check()) {
            $user = auth()->user();
            $uiMode = session('ui_mode');

            // Admin (hc) defaults to original Cuba theme, regular users (nhc) default to MATTA theme
            $isCuba = ($uiMode === 'cuba') || ($uiMode !== 'matta' && $user->userrole === 'hc');

            if ($isCuba) {
                foreach ($this->paths as $path) {
                    $cubaFile = $path . '/' . str_replace('.', '/', $name) . '.cuba.blade.php';
                    if ($this->files->exists($cubaFile)) {
                        return $cubaFile;
                    }
                }
            }
        }

        return parent::find($name);
    }
}

