<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class FrontendController extends Controller
{
    /**
     * Show the frontend landing page
     */
    public function index(): View
    {
        $user = auth()->user();

        return view('frontend', [
            'user' => $user,
            'isAuthenticated' => auth()->check()
        ]);
    }
}
