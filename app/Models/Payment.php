<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'payment_date',
        'package_period',
        'amount',
        'transfer_method',
        'payment_status',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($payment) {
            if ($payment->student) {
                $payment->student->recalculateQuota();
            }
        });

        static::deleted(function ($payment) {
            if ($payment->student) {
                $payment->student->recalculateQuota();
            }
        });
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
