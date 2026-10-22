<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SolicitudCuentaRecibida extends Notification
{
    use Queueable;

    public function __construct(public User $solicitante) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return ['titulo' => 'Nueva solicitud de cuenta',
            'mensaje' => $this->solicitante->nombre.' '.$this->solicitante->apellido.' espera aprobación.',
            'usuario_id' => $this->solicitante->id, 'url' => route('solicitudes.index')];
    }
}
