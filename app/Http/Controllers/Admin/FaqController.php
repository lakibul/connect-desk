<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqController extends Controller
{
    /**
     * List all FAQs.
     */
    public function index(): View
    {
        $faqs = Faq::orderBy('sort_order')->orderBy('id')->get();

        return view('admin.faqs.index', compact('faqs'));
    }

    /**
     * Store a new FAQ.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question'   => 'required|string|max:255',
            'answer'     => 'required|string',
            'payload'    => 'required|string|max:100|unique:faqs,payload|regex:/^[a-z0-9_]+$/',
            'sort_order' => 'nullable|integer|min:0',
            'is_active'  => 'nullable|boolean',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active']  = $request->boolean('is_active', true);

        $faq = Faq::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'FAQ created successfully.',
            'faq'     => $faq,
        ]);
    }

    /**
     * Update an existing FAQ.
     */
    public function update(Request $request, Faq $faq): JsonResponse
    {
        $validated = $request->validate([
            'question'   => 'required|string|max:255',
            'answer'     => 'required|string',
            'payload'    => "required|string|max:100|unique:faqs,payload,{$faq->id}|regex:/^[a-z0-9_]+$/",
            'sort_order' => 'nullable|integer|min:0',
            'is_active'  => 'nullable|boolean',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active']  = $request->boolean('is_active', true);

        $faq->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'FAQ updated successfully.',
            'faq'     => $faq->fresh(),
        ]);
    }

    /**
     * Delete an FAQ.
     */
    public function destroy(Faq $faq): JsonResponse
    {
        $faq->delete();

        return response()->json([
            'success' => true,
            'message' => 'FAQ deleted successfully.',
        ]);
    }

    /**
     * Toggle active status.
     */
    public function toggleActive(Faq $faq): JsonResponse
    {
        $faq->update(['is_active' => ! $faq->is_active]);

        return response()->json([
            'success'   => true,
            'is_active' => $faq->is_active,
        ]);
    }

    /**
     * Reorder FAQs (drag-drop or sort_order updates).
     * Expects: { "order": [{"id": 1, "sort_order": 0}, ...] }
     */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'order'              => 'required|array',
            'order.*.id'         => 'required|integer|exists:faqs,id',
            'order.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($request->input('order') as $item) {
            Faq::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['success' => true]);
    }
}
