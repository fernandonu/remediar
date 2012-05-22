-- DROP SEQUENCE trazadoras.nino_new_id_nino_new_seq;

ALTER TABLE "facturacion"."prestacion"
  DROP CONSTRAINT "prestacion_id_anexo_fkey" RESTRICT;

DROP TABLE "trazadoras"."nino";

CREATE SEQUENCE trazadoras.embarazadas2_id_emb_seq
  INCREMENT 1 MINVALUE 1
  MAXVALUE 9223372036854775807 START 1
  CACHE 1;

CREATE SEQUENCE trazadoras.embarazadas2_id_emb_tmp_seq
  INCREMENT 1 MINVALUE 1
  MAXVALUE 9223372036854775807 START 1
  CACHE 1;

ALTER SEQUENCE trazadoras.embarazadas2_id_emb_tmp_seq RESTART WITH 21514;

CREATE SEQUENCE facturacion.informados_id_informado_seq
  INCREMENT 1 MINVALUE 1
  MAXVALUE 9223372036854775807 START 1
  CACHE 1;

ALTER SEQUENCE facturacion.informados_id_informado_seq RESTART WITH 478639;

CREATE SEQUENCE trazadoras.mu_tmp_id_mu_seq
  INCREMENT 1 MINVALUE 1
  MAXVALUE 9223372036854775807 START 1
  CACHE 1;

ALTER SEQUENCE trazadoras.mu_tmp_id_mu_seq RESTART WITH 29;

CREATE SEQUENCE facturacion.muertes_idm_seq
  INCREMENT 1 MINVALUE 1
  MAXVALUE 9223372036854775807 START 1
  CACHE 1;

ALTER SEQUENCE facturacion.muertes_idm_seq RESTART WITH 62;

CREATE SEQUENCE trazadoras.nino_new2_id_nino_new_seq
  INCREMENT 1 MINVALUE 1
  MAXVALUE 9223372036854775807 START 1
  CACHE 1;

ALTER SEQUENCE trazadoras.nino_new2_id_nino_new_seq RESTART WITH 9488;

CREATE SEQUENCE trazadoras.nino_new2_id_nino_tmp_seq
  INCREMENT 1 MINVALUE 1
  MAXVALUE 9223372036854775807 START 13
  CACHE 1;

ALTER SEQUENCE trazadoras.nino_new2_id_nino_tmp_seq RESTART WITH 6621;

CREATE SEQUENCE facturacion.objetivos_id_seq
  INCREMENT 1 MINVALUE 1
  MAXVALUE 9223372036854775807 START 1
  CACHE 1;

CREATE SEQUENCE trazadoras.partos2_id_par_seq
  INCREMENT 1 MINVALUE 1
  MAXVALUE 9223372036854775807 START 1
  CACHE 1;

ALTER SEQUENCE trazadoras.partos2_id_par_seq RESTART WITH 6800;

CREATE SEQUENCE trazadoras.partos2_id_par_tmp_seq
  INCREMENT 1 MINVALUE 1
  MAXVALUE 9223372036854775807 START 1
  CACHE 1;

ALTER SEQUENCE trazadoras.partos2_id_par_tmp_seq RESTART WITH 671;

CREATE SEQUENCE facturacion."recepcion_idRecepcion_seq"
  INCREMENT 1 MINVALUE 1
  MAXVALUE 9223372036854775807 START 1
  CACHE 1;

ALTER SEQUENCE facturacion."recepcion_idRecepcion_seq" RESTART WITH 30304;

CREATE SEQUENCE facturacion.tempobjetivos_idobjetivo_seq
  INCREMENT 1 MINVALUE 1
  MAXVALUE 9223372036854775807 START 1
  CACHE 1;

CREATE SEQUENCE facturacion.tmp_infometa_idtmp_seq
  INCREMENT 1 MINVALUE 1
  MAXVALUE 9223372036854775807 START 1
  CACHE 1;

CREATE SEQUENCE trazadoras.trz_antisarampionosa_idantisarampionosa_seq
  INCREMENT 1 MINVALUE 1
  MAXVALUE 9223372036854775807 START 1
  CACHE 1;

ALTER TABLE facturacion.comprobante
  ADD COLUMN "idvacuna" INTEGER;

