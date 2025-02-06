<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMovieRequest;
use App\Http\Requests\UpdateMovieRequest;
use App\Models\Movie;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MovieController extends Controller
{
    //Fetch All Movies (with Filters, Search, pAgination)
    public function index(Request $request) {
        try {
            $query = Movie::with('categories', 'genre', 'seasons', 'relatedMovies');

            if($request->has('category')) {
                $query->whereHas('categories', function ($q) use ($request) {
                    $q->where('slug', $request->category);
                });
            }

            if($request->has('genre')) {
                $query->whereHas('genre', function($q) use ($request) {
                    $q->where('slug', $request->genre);
                });
            }

            if($request->has('season')) {
                $query->whereHas('seasons', function ($q) use($request) {
                    $q->where('season_number', $request->season);
                });
            }

            if ($request->has('search')) {
                $query->where('title', 'LIKE', '%' . $request->search . '%');
            }

            if($request->has('type')) {
                $query->where('type', $request->type);
            }

            $movies = $query->paginate(100);
            return response()->json($movies);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'Error fetching movies'
            ], 500);
        }
        
    }

    public function show ($id) {
        try {
            $movie = Movie::with(['genre', 'categories', 'seasons.episodes'])->findOrFail($id);
            return response()->json($movie);
        } catch(Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'Error getting movie',
            ], 500);
        }
    }

    //Fetch Related Movies
    public function related($id) {
        try {
            
            $movie = Movie::findOrFail($id);
            $relatedMovies = $movie->relatedMovies()->with('categories', 'genre')->get();
            return response()->json($relatedMovies);
        } catch(Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'Error getting related movies',
            ], 500);
        }
    }

    public function store(StoreMovieRequest $request) {
        try {
            $data = $request->validated();
            if ($request->hasFile('banner_image')) {
                $data['banner_image'] = $this->storeFile($request->file('banner_image'),'movies/banner');
            }
            if ($request->hasFile('cover_image')) {
                $data['cover_image'] = $this->storeFile($request->file('cover_image'),'movies/covers');
            }
            if ($request->hasFile('standard_image')) {
                $data['standard_image'] = $this->storeFile($request->file('standard_image'),'movies/standard');
            }
            if ($request->hasFile('thumbnail_image')) {
                $data['thumbnail_image'] = $this->storeFile($request->file('thumbnail_image'),'movies/thumbnail');
            }
            if ($request->hasFile('movie_file')) {
                $data['movie_file'] = $this->storeFile($request->file('movie_file'),'movies/videos');
            }
            $movie = Movie::create($data);
            if($request->has('category_ids') && is_array($request->category_ids)) {
                $movie->categories()->sync($request->category_ids);
            }

            if($request->has('related_movie_ids') && is_array($request->related_movie_ids)) {
                $movie->relatedMovies()->sync($request->related_movie_ids);
            }

            return response()->json($movie, 201);
        } catch(Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'Error creating new movie',
            ], 500);
        }
        
    }

    public function update(UpdateMovieRequest $request, Movie $movie) {
        // public function update(Request $request, Movie $movie) {
        try {
            $updateData = array_filter($request->validated(), fn($value) => !is_null($value));
            if (!isset($updateData['category_ids'])) {
                unset($updateData['category_ids']);
            }
            if (!isset($updateData['related_movie_ids'])) {
                unset($updateData['related_movie_ids']);
            }

            // $updateData = $request->only(array_keys($request->rules()));
            Log::info('movie to update', [
                $updateData
            ]);
            if (!empty($updateData)) {
                $movie->update($updateData);
            }

            // $movie = Movie::findOrFail($id);
            // $movie->update($request->validated());
            if($request->has('category_ids')) {
                $movie->categories()->sync($request->categories);
            }
            if($request->has('related_movie_ids')) {
                $movie->relatedMovies()->sync($request->related_movie_ids);
            }
            

            return response()->json($movie);
        } catch(Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'Error while updating movie',
            ], 500);
        }   
    }

    public function destroy(Movie $movie) {
        try {
            // $movie = Movie::findOrFail($id);
            $movie->delete();

            return response()->json(['message' => 'Movie deleted successfully']);
        } catch(Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'Error deleting movie',
            ], 500);
        }
    }

    /**
     * Store uploaded file and return full url
     */
    private function storeFile($file, $folder) {
        $path = $file->store($folder, 'public');
        return Storage::url($path);
    }
}