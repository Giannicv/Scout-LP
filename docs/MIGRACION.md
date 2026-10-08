# Informe de migración

Origen conservado: `C:\Users\rufin\OneDrive\Documentos\Proyeto - Scaut`.
Destino: `C:\Users\rufin\OneDrive\Documentos\Scaut - Proyecto\scouts-bolivia-laravel`.

## Mapeo final por archivo

Las páginas dinámicas se convierten a vistas Blade: no se copian HTML y API antiguos dentro
de `public/`. Los modelos y servicios reemplazan el almacenamiento JavaScript. Las tablas
y los datos existentes se conservan en PostgreSQL.

| Archivo de origen | Archivo o responsabilidad de destino |
|---|---|
| `acceso-denegado.html` | `resources/views/errors/403.blade.php` |
| `admin/contenido.html` | `resources/views/admin/contenido.blade.php` |
| `admin/dashboard.html` | `resources/views/admin/dashboard.blade.php` |
| `admin/eventos.html` | `resources/views/admin/eventos.blade.php` |
| `admin/login.html` | `Ruta de login única /login` |
| `admin/mensajes.html` | `resources/views/admin/mensajes.blade.php` |
| `admin/miembros.html` | `resources/views/admin/miembros.blade.php` |
| `admin/noticias.html` | `resources/views/admin/noticias.blade.php` |
| `admin/ramas.html` | `resources/views/admin/ramas.blade.php` |
| `admin/usuarios.html` | `resources/views/admin/usuarios.blade.php` |
| `api/.env` | `No copiado: credenciales/configuración privada` |
| `api/.env.example` | `No copiado: credenciales/configuración privada` |
| `api/.htaccess` | `No copiado: protección del servidor anterior; Laravel sirve solo public/` |
| `api/auth.php` | `app/Http/Controllers/, app/Services/, app/Models/ y middleware` |
| `api/auth_helpers.php` | `app/Http/Controllers/, app/Services/, app/Models/ y middleware` |
| `api/config.php` | `app/Http/Controllers/, app/Services/, app/Models/ y middleware` |
| `api/data.php` | `app/Http/Controllers/, app/Services/, app/Models/ y middleware` |
| `api/db.php` | `app/Http/Controllers/, app/Services/, app/Models/ y middleware` |
| `api/index.php` | `app/Http/Controllers/, app/Services/, app/Models/ y middleware` |
| `api/operations.php` | `app/Http/Controllers/, app/Services/, app/Models/ y middleware` |
| `api/salud.php` | `app/Http/Controllers/, app/Services/, app/Models/ y middleware` |
| `coordinador/dashboard.html` | `resources/views/coordinador/dashboard.blade.php` |
| `coordinador/dirigentes.html` | `resources/views/coordinador/dirigentes.blade.php` |
| `coordinador/inscripciones.html` | `resources/views/coordinador/inscripciones.blade.php` |
| `coordinador/reportes.html` | `resources/views/coordinador/reportes.blade.php` |
| `coordinador/unidades.html` | `resources/views/coordinador/unidades.blade.php` |
| `css/admin.css` | `public/css/admin.css (formateado)` |
| `css/base.css` | `public/css/base.css (formateado)` |
| `css/componentes.css` | `public/css/componentes.css (formateado)` |
| `css/panel.css` | `public/css/panel.css (formateado)` |
| `css/publico.css` | `public/css/publico.css (formateado)` |
| `css/variables.css` | `public/css/variables.css (formateado)` |
| `database/.htaccess` | `database/migrations/2026_10_06_000000_adoptar_esquema_scoutbo.php` |
| `database/migrate.php` | `database/migrations/2026_10_06_000000_adoptar_esquema_scoutbo.php` |
| `database/migrations/002_roles_y_sprint2.sql` | `database/migrations/2026_10_06_000000_adoptar_esquema_scoutbo.php` |
| `database/scoutbo_schema.sql` | `database/migrations/2026_10_06_000000_adoptar_esquema_scoutbo.php` |
| `database/seed-data.json` | `database/seeders/datos-demo.json (sin hashes antiguos)` |
| `database/seed.php` | `database/seeders/*Seeder.php` |
| `database/verificacion.sql` | `database/verificacion.sql` |
| `dirigente/actividades.html` | `resources/views/dirigente/actividades.blade.php` |
| `dirigente/asistencia.html` | `resources/views/dirigente/asistencia.blade.php` |
| `dirigente/comunicados.html` | `resources/views/dirigente/comunicados.blade.php` |
| `dirigente/dashboard.html` | `resources/views/dirigente/dashboard.blade.php` |
| `dirigente/mi-unidad.html` | `resources/views/dirigente/mi-unidad.blade.php` |
| `dirigente/progresiones.html` | `resources/views/dirigente/progresiones.blade.php` |
| `familia/autorizaciones.html` | `resources/views/familia/autorizaciones.blade.php` |
| `familia/comunicados.html` | `resources/views/familia/comunicados.blade.php` |
| `familia/dashboard.html` | `resources/views/familia/dashboard.blade.php` |
| `familia/hijos.html` | `resources/views/familia/hijos.blade.php` |
| `familia/inscripcion.html` | `resources/views/familia/inscripcion.blade.php` |
| `familia/pagos.html` | `resources/views/familia/pagos.blade.php` |
| `img/favicon.svg` | `public/img/favicon.svg` |
| `img/LEEME.txt` | `public/img/LEEME.txt` |
| `index.html` | `resources/views/inicio.blade.php` |
| `js/acceso.js` | `Sustituido por Blade, controladores y public/js/interacciones.js` |
| `js/admin.js` | `Sustituido por Blade, controladores y public/js/interacciones.js` |
| `js/api.js` | `Sustituido por Blade, controladores y public/js/interacciones.js` |
| `js/auth.js` | `Sustituido por Blade, controladores y public/js/interacciones.js` |
| `js/componentes.js` | `Sustituido por Blade, controladores y public/js/interacciones.js` |
| `js/coordinador.js` | `Sustituido por Blade, controladores y public/js/interacciones.js` |
| `js/dirigente.js` | `Sustituido por Blade, controladores y public/js/interacciones.js` |
| `js/familia.js` | `Sustituido por Blade, controladores y public/js/interacciones.js` |
| `js/guard.js` | `Sustituido por Blade, controladores y public/js/interacciones.js` |
| `js/inicio-api.js` | `Sustituido por Blade, controladores y public/js/interacciones.js` |
| `js/login-anterior.js` | `No migrado: código anterior sin uso` |
| `js/miembro.js` | `Sustituido por Blade, controladores y public/js/interacciones.js` |
| `js/navbar-sesion.js` | `Sustituido por Blade, controladores y public/js/interacciones.js` |
| `js/panel.js` | `Sustituido por Blade, controladores y public/js/interacciones.js` |
| `js/publico.js` | `Sustituido por Blade, controladores y public/js/interacciones.js` |
| `js/roles.js` | `Sustituido por Blade, controladores y public/js/interacciones.js` |
| `js/storage.js` | `Sustituido por Blade, controladores y public/js/interacciones.js` |
| `js/usuarios.js` | `Sustituido por Blade, controladores y public/js/interacciones.js` |
| `login.html` | `resources/views/auth/login.blade.php` |
| `mensaje-enviado.html` | `resources/views/mensaje-enviado.blade.php` |
| `miembro/calendario.html` | `resources/views/miembro/calendario.blade.php` |
| `miembro/dashboard.html` | `resources/views/miembro/dashboard.blade.php` |
| `miembro/mi-unidad.html` | `resources/views/miembro/mi-unidad.blade.php` |
| `miembro/perfil.html` | `resources/views/miembro/perfil.blade.php` |
| `miembro/progresion.html` | `resources/views/miembro/progresion.blade.php` |
| `nosotros.html` | `resources/views/nosotros.blade.php` |
| `noticia.html` | `resources/views/noticia.blade.php` |
| `noticias.html` | `resources/views/noticias.blade.php` |
| `ramas.html` | `resources/views/ramas.blade.php` |
| `recuperar.html` | `resources/views/auth/recuperar.blade.php` |
| `registro.html` | `resources/views/auth/registro.blade.php` |
| `router.php` | `Reemplazado por public/index.php y routes/web.php` |
| `verificar-postgres.cjs` | `Reemplazado por tests/Feature/*ScoutTest.php` |
| `verificar-seguridad.cjs` | `Reemplazado por tests/Feature/*ScoutTest.php` |
| `verificar-sprint2.cjs` | `Reemplazado por tests/Feature/*ScoutTest.php` |
| `verificar.cjs` | `Reemplazado por tests/Feature/*ScoutTest.php` |

