<?php

namespace App\Http\Controllers;

use App\Services\System\FaqService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FaqController extends Controller
{
    protected $faqService;

    public function __construct(FaqService $faqService)
    {
        $this->faqService = $faqService;
    }

    public function show($id)
    {
        $faq = $this->faqService->getFaqById($id);

        if (!$faq) {
            return response()->json([
                'success' => false,
                'message' => 'Faq not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $faq
        ]);
    }

    public function index(Request $request)
    {
        $search = $request->input('search');

        $faqs = $this->faqService->getAllFaqs($search, $request->limit);

        return response()->json([
            'success' => true,
            'data' => $faqs->items(),
            'meta' => [
                'current_page' => $faqs->currentPage(),
                'last_page' => $faqs->lastPage(),
                'per_page' => $faqs->perPage(),
                'total' => $faqs->total(),
            ]
        ]);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question' => 'required|string',
            'answer' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $validatedData = $validator->validated();
        $user = Auth::user();
        $validatedData['admin_id'] = $user->id;

        $faq = $this->faqService->createFaq($validatedData);

        return response()->json([
            'success' => true,
            'data' => $faq,
        ]);
    }

    public function update(Request $request, $id)
    {
        $faq = $this->faqService->getFaqById($id);

        if (!$faq) {
            return response()->json([
                'success' => false,
                'message' => 'Faq not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'question' => 'nullable|string',
            'answer' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $validatedData = $validator->validated();
        $faq = $this->faqService->updateFaq($faq, $validatedData);

        return response()->json([
            'success' => true,
            'message' => 'FAQ updated successfully',
            'data' => $faq
        ]);
    }

    public function destroy($id)
    {
        $faq = $this->faqService->getFaqById($id);

        if (!$faq) {
            return response()->json([
                'success' => false,
                'message' => 'Faq not found.',
            ], 404);
        }

        $this->faqService->deleteFaq($faq);

        return response()->json([
            'success' => true,
            'message' => 'FAQ deleted successfully'
        ]);
    }
}
