<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MedicalHistoryController;
use App\Http\Controllers\MedicineCategoryController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\MedicineUsageController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PatientTreatmentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TreatmentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ---------- Home: send visitors to the correct page ----------
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

// ---------- Guest only ----------
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

// ---------- Authenticated (all roles) ----------
Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ---------- Users + Settings (admin only) ----------
    Route::middleware('role:admin')->group(function () {
        // 'users/roles' must come BEFORE the users resource
        Route::get('users/roles', [UserController::class, 'roles'])->name('users.roles');
        Route::resource('users', UserController::class)->except(['show']);

        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    });

    // ---------- Patients: manage (admin, receptionist, staff) ----------
    // The resource is registered BEFORE patients/{patient} below,
    // so /patients/create matches "create", not {patient}.
    Route::middleware('role:admin,receptionist,staff')->group(function () {
        Route::resource('patients', PatientController::class)->except(['index', 'show']);
    });

    // ---------- Patients: view (every role, incl. accountant) ----------
    Route::get('patients', [PatientController::class, 'index'])->name('patients.index');
    Route::get('patients/{patient}', [PatientController::class, 'show'])->name('patients.show');

    // ---------- Medical History (admin, staff) ----------
    // Create/store nested under the patient; edit/update/destroy on the record.
    Route::middleware('role:admin,staff')->group(function () {
        Route::get('patients/{patient}/medical-histories/create',
            [MedicalHistoryController::class, 'create'])->name('medical-histories.create');
        Route::post('patients/{patient}/medical-histories',
            [MedicalHistoryController::class, 'store'])->name('medical-histories.store');
        Route::get('medical-histories/{medicalHistory}/edit',
            [MedicalHistoryController::class, 'edit'])->name('medical-histories.edit');
        Route::put('medical-histories/{medicalHistory}',
            [MedicalHistoryController::class, 'update'])->name('medical-histories.update');
        Route::delete('medical-histories/{medicalHistory}',
            [MedicalHistoryController::class, 'destroy'])->name('medical-histories.destroy');
    });

    // ---------- Appointments (admin, receptionist, staff) ----------
    // Written manually (not a resource) so /appointments/create
    // is registered before /appointments/{appointment}.
    Route::middleware('role:admin,receptionist,staff')->group(function () {
        Route::get('appointments', [AppointmentController::class, 'index'])->name('appointments.index');
        Route::get('appointments/create', [AppointmentController::class, 'create'])->name('appointments.create');
        Route::post('appointments', [AppointmentController::class, 'store'])->name('appointments.store');
        Route::get('appointments/{appointment}', [AppointmentController::class, 'show'])->name('appointments.show');
        Route::get('appointments/{appointment}/edit', [AppointmentController::class, 'edit'])->name('appointments.edit');
        Route::put('appointments/{appointment}', [AppointmentController::class, 'update'])->name('appointments.update');
        Route::delete('appointments/{appointment}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');
    });

    // ---------- Treatments catalog (admin, staff) ----------
    Route::middleware('role:admin,staff')->group(function () {
        Route::resource('treatments', TreatmentController::class);
    });

    // ---------- Patient Treatments (admin, receptionist, staff) ----------
    Route::middleware('role:admin,receptionist,staff')->group(function () {
        Route::resource('patient-treatments', PatientTreatmentController::class);
    });

    // ---------- Billing: Invoices (admin, accountant, receptionist) ----------
    Route::middleware('role:admin,accountant,receptionist')->group(function () {
        // print BEFORE the resource so {invoice} can't swallow it
        Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
        Route::resource('invoices', InvoiceController::class);
    });

    // ---------- Billing: Payments (admin, accountant, receptionist) ----------
    Route::middleware('role:admin,accountant,receptionist')->group(function () {
        // outstanding + print BEFORE the resource so {payment} can't swallow them
        Route::get('payments/outstanding', [PaymentController::class, 'outstanding'])->name('payments.outstanding');
        Route::get('payments/{payment}/print', [PaymentController::class, 'print'])->name('payments.print');
        Route::resource('payments', PaymentController::class);
    });

    // ---------- Inventory (admin only) ----------
    Route::middleware('role:admin')->group(function () {
        Route::resource('medicine-categories', MedicineCategoryController::class);
        Route::resource('suppliers', SupplierController::class);
        Route::resource('medicines', MedicineController::class);
        // A ledger: no edit/update — mistakes are reversed, not rewritten
        Route::resource('stock', StockController::class)->only(['index', 'create', 'store', 'destroy']);
    });

    // ---------- Medicine Usage (admin, staff) ----------
    Route::middleware('role:admin,staff')->group(function () {
        // Ledger: no edit/update
        Route::resource('medicine-usages', MedicineUsageController::class)
            ->only(['index', 'create', 'store', 'destroy']);
    });

    // ---------- Expenses (admin, accountant) ----------
    Route::middleware('role:admin,accountant')->group(function () {
        Route::resource('expense-categories', ExpenseCategoryController::class);
        Route::resource('expenses', ExpenseController::class);
    });

    // ---------- Reports (admin, accountant) ----------
    Route::middleware('role:admin,accountant')->group(function () {
        Route::get('reports/patients',  [ReportController::class, 'patients'])->name('reports.patients');
        Route::get('reports/payments',  [ReportController::class, 'payments'])->name('reports.payments');
        Route::get('reports/expenses',  [ReportController::class, 'expenses'])->name('reports.expenses');
        Route::get('reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
        Route::get('reports/usage',     [ReportController::class, 'usage'])->name('reports.usage');
        Route::get('reports/financial', [ReportController::class, 'financial'])->name('reports.financial');
    });
});
