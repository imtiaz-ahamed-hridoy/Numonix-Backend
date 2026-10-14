<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Faq extends Model
{

    use LogsActivity;

    protected $fillable = [
        "section",
        "question",
        "answer",
        "status",
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

    protected $casts = [
        'created_at' => 'date: d-m-Y',
        'updated_at' => 'date: d-m-Y',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
