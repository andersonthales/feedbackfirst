# FeedbackFirst — Avalie Antes

> Plugin para GLPI 10 que bloqueia a abertura de novos chamados por usuários que possuem pesquisas de satisfação pendentes e não respondidas.

**Autor:** Anderson Thales  
**Licença:** GPLv2+  
**Versão:** 1.0.0  
**Compatibilidade:** GLPI 10.0 – 11.0

---

## 📋 Funcionalidades

- **Bloqueio no backend** via hook `pre_item_add` — impede o salvamento mesmo com JavaScript desativado
- **Banner de aviso** no formulário de abertura, com links diretos para os chamados pendentes
- **Respeita o prazo** da pesquisa (`date_begin + inquest_duration`) — não bloqueia pesquisas expiradas
- **Dupla fonte:** detecta pesquisas nativas do GLPI e pesquisas do plugin [Satisfaction](https://github.com/pluginsGLPI/satisfaction)
- **Configuração por perfil** — escolha quais perfis serão bloqueados
- **Totalmente em português** (pt_BR)

---

## ⚙️ Requisitos

| Componente | Versão |
|---|---|
| GLPI | >= 10.0 |
| PHP | >= 8.0 |
| Plugin Satisfaction *(opcional)* | >= 1.6 |

---

## 🚀 Instalação

1. Copie a pasta `feedbackfirst` para o diretório de plugins do GLPI:
   ```
   /var/www/html/glpi/plugins/feedbackfirst/
   ```
2. Acesse **Configuração → Plugins**
3. Localize **Avalie Antes** e clique em **Instalar** → **Ativar**

---

## 🔧 Configuração

Acesse **Configuração → Plugins → Avalie Antes**.

| Opção | Descrição |
|---|---|
| Bloquear por pesquisa nativa do GLPI | Verifica `glpi_ticketsatisfactions` |
| Bloquear por pesquisa do plugin Satisfaction | Verifica respostas do plugin (requer plugin ativo) |
| Listar chamados pendentes | Exibe links na mensagem de bloqueio |
| Perfis Bloqueados | Restringe o bloqueio a perfis específicos (vazio = todos) |

---

## 🗂️ Estrutura

```
feedbackfirst/
├── setup.php                    # Hooks e definições
├── hook.php                     # Install / Uninstall
├── index.php                    # Proteção de diretório
├── inc/
│   ├── blocker.class.php        # Lógica principal de bloqueio
│   └── config.class.php         # Tela de configuração
├── front/
│   └── config.php               # Controller da configuração
├── install/sql/
│   └── empty-1.0.0.sql          # Schema inicial
├── css/feedbackfirst.css        # Estilos
└── js/feedbackfirst.js          # Comportamento client-side
```

---

## 🛡️ Como funciona

```
Usuário tenta abrir chamado
        ↓
[hook pre_item_add] → PluginFeedbackfirstBlocker::checkPendingSurveysBeforeAdd()
        ↓
Busca pesquisas: satisfaction IS NULL AND date_answered IS NULL
                 AND (date_begin + inquest_duration) > agora
        ↓
Há pendências?
   NÃO → Chamado criado normalmente ✅
   SIM → Bloqueia + exibe mensagem com links ❌
```

---

## 📝 Licença

GPLv2+. Veja o arquivo `LICENSE` para detalhes.
