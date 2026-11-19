<?php

namespace App\Http\Controllers;

use App\Services\ChallengeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ChallengeController extends Controller
{
    public function __construct(private readonly ChallengeService $challenges) {}

    public function next(Request $request): RedirectResponse
    {
        return redirect()->route('home')
            ->with('challenge_notice', $this->challenges->next($request->user()));
    }

    public function complete(Request $request): RedirectResponse
    {
        return redirect()->route('home')
            ->with('challenge_completion_notice', $this->challenges->complete($request->user()));
    }
}
