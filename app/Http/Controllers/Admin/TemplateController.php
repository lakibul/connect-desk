<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MessageTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TemplateController extends Controller
{
    /**
     * List all templates.
     */
    public function index(): View
    {
        $templates = MessageTemplate::orderBy('name')->get();

        return view('admin.templates.index', compact('templates'));
    }

    /**
     * Store a new template.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:100|unique:message_templates,name|regex:/^[a-z0-9_]+$/',
            'label'     => 'required|string|max:255',
            'content'   => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $template = MessageTemplate::create($validated);

        return response()->json([
            'success'  => true,
            'message'  => 'Template created successfully.',
            'template' => $template,
        ]);
    }

    /**
     * Update an existing template.
     */
    public function update(Request $request, MessageTemplate $template): JsonResponse
    {
        $validated = $request->validate([
            'name'      => "required|string|max:100|unique:message_templates,name,{$template->id}|regex:/^[a-z0-9_]+$/",
            'label'     => 'required|string|max:255',
            'content'   => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $template->update($validated);

        return response()->json([
            'success'  => true,
            'message'  => 'Template updated successfully.',
            'template' => $template->fresh(),
        ]);
    }

    /**
     * Delete a template.
     */
    public function destroy(MessageTemplate $template): JsonResponse
    {
        $template->delete();

        return response()->json([
            'success' => true,
            'message' => 'Template deleted successfully.',
        ]);
    }

    /**
     * Toggle active status.
     */
    public function toggleActive(MessageTemplate $template): JsonResponse
    {
        $template->update(['is_active' => ! $template->is_active]);

        return response()->json([
            'success'   => true,
            'is_active' => $template->is_active,
        ]);
    }
}
