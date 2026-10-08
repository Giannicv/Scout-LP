SELECT 1 FROM pg_type WHERE typname = 'estado_inscripcion';

CREATE TYPE "estado_inscripcion" AS ENUM ('pendiente', 'aprobada', 'rechazada', 'cancelada');

SELECT 1 FROM pg_type WHERE typname = 'estado_mensaje';

CREATE TYPE "estado_mensaje" AS ENUM ('nuevo', 'leido', 'respondido');

SELECT 1 FROM pg_type WHERE typname = 'estado_publicacion';

CREATE TYPE "estado_publicacion" AS ENUM ('borrador', 'publicado', 'archivado');

SELECT 1 FROM pg_type WHERE typname = 'genero_tipo';

CREATE TYPE "genero_tipo" AS ENUM ('masculino', 'femenino', 'otro');

select exists (select 1 from pg_class c, pg_namespace n where n.nspname = current_schema() and c.relname = 'actividades' and c.relkind in ('r', 'p') and n.oid = c.relnamespace);

create table "actividades" ("id" serial not null primary key, "unidad_id" integer not null, "titulo" varchar(200) not null, "fecha" date not null, "hora" time(0) without time zone not null default '09:00:00'::time without time zone, "lugar" varchar(200) not null, "descripcion" text not null, "requiere_autorizacion" boolean not null default false);

select exists (select 1 from pg_class c, pg_namespace n where n.nspname = current_schema() and c.relname = 'asistencias' and c.relkind in ('r', 'p') and n.oid = c.relnamespace);

create table "asistencias" ("id" serial not null primary key, "actividad_id" integer not null, "miembro_id" integer not null, "fecha" date not null, "estado" text not null, "observacion" text null default ''::text);

alter table "asistencias" add constraint "asistencias_actividad_id_miembro_id_key" unique ("actividad_id", "miembro_id");

create index "idx_asistencias_miembro" on "asistencias" ("miembro_id");

select exists (select 1 from pg_class c, pg_namespace n where n.nspname = current_schema() and c.relname = 'auditoria' and c.relkind in ('r', 'p') and n.oid = c.relnamespace);

create table "auditoria" ("id" bigserial not null primary key, "actor_id" integer null, "accion" text not null, "detalle" jsonb not null default '{}'::jsonb, "creado_en" timestamp(0) with time zone not null default now());

select exists (select 1 from pg_class c, pg_namespace n where n.nspname = current_schema() and c.relname = 'autorizaciones' and c.relkind in ('r', 'p') and n.oid = c.relnamespace);

create table "autorizaciones" ("id" serial not null primary key, "actividad_id" integer not null, "miembro_id" integer not null, "tutor_id" integer not null, "estado" text not null default 'pendiente'::text, "firmada_en" timestamp(0) with time zone null);

alter table "autorizaciones" add constraint "autorizaciones_actividad_id_miembro_id_tutor_id_key" unique ("actividad_id", "miembro_id", "tutor_id");

create index "idx_autorizaciones_tutor" on "autorizaciones" ("tutor_id");

select exists (select 1 from pg_class c, pg_namespace n where n.nspname = current_schema() and c.relname = 'categorias' and c.relkind in ('r', 'p') and n.oid = c.relnamespace);

create table "categorias" ("id" smallserial not null primary key, "nombre" varchar(60) not null, "slug" varchar(60) not null);

alter table "categorias" add constraint "categorias_nombre_key" unique ("nombre");

alter table "categorias" add constraint "categorias_slug_key" unique ("slug");

select exists (select 1 from pg_class c, pg_namespace n where n.nspname = current_schema() and c.relname = 'comunicado_lecturas' and c.relkind in ('r', 'p') and n.oid = c.relnamespace);

create table "comunicado_lecturas" ("comunicado_id" integer not null, "usuario_id" integer not null, "leido_en" timestamp(0) with time zone not null default now());

alter table "comunicado_lecturas" add primary key ("comunicado_id", "usuario_id");

select exists (select 1 from pg_class c, pg_namespace n where n.nspname = current_schema() and c.relname = 'comunicados' and c.relkind in ('r', 'p') and n.oid = c.relnamespace);

