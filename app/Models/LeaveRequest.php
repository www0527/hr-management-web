<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

#[Fillable([
    'Employee_id', 'leave_type_id', 'start_date', 'end_date',
    'days', 'reason' , 'status', 'reviewed_by', 'reviewed_at', 'review_note'
])]
class LeaveRequest extends Model
{
    /** @use HasFactory<\Database\Factories\LeaveRequestFactory> */
    use HasFactory;

    protected function casts() : array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'reviewed_at' => 'datetime',
        ];
    }

    public function Employee() : BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType() : BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    /**
     * 是誰審閱假單（通常是 HR or Manager）
     * @return BelongsTo<Employee,$this>
     */
    public function reviewer() : BelongsTo
    {
        return $this->belongsTo(Employee::class, 'reviewed_by');
    }

}
