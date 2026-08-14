<?php
if (!defined('BARABEAT_MANUAL_RENDER')) { http_response_code(404); exit; }
?>      <section id="quickstart">
        <h2>1. Inicio rápido</h2>
        <p>BaraBeat Studio es un editor y reproductor de notación para Djembe y Dunun. Permite escribir y escuchar directamente Pattern como Call, Intro, Pattern de acompañamiento, Solo o Échauffement.</p>
        <h3>BaraBeat en menos de 3 minutos</h3>
        <p>El video muestra los pasos principales, desde la primera entrada en la partitura hasta la reproducción y el guardado de un ritmo. Después podrás repasar con calma cada paso del inicio rápido.</p>
        <video class="manual-quickstart-video" controls playsinline preload="metadata" poster="<?php echo barabeat_manual_escape($manualAssetBaseUrl . '/poster.png'); ?>">
          <source src="<?php echo barabeat_manual_escape($manualAssetBaseUrl . '/barabeat-quickstart.mp4'); ?>" type="video/mp4">
          Tu navegador no permite reproducir este video.
        </video>
        <div class="workflow"><h3>Flujo rápido</h3><ol>
          <li>Abre una partitura con <strong>Archivo</strong> o crea una nueva con <strong>Partitura</strong>.</li><li>Escribe el <strong>nombre del ritmo</strong> en la parte superior.</li><li>Identifica la parte con los selectores de instrumento y función, por ejemplo <em>Djembe 1</em> y <em>Pattern de acompañamiento</em>.</li><li>Coloca en la cuadrícula las notas y, si hace falta, los signos de control de la paleta.</li><li>Activa la casilla situada delante del Pattern que quieras escuchar.</li><li>Ajusta los BPM e inicia la <strong>reproducción inmediata</strong> con Play.</li><li>Guarda con <strong>Archivo → Guardar</strong>; usa <strong>Guardar como</strong> para otro nombre o una copia.</li>
        </ol></div>
        <p>Para practicar de forma sistemática, pasa al <strong>modo de práctica</strong>; crea secuencias largas en el <strong>Arreglo</strong>. En smartphones, la orientación vertical sirve principalmente para leer y reproducir, y la horizontal para editar.</p>
      </section>
