<?php

namespace App\Http\Controllers;

use App\Services\System\TestimonialService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TestimonialController extends Controller
{
    protected $testimonialService;

    public function __construct(TestimonialService $testimonialService)
    {
        $this->testimonialService = $testimonialService;
    }

    public function show($id)
    {
        $testimonial = $this->testimonialService->getTestimonialById($id);

        if (!$testimonial) {
            return response()->json([
                'success' => false,
                'message' => 'Testimonial not found.',
            ], 404);
        }

        $testimonial->profile_photo = asset("storage/" . $testimonial->profile_photo);

        return response()->json([
            'success' => true,
            'data' => $testimonial,
        ], 200);
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $testimonials = $this->testimonialService->getAllTestimonials($search, $request->limit);

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

        $validated = $validator->validated();
        $user = Auth::user();
        $validated['admin_id'] = $user->id;

        $testimonial = $this->testimonialService->createTestimonial($validated, $request->file('profile_photo'));

        $testimonial->profile_photo = asset("storage/" . $testimonial->profile_photo);

        return response()->json([
            'success' => true,
            'message' => 'Testimonial created successfully',
            'data' => $testimonial,
        ]);
    }

    public function update(Request $request, $id)
    {
        $testimonial = $this->testimonialService->getTestimonialById($id);

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
        $profilePhoto = $request->hasFile('profile_photo') ? $request->file('profile_photo') : null;

        $testimonial = $this->testimonialService->updateTestimonial($testimonial, $validated, $profilePhoto);

        $testimonial->profile_photo = asset("storage/" . $testimonial->profile_photo);

        return response()->json([
            'success' => true,
            'message' => 'Testimonial updated successfully',
            'data' => $testimonial,
        ]);
    }

    public function destroy($id)
    {
        $testimonial = $this->testimonialService->getTestimonialById($id);

        if (!$testimonial) {
            return response()->json([
                'success' => false,
                'message' => 'Testimonial not found.',
            ], 404);
        }

        $this->testimonialService->deleteTestimonial($testimonial);

        return response()->json([
            'success' => true,
            'message' => 'Testimonial deleted successfully.',
        ]);
    }
}
