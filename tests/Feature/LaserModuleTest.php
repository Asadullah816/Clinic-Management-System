<?php

use App\Models\Invoice;
use App\Models\LaserExpense;
use App\Models\LaserExpenseCategory;
use App\Models\LaserPatient;
use App\Models\LaserSession;
use App\Models\LaserTreatment;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('laser patient can be registered with fitzpatrick skin type and separate LP-xxxx numbering', function () {
    $user = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($user)->post(route('laser.patients.store'), [
        'first_name' => 'Zara',
        'last_name' => 'Ahmed',
        'gender' => 'female',
        'date_of_birth' => '1998-05-14',
        'phone' => '03001234567',
        'email' => 'zara@example.com',
        'address' => 'Gulberg III, Lahore',
        'skin_type' => 'Type III',
        'medical_notes' => 'No sun exposure in last 2 weeks; no photosensitizing medications.',
        'status' => 'active',
    ]);

    $patient = LaserPatient::where('email', 'zara@example.com')->first();

    expect($patient)->not->toBeNull();
    expect($patient->patient_number)->toBe('LP-0001');
    expect($patient->skin_type)->toBe('Type III');
    expect($patient->full_name)->toBe('Zara Ahmed');

    $response->assertRedirect(route('laser.patients.show', $patient));

    // Verify laser patients are isolated and do not appear in regular clinic patients table
    $this->assertDatabaseMissing('patients', ['phone' => '03001234567']);
});

test('laser treatment catalog can be created and managed', function () {
    $user = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($user)->post(route('laser.treatments.store'), [
        'name' => 'Laser Hair Removal - Full Face',
        'code' => 'LHR-FF',
        'body_area' => 'Full Face',
        'price' => 5000.00,
        'duration' => 30,
        'description' => 'Cheeks, upper lip, chin and jawline.',
        'status' => 'active',
    ]);

    $response->assertRedirect(route('laser.treatments.index'));
    $this->assertDatabaseHas('laser_treatments', [
        'name' => 'Laser Hair Removal - Full Face',
        'code' => 'LHR-FF',
        'price' => '5000.00',
    ]);
});

test('recording a laser session calculates separate discount and generates unique LINV-xxxx invoice', function () {
    $user = User::factory()->create(['role' => 'admin']);

    $patient = LaserPatient::create([
        'patient_number' => 'LP-0001',
        'first_name' => 'Ayesha',
        'last_name' => 'Khan',
        'gender' => 'female',
        'phone' => '03111223344',
        'skin_type' => 'Type IV',
        'status' => 'active',
    ]);

    $treatment = LaserTreatment::create([
        'name' => 'Full Body Laser Hair Removal',
        'code' => 'LHR-FB',
        'body_area' => 'Full Body',
        'price' => 15000.00,
        'duration' => 90,
        'status' => 'active',
    ]);

    // Record session with 20% discount (PKR 3,000 off -> PKR 12,000 net)
    $response = $this->actingAs($user)->post(route('laser.sessions.store'), [
        'laser_patient_id' => $patient->id,
        'laser_treatment_id' => $treatment->id,
        'session_date' => '2026-09-30',
        'session_number' => 1,
        'total_sessions' => 6,
        'laser_machine' => 'Diode 808nm',
        'fluence' => '18 J/cm²',
        'pulse_width' => '30 ms',
        'spot_size' => '12x12 mm',
        'pulses_count' => 1450,
        'price' => 15000.00,
        'discount_type' => 'percentage',
        'discount_percentage' => 20.00,
        'discount' => 3000.00,
        'paid_amount' => 12000.00, // fully paid
        'payment_method' => 'card',
        'payment_reference' => 'POS-TXN-101',
        'notes' => 'Mild erythema, patient tolerated well with cooling.',
    ]);

    $session = LaserSession::first();
    expect($session)->not->toBeNull();
    expect($session->invoice_number)->toBe('LINV-0001');
    expect((float) $session->price)->toBe(15000.00);
    expect((float) $session->discount)->toBe(3000.00);
    expect((float) $session->total_amount)->toBe(12000.00);
    expect((float) $session->paid_amount)->toBe(12000.00);
    expect((float) $session->due_amount)->toBe(0.00);
    expect($session->status)->toBe('paid');
    expect($session->pulses_count)->toBe(1450);

    $response->assertRedirect(route('laser.sessions.show', $session));
});

test('laser expenses can be recorded independently from clinic expenses', function () {
    $user = User::factory()->create(['role' => 'admin']);

    $category = LaserExpenseCategory::create([
        'name' => 'Optics & Handpieces',
        'description' => 'Laser parts and servicing',
    ]);

    $response = $this->actingAs($user)->post(route('laser.expenses.store'), [
        'laser_expense_category_id' => $category->id,
        'title' => 'Diode Handpiece Service & Recalibration',
        'amount' => 25000.00,
        'expense_date' => '2026-09-30',
        'payment_method' => 'bank',
        'reference' => 'INV-OPT-99',
        'description' => 'Replaced cooling window and checked fluence meter',
    ]);

    $response->assertRedirect(route('laser.expenses.index'));

    $this->assertDatabaseHas('laser_expenses', [
        'title' => 'Diode Handpiece Service & Recalibration',
        'amount' => '25000.00',
    ]);

    // Ensure clinic regular expenses table is untouched
    $this->assertDatabaseMissing('expenses', [
        'title' => 'Diode Handpiece Service & Recalibration',
    ]);
});

