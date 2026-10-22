<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactoMiembro extends Model
{
    protected $table = 'contactos_miembro';

    protected $fillable = ['miembro_id', 'nombre', 'parentesco', 'telefono', 'tipo'];

    public function miembro(): BelongsTo
    {
        return $this->belongsTo(Miembro::class, 'miembro_id');
    }
}