create table "comunicados" ("id" serial not null primary key, "autor_id" integer null, "unidad_id" integer null, "departamento" varchar(50) null, "titulo" text not null, "contenido" text not null, "destino" text not null default 'ambos'::text, "creado_en" timestamp(0) with time zone not null default now());

select exists (select 1 from pg_class c, pg_namespace n where n.nspname = current_schema() and c.relname = 'contenido_institucional' and c.relkind in ('r', 'p') and n.oid = c.relnamespace);

create table "contenido_institucional" ("id" serial not null primary key, "clave" varchar(60) not null, "titulo" varchar(150) not null, "contenido" text not null, "actualizado_por" integer null, "actualizado_en" timestamp(0) with time zone not null default now());

alter table "contenido_institucional" add constraint "contenido_institucional_clave_key" unique ("clave");

select exists (select 1 from pg_class c, pg_namespace n where n.nspname = current_schema() and c.relname = 'cuotas' and c.relkind in ('r', 'p') and n.oid = c.relnamespace);

create table "cuotas" ("id" serial not null primary key, "miembro_id" integer not null, "concepto" text not null, "monto" decimal(12, 2) not null, "estado" text not null default 'pendiente'::text, "vencimiento" date not null, "comprobante_nombre" text null, "registrado_en" timestamp(0) with time zone null);

create index "idx_cuotas_miembro" on "cuotas" ("miembro_id");

select exists (select 1 from pg_class c, pg_namespace n where n.nspname = current_schema() and c.relname = 'distritos' and c.relkind in ('r', 'p') and n.oid = c.relnamespace);

create table "distritos" ("id" serial not null primary key, "nombre" varchar(100) not null, "departamento" varchar(50) not null);

alter table "distritos" add constraint "distritos_nombre_departamento_key" unique ("nombre", "departamento");

select exists (select 1 from pg_class c, pg_namespace n where n.nspname = current_schema() and c.relname = 'eventos' and c.relkind in ('r', 'p') and n.oid = c.relnamespace);

create table "eventos" ("id" serial not null primary key, "autor_id" integer null, "rama_id" smallint null, "titulo" varchar(200) not null, "descripcion" text null, "lugar" varchar(200) null, "fecha_inicio" timestamp(0) with time zone not null, "fecha_fin" timestamp(0) with time zone null, "imagen_url" varchar(255) null, "estado" estado_publicacion not null default 'borrador'::estado_publicacion, "creado_en" timestamp(0) with time zone not null default now());

create index "idx_eventos_fecha" on "eventos" ("fecha_inicio");

select exists (select 1 from pg_class c, pg_namespace n where n.nspname = current_schema() and c.relname = 'fichas_medicas' and c.relkind in ('r', 'p') and n.oid = c.relnamespace);

create table "fichas_medicas" ("miembro_id" integer not null, "alergias" text null default ''::text, "contacto_emergencia" text null default ''::text, "telefono_emergencia" varchar(30) null default ''::character varying, "observaciones" text null default ''::text);

alter table "fichas_medicas" add primary key ("miembro_id");

select exists (select 1 from pg_class c, pg_namespace n where n.nspname = current_schema() and c.relname = 'grupos_scout' and c.relkind in ('r', 'p') and n.oid = c.relnamespace);

create table "grupos_scout" ("id" serial not null primary key, "distrito_id" integer not null, "nombre" varchar(120) not null, "direccion" varchar(200) null, "telefono" varchar(30) null, "email" varchar(150) null, "activo" boolean not null default true);

alter table "grupos_scout" add constraint "grupos_scout_distrito_id_nombre_key" unique ("distrito_id", "nombre");

select exists (select 1 from pg_class c, pg_namespace n where n.nspname = current_schema() and c.relname = 'inscripciones' and c.relkind in ('r', 'p') and n.oid = c.relnamespace);

