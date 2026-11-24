<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Treatment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorAssignmentAndRoleScopingTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::create([
            'name' => 'Dr. Badshah Afsar (Admin)',
            'email' => 'admin@clinic.test',
            'role' => User::ROLE_ADMIN,
            'password' => 'password',
        ]);
    }

    private function createDoctor(string $name = 'Dr. Tariq Khan', string $email = 'doctor@clinic.test'): User
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'role' => User::ROLE_DOCTOR,
            'password' => 'password',
        ]);
    }

    private function createPatient(string $firstName = 'Muhammad', string $lastName = 'Ali', ?int $assignedDoctorId = null): Patient
    {
        $patient = Patient::create([
            'patient_number' => 'P-9999',
            'assigned_doctor_id' => $assignedDoctorId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'gender' => 'male',
            'phone' => '03001234567',
            'status' => 'active',
        ]);

        $patient->update(['patient_number' => 'P-'.str_pad($patient->id, 4, '0', STR_PAD_LEFT)]);

        return $patient;
    }

    private function createTreatment(string $name = 'Root Canal Treatment', float $price = 12000.00): Treatment
    {
        return Treatment::create([
            'name' => $name,
            'price' => $price,
            'duration' => 60,
            'status' => Treatment::STATUS_ACTIVE,
        ]);
    }

    public function test_user_roles_include_doctor_and_admin_is_super_admin(): void
    {
        $admin = $this->createAdmin();
        $doctor = $this->createDoctor();

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isDoctor());

        $this->assertTrue($doctor->isDoctor());
        $this->assertFalse($doctor->isAdmin());

        $roles = User::roles();
        $this->assertArrayHasKey(User::ROLE_DOCTOR, $roles);
        $this->assertArrayHasKey(User::ROLE_ADMIN, $roles);

        $doctors = User::doctors()->get();
        $this->assertCount(2, $doctors);
    }

    public function test_admin_can_book_appointment_with_assigned_doctor_and_auto_assigns_patient(): void
    {
        $admin = $this->createAdmin();
        $doctor = $this->createDoctor();
        $patient = $this->createPatient();
        $treatment = $this->createTreatment();

        $tomorrow = today()->addDay()->toDateString();

        $response = $this->actingAs($admin)->post(route('appointments.store'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'treatment_id' => $treatment->id,
            'treatment_ids' => [$treatment->id],
            'appointment_date' => $tomorrow,
            'appointment_time' => '11:00',
            'status' => Appointment::STATUS_SCHEDULED,
            'notes' => 'Patient assigned to Dr. Tariq',
        ]);

        $response->assertRedirect(route('appointments.index'));
        $this->assertDatabaseHas('appointments', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => $tomorrow.' 00:00:00',
            'status' => Appointment::STATUS_SCHEDULED,
        ]);

        // Patient assigned_doctor_id should be automatically updated to the doctor
        $this->assertEquals($doctor->id, $patient->fresh()->assigned_doctor_id);
    }

    public function test_conflict_prevented_for_same_doctor_at_same_time_but_allowed_for_different_doctors(): void
    {
        $admin = $this->createAdmin();
        $doctor1 = $this->createDoctor('Dr. Tariq Khan', 'tariq@clinic.test');
        $doctor2 = $this->createDoctor('Dr. Ayesha Malik', 'ayesha@clinic.test');
        $patient1 = $this->createPatient('Patient', 'One');
        $patient2 = $this->createPatient('Patient', 'Two');
        $patient3 = $this->createPatient('Patient', 'Three');
        $treatment = $this->createTreatment();

        $tomorrow = today()->addDay()->toDateString();

        // 1. Book appointment for Doctor 1 at 11:00
        $this->actingAs($admin)->post(route('appointments.store'), [
            'patient_id' => $patient1->id,
            'doctor_id' => $doctor1->id,
            'treatment_id' => $treatment->id,
            'appointment_date' => $tomorrow,
            'appointment_time' => '11:00',
            'status' => Appointment::STATUS_SCHEDULED,
        ])->assertRedirect(route('appointments.index'));

        // 2. Try booking same Doctor 1 at 11:00 -> Conflict rejected
        $conflictResponse = $this->actingAs($admin)->from(route('appointments.create'))->post(route('appointments.store'), [
            'patient_id' => $patient2->id,
            'doctor_id' => $doctor1->id,
            'treatment_id' => $treatment->id,
            'appointment_date' => $tomorrow,
            'appointment_time' => '11:00',
            'status' => Appointment::STATUS_SCHEDULED,
        ]);

        $conflictResponse->assertRedirect(route('appointments.create'));
        $conflictResponse->assertSessionHasErrors(['appointment_time']);

        // 3. Book Doctor 2 at the exact same time (11:00) -> Allowed!
        $allowedResponse = $this->actingAs($admin)->post(route('appointments.store'), [
            'patient_id' => $patient3->id,
            'doctor_id' => $doctor2->id,
            'treatment_id' => $treatment->id,
            'appointment_date' => $tomorrow,
            'appointment_time' => '11:00',
            'status' => Appointment::STATUS_SCHEDULED,
        ]);

        $allowedResponse->assertRedirect(route('appointments.index'));
        $this->assertDatabaseHas('appointments', [
            'patient_id' => $patient3->id,
            'doctor_id' => $doctor2->id,
            'status' => Appointment::STATUS_SCHEDULED,
        ]);
    }

    public function test_doctor_only_sees_their_own_appointments_while_admin_sees_all(): void
    {
        $admin = $this->createAdmin();
        $doctor1 = $this->createDoctor('Dr. Tariq Khan', 'tariq@clinic.test');
        $doctor2 = $this->createDoctor('Dr. Ayesha Malik', 'ayesha@clinic.test');
        $patient1 = $this->createPatient('Patient', 'One');
        $patient2 = $this->createPatient('Patient', 'Two');
        $treatment = $this->createTreatment();

        $tomorrow = today()->addDay()->toDateString();

        $appt1 = Appointment::create([
            'patient_id' => $patient1->id,
            'doctor_id' => $doctor1->id,
            'treatment_id' => $treatment->id,
            'appointment_date' => $tomorrow,
            'appointment_time' => '10:00',
            'status' => Appointment::STATUS_SCHEDULED,
            'created_by' => $admin->id,
        ]);

        $appt2 = Appointment::create([
            'patient_id' => $patient2->id,
            'doctor_id' => $doctor2->id,
            'treatment_id' => $treatment->id,
            'appointment_date' => $tomorrow,
            'appointment_time' => '14:00',
            'status' => Appointment::STATUS_SCHEDULED,
            'created_by' => $admin->id,
        ]);

        // Login as Doctor 1
        $doctorResponse = $this->actingAs($doctor1)->get(route('appointments.index'));
        $doctorResponse->assertStatus(200);
        $doctorResponse->assertSee($patient1->full_name);
        $doctorResponse->assertDontSee($patient2->full_name);

        // Doctor 1 viewing own appointment details
        $this->actingAs($doctor1)->get(route('appointments.show', $appt1))->assertStatus(200);

        // Doctor 1 trying to view Doctor 2's appointment details -> 403 Forbidden
        $this->actingAs($doctor1)->get(route('appointments.show', $appt2))->assertStatus(403);

        // Login as Admin (Super Admin) -> Sees both appointments
        $adminResponse = $this->actingAs($admin)->get(route('appointments.index'));
        $adminResponse->assertStatus(200);
        $adminResponse->assertSee($patient1->full_name);
        $adminResponse->assertSee($patient2->full_name);

        // Admin filters by Doctor 2
        $adminFiltered = $this->actingAs($admin)->get(route('appointments.index', ['doctor_id' => $doctor2->id]));
        $adminFiltered->assertStatus(200);
        $adminFiltered->assertDontSee($patient1->full_name);
        $adminFiltered->assertSee($patient2->full_name);
    }

    public function test_doctor_only_sees_their_own_patients_and_cannot_view_other_patients(): void
    {
        $admin = $this->createAdmin();
        $doctor1 = $this->createDoctor('Dr. Tariq Khan', 'tariq@clinic.test');
        $doctor2 = $this->createDoctor('Dr. Ayesha Malik', 'ayesha@clinic.test');

        $patient1 = $this->createPatient('TariqPatient', 'Alpha', $doctor1->id);
        $patient2 = $this->createPatient('AyeshaPatient', 'Beta', $doctor2->id);

        // Doctor 1 patient list
        $response1 = $this->actingAs($doctor1)->get(route('patients.index'));
        $response1->assertStatus(200);
        $response1->assertSee($patient1->full_name);
        $response1->assertDontSee($patient2->full_name);

        // Doctor 1 access own patient profile -> 200
        $this->actingAs($doctor1)->get(route('patients.show', $patient1))->assertStatus(200);

        // Doctor 1 access Doctor 2 patient profile -> 403 Forbidden
        $this->actingAs($doctor1)->get(route('patients.show', $patient2))->assertStatus(403);

        // Super Admin sees both patients and can access both profiles
        $adminResponse = $this->actingAs($admin)->get(route('patients.index'));
        $adminResponse->assertStatus(200);
        $adminResponse->assertSee($patient1->full_name);
        $adminResponse->assertSee($patient2->full_name);

        $this->actingAs($admin)->get(route('patients.show', $patient1))->assertStatus(200);
        $this->actingAs($admin)->get(route('patients.show', $patient2))->assertStatus(200);
    }

    public function test_dashboard_is_scoped_for_doctor(): void
    {
        $admin = $this->createAdmin();
        $doctor1 = $this->createDoctor('Dr. Tariq Khan', 'tariq@clinic.test');
        $doctor2 = $this->createDoctor('Dr. Ayesha Malik', 'ayesha@clinic.test');

        $patient1 = $this->createPatient('TariqPatient', 'Alpha', $doctor1->id);
        $patient2 = $this->createPatient('AyeshaPatient', 'Beta', $doctor2->id);
        $treatment = $this->createTreatment();

        // Today's appointments
        Appointment::create([
            'patient_id' => $patient1->id,
            'doctor_id' => $doctor1->id,
            'treatment_id' => $treatment->id,
            'appointment_date' => today(),
            'appointment_time' => '10:00',
            'status' => Appointment::STATUS_SCHEDULED,
            'created_by' => $admin->id,
        ]);

        Appointment::create([
            'patient_id' => $patient2->id,
            'doctor_id' => $doctor2->id,
            'treatment_id' => $treatment->id,
            'appointment_date' => today(),
            'appointment_time' => '11:00',
            'status' => Appointment::STATUS_SCHEDULED,
            'created_by' => $admin->id,
        ]);

        // Doctor 1 visits dashboard
        $responseDoc1 = $this->actingAs($doctor1)->get(route('dashboard'));
        $responseDoc1->assertStatus(200);
        $responseDoc1->assertSee($patient1->full_name);
        $responseDoc1->assertDontSee($patient2->full_name);

        // Super Admin visits dashboard
        $responseAdmin = $this->actingAs($admin)->get(route('dashboard'));
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertSee($patient1->full_name);
        $responseAdmin->assertSee($patient2->full_name);
    }

    public function test_doctor_recording_treatment_automatically_assigns_doctor_id(): void
    {
        $doctor = $this->createDoctor();
        $patient = $this->createPatient('Hamza', 'Raza');
        $treatment = $this->createTreatment('Teeth Whitening', 15000.00);

        $response = $this->actingAs($doctor)->post(route('patient-treatments.store'), [
            'patient_id' => $patient->id,
            'treatment_id' => $treatment->id,
            'treatment_date' => today()->toDateString(),
            'price' => 15000.00,
            'discount' => 0.00,
            'notes' => 'Laser whitening performed',
        ]);

        $response->assertRedirect(route('patient-treatments.index'));

        $this->assertDatabaseHas('patient_treatments', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'price' => 15000.00,
        ]);

        // Patient is now linked to the doctor
        $this->assertEquals($doctor->id, $patient->fresh()->assigned_doctor_id);
    }
}