ALTER TABLE facturacion.comprobante
  ADD COLUMN "mensaje" TEXT;

ALTER TABLE facturacion.comprobante
  ADD COLUMN "fila" INTEGER;

ALTER TABLE facturacion.comprobante
  ADD COLUMN "idprestacion" BIGINT;

ALTER TABLE facturacion.debito
  ADD COLUMN "mensaje_baja" TEXT;

CREATE TABLE "facturacion"."recepcion" (
  "nombrearchivo" VARCHAR(33) NOT NULL, 
  "cantdebito" INTEGER, 
  "estadobd" CHAR(7), 
  "idrecepcion" INTEGER DEFAULT nextval('facturacion."recepcion_idRecepcion_seq"'::regclass) NOT NULL, 
  "cod_org" INTEGER NOT NULL, 
  "no_correlativo" INTEGER NOT NULL, 
  "ano_exp" INTEGER NOT NULL, 
  "cuerpo" INTEGER NOT NULL, 
  CONSTRAINT "recepcion_pkey" PRIMARY KEY(idrecepcion)
) WITHOUT OIDS;

CREATE UNIQUE INDEX "nombrearchivo_unique" ON "facturacion"."recepcion"
  USING btree (nombrearchivo);

ALTER TABLE trazadoras.embarazadas
  ALTER COLUMN "id_emb" SET DEFAULT nextval('trazadoras.embarazadas2_id_emb_seq'::regclass);

ALTER TABLE "trazadoras"."embarazadas"
  ALTER COLUMN "num_doc" TYPE BIGINT;

ALTER TABLE trazadoras.embarazadas
  ADD COLUMN "estado_nutricional" TEXT;

ALTER TABLE trazadoras.embarazadas
  ADD COLUMN "antitetanica_primera_dosis" DATE;

ALTER TABLE trazadoras.embarazadas
  ADD COLUMN "antitetanica_segunda_dosis" DATE;

ALTER TABLE trazadoras.embarazadas
  ADD COLUMN "hiv" TEXT;

ALTER TABLE trazadoras.embarazadas
  ADD COLUMN "eco" DATE;

ALTER TABLE trazadoras.embarazadas
  ADD COLUMN "fecha_obito" DATE;

ALTER TABLE trazadoras.embarazadas
  ADD COLUMN "nro_control_actual" INTEGER;

ALTER TABLE trazadoras.embarazadas
  ADD COLUMN "tension_arterial_maxima" NUMERIC(30,2);

ALTER TABLE trazadoras.embarazadas
  ADD COLUMN "tension_arterial_minima" NUMERIC(30,2);

ALTER TABLE trazadoras.embarazadas
  ADD COLUMN "altura_uterina" NUMERIC(30,2);

ALTER TABLE trazadoras.embarazadas
  ADD COLUMN "peso_embarazada" NUMERIC(30,2);

ALTER TABLE trazadoras.embarazadas
  ADD COLUMN "vdrl_fecha" DATE;

ALTER TABLE trazadoras.embarazadas
  ADD COLUMN "hiv_fecha" DATE;

ALTER TABLE trazadoras.embarazadas
  ADD COLUMN "municipio" INTEGER;

ALTER TABLE trazadoras.embarazadas
  ALTER COLUMN "municipio" SET DEFAULT 0;

ALTER TABLE trazadoras.embarazadas
  ADD COLUMN "discapacitado" VARCHAR(1);

ALTER TABLE trazadoras.embarazadas
  ADD COLUMN "fecha_nacimiento" DATE;

ALTER TABLE trazadoras.embarazadas
  ADD COLUMN "id_prestacion" BIGINT;

ALTER TABLE trazadoras.embarazadas
  ADD COLUMN "eliminado" CHAR(1);

ALTER TABLE trazadoras.embarazadas
  ADD COLUMN "id_recepcion" INTEGER NOT NULL;

ALTER TABLE "trazadoras"."embarazadas"
  ADD CONSTRAINT "recepcion" FOREIGN KEY ("id_recepcion")
    REFERENCES "facturacion"."recepcion"("idrecepcion")
    MATCH FULL
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
    NOT DEFERRABLE;

CREATE INDEX "fki_recepcion" ON "trazadoras"."embarazadas"
  USING btree (id_recepcion);

