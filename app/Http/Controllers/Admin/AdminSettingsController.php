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
            'twilio_account_sid' => 'nullable|string',
            'twilio_auth_token' => 'nullable|string',
            'twilio_whatsapp_from' => 'nullable|string',
            'phone_number' => 'nullable|string',
        ]);

        $admin = auth()->user();
        $admin->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Twilio WhatsApp credentials updated successfully',
        ]);
    }
}
