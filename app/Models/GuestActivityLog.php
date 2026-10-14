<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class GuestActivityLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'ip_address',
        'user_agent',
        'endpoint',
        'content_type',
        'content_id',
        'was_locked',
        'is_authenticated',
        'user_subscription_tier',
        'user_id',
        'created_at',
    ];

    protected $casts = [
        'was_locked'       => 'boolean',
        'is_authenticated' => 'boolean',
        'created_at'       => 'datetime',
    ];

    /* ------------------------------------------------------------------
     * Scopes
     * ----------------------------------------------------------------*/

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek(Builder $query): Builder
    {
        return $query->where('created_at', '>=', now()->startOfWeek());
    }

    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->where('created_at', '>=', now()->startOfMonth());
    }

    public function scopeGuests(Builder $query): Builder
    {
        return $query->where('is_authenticated', false);
    }

    public function scopeAuthenticated(Builder $query): Builder
    {
        return $query->where('is_authenticated', true);
    }

    public function scopeUniqueSessions(Builder $query): Builder
    {
        return $query->distinct('session_id');
    }

    public function scopeDateRange(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    public function scopeForContentType(Builder $query, string $type): Builder
    {
        return $query->where('content_type', $type);
    }

    public function scopeLocked(Builder $query): Builder
    {
        return $query->where('was_locked', true);
    }

    /* ------------------------------------------------------------------
     * Relationships
     * ----------------------------------------------------------------*/

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
