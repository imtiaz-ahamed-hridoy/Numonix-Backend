<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class LearningPage extends Model
{
    use LogsActivity;

    protected $fillable = [
        'learning_id',
        'page_number',
        'title',
        'section_title',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

    protected $casts = [
        'page_number' => 'integer',
    ];

    public function learning(): BelongsTo
    {
        return $this->belongsTo(Learning::class);
    }

    public function elements(): HasMany
    {
        return $this->hasMany(LearningPageElement::class)->orderBy('position')->orderBy('id');
    }
}
