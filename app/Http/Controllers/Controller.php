<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

abstract class Controller
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Period options for filter dropdowns (shared by patients, expenses, dashboard).
     */
    protected function periods(): array
    {
        return [
            'today' => 'Today',
            '3days' => 'Last 3 Days',
            '7days' => 'Last 7 Days',
            'month' => 'This Month',
            'all'   => 'All Time',
        ];
    }

    /**
     * Convert a period key into [$start, $end, $label].
     * $start/$end are datetime boundaries (or null for "all time").
     */
    protected function periodRange(?string $period): array
    {
        return match ($period) {
            'today' => [today()->startOfDay(), today()->endOfDay(), 'Today'],
            '3days' => [today()->subDays(2)->startOfDay(), today()->endOfDay(), 'Last 3 Days'],
            '7days' => [today()->subDays(6)->startOfDay(), today()->endOfDay(), 'Last 7 Days'],
            'month' => [now()->startOfMonth(), now()->endOfMonth(), 'This Month'],
            default => [null, null, 'All Time'],
        };
    }
}
