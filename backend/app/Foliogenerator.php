<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

/**
 * Trait FolioGenerator
 *
 * Usar en el modelo Solicitud para generar el folio único al enviar.
 *
 * Uso en SolicitudService::enviar(Solicitud $solicitud):
 *   $solicitud->folio = FolioGenerator::generar();
 *
 * Formato: GD-{AÑO}-{NNNN}  →  GD-2025-0042
 */
trait FolioGenerator
{
    /**
     * Genera el siguiente folio usando la secuencia de PostgreSQL.
     * Es atómico: no puede haber duplicados aunque dos usuarios envíen al mismo tiempo.
     */
    public static function generarFolio(): string
    {
        $secuencial = DB::selectOne("SELECT nextval('solicitudes_folio_seq') AS val")->val;

        return sprintf('GD-%s-%04d', date('Y'), $secuencial);
    }
}