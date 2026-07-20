<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Student;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with('student')->latest('payment_date')->get();
        return response()->json($payments);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'payment_date' => 'required|date',
            'package_period' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'transfer_method' => 'required|string',
            'payment_status' => 'required|in:Lunas,Belum Bayar',
        ]);

        $payment = Payment::create($validated);
        return response()->json(['success' => true, 'message' => 'Transaksi pembayaran berhasil disimpan!', 'data' => $payment]);
    }

    public function update(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'payment_date' => 'required|date',
            'package_period' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'transfer_method' => 'required|string',
            'payment_status' => 'required|in:Lunas,Belum Bayar',
        ]);

        $payment->update($validated);
        return response()->json(['success' => true, 'message' => 'Transaksi pembayaran berhasil diperbarui!', 'data' => $payment]);
    }

    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);
        $payment->delete();
        return response()->json(['success' => true, 'message' => 'Transaksi pembayaran berhasil dihapus!']);
    }
}
