# Changelog

## [1.0.1] - 2026-10-08

### Corrigido
- Pendências passam a ser buscadas pelo **requerente** do chamado (`glpi_tickets_users`, tipo 1), não por quem o digitou (`users_id_recipient`)
- Consulta ao plugin Satisfaction usava colunas inexistentes (`tickets_id`, `users_id`); agora usa `ticketsatisfactions_id`
- Tela de perfis: desmarcar todos agora significa "ninguém é bloqueado", e não "todos"
- Compatibilidade declarada limitada ao GLPI 10 (`max` = 10.0.99)

## [1.0.0] - 2026-03-09

### Lançamento inicial
- Bloqueio da abertura de chamados com pesquisa de satisfação pendente (hook `pre_item_add`)
- Aviso no formulário com links para os chamados pendentes
- Respeito ao prazo da pesquisa (`date_begin` + `inquest_duration` da entidade)
- Suporte às pesquisas nativas do GLPI e do plugin Satisfaction
- Bloqueio por perfil
