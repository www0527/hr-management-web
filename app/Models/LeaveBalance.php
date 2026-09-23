<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['employee_id', 'leave_type_id', 'year', 'entitled_days', 'used_days'])]
class LeaveBalance extends Model
{
    /** @use HasFactory<\Database\Factories\LeaveBalanceFactory> */
    use HasFactory;

    /**
     * 自動計算剩餘天數 <br/>
     * $this->entitled_days - $this->used_days
     * @return Attribute<int,$this>
     */
    protected function remainingDays() : Attribute {
        return Attribute:: get(fn(): int=> $this->entitled_days - $this->used_days);
    }

    public function Employee() : BelongsTo {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType() : BelongsTo {
        return  $this->belongsTo(LeaveType::class);
    }
}
