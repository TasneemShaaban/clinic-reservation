<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Reservation;
use App\Support\BookingSlots;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;
use Throwable;

class ReservationController extends Controller
{
    #[OA\Post(
        path: '/api/reservations',
        operationId: 'createReservation',
        tags: ['Reservations'],
        summary: 'Book an appointment reservation',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['doctor_id', 'reservation_date', 'time_slot', 'patient_name', 'patient_email', 'patient_phone'],
                properties: [
                    new OA\Property(property: 'doctor_id', type: 'integer', example: 1),
                    new OA\Property(property: 'reservation_date', type: 'string', format: 'date', example: '2026-05-13'),
                    new OA\Property(property: 'time_slot', type: 'string', example: '10:00 AM'),
                    new OA\Property(property: 'patient_name', type: 'string', example: 'Mona Ahmed'),
                    new OA\Property(property: 'patient_email', type: 'string', format: 'email', example: 'mona@example.com'),
                    new OA\Property(property: 'patient_phone', type: 'string', example: '01012345678'),
                    new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'First visit'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Reservation created successfully'),
            new OA\Response(response: 409, description: 'Selected time slot is unavailable'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $requestData['reservation_date'] = $requestData['reservation_date']
            ?? $requestData['appointment_date']
            ?? null;

        $validator = Validator::make($requestData, [
            'doctor_id' => ['required', 'integer', Rule::exists('doctors', Doctor::keyColumn())],
            'reservation_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'time_slot' => ['required', 'string', 'max:20'],
            'patient_name' => ['required', 'string', 'max:150'],
            'patient_email' => ['required', 'email', 'max:150'],
            'patient_phone' => ['required', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please complete the required booking details.',
                'data' => [
                    'errors' => $validator->errors(),
                ],
            ], 422);
        }

        $payload = $validator->validated();

        if (! BookingSlots::isKnownSlot($payload['time_slot'])) {
            return response()->json([
                'success' => false,
                'message' => 'Selected time slot is invalid.',
                'data' => [],
            ], 422);
        }

        if (BookingSlots::isBlockedSlot($payload['time_slot'])) {
            return response()->json([
                'success' => false,
                'message' => 'Selected time slot is unavailable.',
                'data' => [],
            ], 409);
        }

        $doctor = Doctor::query()
            ->with('user')
            ->find($payload['doctor_id']);

        if (! $doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Selected doctor was not found.',
                'data' => [],
            ], 422);
        }

        $reservationDateColumn = Reservation::dateColumn();
        $activeReservationQuery = Reservation::query()
            ->where('doctor_id', $doctor->doctor_id)
            ->whereDate($reservationDateColumn, $payload['reservation_date'])
            ->where('time_slot', $payload['time_slot']);

        if ($this->reservationHasColumn('status')) {
            $activeReservationQuery->where('status', '!=', 'cancelled');
        }

        $hasActiveReservation = $activeReservationQuery->exists();

        if ($hasActiveReservation) {
            return response()->json([
                'success' => false,
                'message' => 'Selected time slot is already booked. Please choose another slot.',
                'data' => [],
            ], 409);
        }

        $reservation = DB::transaction(function () use ($payload, $doctor, $reservationDateColumn): Reservation {
            return Reservation::query()->create($this->reservationPayload(
                $payload,
                $doctor,
                $reservationDateColumn
            ));
        });

