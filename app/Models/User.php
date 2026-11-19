<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['email', 'username', 'password', 'full_name', 'birth_date'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'xp' => 'integer',
            'password' => 'hashed',
        ];
    }

    /**
     * @return HasMany<Activity, $this>
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * @return HasMany<ChallengeCompletion, $this>
     */
    public function challengeCompletions(): HasMany
    {
        return $this->hasMany(ChallengeCompletion::class);
    }

    /**
     * Maximaal twee initialen voor de avatar (er is geen profielfoto).
     */
    public function initials(): string
    {
        $initials = '';

        foreach (preg_split('/\s+/', trim($this->full_name)) as $part) {
            if ($part !== '') {
                $initials .= mb_substr($part, 0, 1);
            }

            if (mb_strlen($initials) >= 2) {
                break;
            }
        }

        return $initials !== '' ? mb_strtoupper($initials) : '?';
    }
}
