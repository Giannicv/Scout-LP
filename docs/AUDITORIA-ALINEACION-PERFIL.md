# Auditoría y alineación con el perfil del proyecto

## Resultado

Se revisaron el perfil académico, la especificación funcional adjunta, el código Laravel y el
esquema PostgreSQL original. Con autorización expresa se eliminaron tanto los datos como el código
legacy, y se reconstruyó la fase web sobre un modelo mínimo y coherente. No se realizó la aplicación
móvil porque el orden acordado es estabilizar primero la web.

## Criterios aplicados

El perfil aporta la visión del Distrito Scout La Paz. La especificación posterior resuelve las
ambigüedades: Lobatos, Exploradores, Pioneros y Rovers son ramas, no roles; una inscripción pertenece
a un evento, no al ingreso al movimiento; y “control de participantes” significa inscritos y cupos,
no asistencia.

Los roles implementados son:

- `dirigente_distrital`: administra el distrito y aprueba solicitudes de dirigentes de grupo.
- `dirigente_grupo`: trabaja dentro de un grupo y aprueba únicamente a sus miembros.
- `miembro`: consulta eventos de su rama, se inscribe y, si pertenece a Rovers, propone proyectos.

## Modelo resultante

```text
distritos 1 ── N grupos_scout
grupos_scout 1 ── N miembros
ramas 1 ── N miembros
roles 1 ── N usuarios
usuarios 0..1 ── 1 miembros
miembros 1 ── N contactos_miembro

eventos N ── N ramas                  evento_rama
eventos 1 ── N inscripciones_evento
miembros 1 ── N inscripciones_evento
usuarios 1 ── N eventos               creador/publicador
miembros 0..1 ── N eventos            proponente Rover

categorias_recursos 1 ── N recursos
usuarios 1 ── N notifications
usuarios 1 ── N auditoria
```

También se conservan noticias, categorías de noticias, contenido institucional y mensajes de
contacto público.

## Reglas verificadas en backend

1. El registro público admite miembro o dirigente de grupo, nunca dirigente distrital.
2. Toda cuenta pública nace pendiente y no puede iniciar sesión.
3. Un dirigente de grupo solo resuelve solicitudes de miembros de su propio grupo.
4. Solo el dirigente distrital resuelve solicitudes de dirigentes de grupo.
5. Los eventos pueden dirigirse a varias ramas y no se filtran por grupo.
6. La inscripción exige evento publicado, habilitado, dentro de plazo, con cupo y rama compatible.
7. La combinación evento/miembro es única y una cancelación puede reactivarse sin duplicarla.
8. Solo un miembro Rover puede proponer `proyecto_rover`; la propuesta nace como `propuesto`.
9. Dirigentes autorizados pueden aprobar o rechazar propuestas Rover.
10. Solo el rol distrital publica o elimina archivos de biblioteca; usuarios activos pueden leerlos.
11. Aprobaciones, rechazos, publicaciones e inscripciones críticas quedan auditadas.
12. PostgreSQL guarda rutas y metadata de biblioteca; los binarios viven en Laravel Storage.

## Elementos eliminados

Se retiraron tablas, modelos, controladores, servicios, formularios, rutas, vistas, semillas y pruebas
de: cuentas familiares/tutores como usuarios, unidades, admisión al movimiento, actividades separadas,
asistencia, autorizaciones digitales individuales, cuotas, fichas médicas, comunicados por unidad,
insignias y progresión. `contactos_miembro` reemplaza únicamente los datos mínimos de tutor o
emergencia necesarios.

## Privacidad y seguridad

El nuevo miembro conserva solo nombre, apellido, fecha de nacimiento, contacto opcional, grupo y
rama. Se retiraron CI, dirección, género y datos médicos. Las contraseñas usan el hasher de Laravel,
las sesiones inactivas o suspendidas se invalidan y la autorización sensible se valida del lado del
servidor. La recuperación usa el broker estándar de Laravel; en entorno local se muestra el enlace
de prueba sin afirmar que se envió correo.

## Observaciones del documento

- La portada indica 19 de septiembre de 2026 y la carta 19 de septiembre de 2025; debe corregirse
  la fecha documental cuando se prepare la versión final.
- La página 2 está vacía.
- La descripción de ramas como “tipos de usuario” debe actualizarse para reflejar el modelo real.
- La app móvil/API queda explícitamente como fase posterior.

## Verificación técnica

La migración monolítica anterior, cuyo `down()` fallaba deliberadamente, fue reemplazada por una
migración limpia con rollback en orden seguro de claves foráneas. Los seeders generan un distrito,
tres grupos, cuatro ramas, cuentas por rol, miembros, contactos, noticias, eventos de ejemplo y
categorías de biblioteca. Las pruebas cubren el esquema sin legacy, solicitudes, permisos,
eventos multirrama, inscripciones, cupos/plazos, proyectos Rover, biblioteca y páginas públicas.

Una revisión posterior añadió Policies explícitas, enums de dominio, auditoría centralizada,
bloqueos pesimistas para resolver solicitudes y propuestas, restricciones `CHECK` de PostgreSQL,
archivado de publicaciones y recursos, cancelación de eventos y cabeceras HTTP de seguridad. Las
pruebas automatizadas ahora usan SQLite en memoria y no dependen de la base de desarrollo.
