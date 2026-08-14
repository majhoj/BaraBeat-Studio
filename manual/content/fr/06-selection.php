<?php
if (!defined('BARABEAT_MANUAL_RENDER')) { http_response_code(404); exit; }
?>      <section id="selection">
        <h2>6. Sélection, copie et annulation</h2>
        <p>Les éléments se déplacent séparément ou ensemble après sélection par un cadre. La sélection reste active après le déplacement jusqu’à un clic à l’extérieur.</p>
        <h3>Clavier</h3>
        <table><thead><tr><th>Touche</th><th>Fonction</th></tr></thead><tbody><tr><td><kbd>Alt</kbd> pendant le glissement</td><td>Cloner l’élément.</td></tr><tr><td><kbd>Cmd</kbd> + <kbd>Z</kbd></td><td>Annuler.</td></tr><tr><td><kbd>Cmd</kbd> + <kbd>Shift</kbd> + <kbd>Z</kbd></td><td>Rétablir.</td></tr><tr><td><kbd>Cmd</kbd> + <kbd>C</kbd></td><td>Copier la sélection.</td></tr><tr><td><kbd>Cmd</kbd> + <kbd>X</kbd></td><td>Couper la sélection.</td></tr><tr><td><kbd>Cmd</kbd> + <kbd>V</kbd></td><td>Coller. Les éléments restent sélectionnés et se déplacent immédiatement.</td></tr></tbody></table>
        <h3>Supprimer</h3><p>Sur ordinateur, <kbd>Cmd</kbd> + <kbd>X</kbd> retire la sélection. Dans l’éditeur mobile en paysage, l’outil de suppression retire l’élément touché ou toute la sélection. Chaque sélecteur possède aussi sa commande de suppression.</p>
        <h3>Copier entre des onglets</h3><p>Les éléments sélectionnés peuvent être copiés via le presse-papiers entre deux onglets ou fenêtres. Si le navigateur bloque le presse-papiers système, BaraBeat Studio utilise en plus un presse-papiers local dans l’onglet ouvert.</p>
        <h3>Grille lors du déplacement</h3><p>Pendant le glissement, l’élément suit librement le pointeur ; au relâchement, il rejoint la position appropriée la plus proche. Cela vaut pour notes, reprises, In/Out, ShortBar et sélecteurs.</p>
        <h3>Sélection sur iPhone</h3><p>Dans l’éditeur en paysage, activez l’outil de sélection puis tracez un cadre. Déplacez, dupliquez ou supprimez ensuite l’ensemble. La sélection de texte iOS est désactivée dans la partition afin d’éviter les conflits de gestes.</p>
      </section>
