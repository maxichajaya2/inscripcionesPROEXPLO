<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\InscripcionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [InscripcionController::class, 'index'])->name('inscripcion.index');
    /*return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);*/

Route::get('/registro/convencionista', [InscripcionController::class, 'convencionista'])->name('inscripcion.convencionista');
Route::get('/registro/docente', [InscripcionController::class, 'docente'])->name('inscripcion.docente');
Route::get('/registro/estudiante', [InscripcionController::class, 'estudiante'])->name('inscripcion.estudiante');
Route::get('/registro/extemin', [InscripcionController::class, 'extemin'])->name('inscripcion.extemin');

