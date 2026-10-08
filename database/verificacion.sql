-- Ejecutar en Query Tool de pgAdmin con la base scoutbo seleccionada.
-- Solo lectura. Reemplaza el correo de ejemplo por el de tu prueba.
SELECT current_database(), current_user;
-- 1. Familia y tutor creados juntos.
SELECT u.id,u.email,r.nombre rol,t.id tutor_id,t.nombre
FROM usuarios u JOIN roles r ON r.id=u.rol_id
LEFT JOIN tutores t ON t.id=u.tutor_id ORDER BY u.id DESC;
-- 2 y 3. Inscripción pendiente/aprobada, ficha y unidad.
SELECT i.id,i.nombre_aspirante,i.estado,i.miembro_id,m.unidad_id
FROM inscripciones i LEFT JOIN miembros m ON m.id=i.miembro_id ORDER BY i.id DESC;
-- 4. Noticias publicadas.
SELECT id,titulo,estado,autor_id FROM noticias ORDER BY id DESC;
-- 5. Contacto.
SELECT id,nombre,email,asunto,estado FROM mensajes_contacto ORDER BY id DESC;
-- 6. Asistencia.
SELECT actividad_id,miembro_id,fecha,estado FROM asistencias ORDER BY id DESC;
-- 7. Firmas.
SELECT actividad_id,miembro_id,tutor_id,estado,firmada_en FROM autorizaciones ORDER BY id DESC;
-- 8. Roles actuales.
SELECT u.id,u.email,r.nombre FROM usuarios u JOIN roles r ON r.id=u.rol_id ORDER BY u.id DESC;
-- 9. Anota los IDs antes de borrar. El correo debe desaparecer.
SELECT id FROM usuarios WHERE email='prueba@example.com';
SELECT id,nombre,tutor_id,unidad_id FROM miembros ORDER BY id DESC;
SELECT id,nombre,dirigente_id FROM unidades ORDER BY id;
SELECT id,titulo,autor_id FROM noticias WHERE autor_id IS NULL;
SELECT id,titulo,autor_id FROM eventos WHERE autor_id IS NULL;
SELECT id,titulo,autor_id FROM comunicados WHERE autor_id IS NULL;
SELECT * FROM auditoria ORDER BY id DESC LIMIT 20;
-- Integridad: todas estas cuentas deben ser cero.
SELECT 'asistencias huerfanas' caso,count(*) FROM asistencias a LEFT JOIN miembros m ON m.id=a.miembro_id WHERE m.id IS NULL
UNION ALL SELECT 'cuotas huerfanas',count(*) FROM cuotas c LEFT JOIN miembros m ON m.id=c.miembro_id WHERE m.id IS NULL
UNION ALL SELECT 'autorizaciones huerfanas',count(*) FROM autorizaciones a LEFT JOIN miembros m ON m.id=a.miembro_id WHERE m.id IS NULL;
-- 10. El último administrador sigue activo después del rechazo.
SELECT u.id,u.email,u.activo FROM usuarios u JOIN roles r ON r.id=u.rol_id WHERE r.nombre='administrador';
-- 11. El control 403 se comprueba en el navegador/API; comparar filas antes/después.
SELECT count(*) total_usuarios FROM usuarios;
