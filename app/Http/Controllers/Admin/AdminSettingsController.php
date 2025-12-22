<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminSettingsController extends Controller
{
    public function index()
    {
        $admin = auth()->user();
        return view('admin.settings', compact('admin'));
    }

    public function updateWhatsAppCredentials(Request $request)
    {
        $validated = $request->validate([
            'whatsapp_access_token' => 'required|string',
            'whatsapp_phone_number_id' => 'required|string',
            'phone_number' => 'nullable|string',
        ]);

        $admin = auth()->user();
        $admin->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'WhatsApp credentials updated successfully',
        ]);
    }
}
