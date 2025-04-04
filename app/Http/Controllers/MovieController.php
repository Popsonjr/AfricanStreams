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
use App\Models\WatchHistory;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
// use Intervention\Image\Facades\Image;

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

    public function stream(Movie $movie, Request $request) {
        if (!$request->user()->subscriptions()->where('status', 'active')->exists()) {
            return response()->json(['message' => 'Active subscription required'], 403);
        }
        
        if (!$movie->file_path || !Storage::disk('public')->exists($movie->file_path)) {
            return response()->json(['message' => 'Movie file not found'], 404);
        }

        // Record watch history
            $watchHistory = WatchHistory::updateOrCreate(
                [
                    'user_id' => $request->user()->id,
                    'movie_id' => $movie->id,
                ],
                [
                    'watched_at' => now(),
                    'progress' => 0, // Reset progress if resuming
                ]
            );

            Log::info('Watch history recorded', [
                'user_id' => $request->user()->id,
                'movie_id' => $movie->id,
                'watch_history_id' => $watchHistory->id,
            ]);

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
            // Check for active subscription
            // if (!$request->user()->subscriptions()->where('status', 'active')->exists()) {
            //     return response()->json(['message' => 'Active subscription required'], 403);
            // }

            // Get sort field (default to created_at)
            $sortField = $request->query('sort', 'created_at');
            $allowedSortFields = ['created_at', 'popularity', 'title', 'release_date'];
            
            // Validate sort field
            if (!in_array($sortField, $allowedSortFields)) {
                $sortField = 'created_at';
            }

            // Fetch movies sorted by the specified field in descending order
            $movies = Movie::orderBy($sortField, 'desc')
                ->with(['genres'])
                ->paginate(50, ['*'], 'page', $request->query('page', 1));

            return response()->json([
                'page' => $movies->currentPage(),
                'results' => MovieResource::collection($movies),
                'total_pages' => $movies->lastPage(),
                'total_results' => $movies->total(),
            ]);

            
            // $query = Movie::with('categories', 'genre', 'seasons', 'relatedMovies');

            // if($request->has('category')) {
            //     $query->whereHas('categories', function ($q) use ($request) {
            //         $q->where('slug', $request->category);
            //     });
            // }

            // if($request->has('genre')) {
            //     $query->whereHas('genre', function($q) use ($request) {
            //         $q->where('slug', $request->genre);
            //     });
            // }

            // if($request->has('season')) {
            //     $query->whereHas('seasons', function ($q) use($request) {
            //         $q->where('season_number', $request->season);
            //     });
            // }

            // if ($request->has('search')) {
            //     $query->where('title', 'LIKE', '%' . $request->search . '%');
            // }

            // if($request->has('type')) {
            //     $query->where('type', $request->type);
            // }

            // $movies = $query->paginate(100);
            // return response()->json($movies);
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
            // Log::info('StoreMovieRequest Files:', $request->allFiles());
            Log::info('StoreMovieRequest Data:', $request->all());
            // Log::info('trailer Url :', ['trailer_url' => $request->trailer_url]);

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

            // Store the movie file in public/movies
            $trailerFile = $request->file('trailer_url');
            if (!$trailerFile instanceof \Illuminate\Http\UploadedFile || !$trailerFile->isValid()) {
                Log::error('Invalid or missing trailer_url', ['file' => $trailerFile]);
                return response()->json([
                    'message' => 'Trailer Url is missing or invalid.',
                    'error' => 'Validation failed',
                ], 422);
            }
            $trailerFileName = time() . '_' . $trailerFile->getClientOriginalName();
            $trailerFilePath = 'trailers/' . $trailerFileName;
            $trailerFile->move(public_path('trailers'), $trailerFileName);
            Log::info('Trailer File Path:', ['file_path' => $trailerFilePath]);

            // Store poster image
            $posterPath = null;
            $poster = $request->file('poster');
            if ($poster instanceof \Illuminate\Http\UploadedFile && $poster->isValid()) {
                $posterName = time() . '_poster_' . $poster->getClientOriginalName();
                $posterPath = 'movies/posters/' . $posterName;

                $originalDir = public_path('movies/posters');
                if (!file_exists($originalDir)) {
                    mkdir($originalDir, 0755, true);
                }
                $poster->move($originalDir, $posterName);

                // Create w500 and w720 versions
                $this->resizeImageVariants($posterPath);
            }

            // Store backdrop image
            $backdropPath = null;
            $backdrop = $request->file('backdrop');
            if ($backdrop instanceof \Illuminate\Http\UploadedFile && $backdrop->isValid()) {
                $backdropName = time() . '_backdrop_' . $backdrop->getClientOriginalName();
                $backdropPath = 'movies/backdrops/' . $backdropName;

                $originalDir = public_path('movies/backdrops');
                if (!file_exists($originalDir)) {
                    mkdir($originalDir, 0755, true);
                }
                $backdrop->move($originalDir, $backdropName);

                // Create w500 and w720 versions
                $this->resizeImageVariants($backdropPath);
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
                'trailer_url' => $trailerFilePath,
                'user_id' => $request->user()->id,
            ]);

            // Sync genres if provided
            if (isset($data['genres'])) {
                $movie->genres()->sync($data['genres']);
            }

            // Load relations for response
            $movie->load(['genres']);

            Log::info('Movie Created:', ['movie_id' => $movie->id, 'file_path' => $movie->trailer_url]);
            return new MovieResource($movie);
        } catch(Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'Error creating new movie',
            ], 500);
        }
        
    }

    private function resizeImageVariants($relativePath) {
        $sourcePath = public_path($relativePath);
        $fileName = basename($relativePath);
        $subPath = dirname($relativePath);
    
        // Determine image type
        $mime = mime_content_type($sourcePath);
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $createFunc = 'imagecreatefromjpeg';
                $saveFunc = 'imagejpeg';
                $extension = 'jpg';
                break;
            case 'image/png':
                $createFunc = 'imagecreatefrompng';
                $saveFunc = 'imagepng';
                $extension = 'png';
                break;
            default:
                throw new \Exception("Unsupported image format: $mime");
        }
    
        foreach ([500, 720] as $targetWidth) {
            $variantDir = public_path("w{$targetWidth}/" . $subPath);
            if (!file_exists($variantDir)) {
                mkdir($variantDir, 0755, true);
            }
    
            $destinationPath = $variantDir . '/' . $fileName;
    
            list($originalWidth, $originalHeight) = getimagesize($sourcePath);
            $targetHeight = intval($targetWidth * ($originalHeight / $originalWidth));
    
            $src = $createFunc($sourcePath);
            $resized = imagecreatetruecolor($targetWidth, $targetHeight);
    
            // Preserve PNG transparency
            if ($mime === 'image/png') {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
            }
    
            imagecopyresampled($resized, $src, 0, 0, 0, 0, $targetWidth, $targetHeight, $originalWidth, $originalHeight);
            $saveFunc($resized, $destinationPath);
    
            imagedestroy($src);
            imagedestroy($resized);
        }
    }

    public function update(UpdateMovieRequest $request, Movie $movie) {
        try {
            Log::info('UpdateMovieRequest Data:', $request->all());
            
            $data = $request->validated();

            // Convert array fields to JSON
            if (isset($data['production_companies'])) {
                $data['production_companies'] = json_encode($data['production_companies']);
            }
            if (isset($data['production_countries'])) {
                $data['production_countries'] = json_encode($data['production_countries']);
            }
            if (isset($data['belongs_to_collection'])) {
                $data['belongs_to_collection'] = json_encode($data['belongs_to_collection']);
            }
            if (isset($data['spoken_languages'])) {
                $data['spoken_languages'] = json_encode($data['spoken_languages']);
            }

            // Handle movie file upload
            if ($request->hasFile('movie_file')) {
                $file = $request->file('movie_file');
                if ($file instanceof \Illuminate\Http\UploadedFile && $file->isValid()) {
                    // Delete old file if exists
                    if ($movie->file_path && file_exists(public_path($movie->file_path))) {
                        unlink(public_path($movie->file_path));
                    }
                    
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $filePath = 'movies/' . $fileName;
                    $file->move(public_path('movies'), $fileName);
                    $data['file_path'] = $filePath;
                }
            }

            // Handle movie file upload
            if ($request->hasFile('trailer_url')) {
                $trailerFile = $request->file('trailer_url');
                if ($trailerFile instanceof \Illuminate\Http\UploadedFile && $trailerFile->isValid()) {
                    // Delete old file if exists
                    if ($movie->trailer_url && file_exists(public_path($movie->trailer_url))) {
                        unlink(public_path($movie->trailer_url));
                    }
                    
                    $trailerFileName = time() . '_' . $trailerFile->getClientOriginalName();
                    $trailerFilePath = 'trailers/' . $trailerFileName;
                    $trailerFile->move(public_path('trailers'), $trailerFileName);
                    $data['trailer_url'] = $trailerFilePath;
                }
            }

            // Handle poster upload
            if ($request->hasFile('poster')) {
                $poster = $request->file('poster');
                if ($poster instanceof \Illuminate\Http\UploadedFile && $poster->isValid()) {
                    // Delete old poster and its variants if they exist
                    if ($movie->poster_path) {
                        $this->deleteImageVariants($movie->poster_path);
                    }
                    
                    $posterName = time() . '_poster_' . $poster->getClientOriginalName();
                    $posterPath = 'movies/posters/' . $posterName;
                    
                    $originalDir = public_path('movies/posters');
                    if (!file_exists($originalDir)) {
                        mkdir($originalDir, 0755, true);
                    }
                    $poster->move($originalDir, $posterName);
                    
                    // Create resized variants
                    $this->resizeImageVariants($posterPath);
                    $data['poster_path'] = $posterPath;
                }
            }

            // Handle backdrop upload
            if ($request->hasFile('backdrop')) {
                $backdrop = $request->file('backdrop');
                if ($backdrop instanceof \Illuminate\Http\UploadedFile && $backdrop->isValid()) {
                    // Delete old backdrop and its variants if they exist
                    if ($movie->backdrop_path) {
                        $this->deleteImageVariants($movie->backdrop_path);
                    }
                    
                    $backdropName = time() . '_backdrop_' . $backdrop->getClientOriginalName();
                    $backdropPath = 'movies/backdrops/' . $backdropName;
                    
                    $originalDir = public_path('movies/backdrops');
                    if (!file_exists($originalDir)) {
                        mkdir($originalDir, 0755, true);
                    }
                    $backdrop->move($originalDir, $backdropName);
                    
                    // Create resized variants
                    $this->resizeImageVariants($backdropPath);
                    $data['backdrop_path'] = $backdropPath;
                }
            }

            // Handle boolean fields
            if (isset($data['adult'])) {
                $data['adult'] = filter_var($data['adult'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
            }
            if (isset($data['video'])) {
                $data['video'] = filter_var($data['video'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
            }

            // Update the movie
            $movie->update($data);

            // Sync genres if provided
            if (isset($data['genres'])) {
                $movie->genres()->sync($data['genres']);
            }

            // Load relations for response
            $movie->load(['genres']);

            Log::info('Movie Updated:', ['movie_id' => $movie->id]);
            return new MovieResource($movie);
        } catch(Exception $e) {
            Log::error('Error updating movie:', [
                'movie_id' => $movie->id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'Error while updating movie',
            ], 500);
        }
    }

    private function deleteImageVariants($relativePath) {
        $fileName = basename($relativePath);
        $subPath = dirname($relativePath);
        
        // Delete original file
        if (file_exists(public_path($relativePath))) {
            unlink(public_path($relativePath));
        }
        
        // Delete w500 and w720 variants
        foreach ([500, 720] as $width) {
            $variantPath = public_path("w{$width}/" . $subPath . '/' . $fileName);
            if (file_exists($variantPath)) {
                unlink($variantPath);
            }
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

    /**
     * Batch delete movies (admin only)
     * Expects: { "ids": [1,2,3] }
     */
    public function batchDestroy(Request $request)
    {
        // Authorize admin (adjust as needed for your app)
        // if (!$request->user() || !$request->user()->is_admin) {
        //     return response()->json(['error' => 'Unauthorized'], 403);
        // }

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:movies,id',
        ]);

        $ids = $request->input('ids');
        $deleted = [];
        $failed = [];

        $movies = Movie::whereIn('id', $ids)->get();
        foreach ($movies as $movie) {
            try {
                // Delete associated files
                if ($movie->file_path && file_exists(public_path($movie->file_path))) {
                    unlink(public_path($movie->file_path));
                }
                if ($movie->trailer_url && file_exists(public_path($movie->trailer_url))) {
                    unlink(public_path($movie->trailer_url));
                }
                if ($movie->poster_path) {
                    $this->deleteImageVariants($movie->poster_path);
                }
                if ($movie->backdrop_path) {
                    $this->deleteImageVariants($movie->backdrop_path);
                }
                // Detach relationships
                $movie->genres()->detach();
                if (method_exists($movie, 'categories')) {
                    $movie->categories()->detach();
                }
                if (method_exists($movie, 'relatedMovies')) {
                    $movie->relatedMovies()->detach();
                }
                // Delete the movie
                if($movie->delete()) {
                    $deleted[] = $movie->id;
                } else {
                    $failed[] = $movie->id;
                }
                
            } catch (\Exception $e) {
                $failed[] = [
                    'id' => $movie->id,
                    'error' => $e->getMessage(),
                ];
            }
        }
        return response()->json([
            'message' => 'Batch movies deleted successfully',
            'deleted' => $deleted,
            'failed' => $failed,
        ]);
    }
}