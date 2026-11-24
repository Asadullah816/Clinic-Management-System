<?php

namespace Tests\Feature;

use App\Models\Implant;
use App\Models\ImplantBuyer;
use App\Models\ImplantSale;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DentalImplantsAndWhatsAppRemindersTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_generates_valid_whatsapp_reminder_url(): void
    {
        $patient = Patient::create([
            'patient_number' => 'P-0099',
            'first_name' => 'Usman',
            'last_name' => 'Ghani',
            'gender' => 'male',
            'phone' => '03001234567',
            'status' => 'active',
        ]);

        $invoice = Invoice::create([
            'patient_id' => $patient->id,
            'invoice_number' => 'INV-2026-0099',
            'invoice_date' => today(),
            'subtotal' => 15000.00,
            'discount' => 0.00,
            'total_amount' => 15000.00,
            'paid_amount' => 5000.00,
            'due_amount' => 10000.00,
            'status' => 'partial',
        ]);

        $url = $invoice->whatsappReminderUrl();

        expect($url)->not->toBeNull()
            ->and($url)->toStartWith('https://wa.me/923001234567?text=')
            ->and(urldecode($url))->toContain('Usman Ghani')
            ->and(urldecode($url))->toContain('INV-2026-0099')
            ->and(urldecode($url))->toContain('10,000.00');
    }

    public function test_outstandings_page_displays_whatsapp_reminder_button(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $patient = Patient::create([
            'patient_number' => 'P-0100',
            'first_name' => 'Farhan',
            'last_name' => 'Ali',
            'gender' => 'male',
            'phone' => '03451122334',
            'status' => 'active',
        ]);

        $invoice = Invoice::create([
            'patient_id' => $patient->id,
            'invoice_number' => 'INV-2026-0100',
            'invoice_date' => today(),
            'subtotal' => 20000.00,
            'discount' => 0.00,
            'total_amount' => 20000.00,
            'paid_amount' => 10000.00,
            'due_amount' => 10000.00,
            'status' => 'partial',
        ]);

        $response = $this->actingAs($user)->get(route('payments.outstanding'));
        $response->assertOk();
        $response->assertSee('WhatsApp');
        $response->assertSee('https://wa.me/923451122334', false);
    }

    public function test_implant_can_be_created_and_restocked(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $res = $this->actingAs($user)->post(route('implants.store'), [
            'item_code' => 'IMP-TEST-1',
            'name' => 'BLX Bone Level',
            'brand' => 'Straumann',
            'diameter' => '4.0',
            'length' => '10.0',
            'purchase_price' => 30000.00,
            'sale_price' => 45000.00,
            'stock_quantity' => 5,
            'min_stock_alert' => 2,
            'status' => 'active',
        ]);

        $res->assertRedirect(route('implants.index'));

        $implant = Implant::where('item_code', 'IMP-TEST-1')->firstOrFail();
        expect($implant->stock_quantity)->toBe(5);

        // Restock endpoint
        $restockRes = $this->actingAs($user)->post(route('implants.restock', $implant), [
            'quantity' => 10,
            'purchase_price' => 31000.00,
            'notes' => 'Shipment arrived',
        ]);

        $restockRes->assertSessionHasNoErrors();
        expect($implant->fresh()->stock_quantity)->toBe(15);
    }

    public function test_implant_sale_validates_stock_and_decrements_inventory(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $implant = Implant::create([
            'item_code' => 'IMP-STOCK-TEST',
            'name' => 'TSIII Regular',
            'brand' => 'Osstem',
            'diameter' => '4.5',
            'length' => '10.0',
            'purchase_price' => 20000.00,
            'sale_price' => 30000.00,
            'stock_quantity' => 3,
            'min_stock_alert' => 1,
            'status' => 'active',
        ]);

        $doctor = ImplantBuyer::create([
            'doctor_name' => 'Dr. Zeeshan Tariq',
            'clinic_hospital_name' => 'Dental Care Centre',
            'phone' => '03009876543',
            'status' => 'active',
        ]);

        // Attempting to buy 5 units when only 3 in stock should fail validation
        $failRes = $this->actingAs($user)->post(route('implant-sales.store'), [
            'implant_buyer_id' => $doctor->id,
            'sale_date' => today()->toDateString(),
            'items' => [
                [
                    'implant_id' => $implant->id,
                    'quantity' => 5,
                    'unit_price' => 30000.00,
                ],
            ],
            'discount' => 0,
            'paid_amount' => 0,
        ]);

        $failRes->assertSessionHasErrors(['items.0.quantity']);
        expect($implant->fresh()->stock_quantity)->toBe(3);

        // Buying 2 units should succeed and decrement stock to 1
        $successRes = $this->actingAs($user)->post(route('implant-sales.store'), [
            'implant_buyer_id' => $doctor->id,
            'sale_date' => today()->toDateString(),
            'items' => [
                [
                    'implant_id' => $implant->id,
                    'quantity' => 2,
                    'unit_price' => 30000.00,
                ],
            ],
            'discount' => 5000.00,
            'paid_amount' => 25000.00,
            'payment_method' => 'bank_transfer',
        ]);

        $successRes->assertSessionHasNoErrors();
        expect($implant->fresh()->stock_quantity)->toBe(1);

        $sale = ImplantSale::latest('id')->firstOrFail();
        // Subtotal = 2 * 30000 = 60000. Discount = 5000. Total = 55000. Paid = 25000. Due = 30000.
        expect((float) $sale->total_amount)->toBe(55000.00)
            ->and((float) $sale->paid_amount)->toBe(25000.00)
            ->and((float) $sale->due_amount)->toBe(30000.00)
            ->and($sale->payment_status)->toBe('partial');
    }

    public function test_doctor_outstandings_generates_whatsapp_reminder_link(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $implant = Implant::create([
            'item_code' => 'IMP-WA-TEST',
            'name' => 'SuperLine II',
            'brand' => 'Dentium',
            'diameter' => '4.0',
            'length' => '10.0',
            'purchase_price' => 15000.00,
            'sale_price' => 25000.00,
            'stock_quantity' => 10,
            'min_stock_alert' => 2,
            'status' => 'active',
        ]);

        $doctor = ImplantBuyer::create([
            'doctor_name' => 'Dr. Ayesha Malik',
            'clinic_hospital_name' => 'Elite Dentistry',
            'phone' => '03335556677',
            'status' => 'active',
        ]);

        $sale = ImplantSale::create([
            'sale_number' => 'IMP-SALE-0099',
            'implant_buyer_id' => $doctor->id,
            'sale_date' => today(),
            'subtotal' => 50000.00,
            'discount' => 0.00,
            'total_amount' => 50000.00,
            'paid_amount' => 20000.00,
            'due_amount' => 30000.00,
            'payment_status' => 'partial',
            'created_by' => $user->id,
        ]);

        $waUrl = $sale->whatsappReminderUrl();
        expect($waUrl)->not->toBeNull()
            ->and($waUrl)->toStartWith('https://wa.me/923335556677?text=')
            ->and(urldecode($waUrl))->toContain('Dr. Ayesha Malik')
            ->and(urldecode($waUrl))->toContain('IMP-SALE-0099')
            ->and(urldecode($waUrl))->toContain('30,000.00');

        // Check Doctor Outstandings view
        $res = $this->actingAs($user)->get(route('implant-sales.outstanding'));
        $res->assertOk();
        $res->assertSee('Dr. Ayesha Malik');
        $res->assertSee('30,000.00');
        $res->assertSee('https://wa.me/923335556677', false);

        // Receive remaining payment
        $payRes = $this->actingAs($user)->post(route('implant-payments.store'), [
            'implant_sale_id' => $sale->id,
            'amount' => 30000.00,
            'payment_date' => today()->toDateString(),
            'payment_method' => 'cash',
        ]);

        $payRes->assertSessionHasNoErrors();
        expect((float) $sale->fresh()->due_amount)->toBe(0.00)
            ->and($sale->fresh()->payment_status)->toBe('paid');
    }

    public function test_internal_clinic_self_use_sale(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $implant = Implant::create([
            'item_code' => 'IMP-SELF-TEST',
            'name' => 'AnyRidge',
            'brand' => 'Megagen',
            'diameter' => '4.5',
            'length' => '10.0',
            'purchase_price' => 22000.00,
            'sale_price' => 35000.00,
            'stock_quantity' => 5,
            'min_stock_alert' => 1,
            'status' => 'active',
        ]);

        $internalBuyer = ImplantBuyer::create([
            'doctor_name' => 'Dr Badshah Afsar',
            'clinic_hospital_name' => 'In-House Clinic',
            'phone' => '03489030035',
            'is_internal' => true,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->post(route('implant-sales.store'), [
            'implant_buyer_id' => $internalBuyer->id,
            'sale_date' => today()->toDateString(),
            'patient_reference' => 'In-House Patient RCT #12',
            'items' => [
                [
                    'implant_id' => $implant->id,
                    'quantity' => 1,
                    'unit_price' => 35000.00,
                ],
            ],
            'discount' => 0,
            'paid_amount' => 35000.00,
        ]);

        $response->assertSessionHasNoErrors();
        expect($implant->fresh()->stock_quantity)->toBe(4);
    }
}
