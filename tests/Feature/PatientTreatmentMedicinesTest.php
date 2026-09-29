<?php

use App\Models\Invoice;
use App\Models\Medicine;
use App\Models\MedicineCategory;
use App\Models\MedicineUsage;
use App\Models\Patient;
use App\Models\PatientTreatment;
use App\Models\PatientTreatmentMedicine;
use App\Models\Payment;
use App\Models\Treatment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

test('medicine can be created with image and updated with a new image', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $category = MedicineCategory::create([
        'name' => 'Skincare',
        'status' => 'active',
    ]);

    // Fake image upload
    $file = UploadedFile::fake()->image('retinol-serum.jpg', 400, 400);

    $response = $this->actingAs($user)->post(route('medicines.store'), [
        'name'                 => 'Retinol Serum 0.5%',
        'generic_name'         => 'Retinoid',
        'medicine_category_id' => $category->id,
        'purchase_price'       => 20.00,
        'selling_price'        => 45.00,
        'stock_quantity'       => 25,
        'minimum_stock'        => 5,
        'unit'                 => 'bottle',
        'status'               => 'active',
        'image'                => $file,
    ]);

    $response->assertRedirect(route('medicines.index'));
    $response->assertSessionHas('success');

    $medicine = Medicine::where('name', 'Retinol Serum 0.5%')->first();
    expect($medicine)->not->toBeNull();
    expect($medicine->image)->not->toBeNull();
    expect($medicine->image_url)->toContain('/uploads/medicines/');

    // Clean up created file from public/uploads/medicines
    if ($medicine->image && File::exists(public_path('uploads/medicines/' . $medicine->image))) {
        File::delete(public_path('uploads/medicines/' . $medicine->image));
    }
});

