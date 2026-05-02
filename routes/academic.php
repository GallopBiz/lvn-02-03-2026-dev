<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Academic\ExamController;

Route::prefix('academic')->group(function () {
    Route::post('/exams', [ExamController::class, 'store'])->name('academic.exams.store');
    // Add more academic module routes here as needed
});
