<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\Distrito;
use App\Models\Empresa;
use App\Models\Provincia;
use App\Models\TipoPago;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Carrito;
use stdClass;

class PadreController extends Controller
{
    public function getDepartamentos(Request $request)
    {
        $departamentos = Departamento::where('isactive', 1)->where('id_pais', $request->id)->get();
        return ['departamentos' => $departamentos];
    }

    public function getProvincias(Request $request)
    {
        $provincias = Provincia::where('isactive', 1)->where('id_pais', $request->id_pais)->where('id_departamento', $request->id_departamento)->get();
        return ['provincias' => $provincias];
    }

    public function getDistritos(Request $request)
    {
        $distritos = Distrito::where('isactive', 1)->where('id_pais', $request->id_pais)->where(
            'id_departamento',
            $request->id_departamento,
        )->where('id_provincia', $request->id_provincia)->get();
        return ['distritos' => $distritos];
    }

    public function getEmpresa(Request $request)
    {
        $empresa = Empresa::with('direccion')->where('id', $request->id)->first();
        $objeto = new stdClass();
        $objeto->nombre = $empresa->nombre;
        $objeto->id_tipo_documento = $empresa->id_tipo_documento;
        $objeto->documento = $empresa->documento;
        $objeto->id_pais = $empresa->direccion->id_pais;
        $objeto->id_departamento = $empresa->direccion->id_departamento;
        $objeto->id_provincia = $empresa->direccion->id_provincia;
        $objeto->id_distrito = $empresa->direccion->id_distrito;
        $objeto->direccion = $empresa->direccion->direccion;
        return redirect()->route($request->url)->with([
            'response' => $objeto
        ]);
    }

    public function getCurrency()
    {
        $moneda = new stdClass;
        $carrito = Carrito::where('id_empresa', auth()->user()->id_empresa)->where('isactive',true)->first();

        if( !is_null($carrito) ){
            if(isset($carrito->stand->precio->valor)){
                $moneda = $carrito->stand->precio->moneda;
            }else{
                $moneda = $carrito->stand->tipo_stand->precio->moneda;
            }
        }

        return $moneda;
    }

    public function geterror($id){
        $error_pago = \App\Models\Niubiz::find($id);

        return Inertia::render('Pago/Error',[
            'pago' => $error_pago
        ]);
    }
}
