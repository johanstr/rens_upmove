<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['title', 'description', 'img', 'kcal_burned', 'difficulty', 'xp_reward'])]
class Challenge extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kcal_burned' => 'integer',
            'xp_reward' => 'integer',
        ];
    }

    /**
     * @return HasMany<ChallengeCompletion, $this>
     */
    public function completions(): HasMany
    {
        return $this->hasMany(ChallengeCompletion::class);
    }

    /**
     * Afbeelding als volledige URL: externe URL's blijven ongewijzigd,
     * paden worden relatief aan public/ opgelost.
     */
    public function imageUrl(): string
    {
        return Str::startsWith($this->img, ['http://', 'https://', '//'])
            ? $this->img
            : asset($this->img);
    }
}
