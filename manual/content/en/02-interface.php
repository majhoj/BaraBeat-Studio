<?php

if (!defined('BARABEAT_MANUAL_RENDER')) {
    http_response_code(404);
    exit;
}

?>      <section id="interface">
        <h2>2. Interface</h2>
        <p>The application consists of the score, a movable palette, the main menu and optional work views for practice mode and Arrangement.</p>

        <h3>Main menu</h3>
        <table>
          <thead><tr><th>Menu</th><th>Purpose</th></tr></thead>
          <tbody>
            <tr><td><strong>File</strong></td><td>Open and save scores, manage them locally or on the server, export them and select the language. On smartphones, the user guide is also available here.</td></tr>
            <tr><td><strong>Score</strong></td><td>Create a binary, ternary or 9/8 score and add or delete pages.</td></tr>
            <tr><td><strong>Insert</strong></td><td>Insert additional elements or templates.</td></tr>
            <tr><td><strong>Tools</strong></td><td>On the desktop, open practice mode, Arrangement, the user guide and template selection.</td></tr>
          </tbody>
        </table>

        <h3>Language</h3>
        <p>Under <strong>File → Language</strong>, you can select <strong>Deutsch</strong>, <strong>English</strong>, <strong>Français</strong>, <strong>Español</strong> or <strong>Português</strong>. BaraBeat Studio remembers the selection and uses it again the next time you open the application. Language selection changes visible controls and messages only; musical data, free text and saved scores are neither translated nor modified.</p>

        <h3>Sign-in</h3>
        <p>If access protection is enabled, BaraBeat Studio first asks for a password. Sign-in remains valid in that browser or Home Screen app until you choose <strong>File → Sign out</strong> or delete the browser data. Sign out after working on a shared device.</p>

        <h3>Administration: temporary access</h3>
        <p>After signing in normally, you can use <strong>File → Open access for 5 min</strong> to suspend password protection temporarily for all visitors. This also applies to automated browsers, API requests and testing tools without a sign-in or cookie. A confirmation prevents accidental activation. The button shows the remaining time and can close the access window early; protection is restored automatically after five minutes.</p>
        <p>During this window, BaraBeat Studio marks responses with <code>noindex</code> and <code>no-store</code>. Automated tools may access the application, but search engines should neither index nor cache the temporarily open application.</p>

        <h3>Templates</h3>
        <p>Under <strong>Tools → Template</strong>, you can switch between visual presets including <strong>Clear</strong>, <strong>Playful</strong> and <strong>Earthy</strong>. Templates change the visual mood of the interface, not the musical data.</p>
      </section>
