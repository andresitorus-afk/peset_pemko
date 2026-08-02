<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatbotFaq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotFaqController extends Controller
{
    public function index(Request $request)
    {
        return ChatbotFaq::query()
            ->when($request->search, fn ($q, $s) => $q->where('answer', 'ilike', "%{$s}%"))
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 15));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'keywords' => 'required|array|min:1',
            'keywords.*' => 'required|string|max:100',
            'answer' => 'required|string|max:2000',
            'aktif' => 'nullable|boolean',
        ]);

        $faq = ChatbotFaq::create($validated);

        return response()->json($faq, 201);
    }

    public function show(ChatbotFaq $faq): JsonResponse
    {
        return response()->json($faq);
    }

    public function update(Request $request, ChatbotFaq $faq): JsonResponse
    {
        $validated = $request->validate([
            'keywords' => 'sometimes|required|array|min:1',
            'keywords.*' => 'required|string|max:100',
            'answer' => 'sometimes|required|string|max:2000',
            'aktif' => 'nullable|boolean',
        ]);

        $faq->update($validated);

        return response()->json($faq);
    }

    public function destroy(ChatbotFaq $faq): JsonResponse
    {
        $faq->delete();

        return response()->json(['message' => 'Berhasil dihapus.']);
    }
}
