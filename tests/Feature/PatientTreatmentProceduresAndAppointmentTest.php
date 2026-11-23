<?php

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\PatientTreatment;
use App\Models\Treatment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('create treatment page provides patient appointments map for preselection', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $patient = Patient::create([
        'patient_number' => 'P-0001',
        'first_name' => 'Fatima',
        'last_name' => 'Zahra',
        'gender' => 'female',
        'phone' => '03001234567',
        'status' => 'active',
    ]);

    $treatment = Treatment::create([
        'name' => 'Root Canal Treatment (RCT)',
        'price' => 12000.00,
        'duration' => 60,
        'status' => 'active',
    ]);

    $appointment = Appointment::create([
        'patient_id' => $patient->id,
        'treatment_id' => $treatment->id,
        'appointment_date' => now()->toDateString(),
        'appointment_time' => '14:30',
        'status' => Appointment::STATUS_SCHEDULED,
        'created_by' => $user->id,
    ]);

    $response = $this->actingAs($user)->get(route('patient-treatments.create', [
        'patient_id' => $patient->id,
    ]));

    $response->assertOk();
    $response->assertSee('Fatima');
    $response->assertSee('Root Canal Treatment (RCT)');
    $response->assertSee('P-0001');
    $response->assertViewHas('patientAppointments');
    $patientAppointments = $response->viewData('patientAppointments');
    expect($patientAppointments)->toHaveKey($patient->id);
    expect($patientAppointments[$patient->id]['treatment_id'])->toBe($treatment->id);
    expect($patientAppointments[$patient->id]['treatment_name'])->toBe('Root Canal Treatment (RCT)');
});

