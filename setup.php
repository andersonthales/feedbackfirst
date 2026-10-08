<?php
/**
 * -------------------------------------------------------------------------
 * FeedbackFirst plugin for GLPI
 * Copyright (C) 2024 Anderson Thales
 *
 * https://github.com/andersonthales/feedbackfirst
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of FeedbackFirst.
 *
 * FeedbackFirst is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * -------------------------------------------------------------------------
 */

define('PLUGIN_FEEDBACKFIRST_VERSION', '1.0.1');
define('PLUGIN_FEEDBACKFIRST_MIN_GLPI', '10.0');
define('PLUGIN_FEEDBACKFIRST_MAX_GLPI', '10.0.99');

$_feedbackfirst_inc = dirname(__FILE__) . '/inc/blocker.class.php';
if (file_exists($_feedbackfirst_inc)) {
   include_once($_feedbackfirst_inc);
}

function plugin_init_feedbackfirst() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['csrf_compliant']['feedbackfirst'] = true;

   if (!Plugin::isPluginActive('feedbackfirst')) {
      return;
   }

   include_once(Plugin::getPhpDir('feedbackfirst') . '/inc/blocker.class.php');
   include_once(Plugin::getPhpDir('feedbackfirst') . '/inc/config.class.php');

   $PLUGIN_HOOKS['pre_item_add']['feedbackfirst'] = [
      'Ticket' => ['PluginFeedbackfirstBlocker', 'checkPendingSurveysBeforeAdd'],
   ];

   $PLUGIN_HOOKS['pre_item_form']['feedbackfirst'] = [
      'PluginFeedbackfirstBlocker', 'showWarningOnTicketForm',
   ];

   $PLUGIN_HOOKS['add_css']['feedbackfirst']        = ['css/feedbackfirst.css'];
   $PLUGIN_HOOKS['add_javascript']['feedbackfirst'] = ['js/feedbackfirst.js'];

   if (Session::getLoginUserID() && Session::haveRight('config', UPDATE)) {
      $PLUGIN_HOOKS['config_page']['feedbackfirst'] = 'front/config.php';
   }
}

function plugin_version_feedbackfirst() {
   return [
      'name'         => __('Avalie Antes', 'feedbackfirst'),
      'version'      => PLUGIN_FEEDBACKFIRST_VERSION,
      'author'       => 'Anderson Thales',
      'license'      => 'GPLv2+',
      'homepage'     => 'https://github.com/andersonthales/feedbackfirst',
      'requirements' => [
         'glpi' => [
            'min' => PLUGIN_FEEDBACKFIRST_MIN_GLPI,
            'max' => PLUGIN_FEEDBACKFIRST_MAX_GLPI,
         ],
      ],
   ];
}

function plugin_feedbackfirst_check_prerequisites() {
   if (version_compare(GLPI_VERSION, PLUGIN_FEEDBACKFIRST_MIN_GLPI, 'lt')) {
      echo 'Este plugin requer GLPI >= ' . PLUGIN_FEEDBACKFIRST_MIN_GLPI;
      return false;
   }
   return true;
}

function plugin_feedbackfirst_check_config() {
   return true;
}