create table "inscripciones" ("id" serial not null primary key, "rama_id" smallint not null, "grupo_id" integer null, "miembro_id" integer null, "nombre_aspirante" varchar(100) not null, "apellido_aspirante" varchar(100) not null, "fecha_nacimiento" date not null, "nombre_tutor" varchar(150) not null, "telefono_tutor" varchar(30) not null, "email_tutor" varchar(150) not null, "observaciones" text null, "estado" estado_inscripcion not null default 'pendiente'::estado_inscripcion, "revisado_por" integer null, "creado_en" timestamp(0) with time zone not null default now(), "revisado_en" timestamp(0) with time zone null, "tutor_id" integer null, "departamento" varchar(50) null, "unidad_id" integer null, "motivo_rechazo" text null, "ficha_medica" jsonb not null default '{}'::jsonb);

create index "idx_inscripciones_estado" on "inscripciones" ("estado");

select exists (select 1 from pg_class c, pg_namespace n where n.nspname = current_schema() and c.relname = 'insignias' and c.relkind in ('r', 'p') and n.oid = c.relnamespace);

create table "insignias" ("id" serial not null primary key, "requisito_id" integer null, "rama_id" smallint null, "nombre" text not null, "etapa" text not null);

alter table "insignias" add constraint "insignias_requisito_id_key" unique ("requisito_id");

select exists (select 1 from pg_class c, pg_namespace n where n.nspname = current_schema() and c.relname = 'intentos_login' and c.relkind in ('r', 'p') and n.oid = c.relnamespace);

create table "intentos_login" ("clave" text not null, "usuario_id" integer null, "intentos" integer not null default 0, "bloqueado_hasta" timestamp(0) with time zone null);

alter table "intentos_login" add primary key ("clave");

select exists (select 1 from pg_class c, pg_namespace n where n.nspname = current_schema() and c.relname = 'mensajes_contacto' and c.relkind in ('r', 'p') and n.oid = c.relnamespace);

create table "mensajes_contacto" ("id" serial not null primary key, "nombre" varchar(120) not null, "email" varchar(150) not null, "telefono" varchar(30) null, "asunto" varchar(150) not null, "mensaje" text not null, "estado" estado_mensaje not null default 'nuevo'::estado_mensaje, "creado_en" timestamp(0) with time zone not null default now(), "rama_id" smallint null);

select exists (select 1 from pg_class c, pg_namespace n where n.nspname = current_schema() and c.relname = 'miembro_insignias' and c.relkind in ('r', 'p') and n.oid = c.relnamespace);

create table "miembro_insignias" ("miembro_id" integer not null, "insignia_id" integer not null, "fecha" date not null, "observacion" text null default ''::text, "registrado_por" integer null);

alter table "miembro_insignias" add primary key ("miembro_id", "insignia_id");

select exists (select 1 from pg_class c, pg_namespace n where n.nspname = current_schema() and c.relname = 'miembro_requisitos' and c.relkind in ('r', 'p') and n.oid = c.relnamespace);

create table "miembro_requisitos" ("id" serial not null primary key, "miembro_id" integer not null, "requisito_id" integer not null, "fecha" date not null, "observacion" text null default ''::text, "registrado_por" integer null);

alter table "miembro_requisitos" add constraint "miembro_requisitos_miembro_id_requisito_id_key" unique ("miembro_id", "requisito_id");

select exists (select 1 from pg_class c, pg_namespace n where n.nspname = current_schema() and c.relname = 'miembros' and c.relkind in ('r', 'p') and n.oid = c.relnamespace);

create table "miembros" ("id" serial not null primary key, "grupo_id" integer not null, "rama_id" smallint not null, "tutor_id" integer null, "nombre" varchar(100) not null, "apellido" varchar(100) not null, "ci" varchar(20) null, "fecha_nacimiento" date not null, "genero" genero_tipo null, "telefono" varchar(30) null, "email" varchar(150) null, "direccion" varchar(200) null, "fecha_ingreso" date not null default CURRENT_DATE, "activo" boolean not null default true, "creado_en" timestamp(0) with time zone not null default now(), "unidad_id" integer null, "codigo" varchar(60) null, "rol_patrulla" varchar(60) null default 'Integrante'::character varying, "lema" text null default 'Siempre listos'::text, "foto" text null default ''::text);

create index "idx_miembros_grupo" on "miembros" ("grupo_id");

create index "idx_miembros_rama" on "miembros" ("rama_id");

