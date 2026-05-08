<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    const UPDATED_AT = null; // hanya created_at

    protected $fillable = [
        'user_id', 'action', 'description', 'ip_address', 'user_agent',
        'subject_type', 'subject_id', 'metadata'
    ];

    protected $casts = [
        'metadata' => 'json',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Catat aktivitas dengan mudah.
     */
    public static function log(
        string $action,
        ?string $description = null,
        $subject = null,
        ?array $metadata = null
    ): self {
        $request = request();

        return self::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->id : null,
            'metadata' => $metadata,
        ]);
    }
}
