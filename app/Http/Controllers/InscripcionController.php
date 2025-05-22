<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Carbon\Carbon;

class InscripcionController extends Controller
{
    public function index(){
        return Inertia::render('Inscripcion/Index');
    }

    public function convencionista(){
        return Inertia::render('Inscripcion/Convencionista');
    }
}