CREATE TABLE "trazadoras"."embarazadas_old" (
  "id_emb" INTEGER DEFAULT nextval('trazadoras.embarazadas_id_emb_seq'::regclass) NOT NULL, 
  "cuie" TEXT NOT NULL, 
  "clave" TEXT, 
  "tipo_doc" TEXT, 
  "num_doc" BIGINT, 
  "apellido" TEXT, 
  "nombre" TEXT, 
  "fecha_control" DATE, 
  "sem_gestacion" NUMERIC(30,6), 
  "fum" DATE, 
  "fpp" DATE, 
  "fpcp" DATE, 
  "observaciones" TEXT, 
  "fecha_carga" TIMESTAMP WITHOUT TIME ZONE, 
  "usuario" TEXT, 
  "antitetanica" TEXT, 
  "vdrl" TEXT, 
  CONSTRAINT "embarazadas99_pkey" PRIMARY KEY(id_emb)
) WITHOUT OIDS;

ALTER TABLE "trazadoras"."embarazadas_old"
  OWNER TO "postgres";

CREATE TABLE "trazadoras"."embarazadas_tmp" (
  "id_emb_tmp" INTEGER DEFAULT nextval('trazadoras.embarazadas2_id_emb_tmp_seq'::regclass) NOT NULL, 
  "cuie" TEXT NOT NULL, 
  "clave" TEXT, 
  "tipo_doc" TEXT, 
  "num_doc" BIGINT, 
  "apellido" TEXT, 
  "nombre" TEXT, 
  "fecha_control" DATE, 
  "sem_gestacion" NUMERIC(30,6), 
  "fum" DATE, 
  "fpp" DATE, 
  "fpcp" DATE, 
  "observaciones" TEXT, 
  "fecha_carga" TIMESTAMP WITHOUT TIME ZONE, 
  "usuario" TEXT, 
  "antitetanica" TEXT, 
  "vdrl" TEXT, 
  "estado_nutricional" TEXT, 
  "antitetanica_primera_dosis" DATE, 
  "antitetanica_segunda_dosis" DATE, 
  "hiv" TEXT, 
  "eco" DATE, 
  "fecha_obito" DATE, 
  "nro_control_actual" INTEGER, 
  "tension_arterial_maxima" NUMERIC(30,2), 
  "tension_arterial_minima" NUMERIC(30,2), 
  "altura_uterina" NUMERIC(30,2), 
  "peso_embarazada" NUMERIC(30,2), 
  "vdrl_fecha" DATE, 
  "hiv_fecha" DATE, 
  "municipio" INTEGER DEFAULT 0, 
  "discapacitado" VARCHAR(1), 
  "fecha_nacimiento" DATE, 
  "id_prestacion" BIGINT, 
  "mjs" TEXT, 
  "id_recepcion" INTEGER NOT NULL, 
  CONSTRAINT "embarazadas2tmp_pkey" PRIMARY KEY(id_emb_tmp)
) WITHOUT OIDS;

ALTER TABLE "trazadoras"."embarazadas_tmp"
  OWNER TO "postgres";

CREATE INDEX "fki_recepcion2" ON "trazadoras"."embarazadas_tmp"
  USING btree (id_recepcion);

ALTER TABLE "trazadoras"."embarazadas_tmp"
  ADD CONSTRAINT "recepcion2" FOREIGN KEY ("id_recepcion")
    REFERENCES "facturacion"."recepcion"("idrecepcion")
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
    NOT DEFERRABLE;

ALTER TABLE facturacion.factura
  ADD COLUMN "nro_fact_offline" CHAR(14);

ALTER TABLE facturacion.factura
  ADD COLUMN "recepcion_id" INTEGER;

ALTER TABLE facturacion.factura
  ADD COLUMN "fecha_entrada" DATE NOT NULL;

