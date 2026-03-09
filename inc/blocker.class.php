<?php
/**
 * -------------------------------------------------------------------------
 * FeedbackFirst — Avalie Antes
 * Plugin for GLPI 10
 * Copyright (C) 2024 Anderson Thales
 * https://github.com/andersonthales/feedbackfirst
 * -------------------------------------------------------------------------
 */

class PluginFeedbackfirstBlocker {

   public static function checkPendingSurveysBeforeAdd(Ticket $item) {
      $user_id = (int) Session::getLoginUserID();
      if ($user_id <= 0 || !self::isCurrentProfileBlocked()) return true;

      $pending = self::getPendingSurveys($user_id);
      if (empty($pending)) return true;

      $config = self::getConfig();
      Session::addMessageAfterRedirect(
         self::buildBlockMessage($pending, (bool) $config['show_pending_list']),
         true,
         ERROR
      );
      $item->input = false;
      return false;
   }

   public static function showWarningOnTicketForm(array $params): void {
      $item = $params['item'] ?? null;
      if (!($item instanceof Ticket) || (int) $item->getID() > 0) return;

      $user_id = (int) Session::getLoginUserID();
      if ($user_id <= 0 || !self::isCurrentProfileBlocked()) return;

      $pending = self::getPendingSurveys($user_id);
      if (empty($pending)) return;

      $config = self::getConfig();
      self::renderWarningBanner($pending, (bool) $config['show_pending_list']);
   }

   public static function getPendingSurveys(int $user_id): array {
      $config  = self::getConfig();
      $pending = [];

      if ((int) $config['block_on_native_survey'] === 1)
         $pending = array_merge($pending, self::getNativePending($user_id));

      if ((int) $config['block_on_plugin_survey'] === 1 && Plugin::isPluginActive('satisfaction'))
         $pending = array_merge($pending, self::getPluginPending($user_id));

      $unique = [];
      foreach ($pending as $row) $unique[$row['id']] = $row;
      return array_values($unique);
   }

   private static function getNativePending(int $user_id): array {
      global $DB;
      $uid = (int) $user_id;

      $sql = "
         SELECT t.id, t.name, ts.date_begin,
            COALESCE(NULLIF(e.inquest_duration, 0), NULLIF(eroot.inquest_duration, 0), 0) AS inquest_duration
         FROM glpi_ticketsatisfactions ts
         INNER JOIN glpi_tickets t ON t.id = ts.tickets_id
         INNER JOIN glpi_entities e ON e.id = t.entities_id
         LEFT JOIN glpi_entities eroot ON eroot.id = 0
         WHERE t.users_id_recipient = {$uid}
           AND t.is_deleted = 0
           AND ts.satisfaction IS NULL
           AND ts.date_answered IS NULL
         ORDER BY t.id DESC
      ";

      $res = $DB->query($sql);
      if (!$res) return [];

      $result = [];
      $now    = new DateTime();

      while ($row = $res->fetch_assoc()) {
         $d = (int) $row['inquest_duration'];
         if ($d > 0) {
            $exp = (new DateTime($row['date_begin']))->modify("+{$d} days");
            if ($exp < $now) continue;
         }
         $result[] = ['id' => (int) $row['id'], 'name' => $row['name'], 'source' => 'native'];
      }

      return $result;
   }

   private static function getPluginPending(int $user_id): array {
      global $DB;
      $uid = (int) $user_id;

      if (!$DB->tableExists('glpi_plugin_satisfaction_surveyanswers')) return [];

      $sql = "
         SELECT t.id, t.name, ts.date_begin,
            COALESCE(NULLIF(e.inquest_duration, 0), NULLIF(eroot.inquest_duration, 0), 0) AS inquest_duration
         FROM glpi_ticketsatisfactions ts
         INNER JOIN glpi_tickets t ON t.id = ts.tickets_id
         INNER JOIN glpi_entities e ON e.id = t.entities_id
         LEFT JOIN glpi_entities eroot ON eroot.id = 0
         WHERE t.users_id_recipient = {$uid}
           AND t.is_deleted = 0
           AND ts.satisfaction IS NULL
           AND ts.date_answered IS NULL
           AND NOT EXISTS (
               SELECT 1 FROM glpi_plugin_satisfaction_surveyanswers ans
               WHERE ans.tickets_id = t.id AND ans.users_id = {$uid}
           )
         ORDER BY t.id DESC
      ";

      $res = $DB->query($sql);
      if (!$res) return [];

      $result = [];
      $now    = new DateTime();

      while ($row = $res->fetch_assoc()) {
         $d = (int) $row['inquest_duration'];
         if ($d > 0) {
            $exp = (new DateTime($row['date_begin']))->modify("+{$d} days");
            if ($exp < $now) continue;
         }
         $result[] = ['id' => (int) $row['id'], 'name' => $row['name'], 'source' => 'plugin'];
      }

      return $result;
   }

