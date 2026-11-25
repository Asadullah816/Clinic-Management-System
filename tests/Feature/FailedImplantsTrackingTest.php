<?php

namespace Tests\Feature;

use App\Models\FailedImplant;
use App\Models\Implant;
use App\Models\ImplantBuyer;
use App\Models\ImplantSale;
use App\Models\ImplantSaleItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FailedImplantsTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Implant $implant;

    protected ImplantBuyer $buyer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);

        $this->implant = Implant::create([
            'item_code' => 'IMP-STR-01',
            'name' => 'Straumann BLX Fixture',
            'brand' => 'Straumann',
            'diameter' => '4.0mm',
            'length' => '10mm',
            'purchase_price' => 25000.00,
            'sale_price' => 40000.00,
            'stock_quantity' => 10,
            'min_stock_alert' => 2,
            'status' => 'active',
        ]);

        $this->buyer = ImplantBuyer::create([
            'doctor_name' => 'Dr Tariq Khan',
            'clinic_hospital_name' => 'Khyber Dental Center',
            'phone' => '03009876543',
            'status' => 'active',
        ]);
    }

    public function test_failed_implants_index_page_loads_with_separate_kpis(): void
    {
        FailedImplant::create([
            'failure_number' => 'IMP-FAIL-2026-0001',
            'implant_id' => $this->implant->id,
            'implant_buyer_id' => $this->buyer->id,
            'failure_date' => today(),
            'quantity' => 1,
            'unit_cost' => 25000.00,
            'total_cost' => 25000.00,
            'source' => FailedImplant::SOURCE_SALE,
            'failure_stage' => FailedImplant::STAGE_SURGICAL,
            'failure_reason' => FailedImplant::REASON_LACK_OF_STABILITY,
            'patient_name' => 'Amjad Ali',
            'tooth_number' => '14',
            'warranty_status' => FailedImplant::WARRANTY_PENDING,
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('failed-implants.index'));

        $response->assertStatus(200);
        $response->assertSee('Failed Dental Implants');
        $response->assertSee('Calculated Separately');
        $response->assertSee('IMP-FAIL-2026-0001');
        $response->assertSee('Dr Tariq Khan');
        $response->assertSee('25,000.00');
        $response->assertSee('Lack of Primary Stability');
    }

    public function test_can_record_failed_implant_from_stock_and_decrements_inventory(): void
    {
        $initialStock = $this->implant->stock_quantity; // 10

        $postData = [
            'source' => 'stock',
            'implant_id' => $this->implant->id,
            'failure_date' => today()->format('Y-m-d'),
            'quantity' => 2,
            'unit_cost' => 25000.00,
            'failure_stage' => FailedImplant::STAGE_PACKAGING,
            'failure_reason' => FailedImplant::REASON_PACKAGING_DEFECT,
            'warranty_status' => FailedImplant::WARRANTY_PENDING,
            'notes' => 'Sterile seal compromised upon unboxing.',
        ];

        $response = $this->actingAs($this->admin)->post(route('failed-implants.store'), $postData);

        $this->assertDatabaseHas('failed_implants', [
            'implant_id' => $this->implant->id,
            'source' => 'stock',
            'quantity' => 2,
            'unit_cost' => 25000.00,
            'total_cost' => 50000.00,
            'failure_reason' => FailedImplant::REASON_PACKAGING_DEFECT,
        ]);

        // Assert stock decremented
        $this->implant->refresh();
        $this->assertEquals($initialStock - 2, $this->implant->stock_quantity);

        // Assert audit stock transaction logged
        $this->assertDatabaseHas('implant_stock_transactions', [
            'implant_id' => $this->implant->id,
            'type' => 'failed',
            'quantity' => -2,
            'balance_after' => 8,
        ]);

        $response->assertRedirect();
    }

    public function test_can_record_failed_implant_from_sale_without_double_decrementing_stock(): void
    {
        // Create an existing sale
        $sale = ImplantSale::create([
            'sale_number' => 'IMP-2026-0001',
            'implant_buyer_id' => $this->buyer->id,
            'sale_date' => today(),
            'subtotal' => 40000.00,
            'discount' => 0.00,
            'total_amount' => 40000.00,
            'paid_amount' => 10000.00,
            'due_amount' => 30000.00,
            'payment_status' => 'partial',
            'created_by' => $this->admin->id,
        ]);

        ImplantSaleItem::create([
            'implant_sale_id' => $sale->id,
            'implant_id' => $this->implant->id,
            'quantity' => 1,
            'unit_price' => 40000.00,
            'total_price' => 40000.00,
        ]);

        $stockBeforeFailure = $this->implant->stock_quantity; // 10

        $postData = [
            'source' => 'sale',
            'implant_id' => $this->implant->id,
            'implant_buyer_id' => $this->buyer->id,
            'implant_sale_id' => $sale->id,
            'failure_date' => today()->format('Y-m-d'),
            'quantity' => 1,
            'unit_cost' => 25000.00,
            'failure_stage' => FailedImplant::STAGE_SURGICAL,
            'failure_reason' => FailedImplant::REASON_CONTAMINATION,
            'patient_name' => 'Patient Zahid',
            'tooth_number' => '36',
            'warranty_status' => FailedImplant::WARRANTY_PENDING,
            'notes' => 'Dropped on surgical drape during osteotomy insertion.',
        ];

        $response = $this->actingAs($this->admin)->post(route('failed-implants.store'), $postData);

        // Stock should not be decremented again because it came from a sale
        $this->implant->refresh();
        $this->assertEquals($stockBeforeFailure, $this->implant->stock_quantity);

        $this->assertDatabaseHas('failed_implants', [
            'implant_sale_id' => $sale->id,
            'implant_buyer_id' => $this->buyer->id,
            'patient_name' => 'Patient Zahid',
            'source' => 'sale',
        ]);

        $response->assertRedirect();
    }

    public function test_failed_implants_calculations_are_strictly_separated_from_buyer_dues_and_sales_revenue(): void
    {
        // Sale of PKR 80,000, Paid PKR 30,000, Due PKR 50,000
        $sale = ImplantSale::create([
            'sale_number' => 'IMP-2026-0005',
            'implant_buyer_id' => $this->buyer->id,
            'sale_date' => today(),
            'subtotal' => 80000.00,
            'discount' => 0.00,
            'total_amount' => 80000.00,
            'paid_amount' => 30000.00,
            'due_amount' => 50000.00,
            'payment_status' => 'partial',
            'created_by' => $this->admin->id,
        ]);

        // Record a failure of PKR 25,000 for this doctor
        FailedImplant::create([
            'failure_number' => 'IMP-FAIL-2026-0002',
            'implant_id' => $this->implant->id,
            'implant_buyer_id' => $this->buyer->id,
            'implant_sale_id' => $sale->id,
            'failure_date' => today(),
            'quantity' => 1,
            'unit_cost' => 25000.00,
            'total_cost' => 25000.00,
            'source' => FailedImplant::SOURCE_SALE,
            'failure_stage' => FailedImplant::STAGE_HEALING,
            'failure_reason' => FailedImplant::REASON_OSSEOINTEGRATION_FAILURE,
            'patient_name' => 'Nasir Khan',
            'tooth_number' => '46',
            'warranty_status' => FailedImplant::WARRANTY_CLINIC_LOSS,
            'created_by' => $this->admin->id,
        ]);

        // Verification 1: Buyer dues must remain intact (not tainted or subtracted automatically)
        $this->assertEquals(80000.00, $this->buyer->totalPurchases());
        $this->assertEquals(30000.00, $this->buyer->totalPaid());
        $this->assertEquals(50000.00, $this->buyer->totalDue());

        // Verification 2: Failed implant helpers return separate calculation
        $this->assertEquals(1, $this->buyer->totalFailedQuantity());
        $this->assertEquals(25000.00, $this->buyer->totalFailedCost());
        $this->assertEquals(1, $sale->totalFailedQuantity());
        $this->assertEquals(25000.00, $sale->totalFailedCost());

        // Verification 3: Buyer statement page renders separate failure stats
        $response = $this->actingAs($this->admin)->get(route('implant-buyers.show', $this->buyer));
        $response->assertStatus(200);
        $response->assertSee('Current Outstanding Balance');
        $response->assertSee('50,000.00'); // Due remains 50,000
        $response->assertSee('Failed Implants');
        $response->assertSee('25,000.00'); // Separate failure cost
        $response->assertSee('Calculated Separately');
    }

    public function test_can_update_warranty_resolution_and_replacement_implant(): void
    {
        $replacement = Implant::create([
            'item_code' => 'IMP-REP-01',
            'name' => 'Straumann BLX Replacement',
            'brand' => 'Straumann',
            'purchase_price' => 25000.00,
            'sale_price' => 40000.00,
            'stock_quantity' => 5,
            'min_stock_alert' => 1,
            'status' => 'active',
        ]);

        $failure = FailedImplant::create([
            'failure_number' => 'IMP-FAIL-2026-0003',
            'implant_id' => $this->implant->id,
            'failure_date' => today(),
            'quantity' => 1,
            'unit_cost' => 25000.00,
            'total_cost' => 25000.00,
            'source' => FailedImplant::SOURCE_STOCK,
            'failure_stage' => FailedImplant::STAGE_SURGICAL,
            'failure_reason' => FailedImplant::REASON_LACK_OF_STABILITY,
            'warranty_status' => FailedImplant::WARRANTY_PENDING,
            'created_by' => $this->admin->id,
        ]);

        $updateData = [
            'failure_stage' => FailedImplant::STAGE_SURGICAL,
            'failure_reason' => FailedImplant::REASON_LACK_OF_STABILITY,
            'warranty_status' => FailedImplant::WARRANTY_REPLACED,
            'replacement_implant_id' => $replacement->id,
            'notes' => 'Manufacturer approved warranty exchange and issued replacement unit.',
        ];

        $response = $this->actingAs($this->admin)->put(route('failed-implants.update', $failure), $updateData);

        $failure->refresh();
        $this->assertEquals(FailedImplant::WARRANTY_REPLACED, $failure->warranty_status);
        $this->assertEquals($replacement->id, $failure->replacement_implant_id);
        $response->assertRedirect(route('failed-implants.show', $failure));
    }

    public function test_deleting_stock_failure_restores_inventory(): void
    {
        $initialStock = $this->implant->stock_quantity; // 10

        // Create a failure from stock and decrement
        $this->implant->update(['stock_quantity' => $initialStock - 1]); // 9
        $failure = FailedImplant::create([
            'failure_number' => 'IMP-FAIL-2026-0004',
            'implant_id' => $this->implant->id,
            'failure_date' => today(),
            'quantity' => 1,
            'unit_cost' => 25000.00,
            'total_cost' => 25000.00,
            'source' => FailedImplant::SOURCE_STOCK,
            'failure_stage' => FailedImplant::STAGE_PACKAGING,
            'failure_reason' => FailedImplant::REASON_PACKAGING_DEFECT,
            'warranty_status' => FailedImplant::WARRANTY_PENDING,
            'created_by' => $this->admin->id,
        ]);

        $this->assertEquals(9, $this->implant->fresh()->stock_quantity);

        // Delete failure record as admin
        $response = $this->actingAs($this->admin)->delete(route('failed-implants.destroy', $failure));

        $this->assertDatabaseMissing('failed_implants', ['id' => $failure->id]);

        // Inventory should be restored back to 10
        $this->assertEquals(10, $this->implant->fresh()->stock_quantity);

        $this->assertDatabaseHas('implant_stock_transactions', [
            'implant_id' => $this->implant->id,
            'type' => 'adjustment',
            'quantity' => 1,
            'balance_after' => 10,
        ]);

        $response->assertRedirect(route('failed-implants.index'));
    }

    public function test_sidebar_and_implants_views_link_to_failed_implants(): void
    {
        FailedImplant::create([
            'failure_number' => 'IMP-FAIL-2026-0005',
            'implant_id' => $this->implant->id,
            'failure_date' => today(),
            'quantity' => 1,
            'unit_cost' => 25000.00,
            'total_cost' => 25000.00,
            'source' => FailedImplant::SOURCE_STOCK,
            'failure_stage' => FailedImplant::STAGE_PACKAGING,
            'failure_reason' => FailedImplant::REASON_PACKAGING_DEFECT,
            'warranty_status' => FailedImplant::WARRANTY_PENDING,
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('implants.index'));

        $response->assertStatus(200);
        $response->assertSee(route('failed-implants.index'));
        $response->assertSee('Failed Implants Tracked Separately');
        $response->assertSee('25,000.00');
    }
}
