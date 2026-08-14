<?php
if (!defined('BARABEAT_MANUAL_RENDER')) { http_response_code(404); exit; }
?>      <section id="selection">
        <h2>6. Selección, copia y deshacer</h2><p>Los elementos se mueven individualmente o juntos mediante un marco de selección. La selección permanece tras moverla hasta pulsar fuera.</p>
        <h3>Teclado</h3><table><thead><tr><th>Tecla</th><th>Función</th></tr></thead><tbody><tr><td><kbd>Alt</kbd> al arrastrar</td><td>Clonar elemento.</td></tr><tr><td><kbd>Cmd</kbd> + <kbd>Z</kbd></td><td>Deshacer.</td></tr><tr><td><kbd>Cmd</kbd> + <kbd>Shift</kbd> + <kbd>Z</kbd></td><td>Rehacer.</td></tr><tr><td><kbd>Cmd</kbd> + <kbd>C</kbd></td><td>Copiar selección.</td></tr><tr><td><kbd>Cmd</kbd> + <kbd>X</kbd></td><td>Cortar selección.</td></tr><tr><td><kbd>Cmd</kbd> + <kbd>V</kbd></td><td>Pegar. Los elementos siguen seleccionados y se pueden mover.</td></tr></tbody></table>
        <h3>Eliminar</h3><p>En escritorio, <kbd>Cmd</kbd> + <kbd>X</kbd> retira la selección. En el editor móvil horizontal, la herramienta elimina el elemento pulsado o toda la selección. Los selectores tienen su propio control de eliminación.</p>
        <h3>Copiar entre pestañas</h3><p>Los elementos se pueden copiar mediante el portapapeles entre pestañas o ventanas. Si el navegador bloquea el portapapeles del sistema, BaraBeat Studio usa además uno local en la pestaña.</p>
        <h3>Cuadrícula al mover</h3><p>Mientras arrastras, el elemento sigue al puntero; al soltar, encaja en la posición lógica más cercana. Se aplica a notas, repeticiones, In/Out, ShortBar y selectores.</p>
        <h3>Selección en iPhone</h3><p>En el editor horizontal, activa la herramienta y dibuja un marco. Después mueve, duplica o elimina el conjunto. La selección de texto de iOS se desactiva en la partitura para evitar conflictos.</p>
      </section>