   private static function isCurrentProfileBlocked(): bool {
      $config      = self::getConfig();
      $blocked_ids = json_decode($config['block_profiles'] ?? '[]', true);
      if (empty($blocked_ids) || !is_array($blocked_ids)) return true;
      return in_array((int) ($_SESSION['glpiactiveprofile']['id'] ?? 0), $blocked_ids, true);
   }

   public static function getConfig(): array {
      global $DB;
      static $cache = null;
      if ($cache !== null) return $cache;

      if (!$DB->tableExists('glpi_plugin_feedbackfirst_configs')) {
         return $cache = ['block_on_native_survey' => 1, 'block_on_plugin_survey' => 1, 'show_pending_list' => 1, 'block_profiles' => null];
      }

      $iter  = $DB->request(['FROM' => 'glpi_plugin_feedbackfirst_configs', 'LIMIT' => 1]);
      return $cache = $iter->count() > 0
         ? $iter->current()
         : ['block_on_native_survey' => 1, 'block_on_plugin_survey' => 1, 'show_pending_list' => 1, 'block_profiles' => null];
   }

   public static function saveConfig(array $data): bool {
      global $DB;
      return (bool) $DB->update(
         'glpi_plugin_feedbackfirst_configs',
         [
            'block_on_native_survey' => (int) ($data['block_on_native_survey'] ?? 0),
            'block_on_plugin_survey' => (int) ($data['block_on_plugin_survey'] ?? 0),
            'show_pending_list'      => (int) ($data['show_pending_list'] ?? 0),
            'block_profiles'         => !empty($data['block_profiles'])
               ? json_encode(array_map('intval', (array) $data['block_profiles']))
               : null,
            'date_mod'               => date('Y-m-d H:i:s'),
         ],
         ['id' => 1]
      );
   }

   private static function buildBlockMessage(array $pending, bool $show_list): string {
      $count = count($pending);
      $msg   = $count === 1
         ? 'Você possui 1 pesquisa de satisfação pendente. Responda-a antes de abrir um novo chamado.'
         : "Você possui {$count} pesquisas de satisfação pendentes. Responda-as antes de abrir um novo chamado.";

      if ($show_list) {
         $items = '';
         foreach ($pending as $r) {
            $url    = htmlspecialchars(Ticket::getFormURLWithID($r['id']));
            $items .= '<li><a href="'.$url.'" target="_blank">#'.(int)$r['id'].' — '.htmlspecialchars($r['name']).'</a></li>';
         }
         $msg .= '<ul class="feedbackfirst-pending-list">'.$items.'</ul>';
      }

      return $msg;
   }

   private static function renderWarningBanner(array $pending, bool $show_list): void {
      $count = count($pending);
      $texto = $count === 1
         ? 'Você tem 1 pesquisa de satisfação não respondida. Responda-a para poder abrir novos chamados.'
         : "Você tem {$count} pesquisas de satisfação não respondidas. Responda-as para poder abrir novos chamados.";

      echo '<div class="feedbackfirst-banner" id="feedbackfirst-warning">';
      echo '<div class="feedbackfirst-banner__icon">⚠️</div>';
      echo '<div class="feedbackfirst-banner__content">';
      echo '<strong>Pesquisa de Satisfação Pendente</strong>';
      echo "<p>{$texto}</p>";

      if ($show_list) {
         echo '<ul class="feedbackfirst-banner__list">';
         foreach ($pending as $r) {
            $url = htmlspecialchars(Ticket::getFormURLWithID($r['id']));
            echo '<li><a href="'.$url.'" target="_blank">🔗 Chamado #'.(int)$r['id'].' — '.htmlspecialchars($r['name']).'</a></li>';
         }
         echo '</ul>';
      }

      echo '</div></div>';
   }
}
