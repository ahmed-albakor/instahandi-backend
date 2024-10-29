<?php

namespace App\Http\Controllers;

use App\Http\Requests\Faq\CreateRequest;
use App\Http\Requests\Faq\UpdateRequest;
use App\Services\Helper\ResponseService;
use App\Services\System\FaqService;
use Illuminate\Support\Facades\Auth;

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

        return response()->json([
            'success' => true,
            'data' => $faq
        ]);
    }

    public function index()
    {
        $faqs = $this->faqService->getAllFaqs();

        return response()->json([
            'success' => true,
            'data' => $faqs->items(),
            'meta' => ResponseService::meta($faqs),
        ]);
    }

    public function create(CreateRequest $request)
    {
        $validatedData = $request->validated();
        $user = Auth::user();
        $validatedData['admin_id'] = $user->id;

        $faq = $this->faqService->createFaq($validatedData);

        return response()->json([
            'success' => true,
            'data' => $faq,
        ]);
    }

    public function update(UpdateRequest $request, $id)
    {
        $faq = $this->faqService->getFaqById($id);

        $validatedData = $request->validated();

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

        $this->faqService->deleteFaq($faq);

        return response()->json([
            'success' => true,
            'message' => 'FAQ deleted successfully'
        ]);
    }
}
