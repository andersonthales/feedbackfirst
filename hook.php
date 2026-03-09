<?php
/**
 * -------------------------------------------------------------------------
 * FeedbackFirst plugin for GLPI
 * Copyright (C) 2024 Anderson Thales
 * -------------------------------------------------------------------------
 */

function plugin_feedbackfirst_install() {
   global $DB;

   if (!$DB->tableExists('glpi_plugin_feedbackfirst_configs')) {
      $DB->queryOrDie(
         "CREATE TABLE `glpi_plugin_feedbackfirst_configs` (
            `id`                     INT(11)      NOT NULL AUTO_INCREMENT,
            `block_on_native_survey` TINYINT(1)   NOT NULL DEFAULT 1,
            `block_on_plugin_survey` TINYINT(1)   NOT NULL DEFAULT 1,
            `show_pending_list`      TINYINT(1)   NOT NULL DEFAULT 1,
            `block_profiles`         TEXT             NULL DEFAULT NULL,
            `date_mod`               DATETIME         NULL DEFAULT NULL,
            PRIMARY KEY (`id`)
         ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
         "Criando tabela glpi_plugin_feedbackfirst_configs"
      );

      $DB->insertOrDie(
         'glpi_plugin_feedbackfirst_configs',
         [
            'block_on_native_survey' => 1,
            'block_on_plugin_survey' => 1,
            'show_pending_list'      => 1,
            'block_profiles'         => null,
            'date_mod'               => date('Y-m-d H:i:s'),
         ],
         "Inserindo configuração padrão"
      );
   }

   return true;
}

function plugin_feedbackfirst_uninstall() {
   global $DB;

   $DB->queryOrDie(
      "DROP TABLE IF EXISTS `glpi_plugin_feedbackfirst_configs`;",
      "Removendo tabela glpi_plugin_feedbackfirst_configs"
   );

   $DB->queryOrDie(
      "DELETE FROM `glpi_logs` WHERE `itemtype` = 'PluginFeedbackfirstConfig';",
      "Removendo logs do plugin feedbackfirst"
   );

   return true;
}
