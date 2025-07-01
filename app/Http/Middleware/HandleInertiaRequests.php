<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;
use App\Models\Inscripcion;
use App\Models\InscripcionesBeneficio;
use App\Models\Pais;
use App\Models\TipoDocumento;
use App\Models\TipoDocumentoPago;
use App\Models\TipoPago;
use App\Models\TipoServicio;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Carbon\Carbon;


class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $sharedData = parent::share($request);

        $sharedData['general.paises'] = Pais::where('isactive', true)->get();
        $sharedData['general.tipDocPer'] = TipoDocumento::where('isactive', true)
                ->whereJsonContains('tipo', 'persona')->get();
        $sharedData['general.tipoDocumentoPago'] = TipoDocumentoPago::where('isactive', true)->get();
        $sharedData['general.tipDocEmp'] = TipoDocumento::where('isactive', true)
                ->whereJsonContains('tipo', 'empresa')->orWhere('name_es', '=','DNI')->get(); // se agrego dni como documento para el pago
        $sharedData['general.tipoServicios'] = TipoServicio::where('isactive', true)->get();
        $sharedData['general.generos'] = config('data.generos');
        $sharedData['general.reglamento_inscripciones'] = config('app.reglamento_inscripciones');
        $sharedData['flash'] = [
            'success' => $request->session()->get('success'),
            'error' => $request->session()->get('error'),
            'warning' => $request->session()->get('warning'),
            'info' => $request->session()->get('info'),
        ];

        $sharedData['data'] = [
            'response' => $request->session()->get('response'),
        ];


        return $sharedData;

    }
}
