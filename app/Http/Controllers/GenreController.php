<?php

namespace App\Http\Controllers;

use App\Http\Resources\GenreResource;
use App\Models\Genre;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class GenreController extends Controller
{

    /**
     * List all movie genres.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function movieGenres(Request $request)
    {
        $genres = Genre::where('type', 'movie')->get();
        return response()->json([
            'genres' => GenreResource::collection($genres),
        ]);
    }

    /**
     * List all TV show genres.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function tvGenres(Request $request)
    {
        $genres = Genre::where('type', 'tv')->get();
        return response()->json([
            'genres' => GenreResource::collection($genres),
        ]);
    }

    /**
     * Create a new genre.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Restrict to admins (optional)
        // Gate::authorize('create', Genre::class);

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:movie,tv',
        ]);

        $genre = Genre::create($request->only(['name', 'type']));
        return new GenreResource($genre);
    }

    /**
     * Update an existing genre.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $genre = Genre::findOrFail($id);
        // Gate::authorize('update', $genre);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:movie,tv',
        ]);

        $genre->update($request->only(['name', 'type']));
        return new GenreResource($genre);
    }

    /**
     * Delete a genre.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $genre = Genre::findOrFail($id);
        // Gate::authorize('delete', $genre);

        $genre->delete();
        return response()->json(['status_code' => 1, 'status_message' => 'Genre deleted']);
    } 

    
    // /**
    //  * Display a listing of the resource.
    //  */
    // public function index()
    // {
    //     try {
    //         return response()->json(Genre::all(), 200);
    //     } catch(Exception $e) {
    //         return response()->json([
    //             'message' => $e->getMessage(),
    //             'error' => 'Error while getting genres'
    //         ], 500);
    //     }
    // }

    // /**
    //  * Store a newly created resource in storage.
    //  */
    // public function store(Request $request)
    // {
    //     try {
    //         $validated = $request->validate([
    //             'name' => 'required|string|max:255|unique:genres,name'
    //         ]);

    //         $genre = Genre::create([
    //             'name' => $validated['name'],
    //             'slug' => str()->slug($validated['name'])
    //         ]);
    //         return response()->json($genre, 201);
    //     } catch(Exception $e) {
    //         return response()->json([
    //             'message' => $e->getMessage(),
    //             'error' => 'Error while creating genre'
    //         ], 500);
    //     }
    // }

    // /**
    //  * Display the specified resource.
    //  */
    // public function show(Genre $genre)
    // {
    //     try {
    //         return response()->json($genre, 200);
    //     } catch(Exception $e) {
    //         return response()->json([
    //             'message' => $e->getMessage(),
    //             'error' => 'Error while getting genre'
    //         ], 500);
    //     }
    // }

    // /**
    //  * Update the specified resource in storage.
    //  */
    // public function update(Request $request, Genre $genre)
    // {
    //     try {
    //         $validated = $request->validate([
    //             'name' => [
    //                 'required',
    //                 'string',
    //                 'max:255',
    //                 Rule::unique('genres')->ignore($genre->id)
    //             ]
    //             ]);
            
    //         $genre->update([
    //             'name' => $validated['name'],
    //             'slug' => str()->slug($validated['name'])
    //         ]);
    //         return response()->json($genre, 200);
    //     } catch(Exception $e) {
    //         return response()->json([
    //             'message' => $e->getMessage(),
    //             'error' => 'Error while updating genre'
    //         ], 500);
    //     }
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  */
    // public function destroy(Genre $genre)
    // {
    //     try {
    //         $genre->delete();
    //         return response()->json(['message' => 'Genre deleted successfully'], 200);
    //     } catch(Exception $e) {
    //         return response()->json([
    //             'message' => $e->getMessage(),
    //             'error' => 'Error while deleting genre'
    //         ], 500);
    //     }
    // }
}