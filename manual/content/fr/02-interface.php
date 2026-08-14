<?php
if (!defined('BARABEAT_MANUAL_RENDER')) { http_response_code(404); exit; }
?>      <section id="interface">
        <h2>2. Interface</h2>
        <p>L’application comprend la partition, une palette déplaçable, le menu principal et des vues de travail facultatives pour le mode entraînement et l’Arrangement.</p>
        <h3>Menu principal</h3>
        <table><thead><tr><th>Menu</th><th>Utilisation</th></tr></thead><tbody>
          <tr><td><strong>Fichier</strong></td><td>Ouvrir et enregistrer les partitions, les gérer localement ou sur le serveur, les exporter et choisir la langue. Sur smartphone, le mode d’emploi est également accessible ici.</td></tr>
          <tr><td><strong>Partition</strong></td><td>Créer une partition binaire, ternaire ou 9/8, ajouter ou supprimer des pages.</td></tr>
          <tr><td><strong>Insérer</strong></td><td>Insérer des éléments ou des modèles supplémentaires.</td></tr>
          <tr><td><strong>Outils</strong></td><td>Sur ordinateur, ouvrir le mode entraînement, l’Arrangement, le mode d’emploi et le choix des modèles.</td></tr>
        </tbody></table>
        <h3>Langue</h3>
        <p>Avec <strong>Fichier → Langue</strong>, choisissez <strong>Deutsch</strong>, <strong>English</strong>, <strong>Français</strong>, <strong>Español</strong> ou <strong>Português</strong>. BaraBeat Studio mémorise ce choix. Seuls les commandes et messages visibles changent ; les données musicales, textes libres et partitions enregistrées ne sont ni traduits ni modifiés.</p>
        <h3>Connexion</h3>
        <p>Si la protection d’accès est active, un mot de passe est demandé avant BaraBeat Studio. La connexion reste valable dans le navigateur ou l’app de l’écran d’accueil jusqu’à <strong>Fichier → Déconnexion</strong> ou jusqu’à la suppression des données du navigateur. Déconnectez-vous sur un appareil partagé.</p>
        <h3>Administration : accès temporaire</h3>
        <p>Après une connexion normale, <strong>Fichier → Ouvrir l’accès pendant 5 min</strong> suspend temporairement la protection pour tous les visiteurs, y compris les navigateurs automatisés, requêtes API et outils de test sans connexion ni cookie. Une confirmation évite une ouverture accidentelle. Le bouton indique le temps restant et peut fermer l’accès plus tôt ; la protection revient automatiquement après cinq minutes.</p>
        <p>Pendant cette période, BaraBeat Studio marque les réponses avec <code>noindex</code> et <code>no-store</code>. Les outils automatisés peuvent accéder à l’application, mais les moteurs de recherche ne doivent ni l’indexer ni la mettre en cache.</p>
        <h3>Modèles</h3>
        <p><strong>Outils → Modèle</strong> permet de choisir des styles visuels, notamment <strong>Clair</strong>, <strong>Ludique</strong> et <strong>Terre</strong>. Ils modifient l’ambiance visuelle, jamais les données musicales.</p>
      </section>
