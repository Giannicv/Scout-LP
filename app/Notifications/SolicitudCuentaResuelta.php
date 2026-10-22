<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SolicitudCuentaResuelta extends Notification
{
    use Queueable;

    public function __construct(public string $estado) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return ['titulo' => 'Solicitud '.$this->estado, 'mensaje' => 'Tu solicitud de acceso fue '.$this->estado.'.', 'url' => route('login')];
    }
}
