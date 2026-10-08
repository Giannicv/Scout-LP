# Cómo leer y modificar este proyecto

El código se organiza con cuatro espacios de indentación, bloques separados y nombres
que indican qué representa cada dato. Las vistas Blade no están minificadas. Los estilos
mantienen una declaración por línea y el PHP se comprueba con Laravel Pint.

## Ejemplo: cambiar una noticia

1. `routes/web.php` declara las rutas con nombres como `noticias.show`.
2. `PublicoController::noticia()` recibe una `Noticia` mediante route model binding.
3. `app/Models/Noticia.php` indica la tabla `noticias` y sus relaciones.
4. `resources/views/noticia.blade.php` muestra título, imagen y párrafos.
5. `{{ $noticia->titulo }}` escapa el texto antes de insertarlo en HTML.

Para editar la noticia desde administración, `RecursoRequest` valida sus campos,
`RecursoController` recibe la petición y `GestionRecursos` guarda los cambios en una
transacción. Los formularios reutilizan `components/campo.blade.php`.

## Ejemplo de nombres descriptivos

```php
$inscripcion = Inscripcion::where('departamento', $usuario->departamento)
    ->lockForUpdate()
    ->findOrFail($datos['inscripcion_id']);
```

`$inscripcion` es la solicitud que se está revisando. `$usuario` es la persona conectada.
`$datos` contiene únicamente lo que pasó la validación. `lockForUpdate()` impide que dos
coordinadores resuelvan la misma solicitud simultáneamente dentro de la transacción.

## Archivos que puedes estudiar primero

| Quiero cambiar… | Archivo |
|---|---|
| Inicio y sus secciones | `resources/views/inicio.blade.php` |
| Menú público | `resources/views/partials/navegacion.blade.php` |
| Colores | `public/css/variables.css` |
| Menú de cada rol | `config/scouts.php` |
| Etiqueta o campo administrativo | `config/recursos.php` |
| Reglas de inscripción | `app/Http/Requests/OperacionRequest.php` |
| Qué datos puede ver cada rol | `app/Services/AlcanceScout.php` |
| Proceso de aprobación | `app/Http/Controllers/OperacionController.php` |
| Borrado de usuarios | `app/Services/GestionRecursos.php` |
| Menú móvil, calendario y pasos | `public/js/interacciones.js` |

Los nombres obligatorios del framework, como `$request`, `$fillable`, `casts()` y
`getAuthPassword()`, se conservan para que Laravel los reconozca. Las columnas de PostgreSQL
tampoco se renombraron. El código de terceros en `vendor/` permanece intacto.
