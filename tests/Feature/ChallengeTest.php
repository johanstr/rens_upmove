<?php

namespace Tests\Feature;

use App\Models\Challenge;
use App\Models\User;
use App\Services\ChallengeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChallengeTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_shows_a_challenge_from_the_migration_data(): void
    {
        $user = User::factory()->create(['full_name' => 'Sanne']);

        $response = $this->actingAs($user)->get(route('home'))->assertOk();

        $saved = session('home_challenge');
        $challenge = Challenge::query()->findOrFail($saved['id']);

        $response->assertSee('Hé, Sanne', false)
            ->assertSee($challenge->title)
            ->assertSee("{$challenge->xp_reward} XP te", false)
            ->assertSee('Je kunt vandaag nog 2 keer direct wisselen.');
    }

    public function test_completing_a_challenge_awards_xp_only_once_per_interval(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('home'));
        $challenge = Challenge::query()->findOrFail(session('home_challenge')['id']);

        $this->post(route('challenges.complete'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('challenge_completion_notice', "Opdracht voltooid! Je hebt {$challenge->xp_reward} XP verdiend.");

        $this->post(route('challenges.complete'))
            ->assertSessionHas('challenge_completion_notice', 'Je hebt deze opdracht al voltooid.');

        $this->assertSame($challenge->xp_reward, $user->fresh()->xp);
        $this->get(route('home'))->assertSee("Totaal: {$challenge->xp_reward} XP", false);
    }

    public function test_challenge_can_be_changed_twice_per_day(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('home'));
        $first = session('home_challenge')['id'];

        $this->post(route('challenges.next'))
            ->assertSessionHas('challenge_notice', 'Nieuwe opdracht geladen. Je kunt dit vandaag nog 1 keer doen.');
        $this->assertNotSame($first, session('home_challenge')['id']);

        $this->post(route('challenges.next'))
            ->assertSessionHas('challenge_notice', 'Nieuwe opdracht geladen. Je hebt vandaag geen directe opdrachtwissels meer.');

        $this->post(route('challenges.next'))
            ->assertSessionHas('challenge_notice', 'Je hebt je 2 directe opdrachtwissels voor vandaag al gebruikt.');
    }

    public function test_completion_fails_when_interval_has_passed(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('home'));

        $this->travel(ChallengeService::INTERVAL_SECONDS)->seconds();

        $this->post(route('challenges.complete'))
            ->assertSessionHas('challenge_completion_notice', 'Deze opdracht is niet meer actief. Ververs de pagina en probeer het opnieuw.');
        $this->assertSame(0, $user->fresh()->xp);
    }

    public function test_new_interval_picks_a_different_challenge(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('home'));
        $first = session('home_challenge')['id'];

        $this->travel(ChallengeService::INTERVAL_SECONDS)->seconds();
        $this->get(route('home'));

        $this->assertNotSame($first, session('home_challenge')['id']);
    }
}
