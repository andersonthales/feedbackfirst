<?php
/**
 * FeedbackFirst — Avalie Antes
 * Copyright (C) 2024 Anderson Thales
 */

class PluginFeedbackfirstConfig extends CommonGLPI {

   public static function getTypeName($nb = 0) {
      return __('Avalie Antes', 'feedbackfirst');
   }

   public static function showConfigForm(): void {
      $config = PluginFeedbackfirstBlocker::getConfig();

      echo '<div class="feedbackfirst-config-wrapper">';
      echo '<div class="feedbackfirst-config-header">';
      echo '<h2>' . __('Configurações — Avalie Antes', 'feedbackfirst') . '</h2>';
      echo '<p class="feedbackfirst-config-desc">Controle quando e para quem o bloqueio de abertura de chamados será aplicado.</p>';
      echo '</div>';

      $form_action = Plugin::getWebDir('feedbackfirst') . '/front/config.php';
      echo '<form method="post" action="' . htmlspecialchars($form_action) . '">';
      echo '<input type="hidden" name="_glpi_csrf_token" value="' . Session::getNewCSRFToken() . '">';
      echo '<input type="hidden" name="action" value="save">';

      echo '<div class="feedbackfirst-config-grid">';

      // Fontes de pesquisa
      echo '<div class="feedbackfirst-config-section">';
      echo '<h3>FONTES DE PESQUISA</h3>';

      echo '<label class="feedbackfirst-toggle">';
      echo '<input type="checkbox" name="block_on_native_survey" value="1"' . ($config['block_on_native_survey'] ? ' checked' : '') . '>';
      echo '<span class="feedbackfirst-toggle__slider"></span>';
      echo '<span class="feedbackfirst-toggle__label">Bloquear por pesquisa nativa do GLPI</span>';
      echo '</label>';

      echo '<label class="feedbackfirst-toggle">';
      echo '<input type="checkbox" name="block_on_plugin_survey" value="1"' . ($config['block_on_plugin_survey'] ? ' checked' : '');
      if (!Plugin::isPluginActive('satisfaction')) echo ' disabled title="Plugin satisfaction não está ativo"';
      echo '>';
      echo '<span class="feedbackfirst-toggle__slider"></span>';
      echo '<span class="feedbackfirst-toggle__label">Bloquear por pesquisa do plugin Satisfaction';
      if (!Plugin::isPluginActive('satisfaction')) echo ' <em>(plugin não instalado)</em>';
      echo '</span>';
      echo '</label>';
      echo '</div>';

      // Exibição
      echo '<div class="feedbackfirst-config-section">';
      echo '<h3>EXIBIÇÃO</h3>';
      echo '<label class="feedbackfirst-toggle">';
      echo '<input type="checkbox" name="show_pending_list" value="1"' . ($config['show_pending_list'] ? ' checked' : '') . '>';
      echo '<span class="feedbackfirst-toggle__slider"></span>';
      echo '<span class="feedbackfirst-toggle__label">Listar chamados pendentes na mensagem de bloqueio</span>';
      echo '</label>';
      echo '</div>';

      // Perfis
      echo '<div class="feedbackfirst-config-section feedbackfirst-config-section--full">';
      echo '<h3>PERFIS BLOQUEADOS</h3>';
      echo '<p class="feedbackfirst-config-hint">Selecione os perfis que sofrerão o bloqueio. Deixe todos desmarcados para bloquear todos os perfis.</p>';

      $profiles    = getAllDataFromTable('glpi_profiles');
      $blocked_ids = json_decode($config['block_profiles'] ?? '[]', true) ?? [];

      echo '<div class="feedbackfirst-profiles-grid">';
      foreach ($profiles as $profile) {
         $checked = empty($blocked_ids) || in_array((int) $profile['id'], $blocked_ids, true);
         echo '<label class="feedbackfirst-profile-check">';
         echo '<input type="checkbox" name="block_profiles[]" value="' . (int)$profile['id'] . '"' . ($checked ? ' checked' : '') . '>';
         echo htmlspecialchars($profile['name']);
         echo '</label>';
      }
      echo '</div>';
      echo '</div>';

      echo '</div>';

      echo '<div class="feedbackfirst-config-actions">';
      echo '<button type="submit" class="feedbackfirst-btn-save">💾 Salvar Configurações</button>';
      echo '</div>';

      echo '</form>';
      echo '</div>';
   }
}
