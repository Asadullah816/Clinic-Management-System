@extends('layouts.app')

@section('title', 'Roles & Access')

@section('content')

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Roles &amp; Access</h5>
        </div>

        <div class="card-body">
            <p class="text-muted">
                This system uses four fixed roles. Access is enforced by the
                <code>role:</code> middleware on every route group.
            </p>

            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th class="text-start">Module</th>
                            <th>Administrator</th>
                            <th>Accountant</th>
                            <th>Receptionist</th>
                            <th>Staff</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-start">Dashboard</td>
                            <td>&#10004;</td>
                            <td>&#10004;</td>
                            <td>&#10004;</td>
                            <td>&#10004;</td>
                        </tr>
                        <tr>
                            <td class="text-start">Patients (view)</td>
                            <td>&#10004;</td>
                            <td>&#10004;</td>
                            <td>&#10004;</td>
                            <td>&#10004;</td>
                        </tr>
                        <tr>
                            <td class="text-start">Patients (manage)</td>
                            <td>&#10004;</td>
                            <td>&mdash;</td>
                            <td>&#10004;</td>
                            <td>&#10004;</td>
                        </tr>
                        <tr>
                            <td class="text-start">Medical History</td>
                            <td>&#10004;</td>
                            <td>&mdash;</td>
                            <td>&mdash;</td>
                            <td>&#10004;</td>
                        </tr>
                        <tr>
                            <td class="text-start">Appointments</td>
                            <td>&#10004;</td>
                            <td>&mdash;</td>
                            <td>&#10004;</td>
                            <td>&#10004;</td>
                        </tr>
                        <tr>
                            <td class="text-start">Treatments &amp; Patient Treatments</td>
                            <td>&#10004;</td>
                            <td>&mdash;</td>
                            <td>&#10004;</td>
                            <td>&#10004;</td>
                        </tr>
                        <tr>
                            <td class="text-start">Invoices &amp; Payments</td>
                            <td>&#10004;</td>
                            <td>&#10004;</td>
                            <td>&#10004;</td>
                            <td>&mdash;</td>
                        </tr>
                        <tr>
                            <td class="text-start">Inventory (Products, Stock, Suppliers)</td>
                            <td>&#10004;</td>
                            <td>&mdash;</td>
                            <td>&mdash;</td>
                            <td>&mdash;</td>
                        </tr>
                        <tr>
                            <td class="text-start">Medicine/Product Usage</td>
                            <td>&#10004;</td>
                            <td>&mdash;</td>
                            <td>&mdash;</td>
                            <td>&#10004;</td>
                        </tr>
                        <tr>
                            <td class="text-start">Expenses</td>
                            <td>&#10004;</td>
                            <td>&#10004;</td>
                            <td>&mdash;</td>
                            <td>&mdash;</td>
                        </tr>
                        <tr>
                            <td class="text-start">Reports</td>
                            <td>&#10004;</td>
                            <td>&#10004;</td>
                            <td>&mdash;</td>
                            <td>&mdash;</td>
                        </tr>
                        <tr>
                            <td class="text-start">Users &amp; Settings</td>
                            <td>&#10004;</td>
                            <td>&mdash;</td>
                            <td>&mdash;</td>
                            <td>&mdash;</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
