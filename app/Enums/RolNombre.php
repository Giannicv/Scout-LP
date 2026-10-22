<?php

namespace App\Enums;

enum RolNombre: string
{
    case DISTRITAL = 'dirigente_distrital';
    case GRUPO = 'dirigente_grupo';
    case MIEMBRO = 'miembro';
}
