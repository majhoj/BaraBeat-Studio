<?php
if (!defined('BARABEAT_MANUAL_RENDER')) { http_response_code(404); exit; }
?>      <section id="quickstart">
        <h2>1. Prise en main</h2>
        <p>BaraBeat Studio est un éditeur et un lecteur de notation pour Djembe et Dunun. Vous pouvez noter des Patterns tels que Call, Intro, Pattern d’accompagnement, Solo ou Échauffement et les écouter immédiatement.</p>
        <h3>BaraBeat en moins de 3 minutes</h3>
        <p>La vidéo présente les étapes essentielles, de la première saisie dans la partition jusqu’à la lecture et l’enregistrement d’un rythme. Vous pourrez ensuite reprendre tranquillement chaque étape de la prise en main.</p>
        <video class="manual-quickstart-video" controls playsinline preload="metadata" poster="<?php echo barabeat_manual_escape($manualAssetBaseUrl . '/poster.png'); ?>">
          <source src="<?php echo barabeat_manual_escape($manualAssetBaseUrl . '/barabeat-quickstart.mp4'); ?>" type="video/mp4">
          Votre navigateur ne permet pas de lire cette vidéo.
        </video>
        <div class="workflow">
          <h3>Procédure rapide</h3>
          <ol>
            <li>Utilisez <strong>Fichier</strong> pour ouvrir une partition existante ou <strong>Partition</strong> pour en créer une.</li>
            <li>Saisissez le <strong>nom du rythme</strong> en haut de la partition.</li>
            <li>Nommez la partie avec les sélecteurs d’instrument et de fonction, par exemple <em>Djembe 1</em> et <em>Pattern d’accompagnement</em>.</li>
            <li>Placez les notes et, si nécessaire, les signes de commande de la palette sur la grille.</li>
            <li>Activez la case devant le Pattern à écouter.</li>
            <li>Réglez les BPM et lancez la <strong>lecture immédiate</strong> avec le bouton Play.</li>
            <li>Enregistrez avec <strong>Fichier → Enregistrer</strong> ; utilisez <strong>Enregistrer sous</strong> pour un autre nom ou une copie.</li>
          </ol>
        </div>
        <p>Pour un travail systématique, passez au <strong>mode entraînement</strong> ; construisez des séquences plus longues dans l’<strong>Arrangement</strong>. Sur smartphone, le mode portrait sert surtout à lire et à écouter, le mode paysage à modifier.</p>
      </section>
