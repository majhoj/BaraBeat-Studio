<?php
if (!defined('BARABEAT_MANUAL_RENDER')) { http_response_code(404); exit; }
?>      <section id="editor">
        <h2>4. Modifier la partition</h2>
        <p>La partition est la base de toutes les autres fonctions. Les Patterns sont formés de passages nommés par un sélecteur d’instrument et un sélecteur de fonction.</p>
        <h3>Types de partition</h3>
        <table><thead><tr><th>Type</th><th>Grille</th><th>Usage courant</th></tr></thead><tbody>
          <tr><td>Binaire</td><td>Quatre subdivisions par pulsation</td><td>De nombreux rythmes réguliers de Djembe et Dunun</td></tr>
          <tr><td>Ternaire</td><td>Trois subdivisions par pulsation</td><td>Rythmes en triolets</td></tr>
          <tr><td>9/8</td><td>Trois groupes de trois</td><td>Rythmes 9/8 particuliers comme Koreduga</td></tr>
        </tbody></table>
        <h3>Partitions de plusieurs pages</h3>
        <p>Une partition peut compter plusieurs pages de dix lignes. Utilisez <strong>Ajouter une page</strong> si nécessaire. La légende est placée sur la dernière page et des numéros tels que <code>1/2</code> et <code>2/2</code> apparaissent en bas à droite.</p>
        <h3>Palette</h3>
        <p>La palette contient les notes, sélecteurs et signes de commande. Sur ordinateur, elle reste visible pendant le défilement et peut être déplacée. Dans l’éditeur mobile en paysage, elle forme une barre d’outils horizontale défilante en bas de l’écran.</p>
        <h3>Définir l’instrument et la fonction</h3>
        <p>Le sélecteur d’instrument choisit la partie instrumentale : Djembe, Kenkeni, Sangban, Dununba ou Ballet Dununs. Le sélecteur de fonction indique le rôle du Pattern : Call, Intro, Pattern d’accompagnement, Solo, Échauffement ou Outro. Pour <strong>Pattern d’accompagnement</strong> et <strong>Solo</strong>, saisissez librement un nom comme <em>Pattern d’accompagnement 2</em> ou <em>Solo 1</em>. Ce nom réapparaît lors d’une modification ultérieure.</p>
        <p>Dans BaraBeat Studio, <strong>Ballet Dununs</strong> désigne la partie commune ou l’ensemble joué conjointement par Kenkeni, Sangban et Dununba.</p>
        <h3>Nom du rythme</h3>
        <p>Le nom du rythme se trouve en haut de la partition et se modifie directement. Au démarrage, le dernier titre chargé est restauré si le navigateur possède encore cette information.</p>
        <h3>Lecture immédiate depuis la partition</h3>
        <p>Les BPM et un bouton Play se trouvent à côté du nom. Les petites cases devant les Patterns indiquent ce qui doit être lu. Elles deviennent actives dès qu’une mesure est reconnue comme jouable grâce à un instrument, un nom de Pattern ou des notes, sans enregistrement préalable. Sur iPhone, l’en-tête avec le nom et Play reste visible pendant le défilement.</p>
        <ul><li>Un Pattern sélectionné tourne en boucle jusqu’à Stop.</li><li>Les Patterns de différents instruments sont joués en parallèle.</li><li>Les Patterns du même instrument jouent à la suite.</li><li>Un Accompagnement sélectionné continue pendant qu’un Solo supplémentaire joue en parallèle. Son Out est ignoré pour une boucle continue.</li><li>Les notes qui sonnent sont brièvement colorées dans la partition.</li></ul>
        <h3>Déplacer un Pattern complet</h3>
        <p>Un Pattern complet, avec notes, sélecteurs et marques supplémentaires, peut être déplacé vers le haut ou le bas grâce aux flèches placées à son début. Cette fonction existe sur ordinateur et dans l’éditeur mobile en paysage.</p>
      </section>
