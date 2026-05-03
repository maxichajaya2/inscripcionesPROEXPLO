<?php

namespace App\Imports;

use App\Models\PersonalCarbono;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class PersonalCarbonoImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, SkipsEmptyRows
{
    public function model(array $row)
    {
        // TRUCO PARA DEPURAR: Si sigue sin importar nada,
        // abre storage/logs/laravel.log y verás exactamente cómo Laravel está leyendo tus columnas.
        // \Illuminate\Support\Facades\Log::info('Leyendo fila:', $row);

        // Validamos si existe la columna y si tiene datos
        if (!isset($row['correo']) || empty(trim($row['correo']))) {
            return null; // Si no hay correo o la columna se llama distinto, salta la fila
        }

        return new PersonalCarbono([
            // Asegúrate de que las cabeceras en tu Excel digan EXACTAMENTE "nombre" y "correo" (en minúsculas)
            'nombre' => isset($row['nombre']) ? trim($row['nombre']) : 'Sin nombre',
            'correo' => trim($row['correo']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function batchSize(): int
    {
        return 500;
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
