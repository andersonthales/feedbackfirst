/**
 * BlockSurvey Plugin — feedbackfirst.js
 * Comportamento client-side: fecha o banner e bloqueia o submit
 * do formulário de criação de chamado se houver aviso visível.
 */
(function () {
   'use strict';

   document.addEventListener('DOMContentLoaded', function () {
      const banner = document.getElementById('feedbackfirst-warning');
      if (!banner) return;

      // --- Bloqueia o botão de submit do formulário de criação ---
      // O GLPI usa o seletor #itil-object-container ou form[name="helpdesk"]
      const forms = document.querySelectorAll(
         'form[name="asset_item"], form[id*="ticket"], #itil-object-container form'
      );

      forms.forEach(function (form) {
         form.addEventListener('submit', function (e) {
            // Verifica se o banner ainda está visível
            if (document.getElementById('feedbackfirst-warning')) {
               e.preventDefault();
               e.stopImmediatePropagation();

               // Destaca o banner para chamar atenção
               banner.style.animation = 'none';
               banner.style.boxShadow = '0 0 0 3px #f59e0b';
               banner.scrollIntoView({ behavior: 'smooth', block: 'center' });

               setTimeout(function () {
                  banner.style.boxShadow = '';
               }, 1500);
            }
         });
      });

      // --- Botão de fechar o banner (acessibilidade) ---
      const closeBtn = document.createElement('button');
      closeBtn.type        = 'button';
      closeBtn.className   = 'feedbackfirst-banner__close';
      closeBtn.title       = 'Fechar aviso';
      closeBtn.textContent = '✕';
      closeBtn.style.cssText = [
         'position:absolute', 'top:10px', 'right:12px',
         'background:none', 'border:none', 'cursor:pointer',
         'font-size:.9rem', 'color:#78350f', 'opacity:.6',
         'transition:opacity .15s',
      ].join(';');

      closeBtn.addEventListener('mouseenter', function () { this.style.opacity = '1'; });
      closeBtn.addEventListener('mouseleave', function () { this.style.opacity = '.6'; });

      // Fechar apenas esconde visualmente; o backend ainda bloqueia o submit
      closeBtn.addEventListener('click', function () {
         banner.style.transition = 'opacity .25s';
         banner.style.opacity    = '0';
         setTimeout(function () { banner.style.display = 'none'; }, 260);
      });

      banner.style.position = 'relative';
      banner.appendChild(closeBtn);
   });
}());
