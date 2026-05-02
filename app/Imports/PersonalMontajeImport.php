<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\WithValidation;


class PersonalMontajeImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // 1. Verificamos que la fila tenga al menos el DNI/Documento
            if (!isset($row['documento']) || empty($row['documento'])) {
                continue; // Saltamos filas vacías
            }

            // --- LÓGICA DE CONVERSIÓN PARA 'AUTORIZADO' ---
            // Limpiamos el texto: quitamos espacios y pasamos a mayúsculas
            $valorExcel = strtoupper(trim($row['autorizado'] ?? ''));

            // Interpretamos el valor:
            // Si dice 'SI' -> true
            // Si dice 'NO' -> false
            // Si viene vacío o cualquier otra cosa -> true (por defecto)
            $esAutorizado = ($valorExcel === 'NO') ? false : true;

            // 2. Buscamos si el trabajador ya existe en la base de datos secundaria
            $registroExistente = DB::connection('pgsql_second')
                ->table('personal_montaje')
                ->where('documento', $row['documento'])
                ->first();

            if ($registroExistente) {
                // 3. SI EXISTE: Actualizamos sus datos
                DB::connection('pgsql_second')->table('personal_montaje')
                    ->where('id', $registroExistente->id)
                    ->update([
                        'tipo_documento' => $row['tipo_documento'] ?? $registroExistente->tipo_documento,
                        'nombres'        => $row['nombres'] ?? $registroExistente->nombres,
                        'apellidos'      => $row['apellidos'] ?? $registroExistente->apellidos,
                        'correo'         => $row['correo'] ?? $registroExistente->correo,
                        'cargo'          => $row['cargo'] ?? $registroExistente->cargo,
                        'ruc_empresa'    => $row['ruc_empresa'] ?? $registroExistente->ruc_empresa,
                        'nombre_empresa' => $row['nombre_empresa'] ?? $registroExistente->nombre_empresa,
                        'aseguradora'    => $row['aseguradora'] ?? $registroExistente->aseguradora,
                        'poliza'         => $row['poliza'] ?? $registroExistente->poliza,
                        // Aquí aplicamos la lógica del SI/NO
                        'autorizado'     => $esAutorizado,
                        // Si el usuario lo desbloquea vía Excel, limpiamos el motivo
                        'motivo_bloqueo' => $esAutorizado ? null : ($row['motivo_bloqueo'] ?? $registroExistente->motivo_bloqueo),
                        'updated_at'     => Carbon::now(),
                    ]);
            } else {
                // 4. SI NO EXISTE: Lo insertamos como nuevo
                DB::connection('pgsql_second')->table('personal_montaje')->insert([
                    'tipo_documento'   => $row['tipo_documento'] ?? 'DNI',
                    'documento'        => $row['documento'],
                    'nombres'          => $row['nombres'] ?? 'SIN NOMBRE',
                    'apellidos'        => $row['apellidos'] ?? 'SIN APELLIDO',
                    'correo'           => $row['correo'] ?? null,
                    'cargo'            => $row['cargo'] ?? null,
                    'ruc_empresa'      => $row['ruc_empresa'] ?? '00000000000',
                    'nombre_empresa'   => $row['nombre_empresa'] ?? 'SIN EMPRESA',
                    'aseguradora'      => $row['aseguradora'] ?? null,
                    'poliza'           => $row['poliza'] ?? null,
                    'codigo_qr'        => 'PROX26-' . strtoupper(Str::random(6)),
                    // Aplicamos el valor booleano obtenido del Excel
                    'autorizado'       => $esAutorizado,
                    'motivo_bloqueo'   => $esAutorizado ? null : ($row['motivo_bloqueo'] ?? 'Bloqueado por importación'),
                    'estado_presencia' => 'Afuera',
                    'created_at'       => Carbon::now(),
                    'updated_at'       => Carbon::now(),
                ]);
            }
        }
    }

    public function customValidationMessages()
    {
        return [
            'documento.required' => 'La columna Documento no existe o está vacía.',
            'nombres.required' => 'La columna Nombres no existe o está vacía.',
        ];
    }
}
