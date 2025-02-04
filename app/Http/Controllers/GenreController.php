<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GenreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            return response()->json(Genre::all(), 200);
        } catch(Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'Error while getting genres'
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:genres,name'
            ]);

            $genre = Genre::create([
                'name' => $validated['name'],
                'slug' => str()->slug($validated['name'])
            ]);
            return response()->json($genre, 201);
        } catch(Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'Error while creating genre'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Genre $genre)
    {
        try {
            return response()->json($genre, 200);
        } catch(Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'Error while getting genre'
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Genre $genre)
    {
        try {
            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('genres')->ignore($genre->id)
                ]
                ]);
            
            $genre->update([
                'name' => $validated['name'],
                'slug' => str()->slug($validated['name'])
            ]);
            return response()->json($genre, 200);
        } catch(Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'Error while updating genre'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Genre $genre)
    {
        try {
            $genre->delete();
            return response()->json(['message' => 'Genre deleted successfully'], 200);
        } catch(Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'Error while deleting genre'
            ], 500);
        }
    }
}