test('treatment can be recorded with multiple medicines, reducing stock and separating treatment vs medicine pricing', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $patient = Patient::create([
        'patient_number' => 'P-0001',
        'first_name' => 'Ayla',
        'last_name' => 'Khan',
        'gender' => 'female',
        'phone' => '03489030035',
        'status' => 'active',
    ]);

    $treatment = Treatment::create([
        'name' => 'Chemical Peel',
        'price' => 200.00,
        'duration' => 60,
        'status' => 'active',
    ]);

    $category = MedicineCategory::create([
        'name' => 'Dermatology',
        'status' => 'active',
    ]);

    $med1 = Medicine::create([
        'name' => 'Glycolic Neutralizer',
        'generic_name' => 'Glycolic Solution',
        'medicine_category_id' => $category->id,
        'purchase_price' => 15.00,
        'selling_price' => 30.00,
        'stock_quantity' => 20,
        'minimum_stock' => 5,
        'unit' => 'bottle',
        'status' => 'active',
    ]);

    $med2 = Medicine::create([
        'name' => 'Post-Peel Soothing Balm',
        'generic_name' => 'Centella Asiatica',
        'medicine_category_id' => $category->id,
        'purchase_price' => 25.00,
        'selling_price' => 50.00,
        'stock_quantity' => 15,
        'minimum_stock' => 3,
        'unit' => 'tube',
        'status' => 'active',
    ]);

    // Treatment Fee: 200.00, Treatment Discount: 30.00 => Treatment Net = 170.00
    // Med1: 2 x 30.00 = 60.00
    // Med2: 1 x 50.00 = 50.00
    // Medicines Subtotal: 110.00, Medicines Discount: 10.00 => Medicines Net = 100.00
    // Grand Total = 170.00 + 100.00 = 270.00
    // Paid Amount = 250.00 (Due = 20.00)
    $response = $this->actingAs($user)->post(route('patient-treatments.store'), [
        'patient_id' => $patient->id,
        'treatment_id' => $treatment->id,
        'treatment_date' => '2026-09-29',
        'price' => 200.00,
        'discount' => 30.00,
        'medicine_discount' => 10.00,
        'medicines' => [
            ['id' => $med1->id, 'quantity' => 2, 'unit_price' => 30.00],
            ['id' => $med2->id, 'quantity' => 1, 'unit_price' => 50.00],
        ],
        'paid_amount' => 250.00,
        'payment_method' => 'cash',
        'notes' => 'Patient had mild erythema. Prescribed post-peel balm.',
    ]);

    $response->assertRedirect(route('patient-treatments.index'));
    $response->assertSessionHas('success');

    // 1. Verify stock calculation:
    // Med1: 20 - 2 = 18
    // Med2: 15 - 1 = 14
    expect($med1->fresh()->stock_quantity)->toBe(18);
    expect($med2->fresh()->stock_quantity)->toBe(14);

    // 2. Verify PatientTreatment stored with separated amounts
    $pt = PatientTreatment::with('treatmentMedicines')->where('patient_id', $patient->id)->first();
    expect($pt)->not->toBeNull();
    expect((float) $pt->price)->toBe(200.00);
    expect((float) $pt->discount)->toBe(30.00);
    expect((float) $pt->treatment_net)->toBe(170.00);
    expect((float) $pt->medicine_price)->toBe(110.00);
    expect((float) $pt->medicine_discount)->toBe(10.00);
    expect((float) $pt->medicine_net)->toBe(100.00);
    expect((float) $pt->total_amount)->toBe(270.00);

    // 3. Verify PatientTreatmentMedicine records
    expect($pt->treatmentMedicines)->toHaveCount(2);
    $this->assertDatabaseHas('patient_treatment_medicines', [
        'patient_treatment_id' => $pt->id,
        'medicine_id' => $med1->id,
        'quantity' => 2,
        'unit_price' => '30.00',
        'total_price' => '60.00',
    ]);
    $this->assertDatabaseHas('patient_treatment_medicines', [
        'patient_treatment_id' => $pt->id,
        'medicine_id' => $med2->id,
        'quantity' => 1,
        'unit_price' => '50.00',
        'total_price' => '50.00',
    ]);

    // 4. Verify MedicineUsage ledger records
    $this->assertDatabaseHas('medicine_usages', [
        'patient_id' => $patient->id,
        'treatment_id' => $treatment->id,
        'medicine_id' => $med1->id,
        'quantity' => 2,
    ]);
    $this->assertDatabaseHas('medicine_usages', [
        'patient_id' => $patient->id,
        'treatment_id' => $treatment->id,
        'medicine_id' => $med2->id,
        'quantity' => 1,
    ]);

    // 5. Verify Invoice and Payment with combined totals and due
    $invoice = Invoice::where('patient_id', $patient->id)->first();
    expect($invoice)->not->toBeNull();
    expect((float) $invoice->subtotal)->toBe(310.00);   // 200 + 110
    expect((float) $invoice->discount)->toBe(40.00);   // 30 + 10
    expect((float) $invoice->total_amount)->toBe(270.00);
    expect((float) $invoice->paid_amount)->toBe(250.00);
    expect((float) $invoice->due_amount)->toBe(20.00);
    expect($invoice->status)->toBe('partial');

    $payment = Payment::where('invoice_id', $invoice->id)->first();
    expect($payment)->not->toBeNull();
    expect((float) $payment->amount)->toBe(250.00);
    expect($payment->payment_method)->toBe('cash');
});

