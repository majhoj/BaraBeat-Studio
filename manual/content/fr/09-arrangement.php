<?php
if (!defined('BARABEAT_MANUAL_RENDER')) { http_response_code(404); exit; }
?>      <section id="arrangement">
        <h2>9. Arrangement</h2><p>L’Arrangement est la timeline des séquences complètes. Les Patterns de la Bibliothèque de Patterns sont glissés dans des sections puis joués à la suite ou en parallèle.</p>
        <h3>Bibliothèque de Patterns</h3><p>Tous les Patterns trouvés dans la partition apparaissent à gauche. Glissez-en un dans la timeline ou ajoutez-le à la fin avec le bouton plus.</p>
        <h3>Sections</h3><p>Une section contient un ou plusieurs Patterns. Ceux d’une même ligne sont joués en parallèle. Le nombre de répétitions à gauche fixe la durée de la section. Les Accompagnements plus courts peuvent continuer à boucler lorsqu’un Accompagnement plus long détermine les passages.</p>
        <h3>Ajouter en parallèle</h3><p>Une zone de dépôt parallèle apparaît seulement si l’emploi musical est possible. Un instrument ne peut pas jouer simultanément deux Patterns principaux différents du même type, sauf si un Solo remplace précisément un Accompagnement.</p>
        <h3>Cellules de Solo</h3><p>Une grille de Solo apparaît pour les sections d’Accompagnement répétées. Chaque cellule correspond à un passage ; un Solo déposé n’y joue qu’à ce passage.</p><ul><li>Si le Solo est plus court, le reste est silencieux.</li><li>S’il est plus long, la section se prolonge.</li><li>Si le même instrument joue Accompagnement et Solo, l’Accompagnement s’interrompt pendant le Solo puis reprend.</li><li><strong>In</strong> et <strong>Out</strong> s’appliquent aussi aux Solos.</li></ul>
        <h3>Déplacer des sections</h3><p>Les sections montent ou descendent dans la timeline afin de réorganiser l’Arrangement sans reconstruire les Patterns.</p>
        <h3>BPM dans l’Arrangement</h3><p>Chaque section peut avoir ses propres BPM. Le changement se fait progressivement sur environ deux mesures et reste valable jusqu’au prochain changement.</p>
        <h3>Shekere et volumes</h3><p>La pulsation Shekere est disponible dans l’Arrangement. Profil Swing, Feel et volumes s’ouvrent au-dessus de la timeline. Les volumes sont communs à l’entraînement et l’Arrangement et sont enregistrés avec la partition.</p>
        <h3>Annuler et rétablir</h3><p>Les changements de timeline utilisent le même historique que l’éditeur et les réglages. <kbd>Cmd</kbd> + <kbd>Z</kbd> annule et <kbd>Cmd</kbd> + <kbd>Shift</kbd> + <kbd>Z</kbd> rétablit.</p>
      </section>
