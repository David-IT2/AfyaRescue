<?php

namespace App\Services;

use App\Models\Emergency;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmergencyReportingService
{
    public function __construct(
        protected EmergencyAnalyticsService $analytics
    ) {}

    /**
     * Build query with filters: patient search (name/phone), status, hospital, date range.
     */
    public function filteredQuery(array $filters, ?int $hospitalIdScope = null): \Illuminate\Database\Eloquent\Builder
    {
        $q = Emergency::with(['patient:id,name,phone', 'hospital:id,name', 'ambulance:id,plate_number']);
        if ($hospitalIdScope) {
            $q->where('hospital_id', $hospitalIdScope);
        }
        if (! empty($filters['patient'])) {
            $term = $filters['patient'];
            $q->whereHas('patient', function ($q) use ($term) {
                $q->where('name', 'like', '%' . $term . '%')
                    ->orWhere('phone', 'like', '%' . $term . '%');
            });
        }
        if (! empty($filters['patient_id'])) {
            $q->where('patient_id', $filters['patient_id']);
        }
        if (! empty($filters['status'])) {
            $q->where('status', $filters['status']);
        }
        if (! empty($filters['hospital_id'])) {
            $q->where('hospital_id', $filters['hospital_id']);
        }
        if (! empty($filters['from'])) {
            $q->whereDate('created_at', '>=', $filters['from']);
        }
        if (! empty($filters['to'])) {
            $q->whereDate('created_at', '<=', $filters['to']);
        }
        return $q;
    }

    public function exportCsv(array $filters, ?int $hospitalIdScope = null): StreamedResponse
    {
        $query = $this->filteredQuery($filters, $hospitalIdScope)->orderByDesc('created_at')->limit(5000);
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="afya-rescue-emergencies-' . date('Y-m-d-His') . '.csv"',
        ];
        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'ID', 'Status', 'Severity Category', 'Patient Name', 'Patient Phone', 'Hospital', 'Address',
                'Requested At', 'Assigned At', 'Enroute At', 'Arrived At', 'Closed At', 'ETA (min)', 'Ambulance',
            ]);
            $query->cursor()->each(function (Emergency $e) use ($handle) {
                fputcsv($handle, [
                    $e->id,
                    $e->status,
                    $e->severity_category ?? $e->severity_label,
                    $e->patient?->name ?? '',
                    $e->patient?->phone ?? '',
                    $e->hospital?->name ?? '',
                    $e->address_text ?? '',
                    $e->requested_at?->toIso8601String(),
                    $e->assigned_at?->toIso8601String(),
                    $e->enroute_at?->toIso8601String(),
                    $e->arrived_at?->toIso8601String(),
                    $e->closed_at?->toIso8601String(),
                    $e->eta_minutes ?? '',
                    $e->ambulance?->plate_number ?? '',
                ]);
            });
            fclose($handle);
        }, 'emergencies.csv', $headers);
    }
}
