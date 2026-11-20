<?php

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Treatment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('booking appointment in past date is prohibited', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $patient = Patient::create([
        'patient_number' => 'P-0001',
        'first_name' => 'Ahmad',
        'last_name' => 'Khan',
        'gender' => 'male',
        'phone' => '03001234567',
        'status' => 'active',
    ]);

    $treatment = Treatment::create([
        'name' => 'Scaling & Polishing',
        'price' => 3000.00,
        'duration' => 30,
        'status' => 'active',
    ]);

    $pastDate = Carbon::yesterday()->toDateString();

    $response = $this->actingAs($user)->post(route('appointments.store'), [
        'patient_id' => $patient->id,
        'treatment_ids' => [$treatment->id],
        'appointment_date' => $pastDate,
        'appointment_time' => '15:00',
        'status' => 'scheduled',
    ]);

    $response->assertSessionHasErrors('appointment_date');
    expect(Appointment::count())->toBe(0);
});

test('booking appointment for a past time today is prohibited', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $patient = Patient::create([
        'patient_number' => 'P-0002',
        'first_name' => 'Sara',
        'last_name' => 'Khan',
        'gender' => 'female',
        'phone' => '03007654321',
        'status' => 'active',
    ]);

    $treatment = Treatment::create([
        'name' => 'Consultation',
        'price' => 1000.00,
        'duration' => 20,
        'status' => 'active',
    ]);

    // Choose a time earlier than now
    $pastTime = now()->subHours(2)->format('H:i');

    $response = $this->actingAs($user)->post(route('appointments.store'), [
        'patient_id' => $patient->id,
        'treatment_ids' => [$treatment->id],
        'appointment_date' => today()->toDateString(),
        'appointment_time' => $pastTime,
        'status' => 'scheduled',
    ]);

    $response->assertSessionHasErrors('appointment_time');
    expect(Appointment::count())->toBe(0);
});

test('double booking an appointment at the exact same date and time slot is prohibited', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $patient1 = Patient::create([
        'patient_number' => 'P-0003',
        'first_name' => 'Tariq',
        'last_name' => 'Aziz',
        'gender' => 'male',
        'phone' => '03001112233',
        'status' => 'active',
    ]);

    $patient2 = Patient::create([
        'patient_number' => 'P-0004',
        'first_name' => 'Hamza',
        'last_name' => 'Ali',
        'gender' => 'male',
        'phone' => '03004445566',
        'status' => 'active',
    ]);

    $treatment = Treatment::create([
        'name' => 'Tooth Extraction',
        'price' => 4000.00,
        'duration' => 40,
        'status' => 'active',
    ]);

    $futureDate = Carbon::tomorrow()->toDateString();

    // First booking at 15:00
    $res1 = $this->actingAs($user)->post(route('appointments.store'), [
        'patient_id' => $patient1->id,
        'treatment_ids' => [$treatment->id],
        'appointment_date' => $futureDate,
        'appointment_time' => '15:00',
        'status' => 'scheduled',
    ]);
    $res1->assertRedirect(route('appointments.index'));
    expect(Appointment::count())->toBe(1);

    // Second booking attempting same date and 15:00 for another patient
    $res2 = $this->actingAs($user)->post(route('appointments.store'), [
        'patient_id' => $patient2->id,
        'treatment_ids' => [$treatment->id],
        'appointment_date' => $futureDate,
        'appointment_time' => '15:00',
        'status' => 'scheduled',
    ]);

    $res2->assertSessionHasErrors('appointment_time');
    expect(Appointment::count())->toBe(1);
});