## Renombrados representativos

| Antes | Después | Representa |
|---|---|---|
| `$u` | `$usuario` | Persona conectada |
| `$s` | `$inscripcion` | Solicitud de ingreso |
| `$m` | `$miembro` | Ficha scout |
| `$a` | `$actividad` | Actividad de una unidad |
| `$v` | `$datos` | Datos validados |
| `$r` | `$requisito` | Requisito de progresión |
| `$g` | `$grupo` | Grupo scout |
| `$t` | `$tutor` | Tutor de una familia |
| `e` | `eventoClic` / `eventoEnvio` | Evento del navegador |
| `btn` / `b` | `botonSiguiente` / `botonMenu` | Botón específico |
| `el` | `imagen` / `indicador` | Elemento de la interfaz |
| `res` | `respuesta` | Respuesta HTTP |
| `d` | `datosUsuario` | Datos de la persona |
| `i` | `posicionEvento` | Posición al generar fechas demo |
| `render` | `mostrarPaso` / `cargarNoticias` | Función concreta de pantalla |
| `ownUnits` | `AlcanceScout::unidades` | Consulta autorizada |
| `scopeMembers` | `AlcanceScout::miembros` | Miembros visibles por rol |

No se renombraron las clases CSS descriptivas del diseño original. Se añadieron selectores
descriptivos, como `data-inscripcion`, `data-paso-siguiente` y `data-rama-sugerida`.

## Archivos excluidos

- `.env`, sesiones, registros y respaldos del origen: secretos o datos operativos privados.
- `router.php` y `.htaccess` de la API: sustituidos por el front controller de Laravel.
- `js/login-anterior.js`: código muerto anterior al acceso unificado.
- Scripts `verificar*.cjs`: pruebas ligadas al prototipo; reemplazadas por pruebas PHPUnit.
- API y scripts de almacenamiento originales: convertidos a clases, no expuestos en public/.
- Archivos iniciales de Vite: eliminados del destino porque esta aplicación no compila assets.
- `vendor/`: no se copia ni modifica. No se ejecutó composer update.

## Verificación y límites

Se comprueba la conexión PostgreSQL, la lectura de modelos, rutas, compilación Blade,
formato Pint y pruebas transaccionales. Se revisan los cinco roles en Edge a 360 px,
el filtrado de noticias, el código 403 y volver atrás después de cerrar sesión.
Las capturas se guardan en `storage/app/verificacion/`.

La migración sobre scoutbo no se ejecutó: continúa pendiente la confirmación explícita
solicitada en el documento. Esto no bloquea el funcionamiento sobre sus tablas existentes.
La recreación de una base vacía no se probó en scoutbo para no destruir datos.
Recuperación por correo y comprobantes siguen simulados, según el alcance original.
Los detalles visuales y diferencias de formularios se explican en el README.
