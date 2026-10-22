<?php

namespace App\Enums;

enum EstadoEvento: string
{
    case BORRADOR = 'borrador';
    case PROPUESTO = 'propuesto';
    case PUBLICADO = 'publicado';
    case RECHAZADO = 'rechazado';
    case CANCELADO = 'cancelado';
    case FINALIZADO = 'finalizado';
}
