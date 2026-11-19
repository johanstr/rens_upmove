<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * De instellingen zelf worden client-side in localStorage bewaard
     * (zie resources/js/settings-page.js).
     */
    public function index(Request $request): View
    {
        return view('settings.index', ['user' => $request->user()]);
    }
}
