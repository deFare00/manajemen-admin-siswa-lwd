<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\MeetingLog;
use App\Models\Payment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $activeStudentsCount = Student::where('status', 'Aktif')->count();
        
        $monthlyIncome = Payment::where('payment_status', 'Lunas')
            ->whereMonth('payment_date', Carbon::now()->month)
            ->whereYear('payment_date', Carbon::now()->year)
            ->sum('amount');
            
        $unpaidCount = Payment::where('payment_status', 'Belum Bayar')->count();
        $totalSessions = MeetingLog::count();
        
        $unpaidList = Payment::with('student')
            ->where('payment_status', 'Belum Bayar')
            ->latest('payment_date')
            ->get();

        $studentsProgress = Student::with(['meetingLogs', 'payments'])
            ->where('status', 'Aktif')
            ->latest()
            ->get();

        return view('dashboard', compact(
            'activeStudentsCount',
            'monthlyIncome',
            'unpaidCount',
            'totalSessions',
            'unpaidList',
            'studentsProgress'
        ));
    }

    public function apiSummary()
    {
        $activeStudentsCount = Student::where('status', 'Aktif')->count();
        $monthlyIncome = Payment::where('payment_status', 'Lunas')
            ->whereMonth('payment_date', Carbon::now()->month)
            ->whereYear('payment_date', Carbon::now()->year)
            ->sum('amount');
        $unpaidCount = Payment::where('payment_status', 'Belum Bayar')->count();
        $totalSessions = MeetingLog::count();

        $unpaidList = Payment::with('student')
            ->where('payment_status', 'Belum Bayar')
            ->latest('payment_date')
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'date' => $p->payment_date ? $p->payment_date->format('Y-m-d') : '',
                    'name' => $p->student ? $p->student->name : '-',
                    'periode' => $p->package_period,
                    'nominal' => $p->amount,
                    'contact' => $p->student ? $p->student->parent_email : 'Kosong',
                ];
            });

        return response()->json([
            'activeStudentsCount' => $activeStudentsCount,
            'monthlyIncome' => $monthlyIncome,
            'unpaidCount' => $unpaidCount,
            'totalSessions' => $totalSessions,
            'unpaidList' => $unpaidList,
        ]);
    }
}
