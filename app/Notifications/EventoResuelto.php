<?php

namespace App\Notifications;

use App\Models\Evento;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EventoResuelto extends Notification
{
    use Queueable;

    public function __construct(public Evento $evento) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return ['titulo' => 'Proyecto Rover '.($this->evento->estado === 'publicado' ? 'aprobado' : 'rechazado'),
            'mensaje' => $this->evento->titulo.' cambió a estado '.$this->evento->estado.'.',
            'url' => route('eventos.index')];
    }
}
