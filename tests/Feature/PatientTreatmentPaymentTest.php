<?php

use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Treatment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('recording a patient treatment automatically creates a paid invoice and payment', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $patient = Patient::create([
        'patient_number' => 'P-0001',
        'first_name' => 'Sarah',
        'last_name' => 'Connor',
        'gender' => 'female',
        'phone' => '1234567890',
        'status' => 'active',
    ]);

    $treatment = Treatment::create([
        'name' => 'HydraFacial',
        'price' => 150.00,
        'duration' => 45,
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->post(route('patient-treatments.store'), [
        'patient_id' => $patient->id,
        'treatment_id' => $treatment->id,
        'treatment_date' => '2026-09-29',
        'price' => 150.00,
        'discount' => 20.00,
        'payment_method' => 'card',
        'payment_reference' => 'TXN-98765',
        'notes' => 'Routine facial treatment',
    ]);

    $response->assertRedirect(route('patient-treatments.index'));
    $response->assertSessionHas('success');

    // 1. Check patient treatment record
    $this->assertDatabaseHas('patient_treatments', [
        'patient_id' => $patient->id,
        'treatment_id' => $treatment->id,
        'price' => '150.00',
        'discount' => '20.00',
        'total_amount' => '130.00',
    ]);

    // 2. Check invoice record
    $invoice = Invoice::where('patient_id', $patient->id)->first();
    expect($invoice)->not->toBeNull();
    expect($invoice->invoice_number)->toBe('INV-0001');
    expect((float) $invoice->subtotal)->toBe(150.00);
    expect((float) $invoice->discount)->toBe(20.00);
    expect((float) $invoice->total_amount)->toBe(130.00);
    expect((float) $invoice->paid_amount)->toBe(130.00);
    expect((float) $invoice->due_amount)->toBe(0.00);
    expect($invoice->status)->toBe('paid');

    // 3. Check payment record
    $payment = Payment::where('invoice_id', $invoice->id)->first();
    expect($payment)->not->toBeNull();
    expect($payment->patient_id)->toBe($patient->id);
    expect((float) $payment->amount)->toBe(130.00);
    expect($payment->payment_method)->toBe('card');
    expect($payment->reference)->toBe('TXN-98765');
});

test('recording a fully discounted patient treatment creates a paid invoice with zero due and no payment', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $patient = Patient::create([
        'patient_number' => 'P-0002',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'gender' => 'male',
        'phone' => '9876543210',
        'status' => 'active',
    ]);

    $treatment = Treatment::create([
        'name' => 'Skin Consultation',
        'price' => 50.00,
        'duration' => 30,
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->post(route('patient-treatments.store'), [
        'patient_id' => $patient->id,
        'treatment_id' => $treatment->id,
        'treatment_date' => '2026-09-29',
        'price' => 50.00,
        'discount' => 50.00, // 100% discount
    ]);

    $response->assertRedirect(route('patient-treatments.index'));

    $invoice = Invoice::where('patient_id', $patient->id)->first();
    expect($invoice)->not->toBeNull();
    expect((float) $invoice->total_amount)->toBe(0.00);
    expect((float) $invoice->paid_amount)->toBe(0.00);
    expect((float) $invoice->due_amount)->toBe(0.00);
    expect($invoice->status)->toBe('paid');

    // No payment needed when total is 0
    $paymentCount = Payment::where('invoice_id', $invoice->id)->count();
    expect($paymentCount)->toBe(0);
});

test('recording treatment from patient profile with redirect=patient redirects back to patient page', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $patient = Patient::create([
        'patient_number' => 'P-0003',
        'first_name' => 'Alice',
        'last_name' => 'Wonderland',
        'gender' => 'female',
        'phone' => '5551234567',
        'status' => 'active',
    ]);

    $treatment = Treatment::create([
        'name' => 'Chemical Peel',
        'price' => 200.00,
        'duration' => 60,
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->post(route('patient-treatments.store'), [
        'patient_id' => $patient->id,
        'treatment_id' => $treatment->id,
        'treatment_date' => '2026-09-29',
        'price' => 200.00,
        'discount' => 0,
        'payment_method' => 'bank',
        'payment_reference' => 'BNK-7711',
        'redirect' => 'patient',
    ]);

    $response->assertRedirect(route('patients.show', $patient->id).'#treatments');
    $response->assertSessionHas('success');

    $payment = Payment::where('patient_id', $patient->id)->first();
    expect($payment)->not->toBeNull();
    expect($payment->payment_method)->toBe('bank');
    expect($payment->reference)->toBe('BNK-7711');
    expect((float) $payment->amount)->toBe(200.00);
});

test('existing invoices and payments remain untouched when a new patient treatment is recorded', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $patient = Patient::create([
        'patient_number' => 'P-0004',
        'first_name' => 'Bob',
        'last_name' => 'Builder',
        'gender' => 'male',
        'phone' => '5559876543',
        'status' => 'active',
    ]);

    // Create a pre-existing manual pending invoice
    $existingInvoice = Invoice::create([
        'patient_id' => $patient->id,
        'invoice_number' => 'INV-0099',
        'invoice_date' => '2026-09-01',
        'subtotal' => 300.00,
        'discount' => 50.00,
        'total_amount' => 250.00,
        'paid_amount' => 100.00,
        'due_amount' => 150.00,
        'status' => 'partial',
    ]);

    $treatment = Treatment::create([
        'name' => 'Microdermabrasion',
        'price' => 120.00,
        'duration' => 30,
        'status' => 'active',
    ]);

    // Record new patient treatment
    $this->actingAs($user)->post(route('patient-treatments.store'), [
        'patient_id' => $patient->id,
        'treatment_id' => $treatment->id,
        'treatment_date' => '2026-09-29',
        'price' => 120.00,
        'discount' => 10.00,
    ]);

    // Verify existing invoice is completely unchanged
    $existingInvoice->refresh();
    expect($existingInvoice->invoice_number)->toBe('INV-0099');
    expect((float) $existingInvoice->subtotal)->toBe(300.00);
    expect((float) $existingInvoice->discount)->toBe(50.00);
    expect((float) $existingInvoice->total_amount)->toBe(250.00);
    expect((float) $existingInvoice->paid_amount)->toBe(100.00);
    expect((float) $existingInvoice->due_amount)->toBe(150.00);
    expect($existingInvoice->status)->toBe('partial');
});

test('recording treatment with partial payment creates partial invoice with due amount and matching payment', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $patient = Patient::create([
        'patient_number' => 'P-0005',
        'first_name' => 'Charlie',
        'last_name' => 'Brown',
        'gender' => 'male',
        'phone' => '5553334444',
        'status' => 'active',
    ]);

    $treatment = Treatment::create([
        'name' => 'Laser Resurfacing',
        'price' => 300.00,
        'duration' => 60,
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->post(route('patient-treatments.store'), [
        'patient_id' => $patient->id,
        'treatment_id' => $treatment->id,
        'treatment_date' => '2026-09-29',
        'price' => 300.00,
        'discount' => 50.00, // Total = 250.00
        'paid_amount' => 100.00, // Paid 100, Due 150
        'payment_method' => 'card',
        'payment_reference' => 'CARD-4455',
    ]);

    $response->assertRedirect(route('patient-treatments.index'));
    $response->assertSessionHas('success');

    $invoice = Invoice::where('patient_id', $patient->id)->first();
    expect($invoice)->not->toBeNull();
    expect((float) $invoice->total_amount)->toBe(250.00);
    expect((float) $invoice->paid_amount)->toBe(100.00);
    expect((float) $invoice->due_amount)->toBe(150.00);
    expect($invoice->status)->toBe('partial');

    $payment = Payment::where('invoice_id', $invoice->id)->first();
    expect($payment)->not->toBeNull();
    expect((float) $payment->amount)->toBe(100.00);
    expect($payment->payment_method)->toBe('card');
    expect($payment->reference)->toBe('CARD-4455');
});

test('recording treatment with full due creates pending invoice with zero payment', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $patient = Patient::create([
        'patient_number' => 'P-0006',
        'first_name' => 'David',
        'last_name' => 'Miller',
        'gender' => 'male',
        'phone' => '5551112222',
        'status' => 'active',
    ]);

    $treatment = Treatment::create([
        'name' => 'Chemical Peel',
        'price' => 180.00,
        'duration' => 45,
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->post(route('patient-treatments.store'), [
        'patient_id' => $patient->id,
        'treatment_id' => $treatment->id,
        'treatment_date' => '2026-09-29',
        'price' => 180.00,
        'discount' => 0,
        'paid_amount' => 0, // Full due: 0 paid
    ]);

    $response->assertRedirect(route('patient-treatments.index'));

    $invoice = Invoice::where('patient_id', $patient->id)->first();
    expect($invoice)->not->toBeNull();
    expect((float) $invoice->total_amount)->toBe(180.00);
    expect((float) $invoice->paid_amount)->toBe(0.00);
    expect((float) $invoice->due_amount)->toBe(180.00);
    expect($invoice->status)->toBe('pending');

    $paymentCount = Payment::where('invoice_id', $invoice->id)->count();
    expect($paymentCount)->toBe(0);
});

test('recording treatment fails validation if paid amount exceeds total amount', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $patient = Patient::create([
        'patient_number' => 'P-0007',
        'first_name' => 'Emma',
        'last_name' => 'Watson',
        'gender' => 'female',
        'phone' => '5557778888',
        'status' => 'active',
    ]);

    $treatment = Treatment::create([
        'name' => 'Acne Treatment',
        'price' => 100.00,
        'duration' => 30,
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->post(route('patient-treatments.store'), [
        'patient_id' => $patient->id,
        'treatment_id' => $treatment->id,
        'treatment_date' => '2026-09-29',
        'price' => 100.00,
        'discount' => 10.00, // Total = 90.00
        'paid_amount' => 120.00, // Exceeds 90.00
    ]);

    $response->assertSessionHasErrors(['paid_amount']);
    $this->assertDatabaseMissing('patient_treatments', ['patient_id' => $patient->id]);
});