CREATE TABLE "facturacion"."informados" (
  "id_informado" INTEGER DEFAULT nextval('facturacion.informados_id_informado_seq'::regclass) NOT NULL, 
  "idrecepcion" BIGINT, 
  "cuie" CHAR(6), 
  "idprestacion" BIGINT, 
  "clavebeneficiario" CHAR(16), 
  "codnomenclador" CHAR(10), 
  "tipodoc" VARCHAR(5), 
  "nrodoc" CHAR(12), 
  "nombre" VARCHAR(30), 
  "apellido" VARCHAR(30), 
  "fechanac" TIMESTAMP WITHOUT TIME ZONE, 
  "fechaactual" TIMESTAMP WITHOUT TIME ZONE, 
  "idvacuna" BIGINT DEFAULT 0, 
  "idtaller" BIGINT DEFAULT 0, 
  "km" NUMERIC(30,2) DEFAULT 0, 
  "origen" VARCHAR(100), 
  "destino" VARCHAR(100), 
  "estadobd" CHAR(7), 
  "pago" NUMERIC(30,2) DEFAULT 0, 
  "debito" NUMERIC(30,2) DEFAULT 0, 
  "clavemadre" CHAR(16), 
  "sexo" CHAR(1), 
  "municipio" INTEGER, 
  "semgesta" INTEGER, 
  "discapacitado" CHAR(1), 
  "clasedoc" CHAR(1)
) WITHOUT OIDS;

ALTER TABLE "trazadoras"."mu"
  ALTER COLUMN "num_doc" TYPE BIGINT;

ALTER TABLE trazadoras.mu
  ADD COLUMN "comitelocal" TIMESTAMP WITHOUT TIME ZONE;

ALTER TABLE trazadoras.mu
  ADD COLUMN "caso" VARCHAR;

ALTER TABLE trazadoras.mu
  ADD COLUMN "fppmuerte" TIMESTAMP WITHOUT TIME ZONE;

ALTER TABLE trazadoras.mu
  ADD COLUMN "id_prestacion" BIGINT;

ALTER TABLE trazadoras.mu
  ADD COLUMN "eliminado" CHAR(1);

ALTER TABLE trazadoras.mu
  ADD COLUMN "municipio" INTEGER;

ALTER TABLE trazadoras.mu
  ADD COLUMN "clavebeneficiario" CHAR(16);

ALTER TABLE trazadoras.mu
  ADD COLUMN "id_recepcion" INTEGER NOT NULL;

ALTER TABLE "trazadoras"."mu"
  ADD CONSTRAINT "recepcion3" FOREIGN KEY ("id_recepcion")
    REFERENCES "facturacion"."recepcion"("idrecepcion")
    MATCH FULL
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
    NOT DEFERRABLE;

CREATE INDEX "fki_recepcion3" ON "trazadoras"."mu"
  USING btree (id_recepcion);

CREATE TABLE "trazadoras"."mu_tmp" (
  "id_mu" INTEGER DEFAULT nextval('trazadoras.mu_tmp_id_mu_seq'::regclass) NOT NULL, 
  "cuie" TEXT NOT NULL, 
  "tipo_doc" TEXT, 
  "num_doc" BIGINT, 
  "apellido" TEXT, 
  "nombre" TEXT, 
  "fecha_defuncion" DATE, 
  "fecha_auditoria" DATE, 
  "fecha_par_int" DATE, 
  "fecha_nac" DATE, 
  "observaciones" TEXT, 
  "fecha_carga" TIMESTAMP WITHOUT TIME ZONE, 
  "usuario" TEXT, 
  "clase_doc" TEXT, 
  "comitelocal" TIMESTAMP WITHOUT TIME ZONE, 
  "caso" VARCHAR, 
  "fppmuerte" TIMESTAMP WITHOUT TIME ZONE, 
  "id_prestacion" BIGINT, 
  "eliminado" CHAR(1), 
  "municipio" INTEGER, 
  "clavebeneficiario" CHAR(16), 
  "mjs" TEXT, 
  "id_recepcion" INTEGER NOT NULL, 
  CONSTRAINT "mu_tmp_pkey" PRIMARY KEY(id_mu)
) WITHOUT OIDS;

ALTER TABLE "trazadoras"."mu_tmp"
  OWNER TO "postgres";

CREATE INDEX "fki_recepcion4" ON "trazadoras"."mu_tmp"
  USING btree (id_recepcion);

ALTER TABLE "trazadoras"."mu_tmp"
  ADD CONSTRAINT "recepcion4" FOREIGN KEY ("id_recepcion")
    REFERENCES "facturacion"."recepcion"("idrecepcion")
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
    NOT DEFERRABLE;

