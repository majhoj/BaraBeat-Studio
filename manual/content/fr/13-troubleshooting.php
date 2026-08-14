<?php
if (!defined('BARABEAT_MANUAL_RENDER')) { http_response_code(404); exit; }
?>      <section id="troubleshooting">
        <h2>13. Dépannage</h2>
        <h3>Un Pattern n’est pas reconnu</h3><ul><li>Vérifiez les sélecteurs d’instrument et de fonction au-dessus du Pattern.</li><li>Vérifiez que les notes sont sur la grille.</li><li>En 9/8, placez In, Out et ShortBar sur des positions adaptées.</li></ul>
        <h3>La lecture semble décalée</h3><ul><li>Vérifiez la latence Bluetooth.</li><li>Contrôlez les valeurs Feel.</li><li>Aux transitions, vérifiez In/Out et ShortBar.</li></ul>
        <h3>Les notes défilantes présentent des blancs</h3><ul><li>Recherchez des répétitions très longues ou trop de Patterns simultanés.</li><li>Sur iPhone/Safari, utilisez des valeurs réalistes.</li><li>Enregistrez et rouvrez les nouveaux fichiers pour actualiser les métadonnées.</li><li>Aux transitions Call, Intro, ShortBar ou Out d’Accompagnement, vérifiez que le scénario voulu est enregistré.</li></ul>
        <h3>La timeline indique « mixte »</h3><p>Cela peut arriver lorsque des Patterns parallèles ont des répétitions différentes. Les groupes d’Accompagnement d’un seul passage prolongés dans une section ne devraient plus être indiqués comme « mixte ».</p>
        <h3>Un sample manque</h3><p>Le lecteur doit afficher l’erreur. Vérifiez le nom dans le dossier sonore et la présence du fichier sur le serveur.</p>
        <h3>L’éditeur mobile n’apparaît pas</h3><p>Il ne s’active sur iPhone qu’en paysage. Désactivez le verrouillage d’orientation et retournez l’appareil. Le portrait conserve volontairement la lecture compacte.</p>
        <h3>Aucun son sur iPhone</h3><p>Vérifiez volume, sortie audio et Bluetooth. Une session audio de lecture permet normalement le son en mode silencieux. Après un changement d’iOS ou de navigateur, touchez de nouveau Play pour autoriser la sortie.</p>
        <h3>Données hors ligne absentes après réinstallation</h3><p>iOS peut créer un nouveau stockage après suppression et ajout de l’app. Rechargez les partitions du serveur. Les fichiers uniquement locaux ne reviennent que s’ils ont été sauvegardés.</p>
        <h3>Un fichier serveur n’est pas à la version attendue</h3><p>Rouvrez le dialogue pour actualiser la liste. Consultez état et modification. <strong>Charger la version du serveur</strong> remplace la copie locale après confirmation.</p>
        <h3>Impossible d’actualiser ou supprimer une publication</h3><p>Ces actions exigent le jeton de publication local. Utilisez le navigateur ou l’app qui a publié. Un fichier seulement chargé du serveur est lisible, sans droit de gestion automatique.</p>
        <h3>La langue choisie ne modifie pas la partition</h3><p>C’est normal : seuls commandes et messages sont traduits. Noms de rythmes, textes libres, noms de Patterns et données musicales restent intacts.</p>
        <h3>SVG ou PDF introuvable</h3><p>L’export est remis au navigateur comme téléchargement. Vérifiez le dossier ou, sur iPhone, Fichiers et la liste Safari. Les restrictions peuvent demander une confirmation.</p>
      </section>
