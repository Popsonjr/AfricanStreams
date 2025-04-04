<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class UpdateMovieRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        Log::info('Validation rules applied');
        return [
            'title' => 'nullable|string|max:255',
            'overview' => 'nullable|string',
            // 'poster_path' => 'nullable|string|max:255',
            // 'backdrop_path' => 'nullable|string|max:255',
            'poster' => 'nullable|file|mimes:jpg,png,jpeg|max:50120', // Max 50MB
            'backdrop' => 'nullable|file|mimes:jpg,png,jpeg|max:50120',
            'release_date' => 'nullable|date',
            'vote_average' => 'nullable|numeric|min:0|max:10',
            'vote_count' => 'nullable|integer|min:0',
            'adult' => 'nullable|in:true,false,1,0',
            'original_language' => 'nullable|string|max:10',
            'original_title' => 'nullable|string|max:255',
            'runtime' => 'nullable|integer|min:0',
            'status' => 'nullable|string|in:released,post production,in production,canceled',
            'production_companies' => 'nullable|array',
            'production_companies.*.id' => 'nullable|integer',
            'production_companies.*.name' => 'nullable|string|max:255',
            'production_companies.*.logo_path' => 'nullable|string|max:255',
            'production_companies.*.origin_country' => 'nullable|string|max:2',
            'production_countries' => 'nullable|array',
            'production_countries.*.iso_3166_1' => 'nullable|string|max:2',
            'production_countries.*.name' => 'nullable|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'budget' => 'nullable|integer|min:0',
            'revenue' => 'nullable|integer|min:0',
            'homepage' => 'nullable|url',
            'belongs_to_collection' => 'nullable|array',
            'belongs_to_collection.id' => 'nullable|integer',
            'belongs_to_collection.name' => 'nullable|string|max:255',
            'belongs_to_collection.poster_path' => 'nullable|string|max:255',
            'belongs_to_collection.backdrop_path' => 'nullable|string|max:255',
            'spoken_languages' => 'nullable|array',
            'spoken_languages.*.iso_639_1' => 'nullable|string|max:2',
            'spoken_languages.*.name' => 'nullable|string|max:255',
            'imdb_id' => 'nullable|string|max:20',
            'popularity' => 'nullable|numeric|min:0',
            'video' => 'nullable|in:true,false,1,0',
            'genres' => 'nullable|array',
            'genres.*' => 'exists:genres,id',
            'movie_file' => 'nullable|file|mimes:mp4,avi,mov|max:4096000',
            // 'trailer_url' => 'required|string',
            'trailer_url' => 'nullable|file|mimes:mp4,avi,mov|max:4096000',
        ];
    }
}