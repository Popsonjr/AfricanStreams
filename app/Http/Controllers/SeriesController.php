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
                'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:4096'
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->only(['title', 'description', 'cover_image']);

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
            Series::find($series);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error fetching series');
        }
    } 

    public function show(Request $request, Series $series) {
        try {

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error fetching series');
        }
    } 

    public function show(Request $request, Series $series) {
        try {

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error fetching series');
        }
    }
}