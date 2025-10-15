<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'actor_id',
        'actor_type',
        'action',
        'subject_id',
        'subject_type',
        'details',
        'ip_address',
        'user_agent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'details' => 'array',
    ];

    /**
     * Get the actor that performed the action.
     */
    public function actor(): MorphTo
    {
        return $this->morphTo('actor');
    }

    /**
     * Get the subject that was acted upon.
     */
    public function subject(): MorphTo
    {
        return $this->morphTo('subject');
    }

    /**
     * Scope to get logs by action.
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to get logs by actor.
     */
    public function scopeByActor($query, $actor)
    {
        return $query->where('actor_id', $actor->id)
            ->where('actor_type', get_class($actor));
    }

    /**
     * Scope to get logs by subject.
     */
    public function scopeBySubject($query, $subject)
    {
        return $query->where('subject_id', $subject->id)
            ->where('subject_type', get_class($subject));
    }

    /**
     * Scope to get recent logs.
     */
    public function scopeRecent($query, int $limit = 100)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Create an audit log entry.
     */
    public static function createLog(
        $actor,
        string $action,
        $subject = null,
        array $details = [],
        string $ipAddress = null,
        string $userAgent = null
    ): self {
        return self::create([
            'actor_id' => $actor?->id,
            'actor_type' => $actor ? get_class($actor) : null,
            'action' => $action,
            'subject_id' => $subject?->id,
            'subject_type' => $subject ? get_class($subject) : null,
            'details' => $details,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }
}