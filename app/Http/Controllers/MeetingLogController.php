<?php

namespace App\Http\Controllers;

use App\Models\MeetingLog;
use App\Models\Student;
use Illuminate\Http\Request;

class MeetingLogController extends Controller
{
    public function index()
    {
        $logs = MeetingLog::with('student')->latest('meeting_date')->get();
        return response()->json($logs);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'meeting_date' => 'required|date',
            'session_number' => 'required|integer|min:1',
            'topic' => 'required|string|max:255',
            'project_progress' => 'nullable|string',
            'rating' => 'required|integer|between:1,5',
            'evaluation_notes' => 'nullable|string',
            'attendance_status' => 'required|in:Hadir,Izin,Alfa',
        ]);

        $log = MeetingLog::create($validated);
        return response()->json(['success' => true, 'message' => 'Log pertemuan berhasil dicatat!', 'data' => $log]);
    }

    public function update(Request $request, $id)
    {
        $log = MeetingLog::findOrFail($id);
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'meeting_date' => 'required|date',
            'session_number' => 'required|integer|min:1',
            'topic' => 'required|string|max:255',
            'project_progress' => 'nullable|string',
            'rating' => 'required|integer|between:1,5',
            'evaluation_notes' => 'nullable|string',
            'attendance_status' => 'required|in:Hadir,Izin,Alfa',
        ]);

        $log->update($validated);
        return response()->json(['success' => true, 'message' => 'Log pertemuan berhasil diperbarui!', 'data' => $log]);
    }

    public function destroy($id)
    {
        $log = MeetingLog::findOrFail($id);
        $log->delete();
        return response()->json(['success' => true, 'message' => 'Log pertemuan berhasil dihapus!']);
    }
}
