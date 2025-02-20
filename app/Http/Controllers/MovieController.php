<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMovieRequest;
use App\Http\Requests\UpdateMovieRequest;
use App\Models\Movie;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    //Fetch All Movies (with Filters, Search, pAgination)
    public function index(Request $request) {
        $query = Movie::with('categories', 'genre', 'seasons', 'related_movies');

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

        $movies = $query->paginate(10);
        return response()->json($movies);
    }

    public function show ($id) {
        $movie = Movie::with(['genre', 'categories', 'seasons.episodes'])->findOrFail($id);
        return response()->json($movie);
    }

    //Fetch Related Movies
    public function related($id) {
        $movie = Movie::findOrFail($id);
        $relatedMovies = $movie->relatedMovies()->with('categories', 'genre')->get();
        return response()->json($relatedMovies);
    }

    public function store(StoreMovieRequest $request) {
        $movie = Movie::create($request->validated());
        $movie->categories()->sync($request->categories);

        return response()->json($movie, 201);
    
    }

    public function update(UpdateMovieRequest $request, $id) {
        $movie = Movie::findOrFail($id);
        $movie->update($request->validated());
        $movie->categories()->sync($request->categories);

        return response()->json($movie);
    }

    public function destroy($id) {
        $movie = Movie::findOrFail($id);
        $movie->delete();

        return response()->json(['message' => 'Movie deleted successfully']);
    }
}