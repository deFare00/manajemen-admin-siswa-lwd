<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'meeting_date',
        'session_number',
        'topic',
        'project_progress',
        'rating',
        'evaluation_notes',
        'attendance_status',
    ];

    protected $casts = [
        'meeting_date' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($log) {
            if ($log->student) {
                $log->student->recalculateQuota();
            }
        });

        static::deleted(function ($log) {
            if ($log->student) {
                $log->student->recalculateQuota();
            }
        });
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
