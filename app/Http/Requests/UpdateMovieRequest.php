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
            'description' => 'nullable|string',
            'release_date' => 'nullable|date',
            'duration' => 'nullable|string|max:50',
            'cast' => 'nullable|string',
            'genre_id' => 'nullable|exists:genres,id',
            'trailer_uri' => 'nullable|url',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:4096',
            'standard_image' => 'nullable|image|mimes:jpeg,png,jpg|max:4096',
            'thumbnail_image' => 'nullable|image|mimes:jpeg,png,jpg|max:4096',
            'type' => 'nullable|in:movie,series',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'related_movie_ids' => 'nullable|array',
            'related_movie_ids.*' => 'exists:movies,id',
        ];
    }
}