CREATE TABLE "facturacion"."muertes" (
  "idm" INTEGER DEFAULT nextval('facturacion.muertes_idm_seq'::regclass) NOT NULL, 
  "cuie" CHAR(6), 
  "mes" CHAR(2), 
  "ano" CHAR(4), 
  "cantidadt" INTEGER, 
  "cantidadok" INTEGER, 
  CONSTRAINT "muertes_pkey" PRIMARY KEY(idm)
) WITHOUT OIDS;

CREATE TABLE "trazadoras"."nino-old" (
  "id_nino" INTEGER NOT NULL, 
  "cuie" TEXT NOT NULL, 
  "clave" TEXT, 
  "clase_doc" TEXT, 
  "tipo_doc" TEXT, 
  "num_doc" NUMERIC(30,6), 
  "apellido" TEXT, 
  "nombre" TEXT, 
  "fecha_nac" DATE, 
  "fecha_control" DATE, 
  "peso" NUMERIC(30,6), 
  "talla" NUMERIC(30,6), 
  "perim_cefalico" NUMERIC(30,6), 
  "percen_peso_edad" TEXT, 
  "percen_talla_edad" TEXT, 
  "percen_perim_cefali_edad" TEXT, 
  "percen_peso_talla" TEXT, 
  "triple_viral" DATE, 
  "nino_edad" INTEGER, 
  "observaciones" TEXT, 
  "fecha_carga" TIMESTAMP WITHOUT TIME ZONE, 
  "usuario" TEXT, 
  CONSTRAINT "nino_pkey" PRIMARY KEY(id_nino)
) WITHOUT OIDS;

ALTER TABLE "trazadoras"."nino-old"
  OWNER TO "postgres";

ALTER TABLE trazadoras.nino_new
  ALTER COLUMN "id_nino_new" SET DEFAULT nextval('trazadoras.nino_new2_id_nino_new_seq'::regclass);

ALTER TABLE "trazadoras"."nino_new"
  ALTER COLUMN "num_doc" TYPE BIGINT;

ALTER TABLE trazadoras.nino_new
  ADD COLUMN "fecha_obito" DATE;

ALTER TABLE trazadoras.nino_new
  ADD COLUMN "ncontrolanual" INTEGER;

ALTER TABLE trazadoras.nino_new
  ADD COLUMN "id_prestacion" BIGINT;

ALTER TABLE trazadoras.nino_new
  ADD COLUMN "sexo" CHAR(1);

ALTER TABLE trazadoras.nino_new
  ADD COLUMN "municipio" INTEGER;

ALTER TABLE trazadoras.nino_new
  ADD COLUMN "percentilo_imc" CHAR(1);

ALTER TABLE trazadoras.nino_new
  ADD COLUMN "discapacitado" CHAR(1);

ALTER TABLE trazadoras.nino_new
  ADD COLUMN "cod_aldea" VARCHAR(50);

ALTER TABLE trazadoras.nino_new
  ADD COLUMN "descr_aldea" VARCHAR(100);

ALTER TABLE trazadoras.nino_new
  ADD COLUMN "calle" VARCHAR(100);

ALTER TABLE trazadoras.nino_new
  ADD COLUMN "num_calle" VARCHAR(100);

ALTER TABLE trazadoras.nino_new
  ADD COLUMN "barrio" VARCHAR(100);

ALTER TABLE trazadoras.nino_new
  ADD COLUMN "cod_nomenclador" VARCHAR(10);

ALTER TABLE trazadoras.nino_new
  ADD COLUMN "eliminado" CHAR(1);

ALTER TABLE trazadoras.nino_new
  ADD COLUMN "id_recepcion" INTEGER NOT NULL;

ALTER TABLE "trazadoras"."nino_new"
  ADD CONSTRAINT "recepcion5" FOREIGN KEY ("id_recepcion")
    REFERENCES "facturacion"."recepcion"("idrecepcion")
    MATCH FULL
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
    NOT DEFERRABLE;

CREATE INDEX "fki_recepcion5" ON "trazadoras"."nino_new"
  USING btree (id_recepcion);

