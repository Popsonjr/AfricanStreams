<?php
namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait HelperTrait {
    /**
     * Store uploaded file and return full url
     */
    public function storeFile($file, $folder) {
        $path = $file->store($folder, 'public');
        return Storage::url($path);
    }
}