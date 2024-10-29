<?php

namespace App\Services\System;

use App\Models\Faq;
use App\Services\Helper\FilterService;

class FaqService
{
    public function getFaqById($id)
    {
        $faq = Faq::find($id);

        if (!$faq) {
            abort(response()->json([
                'success' => false,
                'message' => 'Faq not found.',
            ], 404));
        }

        return $faq;
    }

    public function getAllFaqs()
    {
        $query = Faq::query();


        $searchFields = ['answer', 'question'];
        $numericFields = [];
        $dateFields = ['created_at'];
        $exactMatchFields = [];
        $inFields = [];

        $faqs = FilterService::applyFilters(
            $query,
            request()->all(),
            $searchFields,
            $numericFields,
            $dateFields,
            $exactMatchFields,
            $inFields
        );


        return $faqs;
    }

    public function createFaq($validatedData)
    {
        return Faq::create($validatedData);
    }

    public function updateFaq($faq, $validatedData)
    {
        $faq->update($validatedData);
        return $faq;
    }

    public function deleteFaq($faq)
    {
        $faq->delete();
    }
}
