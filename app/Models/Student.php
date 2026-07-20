<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_code',
        'name',
        'age_level',
        'parent_email',
        'programming_lang',
        'schedule_notes',
        'learning_system',
        'package_quota',
        'status',
    ];

    protected $appends = ['completed_sessions', 'total_sessions', 'session_progress'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($student) {
            if (empty($student->student_code)) {
                $maxId = static::max('id') ?? 0;
                $student->student_code = 'COD-' . str_pad($maxId + 1, 3, '0', STR_PAD_LEFT);
            }
        });
    }

    public function meetingLogs()
    {
        return $this->hasMany(MeetingLog::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getCompletedSessionsAttribute()
    {
        if ($this->relationLoaded('meetingLogs')) {
            $maxSession = $this->meetingLogs->max('session_number') ?? 0;
            $countSession = $this->meetingLogs->where('attendance_status', 'Hadir')->count();
            return max($maxSession, $countSession);
        }

        $maxSession = $this->meetingLogs()->max('session_number') ?? 0;
        $countSession = $this->meetingLogs()->where('attendance_status', 'Hadir')->count();
        
        return max($maxSession, $countSession);
    }

    public function getTotalSessionsAttribute()
    {
        if ($this->learning_system === 'Paket') {
            $paidPackagesCount = $this->relationLoaded('payments')
                ? $this->payments->where('payment_status', 'Lunas')->count()
                : $this->payments()->where('payment_status', 'Lunas')->count();

            return $paidPackagesCount > 0 ? ($paidPackagesCount * 8) : 8;
        }
        return 0;
    }

    public function getSessionProgressAttribute()
    {
        if ($this->learning_system === 'Paket') {
            return $this->completed_sessions . ' dari ' . $this->total_sessions;
        }
        return $this->completed_sessions . ' Sesi (Bulanan)';
    }

    public function getPackageQuotaAttribute($value)
    {
        if ($this->learning_system === 'Paket') {
            $completed = $this->completed_sessions;
            $paidPackagesCount = $this->relationLoaded('payments')
                ? $this->payments->where('payment_status', 'Lunas')->count()
                : $this->payments()->where('payment_status', 'Lunas')->count();

            $totalSessionsBought = $paidPackagesCount > 0 ? ($paidPackagesCount * 8) : 8;

            return max(0, $totalSessionsBought - $completed);
        }
        return $value;
    }

    public function recalculateQuota()
    {
        if ($this->learning_system === 'Paket') {
            $remaining = $this->package_quota;
            $this->update(['package_quota' => $remaining]);
        }
    }
}