CREATE TABLE "trazadoras"."nino_tmp" (
  "id_nino_tmp" INTEGER DEFAULT nextval('trazadoras.nino_new2_id_nino_tmp_seq'::regclass) NOT NULL, 
  "cuie" TEXT NOT NULL, 
  "clave" TEXT, 
  "clase_doc" TEXT, 
  "tipo_doc" TEXT, 
  "num_doc" BIGINT, 
  "apellido" TEXT, 
  "nombre" TEXT, 
  "fecha_nac" DATE, 
  "fecha_control" DATE, 
  "peso" NUMERIC(30,6), 
  "talla" NUMERIC(30,6), 
  "percen_peso_edad" TEXT, 
  "percen_talla_edad" TEXT, 
  "perim_cefalico" NUMERIC(30,6), 
  "percen_perim_cefali_edad" TEXT, 
  "imc" TEXT, 
  "percen_imc_edad" TEXT, 
  "percen_peso_talla" TEXT, 
  "triple_viral" DATE, 
  "nino_edad" INTEGER, 
  "observaciones" TEXT, 
  "fecha_carga" TIMESTAMP WITHOUT TIME ZONE, 
  "usuario" TEXT, 
  "fecha_obito" DATE, 
  "ncontrolanual" INTEGER, 
  "id_prestacion" BIGINT, 
  "sexo" CHAR(1), 
  "municipio" INTEGER, 
  "percentilo_imc" VARCHAR(10), 
  "discapacitado" CHAR(1), 
  "cod_aldea" VARCHAR(50), 
  "descr_aldea" VARCHAR(100), 
  "calle" VARCHAR(100), 
  "num_calle" VARCHAR(100), 
  "barrio" VARCHAR(100), 
  "cod_nomenclador" VARCHAR(10), 
  "mjs" TEXT, 
  "id_recepcion" INTEGER NOT NULL, 
  CONSTRAINT "nino_new2tmp_pkey" PRIMARY KEY(id_nino_tmp)
) WITHOUT OIDS;

ALTER TABLE "trazadoras"."nino_tmp"
  OWNER TO "postgres";

CREATE INDEX "fki_recepcion6" ON "trazadoras"."nino_tmp"
  USING btree (id_recepcion);

ALTER TABLE "trazadoras"."nino_tmp"
  ADD CONSTRAINT "recepcion6" FOREIGN KEY ("id_recepcion")
    REFERENCES "facturacion"."recepcion"("idrecepcion")
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
    NOT DEFERRABLE;

ALTER TABLE "facturacion"."nomenclador"
  ALTER COLUMN "categoria" TYPE SMALLINT;

CREATE TABLE "facturacion"."objetivos" (
  "id" INTEGER DEFAULT nextval('facturacion.objetivos_id_seq'::regclass) NOT NULL, 
  "cuie" CHAR(6), 
  "obj" INTEGER, 
  "mes" CHAR(2), 
  "ano" CHAR(4), 
  "numerador" INTEGER, 
  "denominador" INTEGER
) WITHOUT OIDS;

ALTER TABLE trazadoras.partos
  ALTER COLUMN "id_par" SET DEFAULT nextval('trazadoras.partos2_id_par_seq'::regclass);

ALTER TABLE "trazadoras"."partos"
  ALTER COLUMN "num_doc" TYPE BIGINT;

ALTER TABLE trazadoras.partos
  ADD COLUMN "obito_bebe" DATE;

ALTER TABLE trazadoras.partos
  ADD COLUMN "obito_madre" DATE;

ALTER TABLE trazadoras.partos
  ADD COLUMN "id_prestacion" BIGINT;

ALTER TABLE trazadoras.partos
  ADD COLUMN "obb_desconocido" CHAR(1);

ALTER TABLE trazadoras.partos
  ADD COLUMN "talla_rn" NUMERIC(6,3);

ALTER TABLE trazadoras.partos
  ADD COLUMN "perimcef_rn" NUMERIC(6,3);

ALTER TABLE trazadoras.partos
  ADD COLUMN "fecha_nacimiento" DATE;

ALTER TABLE trazadoras.partos
  ADD COLUMN "discapacitado" CHAR(1);

ALTER TABLE trazadoras.partos
  ADD COLUMN "municipio" INTEGER;

ALTER TABLE trazadoras.partos
  ADD COLUMN "eliminado" CHAR(1);

ALTER TABLE trazadoras.partos
  ADD COLUMN "id_recepcion" INTEGER NOT NULL;

