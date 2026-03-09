CREATE TABLE IF NOT EXISTS `glpi_plugin_feedbackfirst_configs` (
   `id`                     INT(11)    NOT NULL AUTO_INCREMENT,
   `block_on_native_survey` TINYINT(1) NOT NULL DEFAULT 1,
   `block_on_plugin_survey` TINYINT(1) NOT NULL DEFAULT 1,
   `show_pending_list`      TINYINT(1) NOT NULL DEFAULT 1,
   `block_profiles`         TEXT           NULL DEFAULT NULL,
   `date_mod`               DATETIME       NULL DEFAULT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `glpi_plugin_feedbackfirst_configs`
   (`block_on_native_survey`, `block_on_plugin_survey`, `show_pending_list`, `block_profiles`, `date_mod`)
VALUES (1, 1, 1, NULL, NOW());
