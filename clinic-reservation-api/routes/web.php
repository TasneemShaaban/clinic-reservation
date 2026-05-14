<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/patient/book-appointment/{doctorId?}', function (?int $doctorId = null) {
    return view('patient.book-appointment', [
        'doctorId' => $doctorId,
    ]);
})->whereNumber('doctorId');

Route::view('/patient/record', 'patient.record');
Route::view('/patient/profile', 'patient.record');