create index "idx_miembros_unidad" on "miembros" ("unidad_id");

alter table "miembros" add constraint "miembros_ci_key" unique ("ci");

alter table "miembros" add constraint "miembros_codigo_key" unique ("codigo");

select exists (select 1 from pg_class c, pg_namespace n where n.nspname = current_schema() and c.relname = 'migraciones' and c.relkind in ('r', 'p') and n.oid = c.relnamespace);

create table "migraciones" ("nombre" text not null, "aplicada_en" timestamp(0) with time zone null default now());

alter table "migraciones" add primary key ("nombre");

select exists (select 1 from pg_class c, pg_namespace n where n.nspname = current_schema() and c.relname = 'noticias' and c.relkind in ('r', 'p') and n.oid = c.relnamespace);

create table "noticias" ("id" serial not null primary key, "autor_id" integer null, "categoria_id" smallint null, "titulo" varchar(200) not null, "slug" varchar(220) not null, "resumen" varchar(300) null, "contenido" text not null, "imagen_url" varchar(255) null, "estado" estado_publicacion not null default 'borrador'::estado_publicacion, "publicado_en" timestamp(0) with time zone null, "creado_en" timestamp(0) with time zone not null default now(), "actualizado_en" timestamp(0) with time zone not null default now(), "destacada" boolean not null default false);

create index "idx_noticias_estado_fecha" on "noticias" ("estado", "publicado_en");

alter table "noticias" add constraint "noticias_slug_key" unique ("slug");

select exists (select 1 from pg_class c, pg_namespace n where n.nspname = current_schema() and c.relname = 'ramas' and c.relkind in ('r', 'p') and n.oid = c.relnamespace);

create table "ramas" ("id" smallserial not null primary key, "nombre" varchar(60) not null, "slug" varchar(60) not null, "edad_minima" smallint not null, "edad_maxima" smallint not null, "descripcion" text null, "lema" varchar(150) null, "color_hex" char(7) null, "imagen_url" varchar(255) null, "orden" smallint not null default 0, "actividades" jsonb not null default '[]'::jsonb);

alter table "ramas" add constraint "ramas_nombre_key" unique ("nombre");

alter table "ramas" add constraint "ramas_slug_key" unique ("slug");

select exists (select 1 from pg_class c, pg_namespace n where n.nspname = current_schema() and c.relname = 'requisitos' and c.relkind in ('r', 'p') and n.oid = c.relnamespace);

create table "requisitos" ("id" serial not null primary key, "rama_id" smallint null, "nombre" text not null, "etapa" text not null, "tipo" text not null default 'Requisito'::text);

select exists (select 1 from pg_class c, pg_namespace n where n.nspname = current_schema() and c.relname = 'roles' and c.relkind in ('r', 'p') and n.oid = c.relnamespace);

create table "roles" ("id" smallserial not null primary key, "nombre" varchar(40) not null, "descripcion" varchar(200) null);

alter table "roles" add constraint "roles_nombre_key" unique ("nombre");

select exists (select 1 from pg_class c, pg_namespace n where n.nspname = current_schema() and c.relname = 'semillas' and c.relkind in ('r', 'p') and n.oid = c.relnamespace);

create table "semillas" ("version" text not null, "creado_en" timestamp(0) with time zone not null default now());

alter table "semillas" add primary key ("version");

select exists (select 1 from pg_class c, pg_namespace n where n.nspname = current_schema() and c.relname = 'sesiones_login' and c.relkind in ('r', 'p') and n.oid = c.relnamespace);

create table "sesiones_login" ("token_hash" text not null, "usuario_id" integer not null, "actividad" timestamp(0) with time zone not null default now());

alter table "sesiones_login" add primary key ("token_hash");

select exists (select 1 from pg_class c, pg_namespace n where n.nspname = current_schema() and c.relname = 'tokens_recuperacion' and c.relkind in ('r', 'p') and n.oid = c.relnamespace);

create table "tokens_recuperacion" ("token_hash" text not null, "usuario_id" integer not null, "vence_en" timestamp(0) with time zone not null);

alter table "tokens_recuperacion" add primary key ("token_hash");

