# Scout Distrito La Paz · Laravel 12

Primera fase web de la plataforma de gestión de eventos descrita en el perfil del proyecto.
Usa PHP 8.2+, Laravel 12, Blade y PostgreSQL. No requiere Node, Vite ni una API móvil.

## Instalación local

En XAMPP habilita `pdo_pgsql`, `pgsql` e `intl` en `C:\xampp\php\php.ini`. Crea en
pgAdmin una base vacía llamada `scoutbo` cuyo propietario sea `postgres`.

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
```

Completa `DB_PASSWORD` en `.env` con la contraseña del usuario PostgreSQL (no la contraseña
maestra de pgAdmin si son distintas). Para reconstruir la base de desarrollo y cargar ejemplos:

```powershell
php artisan migrate:fresh --seed
php artisan serve
```

Abre http://127.0.0.1:8000. El servidor web debe publicar la carpeta `public/`.

## Cuentas de demostración

Todas usan la contraseña `Scout2026!`.

| Rol | Correo |
|---|---|
| Dirigente distrital | distrito@scoutlapaz.bo |
| Dirigente grupo Illimani | dirigente1@scoutlapaz.bo |
| Dirigente grupo Mururata | dirigente2@scoutlapaz.bo |
| Dirigente grupo Sajama | dirigente3@scoutlapaz.bo |
| Miembro Lobatos | miembro1@scoutlapaz.bo |
| Miembro Exploradores | miembro2@scoutlapaz.bo |
| Miembro Pioneros | miembro3@scoutlapaz.bo |
| Miembro Rovers | miembro4@scoutlapaz.bo |

## Alcance implementado

- Roles: dirigente distrital, dirigente de grupo y miembro.
- Solicitudes de cuenta con aprobación distrital o por el dirigente del mismo grupo.
- Ramas oficiales: Lobatos, Exploradores, Pioneros y Rovers.
- Eventos unificados (`actividad`, `evento`, `proyecto_rover`) para una o varias ramas.
- Propuestas de Proyecto Rover y aprobación/rechazo.
- Inscripción única con validación de estado, ventana, cupo y rama.
- Contactos de tutor/emergencia sin cuentas familiares.
- Historias/noticias administrables por el dirigente distrital, con selección de destacadas para la portada.
- Biblioteca digital en Laravel Storage.
- Notificaciones internas y auditoría de acciones críticas.
- Interfaz responsive con temas claro y oscuro, detección del sistema y preferencia persistente.

No forman parte de esta fase: aplicación móvil/API, cuentas familiares, unidades, asistencia,
cuotas, fichas médicas, insignias/progresión, admisión al movimiento ni autorizaciones digitales
individuales. El evento solo registra si requiere una autorización física y sus instrucciones.

## Verificación

```powershell
php artisan route:list
php artisan view:cache
php artisan view:clear
php vendor/bin/pint --test
php artisan test
```

`/salud` comprueba que la aplicación responde sin revelar el nombre ni otros detalles de la base.
La migración base tiene `up()` y `down()` funcionales. `migrate:fresh` elimina todos los datos;
úsalo únicamente para desarrollo o tras un respaldo explícito.

Las pruebas usan SQLite en memoria, se migran y se siembran de forma aislada en cada ejecución;
no escriben en `scoutbo`. PostgreSQL mantiene restricciones adicionales para estados, fechas,
costos y cupos mediante una migración reversible.

## Antes de publicar

- Cambia `APP_ENV=production` y `APP_DEBUG=false`.
- Usa HTTPS y establece `SESSION_SECURE_COOKIE=true`.
- Configura un correo real para recuperación de contraseña.
- Ejecuta `php artisan config:cache`, `route:cache` y `view:cache` durante el despliegue.
- Respalda PostgreSQL y `storage/app/private`, donde vive la biblioteca digital.

Consulta [docs/AUDITORIA-ALINEACION-PERFIL.md](docs/AUDITORIA-ALINEACION-PERFIL.md) para el
contraste entre el perfil, la especificación y la implementación.
