<?php

// Catálogo cerrado de formularios administrativos y de unidad.
return [
    'noticias' => [
        'modelo' => 'App\\Models\\Noticia',
        'titulo' => 'Historias para compartir.',
        'campos' => [
            'titulo' => [
                'etiqueta' => 'Título',
                'tipo' => 'text',
                'reglas' => ['required', 'string', 'max:200'],
            ],
            'categoria_id' => [
                'etiqueta' => 'Categoría',
                'tipo' => 'categorias',
                'reglas' => ['required', 'exists:categorias,id'],
            ],
            'publicado_en' => [
                'etiqueta' => 'Fecha',
                'tipo' => 'date',
                'reglas' => ['required', 'date'],
            ],
            'resumen' => [
                'etiqueta' => 'Extracto',
                'tipo' => 'textarea',
                'reglas' => ['required', 'string', 'max:300'],
            ],
            'contenido' => [
                'etiqueta' => 'Contenido',
                'tipo' => 'textarea',
                'reglas' => ['required', 'string'],
            ],
            'imagen_url' => [
                'etiqueta' => 'Imagen',
                'tipo' => 'text',
                'reglas' => ['nullable', 'string', 'max:255', 'regex:~^(https?://|img/)~'],
            ],
            'estado' => [
                'etiqueta' => 'Estado',
                'tipo' => 'publicacion',
                'reglas' => ['required', 'in:borrador,publicado'],
            ],
            'destacada' => [
                'etiqueta' => 'Noticia destacada',
                'tipo' => 'checkbox',
                'reglas' => ['boolean'],
            ],
        ],
    ],
    'eventos' => [
        'modelo' => 'App\\Models\\Evento',
        'titulo' => 'La próxima aventura.',
        'campos' => [
            'titulo' => [
                'etiqueta' => 'Título',
                'tipo' => 'text',
                'reglas' => ['required', 'string', 'max:200'],
            ],
            'fecha_inicio' => [
                'etiqueta' => 'Fecha y hora',
                'tipo' => 'datetime-local',
                'reglas' => ['required', 'date'],
            ],
            'lugar' => [
                'etiqueta' => 'Lugar',
                'tipo' => 'text',
                'reglas' => ['required', 'string', 'max:200'],
            ],
            'descripcion' => [
                'etiqueta' => 'Descripción',
                'tipo' => 'textarea',
                'reglas' => ['required', 'string'],
            ],
            'rama_id' => [
                'etiqueta' => 'Rama',
                'tipo' => 'ramas',
                'reglas' => ['nullable', 'exists:ramas,id'],
            ],
        ],
    ],
    'ramas' => [
        'modelo' => 'App\\Models\\Rama',
        'titulo' => 'Cuatro caminos para crecer.',
        'campos' => [
            'nombre' => [
                'etiqueta' => 'Nombre',
                'tipo' => 'text',
                'reglas' => ['required', 'string', 'max:60'],
            ],
            'lema' => [
                'etiqueta' => 'Frase distintiva',
                'tipo' => 'text',
                'reglas' => ['required', 'string'],
            ],
            'descripcion' => [
                'etiqueta' => 'Descripción',
                'tipo' => 'textarea',
                'reglas' => ['required', 'string'],
            ],
            'actividades_texto' => [
                'etiqueta' => 'Actividades (una por línea)',
                'tipo' => 'textarea',
                'reglas' => ['required', 'string'],
            ],
            'imagen_url' => [
                'etiqueta' => 'Imagen',
                'tipo' => 'text',
                'reglas' => ['nullable', 'string', 'max:255', 'regex:~^(https?://|img/)~'],
            ],
        ],
    ],
    'contenido' => [
        'modelo' => 'App\\Models\\ContenidoInstitucional',
        'titulo' => 'Nuestra voz institucional.',
        'campos' => [
            'titulo' => [
                'etiqueta' => 'Título',
                'tipo' => 'text',
                'reglas' => ['required', 'string', 'max:200'],
            ],
            'contenido' => [
                'etiqueta' => 'Contenido',
                'tipo' => 'textarea',
                'reglas' => ['required', 'string'],
            ],
        ],
    ],
    'mensajes' => [
        'modelo' => 'App\\Models\\MensajeContacto',
        'titulo' => 'Conversaciones que acercan.',
        'campos' => [
            'estado' => [
                'etiqueta' => 'Estado',
                'tipo' => 'mensaje',
                'reglas' => ['required', 'in:nuevo,leido,respondido'],
            ],
        ],
    ],
    'miembros' => [
        'modelo' => 'App\\Models\\Miembro',
        'titulo' => 'Nuestra comunidad.',
        'campos' => [
            'nombre' => [
                'etiqueta' => 'Nombre',
                'tipo' => 'text',
                'reglas' => ['required', 'string', 'max:100'],
            ],
            'apellido' => [
                'etiqueta' => 'Apellidos',
                'tipo' => 'text',
                'reglas' => ['required', 'string', 'max:100'],
            ],
            'fecha_nacimiento' => [
                'etiqueta' => 'Fecha de nacimiento',
                'tipo' => 'date',
                'reglas' => ['required', 'date', 'before:today'],
            ],
            'rama_id' => [
                'etiqueta' => 'Rama',
                'tipo' => 'ramas',
                'reglas' => ['required', 'exists:ramas,id'],
            ],
            'grupo_id' => [
                'etiqueta' => 'Grupo scout',
                'tipo' => 'grupos',
                'reglas' => ['required', 'exists:grupos_scout,id'],
            ],
            'unidad_id' => [
                'etiqueta' => 'Unidad',
                'tipo' => 'unidades',
                'reglas' => ['nullable', 'exists:unidades,id'],
            ],
            'activo' => [
                'etiqueta' => 'Activo',
                'tipo' => 'checkbox',
                'reglas' => ['boolean'],
            ],
        ],
    ],
    'usuarios' => [
        'modelo' => 'App\\Models\\User',
        'titulo' => 'Usuarios y roles.',
        'campos' => [
            'nombre' => [
                'etiqueta' => 'Nombre',
                'tipo' => 'text',
                'reglas' => ['required', 'string', 'max:100'],
            ],
            'apellido' => [
                'etiqueta' => 'Apellidos',
                'tipo' => 'text',
                'reglas' => ['required', 'string', 'max:100'],
            ],
            'email' => [
                'etiqueta' => 'Correo',
                'tipo' => 'email',
                'reglas' => ['required', 'email', 'max:150'],
            ],
            'telefono' => [
                'etiqueta' => 'Teléfono',
                'tipo' => 'text',
                'reglas' => ['nullable', 'string', 'max:30'],
            ],
            'rol_id' => [
                'etiqueta' => 'Rol',
                'tipo' => 'roles',
                'reglas' => ['required', 'exists:roles,id'],
            ],
            'departamento' => [
                'etiqueta' => 'Departamento',
                'tipo' => 'departamentos',
                'reglas' => ['nullable', 'string', 'max:50'],
            ],
            'miembro_id' => [
                'etiqueta' => 'Ficha de miembro',
                'tipo' => 'miembros',
                'reglas' => ['nullable', 'exists:miembros,id'],
            ],
            'password' => [
                'etiqueta' => 'Contraseña',
                'tipo' => 'password',
                'reglas' => ['nullable', 'string', 'min:8', 'regex:/[A-Za-z]/', 'regex:/[0-9]/'],
            ],
            'activo' => [
                'etiqueta' => 'Activo',
                'tipo' => 'checkbox',
                'reglas' => ['boolean'],
            ],
        ],
    ],
    'actividades' => [
        'modelo' => 'App\\Models\\Actividad',
        'titulo' => 'La próxima aventura de tu unidad.',
        'campos' => [
            'unidad_id' => [
                'etiqueta' => 'Unidad',
                'tipo' => 'unidades',
                'reglas' => ['required', 'exists:unidades,id'],
            ],
            'titulo' => [
                'etiqueta' => 'Título',
                'tipo' => 'text',
                'reglas' => ['required', 'string', 'max:200'],
            ],
            'fecha' => [
                'etiqueta' => 'Fecha',
                'tipo' => 'date',
                'reglas' => ['required', 'date'],
            ],
            'hora' => [
                'etiqueta' => 'Hora',
                'tipo' => 'time',
                'reglas' => ['required', 'date_format:H:i'],
            ],
            'lugar' => [
                'etiqueta' => 'Lugar',
                'tipo' => 'text',
                'reglas' => ['required', 'string', 'max:200'],
            ],
            'descripcion' => [
                'etiqueta' => 'Descripción',
                'tipo' => 'textarea',
                'reglas' => ['required', 'string'],
            ],
            'requiere_autorizacion' => [
                'etiqueta' => 'Requiere autorización familiar',
                'tipo' => 'checkbox',
                'reglas' => ['boolean'],
            ],
        ],
    ],
    'comunicados' => [
        'modelo' => 'App\\Models\\Comunicado',
        'titulo' => 'Lo que necesitamos compartir.',
        'campos' => [
            'unidad_id' => [
                'etiqueta' => 'Unidad',
                'tipo' => 'unidades',
                'reglas' => ['required', 'exists:unidades,id'],
            ],
            'titulo' => [
                'etiqueta' => 'Título',
                'tipo' => 'text',
                'reglas' => ['required', 'string', 'max:200'],
            ],
            'contenido' => [
                'etiqueta' => 'Contenido',
                'tipo' => 'textarea',
                'reglas' => ['required', 'string'],
            ],
            'destino' => [
                'etiqueta' => 'Destinatarios',
                'tipo' => 'destinos',
                'reglas' => ['required', 'in:ambos,familia,miembro'],
            ],
        ],
    ],
];
