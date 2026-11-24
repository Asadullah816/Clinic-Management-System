<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\PatientHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientHistoryAndPreAppOutstandingsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $doctorA;

    private User $doctorB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Dr. Badshah Afsar (Admin)',
            'email' => 'admin@clinic.test',
            'role' => User::ROLE_ADMIN,
            'password' => 'password',
        ]);

        $this->doctorA = User::create([
            'name' => 'Doctor Alpha',
            'email' => 'doctor.a@clinic.test',
            'role' => User::ROLE_DOCTOR,
            'password' => 'password',
        ]);

        $this->doctorB = User::create([
            'name' => 'Doctor Beta',
            'email' => 'doctor.b@clinic.test',
            'role' => User::ROLE_DOCTOR,
            'password' => 'password',
        ]);
    }

    private function createPatient(string $firstName = 'Muhammad', string $lastName = 'Ali', ?int $assignedDoctorId = null, string $phone = '03001234567'): Patient
    {
        $patient = Patient::create([
            'patient_number' => 'P-9999',
            'assigned_doctor_id' => $assignedDoctorId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'gender' => 'male',
            'phone' => $phone,
            'status' => 'active',
            'is_historical' => false,
        ]);

        $patient->update(['patient_number' => 'P-'.str_pad($patient->id, 4, '0', STR_PAD_LEFT)]);

        return $patient;
    }

    public function test_can_register_new_historical_patient_with_pre_app_treatment_and_dues(): void
    {
        $response = $this->actingAs($this->admin)->post(route('patient-histories.store'), [
            'patient_type' => 'new',
            'first_name' => 'Tariq',
            'last_name' => 'Mehmood',
            'gender' => 'male',
            'phone' => '03001234567',
            'email' => 'tariq@example.com',
            'visit_date' => '2023-05-15',
            'doctor_id' => $this->doctorA->id,
            'tooth_numbers' => '16, 17',
            'procedures' => 'Old Root Canal Treatment and Metal-Ceramic Crown',
            'diagnosis' => 'Chronic Apical Periodontitis',
            'notes' => 'Paper card #842 from old register',
            'total_cost' => 15000,
            'paid_amount' => 5000,
            'payment_method' => 'cash',
        ]);

        $this->assertDatabaseHas('patients', [
            'first_name' => 'Tariq',
            'last_name' => 'Mehmood',
            'phone' => '03001234567',
            'is_historical' => true,
        ]);

        $patient = Patient::where('phone', '03001234567')->firstOrFail();

        $this->assertDatabaseHas('patient_histories', [
            'patient_id' => $patient->id,
            'doctor_id' => $this->doctorA->id,
            'total_cost' => 15000,
            'paid_amount' => 5000,
            'due_amount' => 10000,
            'status' => PatientHistory::STATUS_PARTIAL,
        ]);

        // Assert payment record was logged for the initial paid amount
        $this->assertDatabaseHas('patient_history_payments', [
            'patient_id' => $patient->id,
            'amount' => 5000,
            'payment_method' => 'cash',
        ]);

        $history = PatientHistory::where('patient_id', $patient->id)->firstOrFail();
        $response->assertRedirect(route('patient-histories.show', $history));
    }

    public function test_can_link_historical_record_to_existing_patient(): void
    {
        $patient = $this->createPatient('Zubair', 'Khan', null, '03129876543');

        $response = $this->actingAs($this->admin)->post(route('patient-histories.store'), [
            'patient_type' => 'existing',
            'patient_id' => $patient->id,
            'visit_date' => '2022-11-20',
            'doctor_id' => $this->doctorA->id,
            'tooth_numbers' => '21',
            'procedures' => 'Composite Veneer Restoration',
            'total_cost' => 8000,
            'paid_amount' => 0,
        ]);

        $this->assertTrue($patient->fresh()->is_historical);

        $this->assertDatabaseHas('patient_histories', [
            'patient_id' => $patient->id,
            'doctor_id' => $this->doctorA->id,
            'total_cost' => 8000,
            'paid_amount' => 0,
            'due_amount' => 8000,
            'status' => PatientHistory::STATUS_PENDING,
        ]);

        $history = PatientHistory::where('patient_id', $patient->id)->firstOrFail();
        $response->assertRedirect(route('patient-histories.show', $history));
    }

    public function test_can_receive_payment_against_historical_dues(): void
    {
        $patient = $this->createPatient('Sajid', 'Ali');
        $patient->update(['is_historical' => true]);

        $history = PatientHistory::create([
            'record_number' => 'HIS-20230101-0001',
            'patient_id' => $patient->id,
            'doctor_id' => $this->doctorA->id,
            'visit_date' => '2023-01-01',
            'procedures' => 'Old Scaling & Polishing',
            'total_cost' => 6000,
            'paid_amount' => 2000,
            'due_amount' => 4000,
            'status' => PatientHistory::STATUS_PARTIAL,
            'created_by' => $this->admin->id,
        ]);

        // Receive partial payment of 2500
        $response = $this->actingAs($this->admin)->post(route('patient-histories.record-payment', $history), [
            'amount' => 2500,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank',
            'reference' => 'TXN-987654',
            'notes' => 'Received via bank transfer',
            'redirect_url' => route('patients.show', $patient).'#patient-history',
        ]);

        $response->assertRedirect(route('patients.show', $patient).'#patient-history');

        $history->refresh();
        $this->assertEquals(4500, $history->paid_amount);
        $this->assertEquals(1500, $history->due_amount);
        $this->assertEquals(PatientHistory::STATUS_PARTIAL, $history->status);

        $this->assertDatabaseHas('patient_history_payments', [
            'patient_history_id' => $history->id,
            'patient_id' => $patient->id,
            'amount' => 2500,
            'payment_method' => 'bank',
            'reference' => 'TXN-987654',
            'received_by' => $this->admin->id,
        ]);

        // Pay remaining 1500 to fully clear the balance
        $this->actingAs($this->admin)->post(route('patient-histories.record-payment', $history), [
            'amount' => 1500,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
        ]);

        $history->refresh();
        $this->assertEquals(6000, $history->paid_amount);
        $this->assertEquals(0, $history->due_amount);
        $this->assertEquals(PatientHistory::STATUS_PAID, $history->status);
    }

    public function test_whatsapp_reminder_link_generation_for_historical_dues(): void
    {
        $patient = $this->createPatient('Kamran', 'Akmal', null, '03335557788');

        $history = PatientHistory::create([
            'record_number' => 'HIS-20230202-0002',
            'patient_id' => $patient->id,
            'doctor_id' => $this->doctorA->id,
            'visit_date' => '2023-02-02',
            'procedures' => 'Wisdom Tooth Surgical Extraction',
            'total_cost' => 12000,
            'paid_amount' => 4000,
            'due_amount' => 8000,
            'status' => PatientHistory::STATUS_PARTIAL,
            'created_by' => $this->admin->id,
        ]);

        $url = $history->whatsappReminderUrl();

        $this->assertNotNull($url);
        $this->assertStringStartsWith('https://wa.me/923335557788?text=', $url);
        $this->assertStringContainsString(urlencode('Kamran Akmal'), $url);
        $this->assertStringContainsString(urlencode('HIS-20230202-0002'), $url);
        $this->assertStringContainsString(urlencode('8,000.00'), $url);
    }

    public function test_historical_outstandings_page_displays_dues_and_filters(): void
    {
        $patient = $this->createPatient('Naveed', 'Ahmed', null, '03009998877');

        PatientHistory::create([
            'record_number' => 'HIS-20230303-0003',
            'patient_id' => $patient->id,
            'doctor_id' => $this->doctorA->id,
            'visit_date' => '2023-03-03',
            'procedures' => 'Old Dental Bridge',
            'total_cost' => 25000,
            'paid_amount' => 10000,
            'due_amount' => 15000,
            'status' => PatientHistory::STATUS_PARTIAL,
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('patient-histories.outstanding'));

        $response->assertOk();
        $response->assertSee('Historical Patients Outstandings &amp; Financial Overview', false);
        $response->assertSee('HIS-20230303-0003');
        $response->assertSee('Naveed Ahmed');
        $response->assertSee('15,000.00');
        $response->assertSee('https://wa.me/923009998877');
    }

    public function test_doctor_only_sees_their_own_historical_records_and_is_scoped(): void
    {
        $patientA = $this->createPatient('PatientA', 'One', null, '03001111111');
        $patientB = $this->createPatient('PatientB', 'Two', null, '03002222222');

        $histA = PatientHistory::create([
            'record_number' => 'HIS-DOCA-001',
            'patient_id' => $patientA->id,
            'doctor_id' => $this->doctorA->id,
            'visit_date' => '2023-04-01',
            'procedures' => 'Doctor A Past Procedure',
            'total_cost' => 10000,
            'paid_amount' => 2000,
            'due_amount' => 8000,
            'status' => PatientHistory::STATUS_PARTIAL,
            'created_by' => $this->admin->id,
        ]);

        $histB = PatientHistory::create([
            'record_number' => 'HIS-DOCB-001',
            'patient_id' => $patientB->id,
            'doctor_id' => $this->doctorB->id,
            'visit_date' => '2023-04-02',
            'procedures' => 'Doctor B Past Procedure',
            'total_cost' => 12000,
            'paid_amount' => 3000,
            'due_amount' => 9000,
            'status' => PatientHistory::STATUS_PARTIAL,
            'created_by' => $this->admin->id,
        ]);

        // Doctor A visits historical outstandings
        $response = $this->actingAs($this->doctorA)->get(route('patient-histories.outstanding'));
        $response->assertOk();
        $response->assertSee('HIS-DOCA-001');
        $response->assertDontSee('HIS-DOCB-001');

        // Doctor A can view their own record
        $this->actingAs($this->doctorA)
            ->get(route('patient-histories.show', $histA))
            ->assertOk();

        // Doctor A cannot view Doctor B's record (403)
        $this->actingAs($this->doctorA)
            ->get(route('patient-histories.show', $histB))
            ->assertForbidden();

        // Doctor A can view patient A profile because they have historical attendance with Doctor A
        $this->actingAs($this->doctorA)
            ->get(route('patients.show', $patientA))
            ->assertOk();

        // Doctor A cannot view patient B profile (403)
        $this->actingAs($this->doctorA)
            ->get(route('patients.show', $patientB))
            ->assertForbidden();
    }

    public function test_patient_profile_displays_pre_app_history_tab_and_combined_due_balance(): void
    {
        $patient = $this->createPatient('Farhan', 'Saeed');

        PatientHistory::create([
            'record_number' => 'HIS-20230505-0005',
            'patient_id' => $patient->id,
            'doctor_id' => $this->doctorA->id,
            'visit_date' => '2023-05-05',
            'tooth_numbers' => '36',
            'procedures' => 'Old Amalgam Removal and Composite Build-up',
            'total_cost' => 7000,
            'paid_amount' => 3000,
            'due_amount' => 4000,
            'status' => PatientHistory::STATUS_PARTIAL,
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('patients.show', $patient));

        $response->assertOk();
        $response->assertSee('Pre-App History');
        $response->assertSee('Pre-App Historical Treatment Records');
        $response->assertSee('HIS-20230505-0005');
        $response->assertSee('Old Amalgam Removal and Composite Build-up');
        $response->assertSee('4,000.00');
        $response->assertSee('Total Combined Balance Due:');
    }
}
