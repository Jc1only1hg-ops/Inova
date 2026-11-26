<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTriggersAndViews extends Migration
{
    public function up()
    {
        // ---------------------------------------------------------------------
        // TRIGGERS
        // ---------------------------------------------------------------------

        // 1. trg_app_insert
        $this->db->query("DROP TRIGGER IF EXISTS `trg_app_insert`");
        $this->db->query("
            CREATE TRIGGER `trg_app_insert`
            AFTER INSERT ON `aplicaciones`
            FOR EACH ROW
            BEGIN
                UPDATE `ofertas`
                SET `aplicantes` = `aplicantes` + 1
                WHERE `id` = NEW.`oferta_id`;
            END
        ");

        // 2. trg_app_delete
        $this->db->query("DROP TRIGGER IF EXISTS `trg_app_delete`");
        $this->db->query("
            CREATE TRIGGER `trg_app_delete`
            AFTER DELETE ON `aplicaciones`
            FOR EACH ROW
            BEGIN
                UPDATE `ofertas`
                SET `aplicantes` = GREATEST(`aplicantes` - 1, 0)
                WHERE `id` = OLD.`oferta_id`;
            END
        ");

        // 3. trg_msg_insert
        $this->db->query("DROP TRIGGER IF EXISTS `trg_msg_insert`");
        $this->db->query("
            CREATE TRIGGER `trg_msg_insert`
            AFTER INSERT ON `mensajes`
            FOR EACH ROW
            BEGIN
                IF NEW.`eliminado_en` IS NULL THEN
                    UPDATE `conversaciones`
                    SET `ultimo_mensaje`       = LEFT(NEW.`cuerpo`, 255),
                        `ultimo_mensaje_fecha` = NEW.`fecha_envio`
                    WHERE `id` = NEW.`conversacion_id`;
                END IF;
            END
        ");

        // 4. trg_msg_update
        $this->db->query("DROP TRIGGER IF EXISTS `trg_msg_update`");
        $this->db->query("
            CREATE TRIGGER `trg_msg_update`
            AFTER UPDATE ON `mensajes`
            FOR EACH ROW
            BEGIN
                DECLARE v_ultimo_cuerpo TEXT DEFAULT NULL;
                DECLARE v_ultima_fecha DATETIME DEFAULT NULL;
                DECLARE v_es_ultimo TINYINT DEFAULT 0;
                DECLARE CONTINUE HANDLER FOR NOT FOUND
                    BEGIN
                        SET v_ultimo_cuerpo = NULL;
                        SET v_ultima_fecha = NULL;
                    END;

                SELECT (c.`ultimo_mensaje_fecha` <=> OLD.`fecha_envio`)
                INTO v_es_ultimo
                FROM `conversaciones` c
                WHERE c.`id` = NEW.`conversacion_id`;

                IF NEW.`eliminado_en` IS NOT NULL
                   AND OLD.`eliminado_en` IS NULL
                   AND v_es_ultimo = 1 THEN

                    SELECT `cuerpo`, `fecha_envio`
                    INTO v_ultimo_cuerpo, v_ultima_fecha
                    FROM `mensajes`
                    WHERE `conversacion_id` = NEW.`conversacion_id`
                      AND `eliminado_en` IS NULL
                      AND `id` <> NEW.`id`
                    ORDER BY `fecha_envio` DESC
                    LIMIT 1;

                    UPDATE `conversaciones`
                    SET `ultimo_mensaje`       = LEFT(v_ultimo_cuerpo, 255),
                        `ultimo_mensaje_fecha` = v_ultima_fecha
                    WHERE `id` = NEW.`conversacion_id`;

                ELSEIF NOT (NEW.`cuerpo` <=> OLD.`cuerpo`)
                       AND NEW.`eliminado_en` IS NULL
                       AND v_es_ultimo = 1 THEN

                    UPDATE `conversaciones`
                    SET `ultimo_mensaje`       = LEFT(NEW.`cuerpo`, 255),
                        `ultimo_mensaje_fecha` = NEW.`fecha_envio`
                    WHERE `id` = NEW.`conversacion_id`;
                END IF;
            END
        ");

        // 5. trg_msg_delete
        $this->db->query("DROP TRIGGER IF EXISTS `trg_msg_delete`");
        $this->db->query("
            CREATE TRIGGER `trg_msg_delete`
            AFTER DELETE ON `mensajes`
            FOR EACH ROW
            BEGIN
                DECLARE v_ultimo_cuerpo TEXT DEFAULT NULL;
                DECLARE v_ultima_fecha DATETIME DEFAULT NULL;
                DECLARE CONTINUE HANDLER FOR NOT FOUND
                    BEGIN
                        SET v_ultimo_cuerpo = NULL;
                        SET v_ultima_fecha = NULL;
                    END;

                SELECT `cuerpo`, `fecha_envio`
                INTO v_ultimo_cuerpo, v_ultima_fecha
                FROM `mensajes`
                WHERE `conversacion_id` = OLD.`conversacion_id`
                  AND `eliminado_en` IS NULL
                ORDER BY `fecha_envio` DESC
                LIMIT 1;

                UPDATE `conversaciones`
                SET `ultimo_mensaje`       = LEFT(v_ultimo_cuerpo, 255),
                    `ultimo_mensaje_fecha` = v_ultima_fecha
                WHERE `id` = OLD.`conversacion_id`;
            END
        ");

        // 6. trg_conv_normalizar
        $this->db->query("DROP TRIGGER IF EXISTS `trg_conv_normalizar`");
        $this->db->query("
            CREATE TRIGGER `trg_conv_normalizar`
            BEFORE INSERT ON `conversaciones`
            FOR EACH ROW
            BEGIN
                DECLARE v_temp INT;
                IF NEW.`usuario1_id` > NEW.`usuario2_id` THEN
                    SET v_temp            = NEW.`usuario1_id`;
                    SET NEW.`usuario1_id` = NEW.`usuario2_id`;
                    SET NEW.`usuario2_id` = v_temp;
                END IF;
            END
        ");

        // 7. trg_oferta_validar_publicador
        $this->db->query("DROP TRIGGER IF EXISTS `trg_oferta_validar_publicador`");
        $this->db->query("
            CREATE TRIGGER `trg_oferta_validar_publicador`
            BEFORE INSERT ON `ofertas`
            FOR EACH ROW
            BEGIN
                DECLARE v_adscrito INT DEFAULT 0;
                DECLARE v_tipo VARCHAR(20);

                SELECT `tipo` INTO v_tipo FROM `organismos` WHERE `id` = NEW.`organismo_id`;
                IF v_tipo IS NULL THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'El organismo indicado no existe';
                END IF;

                SELECT COUNT(*) INTO v_adscrito
                FROM `participante_organismo`
                WHERE `usuario_id` = NEW.`publicado_por`
                  AND `organismo_id` = NEW.`organismo_id`
                  AND `activo` = 1;

                IF v_adscrito = 0 THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'El usuario que publica no pertenece al organismo';
                END IF;
            END
        ");

        // 8. trg_oferta_validar_publicador_update
        $this->db->query("DROP TRIGGER IF EXISTS `trg_oferta_validar_publicador_update`");
        $this->db->query("
            CREATE TRIGGER `trg_oferta_validar_publicador_update`
            BEFORE UPDATE ON `ofertas`
            FOR EACH ROW
            BEGIN
                DECLARE v_adscrito INT DEFAULT 0;

                IF NOT (NEW.`organismo_id` <=> OLD.`organismo_id`)
                   OR NOT (NEW.`publicado_por` <=> OLD.`publicado_por`) THEN

                    SELECT COUNT(*) INTO v_adscrito
                    FROM `participante_organismo`
                    WHERE `usuario_id` = NEW.`publicado_por`
                      AND `organismo_id` = NEW.`organismo_id`
                      AND `activo` = 1;

                    IF v_adscrito = 0 THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'El usuario que publica no pertenece al organismo';
                    END IF;
                END IF;
            END
        ");

        // 9. trg_org_tipo_coherente
        $this->db->query("DROP TRIGGER IF EXISTS `trg_org_tipo_coherente`");
        $this->db->query("
            CREATE TRIGGER `trg_org_tipo_coherente`
            BEFORE INSERT ON `organismos`
            FOR EACH ROW
            BEGIN
                IF NEW.`tipo` = 'empresa' AND NEW.`rif` IS NULL THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Una empresa debe tener RIF';
                END IF;
                IF NEW.`tipo` = 'universidad' AND NEW.`siglas` IS NULL THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Una universidad debe tener siglas';
                END IF;
                IF NEW.`tipo` = 'empresa' AND NEW.`ranking_uni` IS NOT NULL THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'ranking_uni solo aplica a universidades';
                END IF;
            END
        ");

        // 10. trg_perfil_uni_coherente
        $this->db->query("DROP TRIGGER IF EXISTS `trg_perfil_uni_coherente`");
        $this->db->query("
            CREATE TRIGGER `trg_perfil_uni_coherente`
            BEFORE INSERT ON `perfil_estudiantes`
            FOR EACH ROW
            BEGIN
                DECLARE v_tipo VARCHAR(20);
                IF NEW.`organismo_id` IS NOT NULL THEN
                    SELECT `tipo` INTO v_tipo FROM `organismos` WHERE `id` = NEW.`organismo_id`;
                    IF v_tipo IS NULL OR v_tipo <> 'universidad' THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'El organismo de un estudiante debe ser una universidad';
                    END IF;
                END IF;
            END
        ");

        // 11. trg_perfil_uni_coherente_update
        $this->db->query("DROP TRIGGER IF EXISTS `trg_perfil_uni_coherente_update`");
        $this->db->query("
            CREATE TRIGGER `trg_perfil_uni_coherente_update`
            BEFORE UPDATE ON `perfil_estudiantes`
            FOR EACH ROW
            BEGIN
                DECLARE v_tipo VARCHAR(20);
                IF NOT (NEW.`organismo_id` <=> OLD.`organismo_id`)
                   AND NEW.`organismo_id` IS NOT NULL THEN
                    SELECT `tipo` INTO v_tipo FROM `organismos` WHERE `id` = NEW.`organismo_id`;
                    IF v_tipo IS NULL OR v_tipo <> 'universidad' THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'El organismo de un estudiante debe ser una universidad';
                    END IF;
                END IF;
            END
        ");

        // ---------------------------------------------------------------------
        // VISTAS
        // ---------------------------------------------------------------------

        // 1. v_ofertas_completas
        $this->db->query("DROP VIEW IF EXISTS `v_ofertas_completas`");
        $this->db->query("
            CREATE VIEW `v_ofertas_completas` AS
            SELECT
                o.`id`,
                o.`uuid`,
                o.`titulo`,
                o.`descripcion`,
                o.`ubicacion`,
                o.`modalidad`,
                o.`tipo_contrato`,
                o.`salario_min`,
                o.`salario_max`,
                o.`salario_moneda`,
                o.`categoria`,
                o.`nivel_experiencia`,
                o.`estado`,
                o.`vistas`,
                o.`aplicantes`,
                o.`fecha_creacion`,
                org.`id`         AS organismo_id,
                org.`tipo`       AS organismo_tipo,
                org.`nombre`     AS organismo_nombre,
                org.`siglas`     AS organismo_siglas,
                org.`rif`        AS organismo_rif,
                org.`logo`       AS organismo_logo,
                org.`ubicacion`  AS organismo_ubicacion,
                org.`verificado` AS organismo_verificado,
                u.`id`           AS publicado_por_id,
                u.`correo`       AS publicado_por_correo,
                u.`nombre`       AS publicado_por_nombre,
                u.`apellido`     AS publicado_por_apellido,
                u.`avatar`       AS publicado_por_avatar
            FROM `ofertas` o
            JOIN `organismos` org ON org.`id` = o.`organismo_id`
            JOIN `usuarios`   u   ON u.`id`   = o.`publicado_por`
        ");

        // 2. v_conversaciones_full
        $this->db->query("DROP VIEW IF EXISTS `v_conversaciones_full`");
        $this->db->query("
            CREATE VIEW `v_conversaciones_full` AS
            SELECT
                c.`id`,
                c.`tipo`,
                c.`estado`,
                c.`oferta_id`,
                c.`ultimo_mensaje`,
                c.`ultimo_mensaje_fecha`,
                c.`fecha_creacion`,
                c.`usuario1_id`,
                c.`usuario2_id`,
                u1.`correo` AS u1_correo,
                u1.`avatar` AS u1_avatar,
                u2.`correo` AS u2_correo,
                u2.`avatar` AS u2_avatar,
                o.`titulo`  AS oferta_titulo,
                (SELECT COUNT(*) FROM `mensajes` m
                 WHERE m.`conversacion_id` = c.`id`
                   AND m.`leido_en` IS NULL) AS no_leidos
            FROM `conversaciones` c
            JOIN `usuarios` u1 ON u1.`id` = c.`usuario1_id`
            JOIN `usuarios` u2 ON u2.`id` = c.`usuario2_id`
            LEFT JOIN `ofertas` o ON o.`id` = c.`oferta_id`
        ");

        // 3. v_conversaciones_contadores
        $this->db->query("DROP VIEW IF EXISTS `v_conversaciones_contadores`");
        $this->db->query("
            CREATE VIEW `v_conversaciones_contadores` AS
            SELECT
                c.`id` AS conversacion_id,
                c.`usuario1_id` AS usuario_a,
                c.`usuario2_id` AS usuario_b,
                c.`tipo`,
                c.`estado`,
                c.`oferta_id`,
                c.`ultimo_mensaje`,
                c.`ultimo_mensaje_fecha`,
                (SELECT COUNT(*) FROM `mensajes` m
                 WHERE m.`conversacion_id` = c.`id`
                   AND m.`leido_en` IS NULL
                   AND m.`remitente_id` <> c.`usuario1_id`) AS no_leidos_para_a,
                (SELECT COUNT(*) FROM `mensajes` m
                 WHERE m.`conversacion_id` = c.`id`
                   AND m.`leido_en` IS NULL
                   AND m.`remitente_id` <> c.`usuario2_id`) AS no_leidos_para_b
            FROM `conversaciones` c
        ");

        // 4. v_participante_organismos
        $this->db->query("DROP VIEW IF EXISTS `v_participante_organismos`");
        $this->db->query("
            CREATE VIEW `v_participante_organismos` AS
            SELECT
                po.`id`                AS adscripcion_id,
                po.`usuario_id`,
                u.`correo`             AS usuario_correo,
                u.`rol`                AS usuario_rol,
                po.`organismo_id`,
                org.`tipo`             AS organismo_tipo,
                org.`nombre`           AS organismo_nombre,
                org.`siglas`           AS organismo_siglas,
                org.`logo`             AS organismo_logo,
                org.`verificado`       AS organismo_verificado,
                po.`rol_en_organismo`,
                po.`activo`,
                po.`fecha_adscripcion`
            FROM `participante_organismo` po
            JOIN `usuarios`   u   ON u.`id`   = po.`usuario_id`
            JOIN `organismos` org ON org.`id` = po.`organismo_id`
        ");
    }

    public function down()
    {
        // Dropping views
        $this->db->query("DROP VIEW IF EXISTS `v_participante_organismos`");
        $this->db->query("DROP VIEW IF EXISTS `v_conversaciones_contadores`");
        $this->db->query("DROP VIEW IF EXISTS `v_conversaciones_full`");
        $this->db->query("DROP VIEW IF EXISTS `v_ofertas_completas`");

        // Dropping triggers
        $this->db->query("DROP TRIGGER IF EXISTS `trg_perfil_uni_coherente_update`");
        $this->db->query("DROP TRIGGER IF EXISTS `trg_perfil_uni_coherente`");
        $this->db->query("DROP TRIGGER IF EXISTS `trg_org_tipo_coherente`");
        $this->db->query("DROP TRIGGER IF EXISTS `trg_oferta_validar_publicador_update`");
        $this->db->query("DROP TRIGGER IF EXISTS `trg_oferta_validar_publicador`");
        $this->db->query("DROP TRIGGER IF EXISTS `trg_conv_normalizar`");
        $this->db->query("DROP TRIGGER IF EXISTS `trg_msg_delete`");
        $this->db->query("DROP TRIGGER IF EXISTS `trg_msg_update`");
        $this->db->query("DROP TRIGGER IF EXISTS `trg_msg_insert`");
        $this->db->query("DROP TRIGGER IF EXISTS `trg_app_delete`");
        $this->db->query("DROP TRIGGER IF EXISTS `trg_app_insert`");
    }
}
