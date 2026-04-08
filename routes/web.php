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
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Asociados\AsociadosController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CuponController;

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

// --- RUTAS DE ADMINISTRACIÓN ---
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/index', [AdminController::class, 'index'])->name('admin.index');
    // Rutas para el CRUD de Roles
    Route::get('/admin/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::post('/admin/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::put('/admin/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/admin/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

    Route::resource('usuarios', UserController::class)->except(['create', 'show', 'edit']);
    Route::resource('cupones', CuponController::class)->except(['create', 'show', 'edit']);

});

// --- RUTAS DE ASOCIADOS ---
Route::middleware(['auth', 'role:asociado|admin'])->group(function () {
    Route::get('/asociados/index', [AsociadosController::class, 'index'])->name('asociados.index');
});


Route::get('/', [InscripcionController::class, 'index'])->name('inscripcion.index');
Route::get('/cursos/{id}', [InscripcionController::class, 'indexCursos'])->name('inscripcion.indexCursos');
Route::post('/contactanos', [InscripcionController::class, 'contactanos'])->name('contactanos.store');
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
Route::get('/registro/participante', [InscripcionController::class, 'participante'])->name('inscripcion.participante');

Route::get('/registro/general', [InscripcionController::class, 'general'])->name('inscripcion.general');
Route::get('/registro/estudiante', [InscripcionController::class, 'estudiante'])->name('inscripcion.estudiante');
Route::get('/registro/docente', [InscripcionController::class, 'docente'])->name('inscripcion.docente');

Route::get('/registro/cursosviajes', [InscripcionController::class, 'cursosViajes'])->name('inscripcion.cursosviajes');

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

// --- RUTAS DE PERFIL (ESTAS SON LAS QUE FALTABAN) ---
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