test('appointments can be booked with multiple treatments where first is required and others optional', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $patient = Patient::create([
        'patient_number' => 'P-0005',
        'first_name' => 'Zainab',
        'last_name' => 'Bibi',
        'gender' => 'female',
        'phone' => '03112223344',
        'status' => 'active',
    ]);

    $t1 = Treatment::create(['name' => 'Scaling & Polishing', 'price' => 3000.00, 'duration' => 30, 'status' => 'active']);
    $t2 = Treatment::create(['name' => 'Composite Filling', 'price' => 4500.00, 'duration' => 45, 'status' => 'active']);
    $t3 = Treatment::create(['name' => 'Fluoride Application', 'price' => 1500.00, 'duration' => 15, 'status' => 'active']);

    $futureDate = Carbon::tomorrow()->toDateString();

    $response = $this->actingAs($user)->post(route('appointments.store'), [
        'patient_id' => $patient->id,
        'treatment_ids' => [$t1->id, $t2->id, $t3->id],
        'appointment_date' => $futureDate,
        'appointment_time' => '16:00',
        'status' => 'scheduled',
    ]);

    $response->assertRedirect(route('appointments.index'));
    $appointment = Appointment::where('patient_id', $patient->id)->first();
    expect($appointment)->not->toBeNull();
    expect($appointment->treatment_id)->toBe($t1->id); // Primary treatment
    expect($appointment->treatments)->toHaveCount(3);
    expect($appointment->treatment_names)->toContain('Scaling & Polishing');
    expect($appointment->treatment_names)->toContain('Composite Filling');
    expect($appointment->treatment_names)->toContain('Fluoride Application');
});

test('appointments index and dashboard can filter appointments by date and status', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $patient = Patient::create([
        'patient_number' => 'P-0006',
        'first_name' => 'Naveed',
        'last_name' => 'Akhtar',
        'gender' => 'male',
        'phone' => '03445556677',
        'status' => 'active',
    ]);

    $treatment = Treatment::create(['name' => 'Crown Prep', 'price' => 15000.00, 'duration' => 60, 'status' => 'active']);

    $todayDate = today()->toDateString();
    $tomorrowDate = today()->addDay()->toDateString();

    $todayAppt = Appointment::create([
        'patient_id' => $patient->id,
        'treatment_id' => $treatment->id,
        'appointment_date' => $todayDate,
        'appointment_time' => '17:30',
        'status' => Appointment::STATUS_SCHEDULED,
        'created_by' => $user->id,
    ]);

    $tomorrowAppt = Appointment::create([
        'patient_id' => $patient->id,
        'treatment_id' => $treatment->id,
        'appointment_date' => $tomorrowDate,
        'appointment_time' => '11:00',
        'status' => Appointment::STATUS_SCHEDULED,
        'created_by' => $user->id,
    ]);

    // Test Dashboard today filter
    $dashRes = $this->actingAs($user)->get(route('dashboard', ['appt_date' => $todayDate]));
    $dashRes->assertOk();
    $dashRes->assertSee('17:30');
    $dashRes->assertDontSee('11:00');

    // Test Dashboard tomorrow filter
    $dashTomorrowRes = $this->actingAs($user)->get(route('dashboard', ['appt_date' => $tomorrowDate]));
    $dashTomorrowRes->assertOk();
    $dashTomorrowRes->assertSee('11:00');
    $dashTomorrowRes->assertDontSee('17:30');

    // Test Appointments index date filter
    $indexToday = $this->actingAs($user)->get(route('appointments.index', ['date_filter' => 'today']));
    $indexToday->assertOk();
    $indexToday->assertSee('17:30');
    $indexToday->assertDontSee('11:00');
});

test('booked slots endpoint returns booked times for the given date', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $patient = Patient::create([
        'patient_number' => 'P-0007',
        'first_name' => 'Kamran',
        'last_name' => 'Akmal',
        'gender' => 'male',
        'phone' => '03009998877',
        'status' => 'active',
    ]);

    $treatment = Treatment::create(['name' => 'Checkup', 'price' => 500.00, 'duration' => 15, 'status' => 'active']);
    $futureDate = Carbon::tomorrow()->toDateString();

    Appointment::create([
        'patient_id' => $patient->id,
        'treatment_id' => $treatment->id,
        'appointment_date' => $futureDate,
        'appointment_time' => '15:00',
        'status' => Appointment::STATUS_SCHEDULED,
        'created_by' => $user->id,
    ]);

    $response = $this->actingAs($user)->get(route('appointments.booked-slots', ['date' => $futureDate]));
    $response->assertOk();
    $response->assertJsonFragment([
        'time' => '15:00',
        'patient_name' => 'Kamran Akmal',
    ]);
});
