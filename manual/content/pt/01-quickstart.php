<?php
if (!defined('BARABEAT_MANUAL_RENDER')) { http_response_code(404); exit; }
?>      <section id="quickstart">
        <h2>1. Início rápido</h2><p>BaraBeat Studio é um editor e player de notação para Djembe e Dunun. Você pode escrever Patterns como Call, Intro, Pattern de acompanhamento, Solo ou Échauffement e ouvi-los imediatamente.</p>
        <h3>BaraBeat em menos de 3 minutos</h3><p>O vídeo mostra as etapas principais, desde a primeira inserção na partitura até a reprodução e o salvamento de um ritmo. Depois, você pode acompanhar com calma cada etapa do início rápido.</p>
        <video class="manual-quickstart-video" controls playsinline preload="metadata" poster="<?php echo barabeat_manual_escape($manualAssetBaseUrl . '/poster.png'); ?>">
          <source src="<?php echo barabeat_manual_escape($manualAssetBaseUrl . '/barabeat-quickstart.mp4'); ?>" type="video/mp4">
          Seu navegador não permite reproduzir este vídeo.
        </video>
        <div class="workflow"><h3>Fluxo rápido</h3><ol><li>Use <strong>Arquivo</strong> para abrir uma partitura ou <strong>Partitura</strong> para criar uma nova.</li><li>Digite o <strong>nome do ritmo</strong> no alto da partitura.</li><li>Identifique a parte com os seletores de instrumento e função, por exemplo <em>Djembe 1</em> e <em>Pattern de acompanhamento</em>.</li><li>Coloque na grade as notas e, se necessário, os sinais de controle da paleta.</li><li>Ative a caixa antes do Pattern que deseja ouvir.</li><li>Ajuste os BPM e inicie a <strong>reprodução imediata</strong> com Play.</li><li>Salve com <strong>Arquivo → Salvar</strong>; use <strong>Salvar como</strong> para outro nome ou uma cópia.</li></ol></div>
        <p>Para praticar sistematicamente, passe ao <strong>modo de prática</strong>; monte sequências maiores no <strong>Arranjo</strong>. Em smartphones, o modo retrato serve principalmente para ler e reproduzir, e o modo paisagem para editar.</p>
      </section>
