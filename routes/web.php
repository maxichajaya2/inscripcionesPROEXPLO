<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\InscripcionController;
use App\Http\Controllers\DocumentosController;
use App\Http\Controllers\DocumentApiController;
use App\Http\Controllers\PadreController;
use App\Http\Controllers\NiubizController;

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

Route::post('/padre/departamentos', [PadreController::class, 'getDepartamentos'])->name('padre.departamentos');
Route::post('/padre/provincias', [PadreController::class, 'getProvincias'])->name('padre.provincias');
Route::post('/padre/distritos', [PadreController::class, 'getDistritos'])->name('padre.distritos');

Route::get('/registro/autor', [InscripcionController::class, 'autor'])->name('inscripcion.autor');
Route::get('/registro/docente', [InscripcionController::class, 'docente'])->name('inscripcion.docente');
Route::get('/registro/estudiante', [InscripcionController::class, 'estudiante'])->name('inscripcion.estudiante');
Route::get('/registro/participante', [InscripcionController::class, 'participante'])->name('inscripcion.participante');

Route::post('/pago/getform', [InscripcionController::class, 'getForm'])->name('niubiz.getform');
Route::post('/pago/getform/niubiz/{id}/{order}', [InscripcionController::class, 'niubizPayment'])->where('id','[0-9]+');

Route::get('/pago/confirmar/{id}', [InscripcionController::class, 'confirmPayment'])->name('inscripcion.extemin')->where('id','[0-9]+');
Route::get('/pago/error/{id}', [PadreController::class, 'geterror'])->name('pago.geterror');

Route::post('/api/validatepersonsoc', [DocumentApiController::class, 'validatePersonSoc'])->name('api.validatepersonsoc');
Route::post('/api/getperson', [DocumentApiController::class, 'getPersonData'])->name('api.getdataperson');
Route::post('/api/getempresa', [DocumentApiController::class, 'getEmpresaData'])->name('api.getempresa');

Route::post('/api/document', [DocumentApiController::class, 'getData'])->name('api.document');

Route::get('/niubiz/test', [NiubizController::class, 'index'])->name('niubiz.payment');

// Ruta "dummy" para que Niubiz no de error 404 visual si intenta redireccionar solo
Route::post('/niubiz-respuesta', function () {
    return response()->noContent();
})->name('niubiz.dummy');
