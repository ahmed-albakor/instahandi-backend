<?php

namespace App\Http\Controllers;

use App\Http\Requests\Testimonial\CreateRequest;
use App\Http\Requests\Testimonial\UpdateRequest;
use App\Services\Helper\ResponseService;
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

        return response()->json([
            'success' => true,
            'data' => $testimonial,
        ], 200);
    }

    public function index()
    {
        $testimonials = $this->testimonialService->getAllTestimonials();

        return response()->json([
            'success' => true,
            'data' => $testimonials->items(),
            'meta' => ResponseService::meta($testimonials),
        ]);
    }

    public function create(CreateRequest $request)
    {
        $validated = $request->validated();
        $user = Auth::user();
        $validated['admin_id'] = $user->id;

        $testimonial = $this->testimonialService->createTestimonial($validated, $request->file('profile_photo'));

        return response()->json([
            'success' => true,
            'message' => 'Testimonial created successfully',
            'data' => $testimonial,
        ]);
    }

    public function update(UpdateRequest $request, $id)
    {
        $testimonial = $this->testimonialService->getTestimonialById($id);

        $validated = $request->validated();
        $profilePhoto = $request->hasFile('profile_photo') ? $request->file('profile_photo') : null;

        $testimonial = $this->testimonialService->updateTestimonial($testimonial, $validated, $profilePhoto);


        return response()->json([
            'success' => true,
            'message' => 'Testimonial updated successfully',
            'data' => $testimonial,
        ]);
    }

    public function destroy($id)
    {
        $testimonial = $this->testimonialService->getTestimonialById($id);

        $this->testimonialService->deleteTestimonial($testimonial);

        return response()->json([
            'success' => true,
            'message' => 'Testimonial deleted successfully.',
        ]);
    }
}