test('treatment can be recorded with a custom walk-in procedure without catalog treatment', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $patient = Patient::create([
        'patient_number' => 'P-0002',
        'first_name' => 'Usman',
        'last_name' => 'Ali',
        'gender' => 'male',
        'phone' => '03119876543',
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->post(route('patient-treatments.store'), [
        'patient_id' => $patient->id,
        'treatment_id' => null,
        'procedure_name' => 'Emergency Temporary Dressing & Clasp Repair',
        'treatment_date' => now()->toDateString(),
        'price' => 2500.00,
        'discount' => 500.00,
        'paid_amount' => 2000.00,
        'payment_method' => 'cash',
        'notes' => 'Walk-in emergency patient.',
    ]);

    $response->assertRedirect(route('patient-treatments.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('patient_treatments', [
        'patient_id' => $patient->id,
        'treatment_id' => null,
        'procedure_name' => 'Emergency Temporary Dressing & Clasp Repair',
        'price' => '2500.00',
        'discount' => '500.00',
        'total_amount' => '2000.00',
    ]);

    $record = PatientTreatment::where('patient_id', $patient->id)->first();
    expect($record->procedure_title)->toBe('Emergency Temporary Dressing & Clasp Repair');
    expect($record->treatment_net)->toBe(2000.0);

    // Invoice check
    $invoice = Invoice::where('patient_id', $patient->id)->first();
    expect($invoice)->not->toBeNull();
    expect($invoice->notes)->toContain('Emergency Temporary Dressing & Clasp Repair');
    expect((float) $invoice->paid_amount)->toBe(2000.00);
});

test('treatment can be recorded with multiple procedures performed in one visit', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $patient = Patient::create([
        'patient_number' => 'P-0003',
        'first_name' => 'Bilal',
        'last_name' => 'Ahmed',
        'gender' => 'male',
        'phone' => '03335557788',
        'status' => 'active',
    ]);

    $t1 = Treatment::create([
        'name' => 'Dental Scaling & Polishing',
        'price' => 3000.00,
        'duration' => 30,
        'status' => 'active',
    ]);

    $t2 = Treatment::create([
        'name' => 'Composite Tooth Restoration',
        'price' => 4500.00,
        'duration' => 45,
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->post(route('patient-treatments.store'), [
        'patient_id' => $patient->id,
        'treatment_date' => now()->toDateString(),
        'price' => 8300.00,
        'discount' => 300.00,
        'procedures' => [
            [
                'treatment_id' => $t1->id,
                'name' => 'Dental Scaling & Polishing',
                'price' => 3000.00,
                'notes' => 'Full mouth ultrasonic',
            ],
            [
                'treatment_id' => $t2->id,
                'name' => 'Composite Tooth Restoration',
                'price' => 4500.00,
                'notes' => 'Tooth #16 Class II',
            ],
            [
                'treatment_id' => null,
                'name' => 'Periapical Digital X-Ray',
                'price' => 800.00,
                'notes' => 'Upper Right Quadrant',
            ],
        ],
        'paid_amount' => 8000.00,
        'payment_method' => 'cash',
        'notes' => 'Patient advised warm saline rinses.',
    ]);

    $response->assertRedirect(route('patient-treatments.index'));
    $response->assertSessionHas('success');

    $record = PatientTreatment::where('patient_id', $patient->id)->first();
    expect($record)->not->toBeNull();
    expect($record->procedure_title)->toContain('Dental Scaling & Polishing');
    expect($record->procedure_title)->toContain('Composite Tooth Restoration');
    expect($record->procedure_title)->toContain('Periapical Digital X-Ray');
    expect((float) $record->price)->toBe(8300.00);
    expect((float) $record->discount)->toBe(300.00);
    expect((float) $record->total_amount)->toBe(8000.00);
    expect($record->notes)->toContain('Tooth #16 Class II');
});

test('recording treatment linked to scheduled appointment automatically marks appointment as completed', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $patient = Patient::create([
        'patient_number' => 'P-0004',
        'first_name' => 'Maryam',
        'last_name' => 'Nawaz',
        'gender' => 'female',
        'phone' => '03214443322',
        'status' => 'active',
    ]);

    $treatment = Treatment::create([
        'name' => 'Dental Crown Preparation',
        'price' => 15000.00,
        'duration' => 60,
        'status' => 'active',
    ]);

    $appointment = Appointment::create([
        'patient_id' => $patient->id,
        'treatment_id' => $treatment->id,
        'appointment_date' => now()->toDateString(),
        'appointment_time' => '11:00',
        'status' => Appointment::STATUS_SCHEDULED,
        'created_by' => $user->id,
    ]);

    expect($appointment->status)->toBe(Appointment::STATUS_SCHEDULED);

    $response = $this->actingAs($user)->post(route('patient-treatments.store'), [
        'patient_id' => $patient->id,
        'treatment_id' => $treatment->id,
        'appointment_id' => $appointment->id,
        'treatment_date' => now()->toDateString(),
        'price' => 15000.00,
        'paid_amount' => 15000.00,
        'payment_method' => 'cash',
    ]);

    $response->assertRedirect(route('patient-treatments.index'));
    $response->assertSessionHas('success');

    $appointment->refresh();
    expect($appointment->status)->toBe(Appointment::STATUS_COMPLETED);
});

test('pharmacy & stock section and inventory cards are hidden from sidebar and dashboard', function () {
    $user = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($user)->get(route('dashboard'));
    $response->assertOk();
    $response->assertDontSee('Inventory & Stock', false);
    $response->assertDontSee('Total Products', false);
    $response->assertDontSee('Pharmacy &amp; Stock', false);
    $response->assertDontSee('Dental Pharmacy & Stock', false);
});

test('record treatment form hides prescribed medicines and cost breakdown while keeping grand total payable', function () {
    $user = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($user)->get(route('patient-treatments.create'));
    $response->assertOk();

    // Section 2 Prescribed Medicines removed
    $response->assertDontSee('2. Prescribed Products & Medicines', false);
    $response->assertDontSee('Prescribed Products &amp; Medicines', false);
    $response->assertDontSee('id="selected-medicines-table"', false);
    $response->assertDontSee('id="medicine-search-input"', false);

    // Cost Breakdown removed
    $response->assertDontSee('Cost Breakdown', false);
    $response->assertDontSee('Procedure Net + Medicines Net', false);

    // Grand Total Payable and Billing intact
    $response->assertSee('Grand Total Payable', false);
    $response->assertSee('Immediate Payment Settlement', false);
    $response->assertSee('paid_amount', false);
});

test('sidebar and login page display dental tooth logo', function () {
    $loginResponse = $this->get(route('login'));
    $loginResponse->assertOk();
    $loginResponse->assertSee('dental-tooth-logo', false);

    $user = User::factory()->create(['role' => 'admin']);
    $dashResponse = $this->actingAs($user)->get(route('dashboard'));
    $dashResponse->assertOk();
    $dashResponse->assertSee('dental-tooth-logo', false);
});
