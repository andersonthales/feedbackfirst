# Avalie Antes (feedbackfirst) — Plugin para GLPI 10

Impede que um usuário abra um **novo chamado** enquanto tiver **pesquisas de satisfação pendentes**. O formulário de abertura mostra um aviso com links para os chamados que precisam ser avaliados, e o servidor recusa a criação mesmo que o aviso seja ignorado.

| | |
|---|---|
| **Versão** | 1.0.0 |
| **GLPI** | 10.0.x |
| **PHP** | 8.0 ou superior |
| **Opcional** | Plugin [Satisfaction](https://github.com/pluginsGLPI/satisfaction) |
| **Licença** | GPLv2+ |

---

## Como funciona

```
Usuário tenta abrir um chamado
        │
        ▼
Há pesquisa de satisfação pendente, dentro do prazo?
        │
   não ─┴─ sim
   │        │
   ▼        ▼
 Chamado   Chamado recusado (hook pre_item_add)
 criado    + mensagem com links para as pesquisas
```

1. **Aviso no formulário.** Ao abrir o formulário de novo chamado, o usuário vê um banner com a quantidade de pesquisas pendentes e, se configurado, links para cada chamado. O botão de envio fica bloqueado no navegador enquanto o aviso existir.
2. **Bloqueio no servidor.** O hook `pre_item_add` de `Ticket` recusa a criação, então o bloqueio vale mesmo com JavaScript desativado.

### O que conta como pesquisa pendente

Uma linha em `glpi_ticketsatisfactions` que:

- ainda não foi respondida (`satisfaction` e `date_answered` vazios);
- pertence a um chamado não excluído;
- **ainda está no prazo**: `date_begin` + *duração da pesquisa* da entidade (`inquest_duration`). Se a entidade não define o prazo, vale o da entidade raiz; sem prazo nenhum, a pesquisa nunca expira.

Com o plugin **Satisfaction** ativo, também são consideradas as pesquisas dele que ainda não têm resposta.

## Instalação

1. Copie a pasta para `plugins/feedbackfirst` no GLPI:
   ```bash
   cd /var/www/html/glpi/plugins
   git clone https://github.com/andersonthales/feedbackfirst.git
   chown -R www-data:www-data feedbackfirst
   ```
2. Em **Configurar → Plugins**, clique em **Instalar** e depois em **Ativar** no plugin **Avalie Antes**.

A instalação cria a tabela `glpi_plugin_feedbackfirst_configs` com a configuração padrão (tudo ligado, todos os perfis bloqueados).

## Configuração

Em **Configurar → Plugins**, clique no nome **Avalie Antes** (requer o direito *Configuração → Atualizar*).

| Opção | Efeito |
|---|---|
| Bloquear por pesquisa nativa do GLPI | Considera as pesquisas de `glpi_ticketsatisfactions` |
| Bloquear por pesquisa do plugin Satisfaction | Considera também as pesquisas do plugin Satisfaction (só habilitada com o plugin ativo) |
| Listar chamados pendentes | Mostra os links para cada chamado no aviso e na mensagem de bloqueio |
| Perfis bloqueados | Quais perfis sofrem o bloqueio |

### Atenção aos perfis

Com a configuração padrão, **todos os perfis** são bloqueados, inclusive técnicos e Super-Admin. Um técnico que tenha pesquisas pendentes não consegue abrir chamados nem em nome de outras pessoas. Na maioria dos casos, deixe marcado **só o perfil Self-Service**.

## Limitações conhecidas

- O bloqueio considera **quem está logado**, e a pesquisa é procurada pelo campo *Quem abriu o chamado* (`users_id_recipient`). Quando um técnico abre o chamado em nome do usuário, a pendência fica com o técnico, não com o requerente.
- Na tela de perfis, **desmarcar todos** equivale a **bloquear todos**.
- A consulta ao plugin Satisfaction usa colunas que não existem na tabela dele (`tickets_id`, `users_id`). Ela falha e é ignorada, deixando um erro de SQL no log.
- Compatível somente com **GLPI 10**.

## Desinstalação

Remove a tabela de configuração e os registros de histórico do plugin (`glpi_logs` com `itemtype = 'PluginFeedbackfirstConfig'`). Pesquisas e chamados não são afetados.

## Estrutura

```
feedbackfirst/
├── setup.php                  # Registro do plugin e hooks
├── hook.php                   # Instalação e desinstalação
├── index.php                  # Impede listagem do diretório
├── inc/
│   ├── blocker.class.php      # Busca de pendências, bloqueio e aviso
│   └── config.class.php       # Tela de configuração
├── front/config.php           # Controlador da configuração
├── install/sql/empty-1.0.0.sql
├── css/feedbackfirst.css
└── js/feedbackfirst.js        # Bloqueia o envio enquanto o aviso estiver na tela
```

## Changelog

Veja [CHANGELOG.md](CHANGELOG.md).

## Licença

[GPLv2 ou posterior](LICENSE).

## Autor

**Anderson Thales** — [@andersonthales](https://github.com/andersonthales)
