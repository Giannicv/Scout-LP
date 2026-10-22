<?php

namespace App\Enums;

enum EstadoUsuario: string
{
    case PENDIENTE = 'pendiente';
    case ACTIVO = 'activo';
    case RECHAZADO = 'rechazado';
    case SUSPENDIDO = 'suspendido';
}
