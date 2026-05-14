<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use OpenApi\Attributes as OA;
use Throwable;

class PatientRecordController extends Controller
{
    #[OA\Get(
        path: '/api/patient/record',
        operationId: 'patientRecord',
        tags: ['Patient Record'],
        summary: 'Show patient profile and appointment history',
        responses: [
            new OA\Response(response: 200, description: 'Patient record retrieved successfully'),
        ]
    )]
    public function record(): JsonResponse
    {
        $context = $this->resolvePatientContext();
        $appointments = $this->appointmentsForContext($context)
            ->map(fn (Reservation $reservation): array => $this->formatAppointment($reservation))
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Patient record retrieved successfully.',
            'data' => [
                'patient' => $this->formatPatient($context, $appointments),
                'clinical_indicators' => $this->clinicalIndicators($context, $appointments),
                'appointments' => $appointments,
            ],
        ]);
    }

    #[OA\Get(
        path: '/api/patient/appointments',
        operationId: 'patientAppointments',
        tags: ['Patient Record'],
        summary: 'List appointments for the current or demo patient',
        responses: [
            new OA\Response(response: 200, description: 'Patient appointments retrieved successfully'),
        ]
    )]
    public function appointments(): JsonResponse
    {
        $context = $this->resolvePatientContext();
        $appointments = $this->appointmentsForContext($context)
            ->map(fn (Reservation $reservation): array => $this->formatAppointment($reservation))
            ->values();

        return response()->json([
            'success' => true,
            'message' => $appointments->isEmpty()
                ? 'No appointments found for this patient.'
                : 'Patient appointments retrieved successfully.',
            'data' => [
                'appointments' => $appointments,
            ],
        ]);
    }

    private function resolvePatientContext(): array
    {
        $authPatient = $this->authPatient();

        if ($authPatient) {
            return $this->contextFromPatient($authPatient);
        }

        $latestReservation = $this->latestReservation();

        if ($latestReservation) {
            return $this->contextFromReservation($latestReservation);
        }

        $patient = $this->firstPatient();

        if ($patient) {
            return $this->contextFromPatient($patient);
        }

        return [
            'patient' => null,
            'user' => null,
            'reservation' => null,
            'patient_id' => null,
            'name' => 'Demo Patient',
            'email' => 'patient@example.com',
            'phone' => 'Not provided',
        ];
    }

    private function authPatient(): ?Patient
    {
        try {
            $user = auth('api')->user();
        } catch (Throwable) {
            return null;
        }

        if (! $user || $user->role !== 'PATIENT') {
            return null;
        }

        $patient = $user->patient;

        return $patient ? $patient->loadMissing('user') : null;
    }

    private function latestReservation(): ?Reservation
    {
        if (! Schema::hasTable('reservations')) {
            return null;
        }

        $query = Reservation::query()->with($this->reservationRelations());
        $this->orderReservations($query);

        return $query->first();
    }

    private function firstPatient(): ?Patient
    {
        if (! Schema::hasTable('patients')) {
            return null;
        }

        $query = Patient::query();

        if (Schema::hasTable('users')) {
            $query->with('user');
        }

        return $query->first();
    }

    private function contextFromPatient(Patient $patient): array
    {
        $user = $patient->relationLoaded('user') ? $patient->user : null;

        return [
            'patient' => $patient,
            'user' => $user,
            'reservation' => null,
            'patient_id' => $patient->patient_id,
            'name' => $this->patientName($patient, $user, null),
            'email' => $this->patientEmail($user, null),
            'phone' => $this->patientPhone($user, null),
        ];
    }

    private function contextFromReservation(Reservation $reservation): array
    {
        $patient = $reservation->relationLoaded('patient') ? $reservation->patient : null;
        $user = $patient && $patient->relationLoaded('user') ? $patient->user : null;

        return [
            'patient' => $patient,
            'user' => $user,
            'reservation' => $reservation,
            'patient_id' => $patient?->patient_id ?? $reservation->patient_id,
            'name' => $this->patientName($patient, $user, $reservation),
            'email' => $this->patientEmail($user, $reservation),
            'phone' => $this->patientPhone($user, $reservation),
        ];
    }

    private function appointmentsForContext(array $context): Collection
    {
        if (! Schema::hasTable('reservations')) {
            return collect();
        }

        $query = Reservation::query()->with($this->reservationRelations());
        $hasPatientFilter = false;

        if ($context['patient_id'] && $this->reservationHasColumn('patient_id')) {
            $query->where('patient_id', $context['patient_id']);
            $hasPatientFilter = true;
        } elseif ($context['email'] !== 'patient@example.com' && $this->reservationHasColumn('patient_email')) {
            $query->where('patient_email', $context['email']);
            $hasPatientFilter = true;
        } elseif ($context['phone'] !== 'Not provided' && $this->reservationHasColumn('patient_phone')) {
            $query->where('patient_phone', $context['phone']);
            $hasPatientFilter = true;
        }

        if (! $hasPatientFilter && $this->reservationHasColumn('patient_id')) {
            $query->whereNull('patient_id');
        }

        $this->orderReservations($query);

        return $query->get();
    }

    private function reservationRelations(): array
    {
        $relations = ['doctor'];

        if (Schema::hasTable('users')) {
            $relations[] = 'doctor.user';
        }

        if (Schema::hasTable('patients')) {
            $relations[] = 'patient';

            if (Schema::hasTable('users')) {
                $relations[] = 'patient.user';
            }
        }

        return $relations;
    }

    private function orderReservations($query): void
    {
        $dateColumn = Reservation::dateColumn();

        if ($this->reservationHasColumn($dateColumn)) {
            $query->orderByDesc($dateColumn);
        }

        if ($this->reservationHasColumn('created_at')) {
            $query->orderByDesc('created_at');
        }

        $query->orderByDesc(Reservation::keyColumn());
    }

    private function formatPatient(array $context, Collection $appointments): array
    {
        $patient = $context['patient'];
        $dateOfBirth = $patient?->getAttribute('date_of_birth');
        $firstAppointment = $appointments->first();
        $primaryDoctor = is_array($firstAppointment) ? ($firstAppointment['doctor_name'] ?? 'Not assigned') : 'Not assigned';

        return [
            'id' => $context['patient_id'],
            'patient_id' => $context['patient_id'],
            'name' => $context['name'],
            'initials' => $this->initials($context['name']),
            'mrn' => $context['patient_id'] ? 'MRN-'.str_pad((string) $context['patient_id'], 5, '0', STR_PAD_LEFT) : 'MRN-DEMO',
            'date_of_birth' => $this->dateForApi($dateOfBirth),
            'age' => $this->ageLabel($dateOfBirth),
            'gender' => $patient?->getAttribute('gender') ?: 'Not provided',
            'email' => $context['email'],
            'phone' => $context['phone'],
            'address' => $patient?->getAttribute('address') ?: 'Not provided',
            'primary_care_physician' => $primaryDoctor,
            'status' => 'active',
            'status_label' => 'Active Patient',
        ];
    }

    private function clinicalIndicators(array $context, Collection $appointments): array
    {
        $patient = $context['patient'];
        $lastVisit = $this->lastVisitLabel($patient, $appointments);
        $latestAppointment = $appointments->first();

        return [
            'blood_type' => $patient?->getAttribute('blood_type') ?: 'O+',
            'allergies' => $patient?->getAttribute('allergies') ?: 'No known allergies',
            'last_visit' => $lastVisit,
            'medical_history' => $patient?->getAttribute('medical_history') ?: 'No chronic conditions recorded.',
            'prescription_info' => $latestAppointment['prescription_info'] ?? 'No prescriptions recorded.',
        ];
    }

    private function formatAppointment(Reservation $reservation): array
    {
        $doctor = $reservation->doctor;
        $date = $reservation->appointmentDateForApi();
        $status = $reservation->effectiveStatus();

        return [
            'id' => $reservation->reservation_id,
            'reservation_id' => $reservation->reservation_id,
            'reservation_code' => $reservation->reservation_code ?: 'RES-'.$reservation->reservation_id,
            'doctor_id' => $reservation->doctor_id,
            'doctor_name' => optional($doctor)->display_name ?: 'Assigned Doctor',
            'doctor_specialty' => optional($doctor)->specialty ?: 'General Care',
            'appointment_date' => $date,
            'date_label' => $this->dateLabel($date),
            'time_slot' => $reservation->time_slot ?: 'Time pending',
            'status' => $status,
            'status_label' => ucfirst($status),
            'can_cancel' => $reservation->canBeCancelled(),
            'session_details' => $reservation->getAttribute('session_details')
                ?: $reservation->notes
                ?: 'Session details will be updated after your consultation.',
            'prescription_info' => $reservation->getAttribute('prescription_info') ?: 'No prescription recorded.',
            'notes' => $reservation->notes,
            'location' => $reservation->location ?: optional($doctor)->locationForBooking() ?: 'Main City Hospital',
        ];
    }

    private function patientName(?Patient $patient, mixed $user, ?Reservation $reservation): string
    {
        return $reservation?->patient_name
            ?: $user?->full_name
            ?: ($patient?->patient_id ? 'Patient '.$patient->patient_id : 'Demo Patient');
    }

    private function patientEmail(mixed $user, ?Reservation $reservation): string
    {
        return $reservation?->patient_email ?: $user?->email ?: 'patient@example.com';
    }

    private function patientPhone(mixed $user, ?Reservation $reservation): string
    {
        return $reservation?->patient_phone ?: $user?->phone ?: 'Not provided';
    }

    private function initials(string $name): string
    {
        $parts = array_values(array_filter(explode(' ', trim($name))));

        if (! $parts) {
            return 'P';
        }

        return strtoupper(substr($parts[0], 0, 1).substr($parts[1] ?? $parts[0], 0, 1));
    }

    private function ageLabel(mixed $dateOfBirth): string
    {
        if (! $dateOfBirth) {
            return 'Age not provided';
        }

        try {
            return Carbon::parse($dateOfBirth)->age.' years';
        } catch (Throwable) {
            return 'Age not provided';
        }
    }

    private function dateForApi(mixed $date): ?string
    {
        if (! $date) {
            return null;
        }

        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (Throwable) {
            return null;
        }
    }

    private function dateLabel(?string $date): string
    {
        if (! $date) {
            return 'Date pending';
        }

        try {
            return Carbon::parse($date)->format('M d, Y');
        } catch (Throwable) {
            return $date;
        }
    }

    private function lastVisitLabel(?Patient $patient, Collection $appointments): string
    {
        $lastVisit = $patient?->getAttribute('last_visit');

        if ($lastVisit) {
            return $this->dateLabel($this->dateForApi($lastVisit));
        }

        $completed = $appointments->firstWhere('status', 'completed');

        return $completed['date_label'] ?? 'No completed visits yet';
    }

    private function reservationHasColumn(string $column): bool
    {
        try {
            return Schema::hasColumn('reservations', $column);
        } catch (Throwable) {
            return false;
        }
    }
}
