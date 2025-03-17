<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMovieRequest;
use App\Http\Requests\UpdateMovieRequest;
use App\Http\Resources\AccountStateResource;
use App\Http\Resources\CreditResource;
use App\Http\Resources\MovieResource;
use App\Http\Resources\ReviewResource;
use App\Models\Movie;
use App\Models\Rating;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MovieController extends Controller
{

    public function details(Request $request, $id) {
        $movie = Movie::with(['genres', 'credits.person'])->findOrFail($id);
        return new MovieResource($movie);
    }

    public function accountStates(Request $request, $id) {
        $movie = Movie::findOrFail($id);
        return new AccountStateResource($movie);
    }

    public function credits(Request $request, $id) {
        $movie = Movie::findOrFail($id);
        $credits = $movie->credits()->with('person')->get();

        return response()->json([
            'id' => $movie->id,
            'cast' => CreditResource::collection($credits->where('character', '!=', null)),
            'crew' => CreditResource::collection($credits->where('job', '!=', null)),
        ]);
    }

    public function reviews(Request $request, $id) {
        $movie = Movie::findOrFail($id);
        $reviews = $movie->reviews()->paginate(100, ['*'], 'page', $request->query('page', 1));

        return response()->json([
            'id' => $movie->id,
            'page' => $reviews->currentPage(),
            'results' => ReviewResource::collection($reviews),
            'total_pages' => $reviews->lastPage(),
            'total_results' => $reviews->total(),
        ]);
    }

    public function rate(Request $request, $id) {
        $request->validate([
            'value' => 'required|numeric|min:0.5|max:10',
        ]);

        $movie = Movie::findOrFail($id);

        Rating::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'rateable_id' => $movie->id,
                'rateable_type' => Movie::class,
            ],
            ['value' => $request->value]
        );

        return response()->json([
            'status_code' => 1,
            'status_message' => 'Success'
        ]);
    }

    public function deleteRating(Request $request, $id)
    {
        $movie = Movie::findOrFail($id);

        Rating::where('user_id', Auth::id())
            ->where('rateable_id', $movie->id)
            ->where('rateable_type', Movie::class)
            ->delete();

        return response()->json(['status_code' => 1, 'status_message' => 'Success']);
    }

    public function popular(Request $request) {
        $movies = Movie::orderBy('popularity', 'desc')
        ->with(['genres'])
        ->paginate(100, ['*'], 'page', $request->query('page', 1));

        return response()->json([
            'page' => $movies->currentPage(),
            'results' => MovieResource::collection($movies),
            'total_pages' => $movies->lastPage(),
            'total_results' => $movies->total(),
        ]);
    }

    public function nowPlaying(Request $request) {
        $movies = Movie::where('status', 'Released')
        ->where('release_date', '<=', now())
        ->orderBy('release_date', 'desc')
        ->with(['genres'])
        ->paginate(100, ['*'], 'page', $request->query('page', 1));

        return response()->json([
            'page' => $movies->currentPage(),
            'results' => MovieResource::collection($movies),
            'total_pages' => $movies->lastPage(),
            'total_results' => $movies->total(),
        ]);
    }

    public function upcoming(Request $request)
    {
        $movies = Movie::where('status', 'In Production')
            ->where('release_date', '>', now())
            ->orderBy('release_date', 'asc')
            ->with(['genres'])
            ->paginate(100, ['*'], 'page', $request->query('page', 1));

        return response()->json([
            'page' => $movies->currentPage(),
            'results' => MovieResource::collection($movies),
            'total_pages' => $movies->lastPage(),
            'total_results' => $movies->total(),
        ]);
    }

    public function topRated(Request $request)
    {
        $movies = Movie::where('vote_count', '>', 100)
            ->orderBy('vote_average', 'desc')
            ->with(['genres'])
            ->paginate(100, ['*'], 'page', $request->query('page', 1));

        return response()->json([
            'page' => $movies->currentPage(),
            'results' => MovieResource::collection($movies),
            'total_pages' => $movies->lastPage(),
            'total_results' => $movies->total(),
        ]);
    }

    public function stream(Movie $movie) {
        if (!$movie->file_path || !Storage::disk('public')->exists($movie->file_path)) {
            return response()->json(['message' => 'Movie file not found'], 404);
        }

        $path = public_path($movie->file_path);
        $size = filesize($path);
        $mime = mime_content_type($path);

        $start = 0;
        $end = $size - 1;
        $length = $size;

        $headers = [
            'Content-Type' => $mime,
            'Content-Length' => $size,
            'Accept-Ranges' => 'bytes',
        ];

        if ($range = request()->header('Range')) {
            preg_match('/bytes=(\d+)-(\d+)?/', $range, $matches);
            $start = (int) $matches[1];
            $end = isset($matches[2]) ? (int) $matches[2] : $size - 1;
            $length = $end - $start + 1;

            $headers = array_merge($headers, [
                'Content-Length' => $length,
                'Content-Range' => "bytes $start-$end/$size",
            ]);

            return response()->stream(function () use ($path, $start, $length) {
                $stream = fopen($path, 'rb');
                fseek($stream, $start);
                echo fread($stream, $length);
                fclose($stream);
            }, 206, $headers);
        }

        return response()->file($path, $headers);
    }

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
            Log::info('StoreMovieRequest Files:', $request->allFiles());
            Log::info('StoreMovieRequest Data:', $request->all());
            Log::info('Movie File:', ['movie_file' => $request->file('movie_file')]);

            $data = $request->validated();

            // Convert array fields to JSON
            $data['production_companies'] = isset($data['production_companies']) ? json_encode($data['production_companies']) : null;
            $data['production_countries'] = isset($data['production_countries']) ? json_encode($data['production_countries']) : null;
            $data['belongs_to_collection'] = isset($data['belongs_to_collection']) ? json_encode($data['belongs_to_collection']) : null;
            $data['spoken_languages'] = isset($data['spoken_languages']) ? json_encode($data['spoken_languages']) : null;

            // Store the movie file in public/movies
            $file = $request->file('movie_file');
            if (!$file instanceof \Illuminate\Http\UploadedFile || !$file->isValid()) {
                Log::error('Invalid or missing movie_file', ['file' => $file]);
                return response()->json([
                    'message' => 'Movie file is missing or invalid.',
                    'error' => 'Validation failed',
                ], 422);
            }
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = 'movies/' . $fileName;
            $file->move(public_path('movies'), $fileName);
            Log::info('Movie File Path:', ['file_path' => $filePath]);

            // Store poster image
            $posterPath = null;
            $poster = $request->file('poster');
            if ($poster instanceof \Illuminate\Http\UploadedFile && $poster->isValid()) {
                $posterName = time() . '_poster_' . $poster->getClientOriginalName();
                $posterPath = 'movies/posters/' . $posterName;
                $poster->move(public_path('movies/posters'), $posterName);
            }

            // Store backdrop image
            $backdropPath = null;
            $backdrop = $request->file('backdrop');
            if ($backdrop instanceof \Illuminate\Http\UploadedFile && $backdrop->isValid()) {
                $backdropName = time() . '_backdrop_' . $backdrop->getClientOriginalName();
                $backdropPath = 'movies/backdrops/' . $backdropName;
                $backdrop->move(public_path('movies/backdrops'), $backdropName);
            }

            // Create the movie
            $movie = Movie::create([
                'title' => $data['title'],
                'overview' => $data['overview'],
                'poster_path' => $posterPath,
                'backdrop_path' => $backdropPath,
                'release_date' => $data['release_date'],
                'vote_average' => $data['vote_average'],
                'vote_count' => $data['vote_count'],
                'adult' => filter_var($data['adult'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
                'original_language' => $data['original_language'],
                'original_title' => $data['original_title'],
                'runtime' => $data['runtime'],
                'status' => $data['status'],
                'production_companies' => $data['production_companies'],
                'production_countries' => $data['production_countries'],
                'tagline' => $data['tagline'],
                'budget' => $data['budget'],
                'revenue' => $data['revenue'],
                'homepage' => $data['homepage'],
                'belongs_to_collection' => $data['belongs_to_collection'],
                'spoken_languages' => $data['spoken_languages'],
                'imdb_id' => $data['imdb_id'],
                'popularity' => $data['popularity'],
                'video' => filter_var($data['video'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
                'file_path' => $filePath,
                'user_id' => $request->user()->id,
            ]);

            // Sync genres if provided
            if (isset($data['genres'])) {
                $movie->genres()->sync($data['genres']);
            }

            // Load relations for response
            $movie->load(['genres']);

            Log::info('Movie Created:', ['movie_id' => $movie->id, 'file_path' => $movie->file_path]);
            return new MovieResource($movie);
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