select exists (select 1 from pg_class c, pg_namespace n where n.nspname = current_schema() and c.relname = 'tutores' and c.relkind in ('r', 'p') and n.oid = c.relnamespace);

create table "tutores" ("id" serial not null primary key, "nombre" varchar(100) not null, "apellido" varchar(100) not null, "ci" varchar(20) null, "telefono" varchar(30) not null, "email" varchar(150) null, "parentesco" varchar(40) null);

alter table "tutores" add constraint "tutores_ci_key" unique ("ci");

select exists (select 1 from pg_class c, pg_namespace n where n.nspname = current_schema() and c.relname = 'unidades' and c.relkind in ('r', 'p') and n.oid = c.relnamespace);

create table "unidades" ("id" serial not null primary key, "grupo_id" integer not null, "rama_id" smallint not null, "nombre" varchar(120) not null, "dirigente_id" integer null);

create index "idx_unidades_dirigente" on "unidades" ("dirigente_id");

alter table "unidades" add constraint "unidades_grupo_id_nombre_key" unique ("grupo_id", "nombre");

select exists (select 1 from pg_class c, pg_namespace n where n.nspname = current_schema() and c.relname = 'usuarios' and c.relkind in ('r', 'p') and n.oid = c.relnamespace);

create table "usuarios" ("id" serial not null primary key, "rol_id" smallint not null, "nombre" varchar(100) not null, "apellido" varchar(100) not null, "email" varchar(150) not null, "password_hash" varchar(255) not null, "activo" boolean not null default true, "ultimo_acceso" timestamp(0) with time zone null, "creado_en" timestamp(0) with time zone not null default now(), "telefono" varchar(30) null default ''::character varying, "avatar_url" text null, "departamento" varchar(50) null, "distrito_id" integer null, "tutor_id" integer null, "miembro_id" integer null, "version_acceso" integer not null default 0);

create index "idx_usuarios_distrito" on "usuarios" ("distrito_id");

alter table "usuarios" add constraint "usuarios_email_key" unique ("email");

alter table "usuarios" add constraint "usuarios_miembro_id_key" unique ("miembro_id");

alter table "usuarios" add constraint "usuarios_tutor_id_key" unique ("tutor_id");

select exists (select 1 from pg_class c, pg_namespace n where n.nspname = current_schema() and c.relname = 'vinculos_familia' and c.relkind in ('r', 'p') and n.oid = c.relnamespace);

create table "vinculos_familia" ("tutor_id" integer not null, "miembro_id" integer not null);

alter table "vinculos_familia" add primary key ("tutor_id", "miembro_id");

alter table "actividades" add constraint "actividades_unidad_id_fkey" foreign key ("unidad_id") references "unidades" ("id") on delete no action on update no action;

alter table "asistencias" add constraint "asistencias_actividad_id_fkey" foreign key ("actividad_id") references "actividades" ("id") on delete cascade on update no action;

alter table "asistencias" add constraint "asistencias_miembro_id_fkey" foreign key ("miembro_id") references "miembros" ("id") on delete cascade on update no action;

alter table "auditoria" add constraint "auditoria_actor_id_fkey" foreign key ("actor_id") references "usuarios" ("id") on delete set null on update no action;

alter table "autorizaciones" add constraint "autorizaciones_actividad_id_fkey" foreign key ("actividad_id") references "actividades" ("id") on delete cascade on update no action;

alter table "autorizaciones" add constraint "autorizaciones_miembro_id_fkey" foreign key ("miembro_id") references "miembros" ("id") on delete cascade on update no action;

alter table "autorizaciones" add constraint "autorizaciones_tutor_id_fkey" foreign key ("tutor_id") references "tutores" ("id") on delete cascade on update no action;

alter table "comunicado_lecturas" add constraint "comunicado_lecturas_comunicado_id_fkey" foreign key ("comunicado_id") references "comunicados" ("id") on delete cascade on update no action;

alter table "comunicado_lecturas" add constraint "comunicado_lecturas_usuario_id_fkey" foreign key ("usuario_id") references "usuarios" ("id") on delete cascade on update no action;

