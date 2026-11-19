<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_and_account_pages_render(): void
    {
        $user = User::factory()->create(['full_name' => 'Sanne de Vries']);

        $this->actingAs($user)->get(route('settings'))->assertOk()->assertSee('Instellingen');
        $this->get(route('account'))->assertOk()->assertSee('Sanne de Vries')->assertSee('>SD<', false);
    }

    public function test_profile_can_be_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('account.profile'), ['name' => 'Nieuwe Naam', 'email' => 'nieuw@example.com'])
            ->assertRedirect(route('account'))
            ->assertSessionHas('status_message', 'Je profiel is bijgewerkt.');

        $this->assertSame('Nieuwe Naam', $user->fresh()->full_name);
        $this->assertSame('nieuw@example.com', $user->fresh()->email);
    }

    public function test_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'bezet@example.com']);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('account.profile'), ['name' => 'X', 'email' => 'bezet@example.com'])
            ->assertSessionHas('status_message', 'Dit e-mailadres is al in gebruik.');
    }

    public function test_password_can_be_changed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('account.password'), [
                'current_password' => 'fout',
                'new_password' => 'nieuwwachtwoord',
                'confirm_password' => 'nieuwwachtwoord',
            ])
            ->assertSessionHas('status_message', 'Je huidige wachtwoord is onjuist.')
            ->assertSessionHas('password_form_open', true);

        $this->put(route('account.password'), [
            'current_password' => 'password',
            'new_password' => 'nieuwwachtwoord',
            'confirm_password' => 'nieuwwachtwoord',
        ])->assertSessionHas('status_message', 'Je wachtwoord is gewijzigd.');

        $this->assertTrue(Hash::check('nieuwwachtwoord', $user->fresh()->password));
    }

    public function test_account_deletion_requires_confirmation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->delete(route('account.destroy'))
            ->assertSessionHas('status_message', 'Bevestig dat je je account permanent wilt verwijderen.');
        $this->assertModelExists($user);

        $this->delete(route('account.destroy'), ['confirm_delete' => '1'])
            ->assertRedirect(route('login'));
        $this->assertModelMissing($user);
        $this->assertGuest();
    }
}
