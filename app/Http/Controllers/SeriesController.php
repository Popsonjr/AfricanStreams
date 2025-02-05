<?php

namespace App\Http\Controllers;

use App\Traits\HelperTrait;

use App\Models\Series;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SeriesController extends BaseController
{
    use HelperTrait;
    
    public function index(Request $request) {
        try {
        $query = Series::with('seasons.episodes')->get();
        if ($request->has('search')) {
            $query->where('title', 'LIKE', '%' . $request->search . '%');
        }
        $series = $query->paginate(10);
        return $this->successResponse($series, 'All series retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error fetching all series');
        }
    }

    public function store(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'banner_image' => 'nullable|image|mimes:jpeg,png,jpg|max:40960',
                'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:40960'
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->only(['title', 'description', 'cover_image']);

            if ($request->hasFile('banner_image')) {
                $data['banner_image'] = $this->storeFile($request->file('banner_image'),'series/banner');
            }
            if($request->hasFile('cover_image')) {
                $data['cover_image'] = $this->storeFile($request->file('cover_image'), 'series/cover');
            }
            $series = Series::create($data);
            return $this->successResponse($series, 'Series created successfully', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error storing new series');
        }
    } 

    public function show(Request $request, Series $series) {
        try {
            $this->successResponse($series->load('seasons.episodes'), 'Series retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error fetching series');
        }
    } 

    public function update(Request $request, Series $series) {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'banner_image' => 'nullable|image|mimes:jpeg,png,jpg|max:40960',
                'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:40960'
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = array_filter($request->only(['title', 'description']), fn($value) => !is_null($value));

            if($request->hasFile('cover_image')) {
                $data['cover_image'] = $this->storeFile($request->file('cover_image'), 'series/cover');
            }

            $series->update();
            return $this->successResponse($series, 'Series updated successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error updating series');
        }
    } 

    public function destroy(Series $series) {
        try {
            $series->delete();
            return $this->successResponse('', 'Series deleted successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error deleting series');
        }
    }
}