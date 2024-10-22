<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FaqController extends Controller
{

    public function show($id)
    {
        $faq = Faq::find($id);

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

        $faqs = Faq::query()
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('answer', 'like', "%{$search}%")
                        ->orWhere('question', 'like', "%{$search}%");
                });
            })
            ->paginate($request->limit ?? 20);

        return response()->json(
            [
                'success' => true,
                'data' => $faqs->items(),
                'meta' => [
                    'current_page' => $faqs->currentPage(),
                    'last_page' => $faqs->lastPage(),
                    'per_page' => $faqs->perPage(),
                    'total' => $faqs->total(),
                ]
            ]
        );
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


        $faq = Faq::create($validatedData);


        return response()->json([
            'success' => true,
            'data' => $faq,
        ]);
    }


    public function update(Request $request, $id)
    {
        $faq = Faq::find($id);

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


        $faq->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'FAQ updated successfully',
            'data' => $faq
        ]);
    }

    public function destroy($id)
    {
        $faq = Faq::find($id);

        if (!$faq) {
            return response()->json([
                'success' => false,
                'message' => 'Faq not found.',
            ], 404);
        }

        $faq->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'FAQ deleted successfully'
        ]);
    }
}
