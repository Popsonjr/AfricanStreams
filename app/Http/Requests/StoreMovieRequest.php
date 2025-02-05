<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMovieRequest extends FormRequest
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
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'release_date' => 'nullable|date',
            'duration' => 'nullable|string|max:50',
            'cast' => 'nullable|string',
            'genre_id' => 'required|exists:genres,id',
            'trailer_uri' => 'nullable|url',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg|max:40960',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:40960',
            'standard_image' => 'nullable|image|mimes:jpeg,png,jpg|max:40960',
            'thumbnail_image' => 'nullable|image|mimes:jpeg,png,jpg|max:40960',
            'movie_file' => 'nullable|mimes:mp4,mkv,avi,mov,webm,mpeg,mpg,ogv,3gp|max:5096000',
            'type' => 'required|in:movie,series',
            'category_ids' => 'array',
            'category_ids.*' => 'exists:categories,id',
            'related_movie_ids' => 'array',
            'related_movie_ids.*' => 'exists:movies,id',
        ];
    }
}