test('treatment recording fails validation if requested medicine quantity exceeds stock', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $patient = Patient::create([
        'patient_number' => 'P-0002',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'gender' => 'male',
        'phone' => '03001234567',
        'status' => 'active',
    ]);

    $treatment = Treatment::create([
        'name' => 'Laser Resurfacing',
        'price' => 300.00,
        'duration' => 60,
        'status' => 'active',
    ]);

    $category = MedicineCategory::create([
        'name' => 'Pharmacy',
        'status' => 'active',
    ]);

    $med = Medicine::create([
        'name' => 'Limited Cream',
        'medicine_category_id' => $category->id,
        'purchase_price' => 10.00,
        'selling_price' => 20.00,
        'stock_quantity' => 3, // only 3 in stock
        'minimum_stock' => 1,
        'unit' => 'tube',
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->post(route('patient-treatments.store'), [
        'patient_id' => $patient->id,
        'treatment_id' => $treatment->id,
        'treatment_date' => '2026-09-29',
        'price' => 300.00,
        'discount' => 0,
        'medicines' => [
            ['id' => $med->id, 'quantity' => 5, 'unit_price' => 20.00], // requests 5 > 3
        ],
    ]);

    $response->assertSessionHasErrors('medicines');
    // Stock remains unchanged
    expect($med->fresh()->stock_quantity)->toBe(3);
});

test('deleting a treatment restores product stock and cleans up ledger', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $patient = Patient::create([
        'patient_number' => 'P-0003',
        'first_name' => 'Maria',
        'last_name' => 'Garcia',
        'gender' => 'female',
        'phone' => '03121234567',
        'status' => 'active',
    ]);

    $treatment = Treatment::create([
        'name' => 'Microneedling',
        'price' => 180.00,
        'duration' => 45,
        'status' => 'active',
    ]);

    $category = MedicineCategory::create([
        'name' => 'Serums',
        'status' => 'active',
    ]);

    $med = Medicine::create([
        'name' => 'Hyaluronic Serum',
        'medicine_category_id' => $category->id,
        'purchase_price' => 15.00,
        'selling_price' => 35.00,
        'stock_quantity' => 10,
        'minimum_stock' => 2,
        'unit' => 'vial',
        'status' => 'active',
    ]);

    // Create treatment using 4 vials
    $this->actingAs($user)->post(route('patient-treatments.store'), [
        'patient_id' => $patient->id,
        'treatment_id' => $treatment->id,
        'treatment_date' => '2026-09-29',
        'price' => 180.00,
        'discount' => 0,
        'medicines' => [
            ['id' => $med->id, 'quantity' => 4, 'unit_price' => 35.00],
        ],
    ]);

    expect($med->fresh()->stock_quantity)->toBe(6);

    $pt = PatientTreatment::where('patient_id', $patient->id)->first();
    expect($pt)->not->toBeNull();

    // Delete treatment
    $deleteResponse = $this->actingAs($user)->delete(route('patient-treatments.destroy', $pt));
    $deleteResponse->assertRedirect(route('patient-treatments.index'));

    // Stock should be restored back to 10
    expect($med->fresh()->stock_quantity)->toBe(10);
    expect(PatientTreatment::find($pt->id))->toBeNull();
    expect(PatientTreatmentMedicine::where('patient_treatment_id', $pt->id)->count())->toBe(0);
});

test('updating treatment adjusts medicine stock accurately and updates separated totals', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $patient = Patient::create([
        'patient_number' => 'P-0004',
        'first_name' => 'Sara',
        'last_name' => 'Ahmed',
        'gender' => 'female',
        'phone' => '03009876543',
        'status' => 'active',
    ]);

    $treatment = Treatment::create([
        'name' => 'Acne Treatment',
        'price' => 100.00,
        'duration' => 30,
        'status' => 'active',
    ]);

    $category = MedicineCategory::create([
        'name' => 'Acne Care',
        'status' => 'active',
    ]);

    $medA = Medicine::create([
        'name' => 'Benzoyl Peroxide 5%',
        'medicine_category_id' => $category->id,
        'purchase_price' => 10.00,
        'selling_price' => 25.00,
        'stock_quantity' => 20,
        'minimum_stock' => 2,
        'unit' => 'tube',
        'status' => 'active',
    ]);

    $medB = Medicine::create([
        'name' => 'Clindamycin Gel',
        'medicine_category_id' => $category->id,
        'purchase_price' => 12.00,
        'selling_price' => 30.00,
        'stock_quantity' => 10,
        'minimum_stock' => 2,
        'unit' => 'tube',
        'status' => 'active',
    ]);

    // Initial creation with 2 of MedA
    $this->actingAs($user)->post(route('patient-treatments.store'), [
        'patient_id' => $patient->id,
        'treatment_id' => $treatment->id,
        'treatment_date' => '2026-09-29',
        'price' => 100.00,
        'discount' => 10.00,
        'medicine_discount' => 5.00,
        'medicines' => [
            ['id' => $medA->id, 'quantity' => 2, 'unit_price' => 25.00], // 50.00
        ],
    ]);

    expect($medA->fresh()->stock_quantity)->toBe(18); // 20 - 2

    $pt = PatientTreatment::where('patient_id', $patient->id)->first();

    // Now update: remove MedA and select 3 of MedB instead
    $updateResponse = $this->actingAs($user)->put(route('patient-treatments.update', $pt), [
        'patient_id' => $patient->id,
        'treatment_id' => $treatment->id,
        'treatment_date' => '2026-09-29',
        'price' => 120.00,
        'discount' => 20.00,
        'medicine_discount' => 10.00,
        'medicines' => [
            ['id' => $medB->id, 'quantity' => 3, 'unit_price' => 30.00], // 90.00
        ],
    ]);

    $updateResponse->assertRedirect(route('patient-treatments.index'));
    $updateResponse->assertSessionHas('success');

    // MedA stock should be restored to 20
    expect($medA->fresh()->stock_quantity)->toBe(20);
    // MedB stock should be decremented to 7 (10 - 3)
    expect($medB->fresh()->stock_quantity)->toBe(7);

    // Verify updated separated amounts
    $ptFresh = $pt->fresh();
    expect((float) $ptFresh->price)->toBe(120.00);
    expect((float) $ptFresh->discount)->toBe(20.00);
    expect((float) $ptFresh->treatment_net)->toBe(100.00); // 120 - 20
    expect((float) $ptFresh->medicine_price)->toBe(90.00);
    expect((float) $ptFresh->medicine_discount)->toBe(10.00);
    expect((float) $ptFresh->medicine_net)->toBe(80.00); // 90 - 10
    expect((float) $ptFresh->total_amount)->toBe(180.00); // 100 + 80
});

