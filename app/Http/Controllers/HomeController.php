<?php

namespace App\Http\Controllers;

use App\Services\ChallengeService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(Request $request, ChallengeService $challenges): View
    {
        $user = $request->user()->refresh();
        $current = $challenges->current($user);

        return view('home', [
            'user' => $user,
            'selectedChallenge' => $current['challenge'],
            'challengeCompleted' => $current['completed'],
            'noDifferentChallengeAvailable' => $current['noDifferentAvailable'],
            'challengeChangeAt' => $challenges->nextChangeAt(),
            'challengeChangesRemaining' => $challenges->changesRemaining($user),
            'challengeNotice' => session('challenge_notice', ''),
            'challengeCompletionNotice' => session('challenge_completion_notice', ''),
        ]);
    }
}
