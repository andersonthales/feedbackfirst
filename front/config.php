<?php
/**
 * FeedbackFirst — front/config.php
 * Copyright (C) 2024 Anderson Thales
 */

include('../../../inc/includes.php');

Session::checkLoginUser();
Session::checkRight('config', UPDATE);

include_once(Plugin::getPhpDir('feedbackfirst') . '/inc/blocker.class.php');
include_once(Plugin::getPhpDir('feedbackfirst') . '/inc/config.class.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
   PluginFeedbackfirstBlocker::saveConfig($_POST);
   Session::addMessageAfterRedirect('Configurações salvas com sucesso.', false, INFO);
   Html::redirect(Plugin::getWebDir('feedbackfirst') . '/front/config.php');
   exit();
}

Html::header('Avalie Antes — Configurações', $_SERVER['PHP_SELF'], 'config', 'PluginFeedbackfirstConfig');
PluginFeedbackfirstConfig::showConfigForm();
Html::footer();