test('treatment and medicine discounts are validated separately', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $patient = Patient::create([
        'patient_number' => 'P-0005',
        'first_name' => 'Ali',
        'last_name' => 'Raza',
        'gender' => 'male',
        'phone' => '03331112233',
        'status' => 'active',
    ]);

    $treatment = Treatment::create([
        'name' => 'Skin Consultation',
        'price' => 50.00,
        'duration' => 20,
        'status' => 'active',
    ]);

    $category = MedicineCategory::create([
        'name' => 'General',
        'status' => 'active',
    ]);

    $med = Medicine::create([
        'name' => 'Sunscreen SPF 50',
        'medicine_category_id' => $category->id,
        'purchase_price' => 10.00,
        'selling_price' => 25.00,
        'stock_quantity' => 10,
        'minimum_stock' => 1,
        'unit' => 'tube',
        'status' => 'active',
    ]);

    // 1. Treatment discount exceeding treatment price fails
    $res1 = $this->actingAs($user)->post(route('patient-treatments.store'), [
        'patient_id' => $patient->id,
        'treatment_id' => $treatment->id,
        'treatment_date' => '2026-09-29',
        'price' => 50.00,
        'discount' => 60.00, // > 50.00
        'medicine_discount' => 0,
        'medicines' => [
            ['id' => $med->id, 'quantity' => 1, 'unit_price' => 25.00],
        ],
    ]);
    $res1->assertSessionHasErrors('discount');

    // 2. Medicine discount exceeding medicines subtotal fails
    $res2 = $this->actingAs($user)->post(route('patient-treatments.store'), [
        'patient_id' => $patient->id,
        'treatment_id' => $treatment->id,
        'treatment_date' => '2026-09-29',
        'price' => 50.00,
        'discount' => 10.00,
        'medicine_discount' => 30.00, // > 25.00
        'medicines' => [
            ['id' => $med->id, 'quantity' => 1, 'unit_price' => 25.00],
        ],
    ]);
    $res2->assertSessionHasErrors('medicine_discount');
});

