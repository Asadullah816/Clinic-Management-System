<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Treatment;
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
        ]);

        $patient->update(['patient_number' => 'P-'.str_pad($patient->id, 4, '0', STR_PAD_LEFT)]);

        return $patient;
    }

    public function test_sidebar_removes_patient_history_and_add_pre_app_record_links(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertDontSee('Patient History (Pre-App)');
        $response->assertDontSee('Add Pre-App Record');
        $response->assertDontSee('Pre-App Outstandings');

        // Only standard Patients & Care menu items remain
        $response->assertSee('All Patients');
        $response->assertSee('Add Patient');
        $response->assertSee('Appointments');
    }

    public function test_patient_can_have_medical_history_added_directly_on_their_profile(): void
    {
        $patient = $this->createPatient('Usman', 'Ghani');

        // Add medical history record directly to this patient
        $response = $this->actingAs($this->admin)->post(route('medical-histories.store', $patient), [
            'visit_date' => '2023-01-10',
            'chief_complaint' => 'Old chronic sensitivity and previous crown placement',
            'diagnosis' => 'Prior pulpitis with successful obturation',
            'previous_treatment' => 'Root canal on upper right molar done elsewhere',
            'allergies' => 'Penicillin',
            'medical_conditions' => 'Hypertension',
            'notes' => 'Old clinical record transferred into patient chart',
        ]);

        $response->assertRedirect(route('patients.show', $patient).'#medical-history');

        $this->assertDatabaseHas('medical_histories', [
            'patient_id' => $patient->id,
            'chief_complaint' => 'Old chronic sensitivity and previous crown placement',
        ]);

        // Patient profile page displays the medical history and does NOT have a separate pre-app tab
        $showResponse = $this->actingAs($this->admin)->get(route('patients.show', $patient));
        $showResponse->assertOk();
        $showResponse->assertSee('Medical History');
        $showResponse->assertSee('Old chronic sensitivity and previous crown placement');
        $showResponse->assertDontSee('Pre-App History');
        $showResponse->assertDontSee('Pre-App Historical Treatment Records');
        $showResponse->assertDontSee('Pre-App Past Total');
    }

    public function test_patient_prior_treatments_and_dues_use_the_same_records_and_calculations(): void
    {
        $patient = $this->createPatient('Farhan', 'Ahmed', $this->doctorA->id, '03451122334');
        $treatment = Treatment::create([
            'name' => 'Dental Bridge Restoration',
            'price' => 30000,
            'duration' => 60,
            'status' => 'active',
        ]);

        // Record a treatment with a prior visit date directly under this patient
        $response = $this->actingAs($this->admin)->post(route('patient-treatments.store'), [
            'patient_id' => $patient->id,
            'doctor_id' => $this->doctorA->id,
            'treatment_date' => '2023-08-15',
            'procedure_name' => 'Dental Bridge Restoration',
            'treatment_id' => $treatment->id,
            'tooth_number' => '24-26',
            'price' => 30000,
            'procedures' => [
                [
                    'treatment_id' => $treatment->id,
                    'name' => 'Dental Bridge Restoration',
                    'price' => 30000,
                    'notes' => 'Prior procedure completed in 2023',
                ],
            ],
            'paid_amount' => 10000,
            'discount' => 0,
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect();

        // 1. Treatment was saved in standard patient_treatments
        $this->assertDatabaseHas('patient_treatments', [
            'patient_id' => $patient->id,
            'procedure_name' => 'Dental Bridge Restoration',
            'total_amount' => 30000,
        ]);

        // 2. Standard Invoice was generated with due amount
        $this->assertDatabaseHas('invoices', [
            'patient_id' => $patient->id,
            'total_amount' => 30000,
            'paid_amount' => 10000,
            'due_amount' => 20000,
            'status' => 'partial',
        ]);

        // 3. Patient profile calculates with the exact same standard invoices and dues
        $profileResponse = $this->actingAs($this->admin)->get(route('patients.show', $patient));
        $profileResponse->assertOk();
        $profileResponse->assertSee('20,000.00'); // Current Due in Overview
        $profileResponse->assertDontSee('Pre-App History');
        $profileResponse->assertDontSee('Pre-App Remaining Due');

        // 4. Outstanding Dues page displays this patient's invoice due
        $dueResponse = $this->actingAs($this->admin)->get(route('payments.outstanding'));
        $dueResponse->assertOk();
        $dueResponse->assertSee('Outstanding Dues');
        $dueResponse->assertSee('Farhan Ahmed');
        $dueResponse->assertSee('20,000.00');
    }

    public function test_legacy_patient_history_routes_redirect_to_standard_routes(): void
    {
        $patient = $this->createPatient('Naveed', 'Khan');

        $this->actingAs($this->admin)
            ->get(route('patient-histories.index'))
            ->assertRedirect(route('patients.index'));

        $this->actingAs($this->admin)
            ->get(route('patient-histories.create', ['patient_id' => $patient->id]))
            ->assertRedirect(route('patients.show', $patient));

        $this->actingAs($this->admin)
            ->get(route('patient-histories.outstanding'))
            ->assertRedirect(route('payments.outstanding'));
    }

    public function test_doctor_only_sees_their_own_patients_on_outstanding_dues(): void
    {
        $patientA = $this->createPatient('Patient Alpha', 'Test', $this->doctorA->id);
        $patientB = $this->createPatient('Patient Beta', 'Test', $this->doctorB->id);

        Invoice::create([
            'patient_id' => $patientA->id,
            'invoice_number' => 'INV-DOCA-001',
            'invoice_date' => today(),
            'subtotal' => 10000,
            'discount' => 0,
            'total_amount' => 10000,
            'paid_amount' => 2000,
            'due_amount' => 8000,
            'status' => 'partial',
        ]);

        Invoice::create([
            'patient_id' => $patientB->id,
            'invoice_number' => 'INV-DOCB-001',
            'invoice_date' => today(),
            'subtotal' => 15000,
            'discount' => 0,
            'total_amount' => 15000,
            'paid_amount' => 5000,
            'due_amount' => 10000,
            'status' => 'partial',
        ]);

        // Doctor A only sees Patient Alpha on outstanding dues
        $response = $this->actingAs($this->doctorA)->get(route('payments.outstanding'));
        $response->assertOk();
        $response->assertSee('INV-DOCA-001');
        $response->assertSee('Patient Alpha');
        $response->assertDontSee('INV-DOCB-001');
        $response->assertDontSee('Patient Beta');
    }
}
