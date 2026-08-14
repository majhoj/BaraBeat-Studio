<?php
if (!defined('BARABEAT_MANUAL_RENDER')) { http_response_code(404); exit; }
?>      <section id="player">
        <h2>7. Lecteur audio</h2>
        <p>Le lecteur audio joue un entraînement ou un Arrangement. Dans sa vue autonome, il peut aussi lire certaines parties d’une partition.</p>
        <h3>Commandes</h3><ul><li><strong>BPM</strong> règle le tempo en battements par minute.</li><li><strong>Play</strong> démarre ou arrête la lecture.</li><li><strong>Partie</strong> choisit des instruments ou groupes dans le lecteur autonome.</li><li><strong>Exporter en WAV</strong> produit un fichier WAV de l’entraînement ou de l’Arrangement si le bouton est proposé dans la configuration. Il peut être masqué sur smartphone, car les navigateurs mobiles gèrent différemment les téléchargements.</li></ul>
        <h3>Instruments et volumes</h3><p>En mode entraînement et Arrangement, <strong>Volume</strong> ouvre le mixeur. Les parties de Djembe, Kenkeni, Sangban, Dununba, Ballet Dununs et Shekere se règlent ou se coupent séparément. Pour chaque instrument, Bass, Tone, Slap ou Mute peuvent aussi être équilibrés entre eux.</p>
        <h3>Latence Bluetooth</h3><p><strong>Latence Bluetooth ms</strong> décale l’affichage par rapport au son afin que la ligne rouge et la frappe entendue coïncident avec un casque Bluetooth. Sur iPhone, ce réglage est accessible par <strong>Latence</strong> et enregistré sur l’appareil.</p>
        <h3>Notes défilantes</h3><p>Sous le lecteur, les notes vont de droite à gauche sous une ligne rouge fixe. La frappe sonne au passage du symbole. Barres de mesure, fond du Pattern, nom de l’instrument et du Pattern facilitent l’orientation.</p><p>La droite de l’en-tête indique, selon le mode, le nombre de répétitions ou le temps restant. Pendant une progression du tempo, elle indique d’abord les répétitions restantes de cette progression.</p>
      </section>