        return response()->json([
            'success' => true,
            'message' => 'Appointment confirmed successfully.',
            'data' => [
                'reservation' => $this->formatReservation($reservation->fresh('doctor.user')),
            ],
        ], 201);
    }

    #[OA\Patch(
        path: '/api/reservations/{id}/cancel',
        operationId: 'cancelReservation',
        tags: ['Reservations'],
        summary: 'Cancel an appointment reservation',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Reservation cancelled successfully'),
            new OA\Response(response: 404, description: 'Reservation not found'),
            new OA\Response(response: 409, description: 'Reservation cannot be cancelled'),
        ]
    )]
    public function cancel(int $id): JsonResponse
    {
        $reservation = Reservation::query()
            ->with('doctor.user')
            ->find($id);

        if (! $reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Reservation not found.',
                'data' => [],
            ], 404);
        }

        if (! $this->reservationHasColumn('status')) {
            return response()->json([
                'success' => false,
                'message' => 'Reservation status is not available for cancellation.',
                'data' => [],
            ], 409);
        }

        $status = $reservation->effectiveStatus();

        if ($status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'This appointment is already cancelled.',
                'data' => [
                    'reservation' => $this->formatReservation($reservation),
                ],
            ], 409);
        }

        if ($status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Completed appointments cannot be cancelled.',
                'data' => [
                    'reservation' => $this->formatReservation($reservation),
                ],
            ], 409);
        }

        $reservation->status = 'cancelled';
        $reservation->save();

        return response()->json([
            'success' => true,
            'message' => 'Appointment cancelled successfully.',
            'data' => [
                'reservation' => $this->formatReservation($reservation->fresh('doctor.user')),
            ],
        ]);
    }

    private function resolvePatientId(): ?int
    {
        try {
            $user = auth('api')->user();
        } catch (Throwable) {
            return null;
        }

        if (! $user || $user->role !== 'PATIENT') {
            return null;
        }

        return optional($user->patient)->patient_id;
    }

    private function reservationPayload(array $payload, Doctor $doctor, string $reservationDateColumn): array
    {
        $data = [
            'patient_id' => $this->resolvePatientId(),
            'doctor_id' => $doctor->doctor_id,
            $reservationDateColumn => $payload['reservation_date'],
            'time_slot' => $payload['time_slot'],
        ];

        $optionalData = [
            'reservation_code' => $this->generateReservationCode($payload['reservation_date']),
            'status' => 'confirmed',
            'patient_name' => $payload['patient_name'],
            'patient_email' => $payload['patient_email'],
            'patient_phone' => $payload['patient_phone'],
            'notes' => $payload['notes'] ?? null,
            'consultation_fee' => $doctor->consultationFeeForBooking(),
            'processing_fee' => 5,
            'location' => $doctor->locationForBooking(),
        ];

        if ($reservationDateColumn !== 'appointment_date' && $this->reservationHasColumn('appointment_date')) {
            $optionalData['appointment_date'] = $payload['reservation_date'];
        }

        if ($reservationDateColumn !== 'reservation_date' && $this->reservationHasColumn('reservation_date')) {
            $optionalData['reservation_date'] = $payload['reservation_date'];
        }

        if ($this->reservationHasColumn('session_details')) {
            $optionalData['session_details'] = $payload['notes'] ?? 'Patient booking through ClinicReserve.';
        }

        foreach ($optionalData as $column => $value) {
            if ($this->reservationHasColumn($column)) {
                $data[$column] = $value;
            }
        }

        return $data;
    }

    private function generateReservationCode(string $date): string
    {
        $prefix = 'RES-'.Carbon::parse($date)->format('Ymd').'-';

        do {
            $code = $prefix.random_int(1000, 9999);
        } while ($this->reservationHasColumn('reservation_code') && Reservation::query()->where('reservation_code', $code)->exists());

        return $code;
    }

    private function formatReservation(Reservation $reservation): array
    {
        $doctor = $reservation->doctor;
        $date = $reservation->appointmentDateForApi();
        $consultationFee = (float) ($reservation->consultation_fee ?? optional($doctor)->consultationFeeForBooking() ?? 150);
        $processingFee = (float) ($reservation->processing_fee ?? 5);

        return [
            'id' => $reservation->reservation_id,
            'reservation_id' => $reservation->reservation_id,
            'reservation_code' => $reservation->reservation_code,
            'patient_id' => $reservation->patient_id,
            'patient_name' => $reservation->patient_name,
            'patient_email' => $reservation->patient_email,
            'patient_phone' => $reservation->patient_phone,
            'doctor_id' => $reservation->doctor_id,
            'doctor_name' => optional($doctor)->display_name,
            'appointment_date' => $date,
            'reservation_date' => $date,
            'time_slot' => $reservation->time_slot,
            'status' => $reservation->effectiveStatus(),
            'can_cancel' => $reservation->canBeCancelled(),
            'notes' => $reservation->notes,
            'location' => $reservation->location ?: optional($doctor)->locationForBooking(),
            'consultation_fee' => $consultationFee,
            'processing_fee' => $processingFee,
            'total_estimated' => $consultationFee + $processingFee,
        ];
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
