<?php

namespace App\Http\Controllers;

use App\Models\LaserPatient;
use App\Models\LaserSession;
use App\Models\LaserTreatment;
use App\Models\User;
use Illuminate\Http\Request;

class LaserSessionController extends Controller
{
    private function rules(): array
    {
        return [
            'laser_patient_id' => 'required|exists:laser_patients,id',
            'laser_treatment_id' => 'required|exists:laser_treatments,id',
            'session_date' => 'required|date',
            'session_number' => 'required|integer|min:1',
            'total_sessions' => 'nullable|integer|min:1',
            'laser_machine' => 'nullable|string|max:150',
            'fluence' => 'nullable|string|max:50',
            'pulse_width' => 'nullable|string|max:50',
            'spot_size' => 'nullable|string|max:50',
            'pulses_count' => 'nullable|integer|min:0',
            'price' => 'required|numeric|min:0',
            'discount_type' => 'nullable|in:fixed,percentage',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|in:cash,bank,card,online,other',
            'payment_reference' => 'nullable|string|max:150',
            'notes' => 'nullable|string|max:2000',
            'performed_by' => 'nullable|exists:users,id',
        ];
    }

    private function generateInvoiceNumber(): string
    {
        $nextId = (LaserSession::max('id') ?? 0) + 1;
        $num = 'LINV-'.str_pad($nextId, 4, '0', STR_PAD_LEFT);

        while (LaserSession::where('invoice_number', $num)->exists()) {
            $nextId++;
            $num = 'LINV-'.str_pad($nextId, 4, '0', STR_PAD_LEFT);
        }

        return $num;
    }

