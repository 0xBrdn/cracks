<?php
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        header('Allow: POST');
        echo '<p>La déconnexion doit passer par le bouton du menu.</p>';
        return;
    }

    $auth = Auth::getInstance();

    if (!$auth->validCsrf($_POST['csrf'] ?? null)) {
        http_response_code(403);
        echo '<p>Le formulaire a expiré. Recharge la page et réessaie.</p>';
        return;
    }

    $auth->logoff();
    header('Location: index.php', true, 303);
    exit;