test('laser financial report accurately computes net shared operating profit for investment partners', function () {
    $user = User::factory()->create(['role' => 'accountant']);

    $patient = LaserPatient::create([
        'patient_number' => 'LP-0001',
        'first_name' => 'Hina',
        'last_name' => 'Altaf',
        'gender' => 'female',
        'phone' => '03009988776',
        'status' => 'active',
    ]);

    $treatment = LaserTreatment::create([
        'name' => 'Underarms Laser',
        'price' => 3500.00,
    ]);

    // Session 1: Revenue PKR 3,500
    LaserSession::create([
        'laser_patient_id' => $patient->id,
        'laser_treatment_id' => $treatment->id,
        'invoice_number' => 'LINV-0001',
        'session_date' => '2026-09-15',
        'session_number' => 1,
        'price' => 3500.00,
        'discount' => 0,
        'total_amount' => 3500.00,
        'paid_amount' => 3500.00,
        'due_amount' => 0.00,
        'status' => 'paid',
        'payment_method' => 'cash',
    ]);

    // Session 2: Revenue PKR 6,500 (Discount 1,500 from 8,000)
    LaserSession::create([
        'laser_patient_id' => $patient->id,
        'laser_treatment_id' => $treatment->id,
        'invoice_number' => 'LINV-0002',
        'session_date' => '2026-09-20',
        'session_number' => 2,
        'price' => 8000.00,
        'discount' => 1500.00,
        'total_amount' => 6500.00,
        'paid_amount' => 6500.00,
        'due_amount' => 0.00,
        'status' => 'paid',
        'payment_method' => 'card',
    ]);

    // Laser Expense: PKR 2,000
    $category = LaserExpenseCategory::create(['name' => 'Cooling Gels']);
    LaserExpense::create([
        'laser_expense_category_id' => $category->id,
        'title' => 'Laser Gel 5L Container',
        'amount' => 2000.00,
        'expense_date' => '2026-09-18',
        'payment_method' => 'cash',
    ]);

    // Expected:
    // Revenue = 3,500 + 6,500 = 10,000
    // Expenses = 2,000
    // Shared Profit = 10,000 - 2,000 = 8,000

    $response = $this->actingAs($user)->get(route('laser.reports.financial'));

    $response->assertOk();
    $response->assertViewHas('totalRevenue', 10000.00);
    $response->assertViewHas('totalExpenses', 2000.00);
    $response->assertViewHas('netProfit', 8000.00);
    $response->assertViewHas('totalDiscounts', 1500.00);
    $response->assertSee('PKR 8,000.00');
});

test('print views support both 80mm thermal receipt format and A4 invoice format', function () {
    $user = User::factory()->create(['role' => 'admin']);

    // 1. Regular patient & invoice
    $patient = Patient::create([
        'patient_number' => 'P-0001',
        'first_name' => 'Sara',
        'last_name' => 'Ali',
        'gender' => 'female',
        'phone' => '03211112233',
        'status' => 'active',
    ]);

    $invoice = Invoice::create([
        'patient_id' => $patient->id,
        'invoice_number' => 'INV-0001',
        'invoice_date' => '2026-09-30',
        'subtotal' => 3000.00,
        'discount' => 500.00,
        'total_amount' => 2500.00,
        'paid_amount' => 2500.00,
        'due_amount' => 0.00,
        'status' => 'paid',
    ]);

    $payment = Payment::create([
        'patient_id' => $patient->id,
        'invoice_id' => $invoice->id,
        'amount' => 2500.00,
        'payment_date' => '2026-09-30',
        'payment_method' => 'cash',
    ]);

    // Test invoice 80mm POS receipt (default)
    $invReceipt = $this->actingAs($user)->get(route('invoices.print', $invoice));
    $invReceipt->assertOk();
    $invReceipt->assertSee('80mm auto');
    $invReceipt->assertSee('pos-receipt');
    $invReceipt->assertSee('INV-0001');

    // Test invoice A4 format
    $invA4 = $this->actingAs($user)->get(route('invoices.print', ['invoice' => $invoice, 'format' => 'a4']));
    $invA4->assertOk();
    $invA4->assertSee('size: A4');
    $invA4->assertSee('invoice-sheet');

    // Test payment 80mm POS receipt
    $payReceipt = $this->actingAs($user)->get(route('payments.print', $payment));
    $payReceipt->assertOk();
    $payReceipt->assertSee('80mm auto');
    $payReceipt->assertSee('pos-receipt');

    // Test laser session 80mm POS receipt
    $laserPatient = LaserPatient::create([
        'patient_number' => 'LP-0001',
        'first_name' => 'Maria',
        'last_name' => 'Bibi',
        'gender' => 'female',
        'phone' => '03334445566',
        'status' => 'active',
    ]);

    $session = LaserSession::create([
        'laser_patient_id' => $laserPatient->id,
        'invoice_number' => 'LINV-0001',
        'session_date' => '2026-09-30',
        'session_number' => 1,
        'price' => 4000.00,
        'discount' => 0,
        'total_amount' => 4000.00,
        'paid_amount' => 4000.00,
        'due_amount' => 0.00,
        'status' => 'paid',
        'payment_method' => 'cash',
    ]);

    $laserPrint = $this->actingAs($user)->get(route('laser.sessions.print', $session));
    $laserPrint->assertOk();
    $laserPrint->assertSee('80mm auto');
    $laserPrint->assertSee('pos-receipt');
    $laserPrint->assertSee('LINV-0001');
    $laserPrint->assertSee('LASER TREATMENT RECEIPT');
});

test('laser dashboard loads successfully with period filters and metrics', function () {
    $user = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($user)->get(route('laser.dashboard'));
    $response->assertOk();
    $response->assertSee('Laser Aesthetics & Shared Ledger');
    $response->assertSee('Distributable Profit');
    $response->assertSee('Reporting Period:');

    // Test with period filter
    $weekResponse = $this->actingAs($user)->get(route('laser.dashboard', ['period' => 'week']));
    $weekResponse->assertOk();
    $weekResponse->assertSee('This Week');
});
