<?php

namespace App\Models;

use Database\Factories\ActivityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['title', 'description', 'image_path'])]
class Activity extends Model
{
    /** @use HasFactory<ActivityFactory> */
    use HasFactory;

    /**
     * Afbeeldingen staan op de private "local"-schijf en worden via een
     * eigen route geserveerd (werkt in de browser én in de NativePHP-app).
     */
    public const IMAGE_DISK = 'local';

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function imageUrl(): ?string
    {
        return $this->image_path !== null ? route('activities.image', $this) : null;
    }

    protected static function booted(): void
    {
        static::deleted(function (Activity $activity): void {
            if ($activity->image_path !== null) {
                Storage::disk(self::IMAGE_DISK)->delete($activity->image_path);
            }
        });
    }
}
