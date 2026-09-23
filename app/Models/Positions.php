<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable('department_id', 'title', 'description')]
class Positions extends Model
{
    /** @use HasFactory<\Database\Factories\PositionsFactory> */
    use HasFactory;

    public function department() : BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function employee() : HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