test('treatment and medicines support percentage discount calculation accurately', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $patient = Patient::create([
        'patient_number' => 'P-0099',
        'first_name' => 'Sara',
        'last_name' => 'Khan',
        'gender' => 'female',
        'phone' => '03331112233',
        'status' => 'active',
    ]);

    $treatment = Treatment::create([
        'name' => 'HydraFacial Deluxe',
        'price' => 1000.00,
        'duration' => 45,
        'status' => 'active',
    ]);

    $category = MedicineCategory::create([
        'name' => 'Serums',
        'status' => 'active',
    ]);

    $serum = Medicine::create([
        'name' => 'Vitamin C Serum',
        'medicine_category_id' => $category->id,
        'purchase_price' => 150.00,
        'selling_price' => 300.00,
        'stock_quantity' => 20,
        'minimum_stock' => 2,
        'unit' => 'bottle',
        'status' => 'active',
    ]);

    // Procedure: 1000 with 25% discount -> 250 PKR discount -> 750 PKR net
    // Medicine: 2 x 300 = 600 with 10% discount -> 60 PKR discount -> 540 PKR net
    // Grand Total: 750 + 540 = 1290 PKR
    $response = $this->actingAs($user)->post(route('patient-treatments.store'), [
        'patient_id' => $patient->id,
        'treatment_id' => $treatment->id,
        'treatment_date' => '2026-09-29',
        'price' => 1000.00,
        'discount_type' => 'percentage',
        'discount_percentage' => 25,
        'medicine_discount_type' => 'percentage',
        'medicine_discount_percentage' => 10,
        'medicines' => [
            ['id' => $serum->id, 'quantity' => 2, 'unit_price' => 300.00],
        ],
        'paid_amount' => 1290.00,
        'payment_method' => 'cash',
    ]);

    $response->assertRedirect(route('patient-treatments.index'));

    $pt = PatientTreatment::where('patient_id', $patient->id)->first();
    expect($pt)->not->toBeNull();
    expect((float) $pt->price)->toBe(1000.00);
    expect((float) $pt->discount)->toBe(250.00);
    expect((float) $pt->medicine_price)->toBe(600.00);
    expect((float) $pt->medicine_discount)->toBe(60.00);
    expect((float) $pt->medicine_total)->toBe(540.00);
    expect((float) $pt->total_amount)->toBe(1290.00);

    $invoice = Invoice::where('patient_id', $patient->id)->first();
    expect($invoice)->not->toBeNull();
    expect((float) $invoice->subtotal)->toBe(1600.00); // 1000 + 600
    expect((float) $invoice->discount)->toBe(310.00); // 250 + 60
    expect((float) $invoice->total_amount)->toBe(1290.00);
    expect((float) $invoice->paid_amount)->toBe(1290.00);
    expect((float) $invoice->due_amount)->toBe(0.00);
    expect($invoice->status)->toBe('paid');
});

test('invoices support percentage discount calculation on store and update', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $patient = Patient::create([
        'patient_number' => 'P-0100',
        'first_name' => 'Ahmad',
        'last_name' => 'Ali',
        'gender' => 'male',
        'phone' => '03211234567',
        'status' => 'active',
    ]);

    // Subtotal 5000 with 20% discount = 1000 PKR discount -> total 4000 PKR
    $res = $this->actingAs($user)->post(route('invoices.store'), [
        'patient_id' => $patient->id,
        'invoice_date' => '2026-09-29',
        'subtotal' => 5000.00,
        'discount_type' => 'percentage',
        'discount_percentage' => 20,
    ]);

    $invoice = Invoice::where('patient_id', $patient->id)->first();
    expect($invoice)->not->toBeNull();
    $res->assertRedirect(route('invoices.show', $invoice));

    expect((float) $invoice->subtotal)->toBe(5000.00);
    expect((float) $invoice->discount)->toBe(1000.00);
    expect((float) $invoice->total_amount)->toBe(4000.00);
    expect((float) $invoice->due_amount)->toBe(4000.00);

    // Update with fixed discount 500 PKR
    $updateRes = $this->actingAs($user)->put(route('invoices.update', $invoice), [
        'patient_id' => $patient->id,
        'invoice_date' => '2026-09-29',
        'subtotal' => 5000.00,
        'discount_type' => 'fixed',
        'discount' => 500.00,
    ]);
    $updateRes->assertRedirect(route('invoices.show', $invoice));

    $invoice->refresh();
    expect((float) $invoice->discount)->toBe(500.00);
    expect((float) $invoice->total_amount)->toBe(4500.00);
    expect((float) $invoice->due_amount)->toBe(4500.00);
});


