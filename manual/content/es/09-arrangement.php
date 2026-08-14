<?php
if (!defined('BARABEAT_MANUAL_RENDER')) { http_response_code(404); exit; }
?>      <section id="arrangement">
        <h2>9. Arreglo</h2><p>El Arreglo es la línea de tiempo de secuencias completas. Los Pattern de la Biblioteca de Pattern se arrastran a secciones y se reproducen en serie o en paralelo.</p>
        <h3>Biblioteca de Pattern</h3><p>A la izquierda aparecen todos los Pattern de la partitura. Arrástralos a la línea de tiempo o añádelos al final con el botón más.</p>
        <h3>Secciones</h3><p>Una sección contiene uno o varios Pattern. Los de una fila suenan en paralelo. La repetición de la izquierda define los pases. Acompañamientos cortos pueden seguir repitiéndose cuando otro más largo determina la duración.</p>
        <h3>Añadir en paralelo</h3><p>La zona paralela solo aparece si la combinación es musicalmente válida. Un instrumento no puede tocar simultáneamente dos Pattern principales distintos del mismo tipo, salvo que un Solo sustituya deliberadamente a un Acompañamiento.</p>
        <h3>Celdas de Solo</h3><p>Las secciones repetidas de Acompañamiento muestran una cuadrícula. Cada celda representa un pase; el Solo depositado solo suena en ese pase.</p><ul><li>Si es más corto, el resto queda en silencio.</li><li>Si es más largo, la sección continúa.</li><li>Si el mismo instrumento toca Acompañamiento y Solo, el Acompañamiento se pausa durante el Solo y vuelve después.</li><li><strong>In</strong> y <strong>Out</strong> también se respetan en Solos.</li></ul>
        <h3>Mover secciones</h3><p>Las secciones suben o bajan en la línea de tiempo para reorganizar el Arreglo sin reconstruir los Pattern.</p>
        <h3>BPM en el Arreglo</h3><p>Cada sección puede tener BPM propios. El cambio se desliza durante unos dos compases y se mantiene hasta otro cambio.</p>
        <h3>Shekere y volúmenes</h3><p>El pulso de Shekere también está disponible. Perfil de Swing, Feel y volúmenes se abren sobre la línea de tiempo. Los volúmenes son comunes a práctica y Arreglo y se guardan con la partitura.</p>
        <h3>Deshacer y rehacer</h3><p>Los cambios usan el mismo historial del editor y los ajustes. <kbd>Cmd</kbd> + <kbd>Z</kbd> deshace; <kbd>Cmd</kbd> + <kbd>Shift</kbd> + <kbd>Z</kbd> rehace.</p>
      </section>
