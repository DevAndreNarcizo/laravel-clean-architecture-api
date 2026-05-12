<?php

declare(strict_types=1);

namespace Src\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TaskModel extends Model
{
    protected $table = 'tasks';

    protected $fillable = ['project_id', 'title', 'description', 'status'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(ProjectModel::class, 'project_id');
    }
}
