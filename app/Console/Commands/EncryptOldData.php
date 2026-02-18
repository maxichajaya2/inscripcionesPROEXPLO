<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Config;

class EncryptOldData extends Command
{
    // Le cambié el nombre para que sepas que es el completo
    protected $signature = 'personas:encrypt-full-data';
    protected $description = 'Encripta Documento, Correo y Celular existentes y genera sus hashes';

    public function handle()
    {
        $this->info('Iniciando encriptación TOTAL (Doc + Correo + Celular)...');

        // 1. Obtenemos todas las personas (Datos crudos)
        $personas = DB::table('persona')->get();

        // Barra de progreso
        $bar = $this->output->createProgressBar(count($personas));

        // Obtenemos la llave una sola vez para optimizar
        $key = Config::get('app.key');
        $count = 0;

        foreach ($personas as $p) {
            $updates = [];

            // 2. Definimos el "Mapa" de campos a proteger
            // Columna Real => Columna Hash
            $camposAProcesar = [
                'documento' => 'documento_hash',
                'correo'    => 'correo_hash',
                'celular'   => 'celular_hash'
            ];

            foreach ($camposAProcesar as $campoReal => $campoHash) {
                // Obtenemos el valor actual (ej: "juan@gmail.com")
                $valorOriginal = $p->$campoReal;

                // VALIDACIÓN IMPORTANTE:
                // Solo encriptamos si:
                // a) No está vacío.
                // b) Su longitud es corta (menos de 100 caracteres).
                //    (El texto encriptado por Laravel siempre es larguísimo, +150 chars).
                if (!empty($valorOriginal) && strlen($valorOriginal) < 100) {
                    try {
                        // A. Generar Hash (Para poder buscar luego)
                        $updates[$campoHash] = hash_hmac('sha256', $valorOriginal, $key);

                        // B. Encriptar el dato real (Para guardar seguro)
                        $updates[$campoReal] = Crypt::encryptString($valorOriginal);

                    } catch (\Exception $e) {
                        // Si falla uno, lo registramos pero seguimos con los otros
                        $this->error("Error en campo $campoReal del ID {$p->id}: " . $e->getMessage());
                    }
                }
            }

            // 3. Si hubo cambios en este usuario, actualizamos la base de datos
            if (!empty($updates)) {
                DB::table('persona')
                    ->where('id', $p->id)
                    ->update($updates);

                $count++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("¡LISTO! Se han protegido $count registros exitosamente.");
    }
}
