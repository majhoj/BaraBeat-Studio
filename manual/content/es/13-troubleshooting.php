<?php
if (!defined('BARABEAT_MANUAL_RENDER')) { http_response_code(404); exit; }
?>      <section id="troubleshooting">
        <h2>13. Solución de problemas</h2>
        <h3>No se reconoce un Pattern</h3><ul><li>Comprueba los selectores sobre el Pattern.</li><li>Comprueba que las notas estén en la cuadrícula.</li><li>En 9/8, coloca In, Out y ShortBar correctamente.</li></ul>
        <h3>La reproducción suena desplazada</h3><ul><li>Comprueba latencia Bluetooth.</li><li>Revisa Feel.</li><li>En transiciones, revisa In/Out y ShortBar.</li></ul>
        <h3>Las notas en movimiento muestran huecos</h3><ul><li>Comprueba repeticiones muy largas o muchos Pattern simultáneos.</li><li>En iPhone/Safari usa valores realistas.</li><li>Guarda y reabre archivos nuevos para actualizar metadatos.</li><li>En transiciones con Call, Intro, ShortBar u Out del Acompañamiento, comprueba el escenario guardado.</li></ul>
        <h3>La línea de tiempo indica «mixto»</h3><p>Puede ocurrir cuando Pattern paralelos tienen estructuras de repetición distintas. Los grupos de Acompañamiento de un pase prolongados en una sección ya no deberían aparecer como «mixto».</p>
        <h3>Falta un sample</h3><p>El reproductor debe mostrar el error. Comprueba nombre y presencia en el servidor.</p>
        <h3>No aparece el editor móvil</h3><p>Solo se activa en horizontal. Desactiva el bloqueo de orientación y gira de nuevo. La vertical conserva la vista de lectura.</p>
        <h3>No hay sonido en iPhone</h3><p>Comprueba volumen, salida y Bluetooth. La sesión de reproducción permite normalmente audio en silencio; tras cambios de iOS o navegador puede ser necesario pulsar Play otra vez.</p>
        <h3>Faltan datos offline tras reinstalar</h3><p>iOS puede haber creado otro almacenamiento. Vuelve a cargar del servidor. Los archivos solo locales requieren una copia previa.</p>
        <h3>Un archivo del servidor no tiene la versión esperada</h3><p>Reabre el diálogo para actualizar la lista. Comprueba estado y modificación. <strong>Cargar versión del servidor</strong> sustituye la local tras confirmar.</p>
        <h3>No se puede actualizar o borrar una publicación</h3><p>Hace falta el token local. Usa el navegador o app que publicó. Un archivo solo cargado se puede leer, pero no obtiene permiso de administración.</p>
        <h3>El idioma no cambia la partitura</h3><p>Es intencional: solo se traducen controles y mensajes. Nombres, textos, etiquetas de Pattern y datos musicales permanecen.</p>
        <h3>No se encuentra SVG o PDF</h3><p>La exportación se entrega como descarga. Comprueba la carpeta o, en iPhone, Archivos y las descargas de Safari. Las restricciones pueden exigir confirmación.</p>
      </section>
