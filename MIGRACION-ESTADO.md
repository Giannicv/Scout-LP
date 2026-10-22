# Estado de la alineación

La base de desarrollo fue reconstruida con autorización del propietario. Los datos y tablas legacy
fueron eliminados; no se conservaron copias `legacy_` porque no eran necesarios.

La aplicación web ya usa el modelo objetivo del perfil: tres roles, cuatro ramas, grupos de La Paz,
solicitudes de acceso, eventos multirrama, inscripciones, propuestas Rover, contactos, biblioteca,
notificaciones y auditoría. La migración es reversible y las semillas crean datos demo coherentes.

La segunda revisión incorporó navegación móvil, componentes visuales consistentes, filtros y
paginación; Policies, enums, auditoría central, restricciones PostgreSQL, archivado, cancelación de
eventos, validación de edad por rama, cabeceras de seguridad y pruebas aisladas en memoria.

La aplicación móvil y una API se mantienen como fase posterior.
