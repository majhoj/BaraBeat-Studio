<?php
if (!defined('BARABEAT_MANUAL_RENDER')) { http_response_code(404); exit; }
?>      <section id="files">
        <h2>3. Ouvrir et enregistrer des fichiers</h2>
        <p>Les partitions sont enregistrées au format <code>.bbs</code>. Les anciennes partitions <code>.txt</code> restent lisibles.</p>
        <h3>Ouvrir</h3>
        <ul>
          <li><strong>Local</strong> affiche les partitions enregistrées dans ce navigateur ou cette app de l’écran d’accueil. Elles peuvent être classées en dossiers et sous-dossiers.</li>
          <li><strong>Serveur</strong> affiche les partitions du dossier <code>Noten/</code>. La liste est relue à chaque ouverture du dialogue.</li>
          <li>Lors d’un chargement depuis le serveur, BaraBeat Studio mémorise la date et la version. Si le fichier change ensuite sur le serveur, <strong>Serveur modifié depuis</strong> apparaît. Une modification locale est indiquée séparément par <strong>Modifié localement</strong> avec sa date.</li>
          <li>Au besoin, le message au-dessus de la liste propose <strong>Charger la version du serveur</strong>.</li>
          <li>Sur smartphone, la vue est simplifiée afin de garder l’ouverture et la suppression des fichiers locaux accessibles.</li>
        </ul>
        <h3>Enregistrer</h3>
        <ul>
          <li><strong>Enregistrer</strong> remplace la partition ouverte.</li>
          <li><strong>Enregistrer sous</strong> crée un nouveau nom ou une copie.</li>
          <li><strong>Enregistrer sous</strong> est également disponible sur iPhone pour conserver localement ou sur le serveur les partitions créées ou modifiées dans l’éditeur mobile.</li>
          <li>La bibliothèque locale permet de créer et renommer des dossiers. Un dossier ne peut être supprimé que s’il ne contient plus aucune partition ni sous-dossier.</li>
          <li>Un enregistrement réussi affiche brièvement un état ; les erreurs restent visibles.</li>
        </ul>
        <h3>Publier sur le serveur</h3>
        <p>L’enregistrement sur le serveur publie la partition et la conserve aussi localement. BaraBeat Studio place un jeton de publication dans cette copie locale. Il prouve ensuite que vous pouvez actualiser ou retirer cette publication.</p>
        <ul><li>Enregistrer à nouveau le même fichier publié actualise la publication.</li><li>Un nouveau nom crée une nouvelle publication.</li><li><strong>Supprimer la publication</strong> retire la version serveur, sans supprimer la copie locale.</li><li>Sans jeton, le fichier serveur reste lisible mais ne peut pas être géré comme votre publication. Utilisez le navigateur ou l’app qui l’a publié.</li></ul>
        <p>La colonne d’état distingue fichiers locaux, publications et versions modifiées localement. Pour les fichiers serveur, BaraBeat Studio signale aussi une modification depuis le dernier chargement.</p>
        <h3>SVG, PDF et impression</h3>
        <p>Dans <strong>Fichier → Exporter</strong>, saisissez un nom et choisissez :</p>
        <ul><li><strong>SVG</strong>, fichier vectoriel redimensionnable adapté à la retouche ou à un affichage de qualité.</li><li><strong>PDF</strong>, adapté au partage et à l’impression avec une mise en page stable.</li></ul>
        <p>Pour imprimer, ouvrez le PDF exporté et utilisez la commande du navigateur ou du système. BaraBeat Studio ne possède pas de dialogue d’impression propre.</p>
        <div class="hint"><strong>Remarque :</strong> La partition enregistre aussi les réglages d’entraînement, scénarios, sélections de Patterns, la timeline de l’Arrangement, les volumes, Swing, Feel et d’autres métadonnées.</div>
      </section>
