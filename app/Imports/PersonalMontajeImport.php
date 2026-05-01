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

            // 2. Buscamos si el trabajador ya existe
            $registroExistente = DB::connection('pgsql_second')
                ->table('personal_montaje')
                ->where('documento', $row['documento'])
                ->first();

            if ($registroExistente) {
                // 3. SI EXISTE: Actualizamos sus datos
                DB::connection('pgsql_second')->table('personal_montaje')
                    ->where('id', $registroExistente->id)
                    ->update([
                        // Agregamos tipo_documento aquí
                        'tipo_documento' => $row['tipo_documento'] ?? $registroExistente->tipo_documento,
                        'nombres' => $row['nombres'] ?? $registroExistente->nombres,
                        'apellidos' => $row['apellidos'] ?? $registroExistente->apellidos,
                        'correo' => $row['correo'] ?? $registroExistente->correo,
                        'cargo' => $row['cargo'] ?? $registroExistente->cargo,
                        'ruc_empresa' => $row['ruc_empresa'] ?? $registroExistente->ruc_empresa,
                        'nombre_empresa' => $row['nombre_empresa'] ?? $registroExistente->nombre_empresa,
                        'updated_at' => Carbon::now(),
                    ]);
            } else {
                // 4. SI NO EXISTE: Lo insertamos como nuevo
                DB::connection('pgsql_second')->table('personal_montaje')->insert([
                    // Agregamos tipo_documento aquí (con 'DNI' como valor por defecto si viene vacío)
                    'tipo_documento' => $row['tipo_documento'] ?? 'DNI',
                    'documento' => $row['documento'],
                    'nombres' => $row['nombres'] ?? 'SIN NOMBRE',
                    'apellidos' => $row['apellidos'] ?? 'SIN APELLIDO',
                    'correo' => $row['correo'] ?? null,
                    'cargo' => $row['cargo'] ?? null,
                    'ruc_empresa' => $row['ruc_empresa'] ?? '00000000000',
                    'nombre_empresa' => $row['nombre_empresa'] ?? 'SIN EMPRESA',
                    'codigo_qr' => 'PROX26-' . strtoupper(Str::random(6)),
                    'autorizado' => true,
                    'motivo_bloqueo' => null,
                    'estado_presencia' => 'Afuera',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
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
