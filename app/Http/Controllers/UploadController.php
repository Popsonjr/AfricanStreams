<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function upload (Request $request) {
        $request->validate([
            'image' => 'required|image|mimes:jpg,png,jpeg'
        ]);

        $path = $request->file('image')->store('images', 'public');
        return response()->json(['path' => $path]);
    }
}