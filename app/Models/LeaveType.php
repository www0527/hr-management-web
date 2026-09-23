<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

#[Fillable(['name','default_days_per_year','is_paid'])]
class LeaveType extends Model
{
    /** @use HasFactory<\Database\Factories\LeaveTypeFactory> */
    use HasFactory;

    protected function casts() : array
    {
        return [
            'is_paid' => 'boolean',
        ];
    }

    public function leaveRequests() : HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function balances() : HasMany {
        return $this->hasMany(LeaveBalance::class);
    }
}
