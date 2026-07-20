<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        return response()->json(Student::with(['meetingLogs', 'payments'])->latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'age_level' => 'nullable|string',
            'parent_email' => 'nullable|email',
            'programming_lang' => 'required|string',
            'schedule_notes' => 'nullable|string',
            'learning_system' => 'required|in:Paket,Bulanan',
            'package_quota' => 'nullable|integer',
            'status' => 'required|in:Aktif,Cuti,Lulus',
        ]);

        $student = Student::create($validated);
        return response()->json(['success' => true, 'message' => 'Data Siswa berhasil disimpan!', 'data' => $student]);
    }

    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'age_level' => 'nullable|string',
            'parent_email' => 'nullable|email',
            'programming_lang' => 'required|string',
            'schedule_notes' => 'nullable|string',
            'learning_system' => 'required|in:Paket,Bulanan',
            'package_quota' => 'nullable|integer',
            'status' => 'required|in:Aktif,Cuti,Lulus',
        ]);

        $student->update($validated);
        return response()->json(['success' => true, 'message' => 'Data Siswa berhasil diperbarui!', 'data' => $student]);
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();
        return response()->json(['success' => true, 'message' => 'Data Siswa berhasil dihapus!']);
    }
}