ALTER TABLE "trazadoras"."partos"
  ADD CONSTRAINT "recepcion7" FOREIGN KEY ("id_recepcion")
    REFERENCES "facturacion"."recepcion"("idrecepcion")
    MATCH FULL
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
    NOT DEFERRABLE;

CREATE INDEX "fki_recepcion7" ON "trazadoras"."partos"
  USING btree (id_recepcion);

CREATE TABLE "trazadoras"."partos_old" (
  "id_par" INTEGER DEFAULT nextval('trazadoras.partos_id_par_seq'::regclass) NOT NULL, 
  "cuie" TEXT NOT NULL, 
  "clave" TEXT, 
  "tipo_doc" TEXT, 
  "num_doc" NUMERIC(30,6), 
  "apellido" TEXT, 
  "nombre" TEXT, 
  "fecha_parto" DATE, 
  "apgar" NUMERIC(30,6), 
  "peso" NUMERIC(30,6), 
  "vdrl" TEXT, 
  "antitetanica" TEXT, 
  "fecha_conserjeria" DATE, 
  "observaciones" TEXT, 
  "fecha_carga" TIMESTAMP WITHOUT TIME ZONE, 
  "usuario" TEXT, 
  CONSTRAINT "partos99_pkey" PRIMARY KEY(id_par)
) WITHOUT OIDS;

ALTER TABLE "trazadoras"."partos_old"
  OWNER TO "postgres";

CREATE TABLE "trazadoras"."partos_tmp" (
  "id_par_tmp" INTEGER DEFAULT nextval('trazadoras.partos2_id_par_tmp_seq'::regclass) NOT NULL, 
  "cuie" TEXT NOT NULL, 
  "clave" TEXT, 
  "tipo_doc" TEXT, 
  "num_doc" BIGINT, 
  "apellido" TEXT, 
  "nombre" TEXT, 
  "fecha_parto" DATE, 
  "apgar" NUMERIC(30,6), 
  "peso" NUMERIC(30,6), 
  "vdrl" TEXT, 
  "antitetanica" TEXT, 
  "fecha_conserjeria" DATE, 
  "observaciones" TEXT, 
  "fecha_carga" TIMESTAMP WITHOUT TIME ZONE, 
  "usuario" TEXT, 
  "obito_bebe" DATE, 
  "obito_madre" DATE, 
  "id_prestacion" BIGINT, 
  "obb_desconocido" CHAR(1), 
  "talla_rn" NUMERIC(6,3), 
  "perimcef_rn" NUMERIC(6,3), 
  "fecha_nacimiento" DATE, 
  "discapacitado" CHAR(1), 
  "municipio" INTEGER, 
  "mjs" TEXT, 
  "id_recepcion" INTEGER NOT NULL, 
  CONSTRAINT "partos2tmp_pkey" PRIMARY KEY(id_par_tmp)
) WITHOUT OIDS;

ALTER TABLE "trazadoras"."partos_tmp"
  OWNER TO "postgres";

CREATE INDEX "fki_recepcion8" ON "trazadoras"."partos_tmp"
  USING btree (id_recepcion);

ALTER TABLE "trazadoras"."partos_tmp"
  ADD CONSTRAINT "recepcion8" FOREIGN KEY ("id_recepcion")
    REFERENCES "facturacion"."recepcion"("idrecepcion")
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
    NOT DEFERRABLE;

ALTER TABLE facturacion.prestacion
  ADD COLUMN "prestacionid" BIGINT;

ALTER TABLE nacer.smiafiliados
  ADD COLUMN "id_procesobajaautomatica" INTEGER;

CREATE INDEX "clavebeneficiario_idx" ON "nacer"."smiafiliados"
  USING btree (clavebeneficiario);

CREATE INDEX "smiafiliados_idx" ON "nacer"."smiafiliados"
  USING btree (aficlasedoc, afitipodoc, afidni);

