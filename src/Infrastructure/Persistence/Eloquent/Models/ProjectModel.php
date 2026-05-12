<?php

declare(strict_types=1);

namespace Src\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ProjectModel extends Model
{
    use HasFactory;

    protected $table = 'projects';

    protected $fillable = ['owner_id', 'name', 'description', 'status'];

    public function tasks(): HasMany
    {
        return $this->hasMany(TaskModel::class, 'project_id');
    }
}
