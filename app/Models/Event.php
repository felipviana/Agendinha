<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_type_id',
        'schedule_type',
        'title',
        'scheduled_date',
        'start_time',
        'end_time',
        'location',
        'contractor_name',
        'description',
        'status',
        'fee',
        'is_recurring',
        'recurrence_group',
        'recurrence_days',
        'recurrence_start_date',
        'recurrence_end_date',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function workType(){
        return $this->belongsTo(WorkType::class);
    }
}