CREATE TABLE "nacer"."smiprocesobajaautomatica" (
  "id_procbajaautomatica" INTEGER NOT NULL, 
  "nroprocbajaautomatica" VARCHAR(10) NOT NULL, 
  "enproceso" BIT(1) NOT NULL, 
  "procesado" BIT(1) NOT NULL, 
  "programadopara" TIMESTAMP WITHOUT TIME ZONE, 
  "notificaralterminar" VARCHAR(100), 
  "resultado" CHAR(1), 
  "registrosprocesados" INTEGER, 
  "registrosdadosdebaja" INTEGER, 
  "usuariocreacion" VARCHAR(15), 
  "fechacreacion" TIMESTAMP WITHOUT TIME ZONE, 
  "tareaencurso" VARCHAR(50), 
  "porcentajeprogresotarea" INTEGER, 
  "ultimaejecucion" TIMESTAMP WITHOUT TIME ZONE, 
  "periododevalidez" CHAR(6), 
  CONSTRAINT "PK_smibajaautomatica" PRIMARY KEY(id_procbajaautomatica)
) WITHOUT OIDS;

CREATE TABLE "facturacion"."tempobjetivos" (
  "idobjetivo" INTEGER DEFAULT nextval('facturacion.tempobjetivos_idobjetivo_seq'::regclass) NOT NULL, 
  "descripcion" VARCHAR(300), 
  "orden" INTEGER
) WITHOUT OIDS;

CREATE TABLE "facturacion"."tmp_infometa" (
  "idtmp" INTEGER DEFAULT nextval('facturacion.tmp_infometa_idtmp_seq'::regclass) NOT NULL, 
  "municipio" INTEGER, 
  "departamento" INTEGER, 
  "cuie" CHAR(6), 
  "objetivo" INTEGER NOT NULL, 
  "asignado" INTEGER, 
  "informado" INTEGER, 
  "cumplido" CHAR(2) NOT NULL, 
  "puntos" NUMERIC(10,3), 
  "mes" CHAR(2), 
  "ano" CHAR(4), 
  "cerrado" CHAR(1), 
  "expe" CHAR(17), 
  "total" INTEGER, 
  "idc" BIGINT, 
  "orden" INTEGER, 
  "perc" NUMERIC(10,3)
) WITHOUT OIDS;

CREATE TABLE "trazadoras"."trz_antisarampionosa" (
  "codigo_efector" VARCHAR(6) NOT NULL, 
  "clave_beneficiario" VARCHAR(16), 
  "clase_documento" CHAR(1), 
  "tipo_documento" VARCHAR(5), 
  "numero_documento" VARCHAR(12), 
  "apellido" VARCHAR(40), 
  "nombre" VARCHAR(40), 
  "fecha_nacimiento" TIMESTAMP WITHOUT TIME ZONE, 
  "fecha_control" TIMESTAMP WITHOUT TIME ZONE, 
  "peso" NUMERIC(30,6), 
  "talla" NUMERIC(30,6), 
  "perimetro_cefalico" NUMERIC(30,6), 
  "percentilo_peso_edad" CHAR(1), 
  "percentilo_talla_edad" CHAR(1), 
  "percentilo_perim_cefalico_edad" CHAR(1), 
  "percentilo_peso_talla" CHAR(1), 
  "fecha_vacunacion" TIMESTAMP WITHOUT TIME ZONE, 
  "fechaobito" TIMESTAMP WITHOUT TIME ZONE, 
  "n_control_anual" INTEGER, 
  "idtrazadora" BIGINT, 
  "eliminado" CHAR(1), 
  "idbenefrecepcion" BIGINT, 
  "idprestacion" BIGINT, 
  "procesado" CHAR(1), 
  "estado_bd" CHAR(7), 
  "sexo" CHAR(1), 
  "municipio" INTEGER, 
  "discapacitado" CHAR(1), 
  "idantisarampionosa" INTEGER DEFAULT nextval('trazadoras.trz_antisarampionosa_idantisarampionosa_seq'::regclass) NOT NULL, 
  "prestacion_id" BIGINT NOT NULL, 
  CONSTRAINT "pk_antisarampionosa" PRIMARY KEY(idantisarampionosa)
) WITHOUT OIDS;

CREATE INDEX "fki_prestacion_id" ON "trazadoras"."trz_antisarampionosa"
  USING btree (prestacion_id);

ALTER TABLE "trazadoras"."trz_antisarampionosa"
  ADD CONSTRAINT "fk_prestacion_id" FOREIGN KEY ("prestacion_id")
    REFERENCES "facturacion"."prestacion"("id_prestacion")
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
    NOT DEFERRABLE;