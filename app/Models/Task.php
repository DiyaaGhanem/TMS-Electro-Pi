<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = ['project_id', 'title', 'description', 'priority', 'status', 'due_date', 'overdue_notified'];

    protected $casts = [
        'status'             => TaskStatus::class,
        'priority'           => TaskPriority::class,
        'due_date'           => 'date',
        'overdue_notified'   => 'boolean',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