alter table "comunicados" add constraint "comunicados_autor_id_fkey" foreign key ("autor_id") references "usuarios" ("id") on delete set null on update no action;

alter table "comunicados" add constraint "comunicados_unidad_id_fkey" foreign key ("unidad_id") references "unidades" ("id") on delete set null on update no action;

alter table "contenido_institucional" add constraint "contenido_institucional_actualizado_por_fkey" foreign key ("actualizado_por") references "usuarios" ("id") on delete set null on update no action;

alter table "cuotas" add constraint "cuotas_miembro_id_fkey" foreign key ("miembro_id") references "miembros" ("id") on delete cascade on update no action;

alter table "eventos" add constraint "eventos_autor_id_fkey" foreign key ("autor_id") references "usuarios" ("id") on delete set null on update no action;

alter table "eventos" add constraint "eventos_rama_id_fkey" foreign key ("rama_id") references "ramas" ("id") on delete no action on update no action;

alter table "fichas_medicas" add constraint "fichas_medicas_miembro_id_fkey" foreign key ("miembro_id") references "miembros" ("id") on delete cascade on update no action;

alter table "grupos_scout" add constraint "grupos_scout_distrito_id_fkey" foreign key ("distrito_id") references "distritos" ("id") on delete no action on update no action;

alter table "inscripciones" add constraint "inscripciones_grupo_id_fkey" foreign key ("grupo_id") references "grupos_scout" ("id") on delete no action on update no action;

alter table "inscripciones" add constraint "inscripciones_miembro_id_fkey" foreign key ("miembro_id") references "miembros" ("id") on delete cascade on update no action;

alter table "inscripciones" add constraint "inscripciones_rama_id_fkey" foreign key ("rama_id") references "ramas" ("id") on delete no action on update no action;

alter table "inscripciones" add constraint "inscripciones_revisado_por_fkey" foreign key ("revisado_por") references "usuarios" ("id") on delete set null on update no action;

alter table "inscripciones" add constraint "inscripciones_tutor_id_fkey" foreign key ("tutor_id") references "tutores" ("id") on delete cascade on update no action;

alter table "inscripciones" add constraint "inscripciones_unidad_id_fkey" foreign key ("unidad_id") references "unidades" ("id") on delete set null on update no action;

alter table "insignias" add constraint "insignias_rama_id_fkey" foreign key ("rama_id") references "ramas" ("id") on delete no action on update no action;

alter table "insignias" add constraint "insignias_requisito_id_fkey" foreign key ("requisito_id") references "requisitos" ("id") on delete cascade on update no action;

alter table "intentos_login" add constraint "intentos_login_usuario_id_fkey" foreign key ("usuario_id") references "usuarios" ("id") on delete cascade on update no action;

alter table "mensajes_contacto" add constraint "mensajes_contacto_rama_id_fkey" foreign key ("rama_id") references "ramas" ("id") on delete no action on update no action;

alter table "miembro_insignias" add constraint "miembro_insignias_insignia_id_fkey" foreign key ("insignia_id") references "insignias" ("id") on delete cascade on update no action;

alter table "miembro_insignias" add constraint "miembro_insignias_miembro_id_fkey" foreign key ("miembro_id") references "miembros" ("id") on delete cascade on update no action;

alter table "miembro_insignias" add constraint "miembro_insignias_registrado_por_fkey" foreign key ("registrado_por") references "usuarios" ("id") on delete set null on update no action;

alter table "miembro_requisitos" add constraint "miembro_requisitos_miembro_id_fkey" foreign key ("miembro_id") references "miembros" ("id") on delete cascade on update no action;

alter table "miembro_requisitos" add constraint "miembro_requisitos_registrado_por_fkey" foreign key ("registrado_por") references "usuarios" ("id") on delete set null on update no action;

alter table "miembro_requisitos" add constraint "miembro_requisitos_requisito_id_fkey" foreign key ("requisito_id") references "requisitos" ("id") on delete cascade on update no action;

alter table "miembros" add constraint "miembros_grupo_id_fkey" foreign key ("grupo_id") references "grupos_scout" ("id") on delete no action on update no action;

