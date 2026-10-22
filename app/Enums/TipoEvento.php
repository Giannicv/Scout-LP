<?php

namespace App\Enums;

enum TipoEvento: string
{
    case ACTIVIDAD = 'actividad';
    case EVENTO = 'evento';
    case PROYECTO_ROVER = 'proyecto_rover';
}
