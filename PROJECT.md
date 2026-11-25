# Dental Clinic & Implant Center Management System

A comprehensive, role-based Clinic Management System built with **Laravel 12**, **MySQL**, and modern Blade UI, tailored specifically for dental practices, surgical implantology, and multi-doctor clinical workflows.

---

## 📋 Table of Contents
- [Overview](#-overview)
- [Key Features & Modules](#-key-features--modules)
  - [1. User Roles & Doctor Scoping](#1-user-roles--doctor-scoping)
  - [2. Patients & Medical History](#2-patients--medical-history)
  - [3. Appointments & WhatsApp Reminders](#3-appointments--whatsapp-reminders)
  - [4. Clinical Treatments & Procedure Records](#4-clinical-treatments--procedure-records)
  - [5. Dental Implants & External Sales](#5-dental-implants--external-sales)
  - [6. Billing, Invoices & Unified Outstanding Dues](#6-billing-invoices--unified-outstanding-dues)
  - [7. Pharmacy, Stock & In-Clinic Usage](#7-pharmacy-stock--in-clinic-usage)
  - [8. Expenses & Financial Reporting](#8-expenses--financial-reporting)
- [System Architecture](#-system-architecture)
- [Tech Stack](#-tech-stack)
- [Installation & Setup](#-installation--setup)
- [Default User Accounts](#-default-user-accounts)
- [Testing & Quality Assurance](#-testing--quality-assurance)

---

## 🌟 Overview

The **Dental Clinic & Implant Center Management System** is designed for high-efficiency clinical operations. It centralizes patient records, doctor appointments, tooth-specific dental treatments, inventory management, pharmacy dispensing, dental implant tracking, external implant commerce with other doctors, and financial ledgers into a single, cohesive web platform.

---

## 🚀 Key Features & Modules

### 1. User Roles & Doctor Scoping
- **Super Administrator (`admin`)**: Complete control over all clinic operations, user accounts, system settings, financial ledgers, inventory, and cross-doctor analytics.
- **Doctors (`doctor`)**: Independent login credentials with role-scoped data isolation:
  - Doctors only see patients assigned to them or who have appointments/treatments with them.
  - Doctors only view their own appointments, treatment records, and patient outstanding dues.
- **Support Roles**:
  - **Receptionist (`receptionist`)**: Front-desk operations, patient registration, and appointment booking.
  - **Accountant (`accountant`)**: Financial ledgers, invoice settlements, expense tracking, and reports.
  - **Staff Member (`staff`)**: Assisting with patient records, clinical notes, and medicine usage.

### 2. Patients & Medical History
- Comprehensive patient profiles: contact info, age, gender, blood group, emergency contacts, and medical alerts.
- **Unified Treatment & Medical History**:
  - Medical History represents previous treatments performed by the clinic along with their financial records (procedures performed, attending doctor, treatment fees, amount paid, and outstanding dues).
  - All past treatments and historical balances are entered via **Record Treatment & Billing** with any past visit date, generating standard invoices and dues calculating seamlessly alongside all clinic records.
  - General clinical notes, medical background, allergies, and chronic conditions are also recorded directly under the patient chart.

### 3. Appointments & WhatsApp Reminders
- Conflict-preventing appointment scheduler with real-time slot checking (`/appointments/booked-slots`).
- Appointment workflow: `Pending` → `Confirmed` → `Completed` / `Cancelled`.
- **Automated WhatsApp Reminders**:
  - One-click WhatsApp link generator targeting the patient's phone number.
  - Polite reminder message template:
    > *"Please arrive 5 to 10 minutes prior to your scheduled time. If you need to reschedule or have any questions, kindly reply to this message."*
  - Contextual visibility: The reminder button automatically disappears once an appointment is marked **Completed** or **Cancelled**.

### 4. Clinical Treatments & Procedure Records
- Master catalog of dental procedures with standardized pricing and descriptions.
- Tooth-specific charting and treatment records:
  - Select tooth numbers (FDI / Universal numbering).
  - Multi-procedure recording with custom doctor notes.
  - Prescribed medication linking directly from the in-house pharmacy catalog.
  - Automatic invoice generation based on treatment costs.

### 5. Dental Implants & External Sales
- **Implant Inventory Management**:
  - Brand, diameter, length, lot number, unit purchase price, and selling price.
  - Restocking logs and stock transaction history.
- **Failed Implants Tracking**:
  - Dedicated module for failed dental implants.
  - Tracks failure reasons (osseointegration failure, mechanical fracture, infection, etc.), patient details, and doctor notes.
  - Calculated and reported separately from standard inventory consumption.
- **Implant Sales to External Doctors**:
  - Dedicated registry of external doctors and partner clinics (`ImplantBuyer`).
  - Point-of-Sale (POS) order entry with batch selection and custom pricing.
  - Printable sales invoices and buyer payment receipts.
  - Outstanding dues ledger for external doctor implant buyers.

### 6. Billing, Invoices & Unified Outstanding Dues
- Standardized invoicing with itemized treatments, discounts, taxes, and balance calculations.
- Printable invoices and payment receipts with customizable clinic branding.
- **Centralized Outstanding Dues**:
  - Unified ledger tracking all unpaid patient balances across the clinic (`/payments/outstanding`).
  - Searchable by patient name, phone, or invoice number.
  - Doctor filtering: Doctors can filter and view dues specific to their patients.
  - One-click WhatsApp payment reminders with custom balance notices.

### 7. Pharmacy, Stock & In-Clinic Usage
- Medicine catalog with categories, suppliers, expiry dates, purchase costs, and retail prices.
- **Immutable Stock Ledger**: Stock in, stock out, and adjustment audits.
- **In-Clinic Medicine Usage**: Log medications and consumables used during surgeries or appointments to maintain real-time inventory counts.

### 8. Expenses & Financial Reporting
- Categorized clinic operating expenses (utilities, lab fees, equipment, maintenance).
- **Executive Reports & Analytics**:
  - Financial Profit & Loss (Revenue vs. Expenses vs. Outstanding).
  - Patient demographic and visit volume reports.
  - Payment collections by method (Cash, Card, Bank Transfer).
  - Inventory valuation and low-stock threshold alerts.

---

## 🏛 System Architecture

### Key Models & Relationships
- **`User`**: System accounts (`admin`, `doctor`, `accountant`, `receptionist`, `staff`).
- **`Patient`**: Core patient records; scoped by doctor via appointments and treatments.
- **`Appointment`**: Scheduled visits linked to a patient, doctor, and status workflow.
- **`MedicalHistory`**: Clinical conditions, allergies, past diagnoses, and medical background.
- **`Treatment` & `PatientTreatment`**: Master procedures and individual patient visit procedures.
- **`Invoice` & `Payment`**: Billing transactions, payment receipts, and balance calculations.
- **`Implant` & `FailedImplant`**: Surgical implant stock and failed implant audit logs.
- **`ImplantBuyer`, `ImplantSale`, `ImplantPayment`**: Wholesale implant sales to external doctors.
- **`Medicine`, `StockTransaction`, `MedicineUsage`**: Inventory, pharmacy batches, and usage audits.
- **`Expense` & `ExpenseCategory`**: Operational cost accounting.

---

## 🛠 Tech Stack

- **Backend**: [Laravel 12](https://laravel.com/) (PHP 8.2+)
- **Database**: MySQL 8.0+
- **Frontend**: Blade Templates, Bootstrap / Tailwind CSS, Alpine.js, Chart.js
- **Testing**: [Pest PHP](https://pestphp.com/) & PHPUnit
- **Code Standards**: Laravel Pint (PSR-12)

---

## 💻 Installation & Setup

### Prerequisites
- PHP 8.2 or higher
- Composer
- MySQL 8.0+
- Node.js (v18+) & NPM

### Setup Instructions

1. **Clone the repository & enter directory**:
   ```bash
   git clone <repository-url>
   cd Clinic
   ```

2. **Install PHP dependencies**:
   ```bash
   composer install
   ```

3. **Configure Environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Update `.env` with your database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=clinic_dentist
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Run Database Migrations & Seeders**:
   ```bash
   php artisan migrate --seed
   ```

5. **Install & Compile Frontend Assets**:
   ```bash
   npm install
   npm run build
   ```

6. **Start the Development Server**:
   ```bash
   php artisan serve
   ```
   Access the application at `http://127.0.0.1:8000`.

---

## 🔑 Default User Accounts

When seeded, the application includes the following standard test accounts (password for all seeded accounts is typically `password`):

| Role | Email | Capabilities |
| :--- | :--- | :--- |
| **Super Admin** | `admin@clinic.test` | Full system access, users, settings, finances |
| **Doctor** | `doctor@clinic.test` | Assigned patients, appointments, treatments, dues |
| **Accountant** | `accountant@clinic.test` | Invoices, payments, expenses, financial reports |
| **Receptionist** | `receptionist@clinic.test` | Patient registration, scheduling, basic billing |
| **Staff** | `staff@clinic.test` | Patient records, clinical notes, stock usage |

---

## 🧪 Testing & Quality Assurance

The application features comprehensive feature test coverage using Pest / PHPUnit:

```bash
# Run the entire test suite
php artisan test

# Run tests for specific modules
php artisan test --filter=PatientHistoryAndPreAppOutstandingsTest
php artisan test --filter=AppointmentBookingRulesAndFiltersTest

# Run code style formatting check
vendor/bin/pint --test
```

---

## 📄 License

This application is proprietary software designed for private clinical operations. All rights reserved.