alter table "miembros" add constraint "miembros_rama_id_fkey" foreign key ("rama_id") references "ramas" ("id") on delete no action on update no action;

alter table "miembros" add constraint "miembros_tutor_id_fkey" foreign key ("tutor_id") references "tutores" ("id") on delete set null on update no action;

alter table "miembros" add constraint "miembros_unidad_id_fkey" foreign key ("unidad_id") references "unidades" ("id") on delete set null on update no action;

alter table "noticias" add constraint "noticias_autor_id_fkey" foreign key ("autor_id") references "usuarios" ("id") on delete set null on update no action;

alter table "noticias" add constraint "noticias_categoria_id_fkey" foreign key ("categoria_id") references "categorias" ("id") on delete no action on update no action;

alter table "requisitos" add constraint "requisitos_rama_id_fkey" foreign key ("rama_id") references "ramas" ("id") on delete no action on update no action;

alter table "sesiones_login" add constraint "sesiones_login_usuario_id_fkey" foreign key ("usuario_id") references "usuarios" ("id") on delete cascade on update no action;

alter table "tokens_recuperacion" add constraint "tokens_recuperacion_usuario_id_fkey" foreign key ("usuario_id") references "usuarios" ("id") on delete cascade on update no action;

alter table "unidades" add constraint "unidades_dirigente_id_fkey" foreign key ("dirigente_id") references "usuarios" ("id") on delete set null on update no action;

alter table "unidades" add constraint "unidades_grupo_id_fkey" foreign key ("grupo_id") references "grupos_scout" ("id") on delete no action on update no action;

alter table "unidades" add constraint "unidades_rama_id_fkey" foreign key ("rama_id") references "ramas" ("id") on delete no action on update no action;

alter table "usuarios" add constraint "usuarios_distrito_id_fkey" foreign key ("distrito_id") references "distritos" ("id") on delete set null on update no action;

alter table "usuarios" add constraint "usuarios_miembro_id_fkey" foreign key ("miembro_id") references "miembros" ("id") on delete cascade on update no action;

alter table "usuarios" add constraint "usuarios_rol_id_fkey" foreign key ("rol_id") references "roles" ("id") on delete no action on update no action;

alter table "usuarios" add constraint "usuarios_tutor_id_fkey" foreign key ("tutor_id") references "tutores" ("id") on delete cascade on update no action;

alter table "vinculos_familia" add constraint "vinculos_familia_miembro_id_fkey" foreign key ("miembro_id") references "miembros" ("id") on delete cascade on update no action;

alter table "vinculos_familia" add constraint "vinculos_familia_tutor_id_fkey" foreign key ("tutor_id") references "tutores" ("id") on delete cascade on update no action;

ALTER TABLE "ramas" ADD CONSTRAINT "ramas_check" CHECK ((edad_maxima >= edad_minima));

ALTER TABLE "eventos" ADD CONSTRAINT "eventos_check" CHECK (((fecha_fin IS NULL) OR (fecha_fin >= fecha_inicio)));

ALTER TABLE "requisitos" ADD CONSTRAINT "requisitos_tipo_check" CHECK ((tipo = ANY (ARRAY['Requisito'::text, 'Insignia'::text])));

ALTER TABLE "autorizaciones" ADD CONSTRAINT "autorizaciones_estado_check" CHECK ((estado = ANY (ARRAY['pendiente'::text, 'firmada'::text, 'rechazada'::text])));

ALTER TABLE "asistencias" ADD CONSTRAINT "asistencias_estado_check" CHECK ((estado = ANY (ARRAY['presente'::text, 'ausente'::text, 'justificado'::text])));

ALTER TABLE "comunicados" ADD CONSTRAINT "comunicados_destino_check" CHECK ((destino = ANY (ARRAY['ambos'::text, 'familia'::text, 'miembro'::text])));

ALTER TABLE "cuotas" ADD CONSTRAINT "cuotas_estado_check" CHECK ((estado = ANY (ARRAY['pendiente'::text, 'en_revision'::text, 'pagado'::text, 'vencido'::text])));

ALTER TABLE "cuotas" ADD CONSTRAINT "cuotas_monto_check" CHECK ((monto >= (0)::numeric));