    public function index(Request $request)
    {
        $sessions = LaserSession::with(['patient', 'treatment', 'performedBy'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('invoice_number', 'like', "%{$search}%")
                        ->orWhereHas('patient', function ($p) use ($search) {
                            $p->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('patient_number', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('treatment_id'), function ($query) use ($request) {
                $query->where('laser_treatment_id', $request->treatment_id);
            })
            ->when($request->filled('date'), function ($query) use ($request) {
                $query->whereDate('session_date', $request->date);
            })
            ->orderByDesc('session_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('laser.sessions.index', [
            'sessions' => $sessions,
            'treatments' => LaserTreatment::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function create(Request $request)
    {
        $patientId = $request->query('patient_id');
        $treatmentId = $request->query('treatment_id');

        $selectedPatient = $patientId ? LaserPatient::find($patientId) : null;
        $nextSessionNumber = 1;

        if ($selectedPatient) {
            $nextSessionNumber = ($selectedPatient->sessions()->max('session_number') ?? 0) + 1;
        }

        return view('laser.sessions.create', [
            'patients' => LaserPatient::where('status', 'active')->orderBy('first_name')->get(),
            'treatments' => LaserTreatment::where('status', 'active')->orderBy('name')->get(),
            'doctors' => User::whereIn('role', ['admin', 'staff'])->orderBy('name')->get(),
            'selectedPatient' => $selectedPatient,
            'treatmentId' => $treatmentId,
            'nextSessionNumber' => $nextSessionNumber,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $price = (float) $validated['price'];

        $discountType = $validated['discount_type'] ?? 'fixed';
        if ($discountType === 'percentage' && isset($validated['discount_percentage']) && $validated['discount_percentage'] !== '') {
            $discount = round($price * ((float) $validated['discount_percentage'] / 100), 2);
        } else {
            $discount = (float) ($validated['discount'] ?? 0);
        }

        if ($discount > $price) {
            return back()
                ->withErrors(['discount' => 'Discount cannot exceed session fee of PKR '.number_format($price, 2).'.'])
                ->withInput();
        }

        $totalAmount = max(0, $price - $discount);

        $paidAmount = isset($validated['paid_amount']) && $validated['paid_amount'] !== '' && $validated['paid_amount'] !== null
            ? (float) $validated['paid_amount']
            : $totalAmount;

        if ($paidAmount > $totalAmount) {
            return back()
                ->withErrors(['paid_amount' => 'Paid amount cannot exceed net total of PKR '.number_format($totalAmount, 2).'.'])
                ->withInput();
        }

        $dueAmount = max(0, $totalAmount - $paidAmount);
        $status = $dueAmount <= 0 ? LaserSession::STATUS_PAID : ($paidAmount > 0 ? LaserSession::STATUS_PARTIAL : LaserSession::STATUS_PENDING);

        $invoiceNumber = $this->generateInvoiceNumber();

        $session = LaserSession::create([
            'laser_patient_id' => $validated['laser_patient_id'],
            'laser_treatment_id' => $validated['laser_treatment_id'],
            'invoice_number' => $invoiceNumber,
            'session_date' => $validated['session_date'],
            'session_number' => $validated['session_number'],
            'total_sessions' => $validated['total_sessions'] ?? null,
            'laser_machine' => $validated['laser_machine'] ?? null,
            'fluence' => $validated['fluence'] ?? null,
            'pulse_width' => $validated['pulse_width'] ?? null,
            'spot_size' => $validated['spot_size'] ?? null,
            'pulses_count' => $validated['pulses_count'] ?? null,
            'price' => $price,
            'discount_type' => $discountType,
            'discount_percentage' => $validated['discount_percentage'] ?? null,
            'discount' => $discount,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'due_amount' => $dueAmount,
            'status' => $status,
            'payment_method' => $validated['payment_method'] ?? 'cash',
            'payment_reference' => $validated['payment_reference'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'performed_by' => $validated['performed_by'] ?? auth()->id(),
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('laser.sessions.show', $session)
            ->with('success', "Laser Session recorded successfully. Invoice {$session->invoice_number} created.");
    }

    public function show(LaserSession $session)
    {
        $session->load(['patient', 'treatment', 'performedBy', 'createdBy']);

        return view('laser.sessions.show', [
            'session' => $session,
        ]);
    }

    public function print(LaserSession $session)
    {
        $session->load(['patient', 'treatment', 'performedBy', 'createdBy']);

        return view('laser.sessions.print', [
            'session' => $session,
        ]);
    }

    public function edit(LaserSession $session)
    {
        return view('laser.sessions.edit', [
            'session' => $session,
            'patients' => LaserPatient::where('status', 'active')->orderBy('first_name')->get(),
            'treatments' => LaserTreatment::where('status', 'active')->orderBy('name')->get(),
            'doctors' => User::whereIn('role', ['admin', 'staff'])->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, LaserSession $session)
    {
        $validated = $request->validate($this->rules());

        $price = (float) $validated['price'];

        $discountType = $validated['discount_type'] ?? 'fixed';
        if ($discountType === 'percentage' && isset($validated['discount_percentage']) && $validated['discount_percentage'] !== '') {
            $discount = round($price * ((float) $validated['discount_percentage'] / 100), 2);
        } else {
            $discount = (float) ($validated['discount'] ?? 0);
        }

        if ($discount > $price) {
            return back()
                ->withErrors(['discount' => 'Discount cannot exceed session fee of PKR '.number_format($price, 2).'.'])
                ->withInput();
        }

        $totalAmount = max(0, $price - $discount);
        $paidAmount = isset($validated['paid_amount']) ? (float) $validated['paid_amount'] : $session->paid_amount;

        if ($paidAmount > $totalAmount) {
            return back()
                ->withErrors(['paid_amount' => 'Paid amount cannot exceed net total of PKR '.number_format($totalAmount, 2).'.'])
                ->withInput();
        }

        $dueAmount = max(0, $totalAmount - $paidAmount);
        $status = $dueAmount <= 0 ? LaserSession::STATUS_PAID : ($paidAmount > 0 ? LaserSession::STATUS_PARTIAL : LaserSession::STATUS_PENDING);

        $session->update([
            'laser_patient_id' => $validated['laser_patient_id'],
            'laser_treatment_id' => $validated['laser_treatment_id'],
            'session_date' => $validated['session_date'],
            'session_number' => $validated['session_number'],
            'total_sessions' => $validated['total_sessions'] ?? null,
            'laser_machine' => $validated['laser_machine'] ?? null,
            'fluence' => $validated['fluence'] ?? null,
            'pulse_width' => $validated['pulse_width'] ?? null,
            'spot_size' => $validated['spot_size'] ?? null,
            'pulses_count' => $validated['pulses_count'] ?? null,
            'price' => $price,
            'discount_type' => $discountType,
            'discount_percentage' => $validated['discount_percentage'] ?? null,
            'discount' => $discount,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'due_amount' => $dueAmount,
            'status' => $status,
            'payment_method' => $validated['payment_method'] ?? $session->payment_method,
            'payment_reference' => $validated['payment_reference'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'performed_by' => $validated['performed_by'] ?? $session->performed_by,
        ]);

        return redirect()
            ->route('laser.sessions.show', $session)
            ->with('success', "Laser Session {$session->invoice_number} updated successfully.");
    }

    public function destroy(LaserSession $session)
    {
        $session->delete();

        return redirect()
            ->route('laser.sessions.index')
            ->with('success', 'Laser Session deleted successfully.');
    }
}
