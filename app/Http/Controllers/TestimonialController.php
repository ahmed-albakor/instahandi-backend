<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use App\Models\Image;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TestimonialController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $testimonials = Testimonial::query()
            ->when($search, function ($query, $search) {
                return $query->where('message', 'like', "%{$search}%")
                    ->orWhere('client_name', 'like', "%{$search}%")
                    ->orWhere('job', 'like', "%{$search}%");
            })
            ->paginate(10);


        return response()->json([
            'success' => true,
            'data' => $testimonials->items(),
            'meta' => [
                'current_page' => $testimonials->currentPage(),
                'last_page' => $testimonials->lastPage(),
                'per_page' => $testimonials->perPage(),
                'total' => $testimonials->total(),
            ]
        ]);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string',
            'rating' => 'required|integer|between:1,5',
            'client_name' => 'required|string|max:255',
            'job' => 'required|string|max:255',
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();


        $validated = $validator->validated();

        $testimonial = Testimonial::create([
            'message' => $validated['message'],
            'rating' => $validated['rating'],
            'client_name' => $validated['client_name'],
            'job' => $validated['job'],
            'profile_photo' => ' ',
            'admin_id' => $user->id,
        ]);

        $path = ImageService::storeImage($request->file('profile_photo'), 'photos_testimonials', $testimonial->id);

        $testimonial->update(['profile_photo' => $path]);

        return response()->json([
            'success' => true,
            'message' => 'Testimonial created successfully',
            'data' => $testimonial,
        ]);
    }

    public function update(Request $request, $id)
    {
        $testimonial = Testimonial::find($id);

        if (!$testimonial) {
            return response()->json([
                'success' => false,
                'message' => 'Testimonial not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'message' => 'nullable|string',
            'rating' => 'nullable|integer|between:1,5',
            'client_name' => 'nullable|string|max:255',
            'job' => 'nullable|string|max:255',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        if ($request->hasFile('profile_photo')) {
            $path = ImageService::storeImage($request->file('profile_photo'), 'photos_testimonials', $testimonial->code);
            $validated['profile_photo'] = $path;
        }

        $testimonial->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Testimonial updated successfully',
            'data' => $testimonial,
        ]);
    }

    public function destroy($id)
    {
        $testimonial = Testimonial::find($id);

        if (!$testimonial) {
            return response()->json([
                'success' => false,
                'message' => 'Testimonial not found.',
            ], 404);
        }

        $testimonial->delete();

        return response()->json([
            'success' => true,
            'message' => 'Testimonial deleted successfully.',
        ]);
    }
}
