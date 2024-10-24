<?php

namespace App\Services\System;

use App\Models\Faq;

class FaqService
{
    public function getFaqById($id)
    {
        return Faq::find($id);
    }

    public function getAllFaqs($search, $limit)
    {
        return Faq::query()
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('answer', 'like', "%{$search}%")
                        ->orWhere('question', 'like', "%{$search}%");
                });
            })
            ->paginate($limit ?? 20);
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
