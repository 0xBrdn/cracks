<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Paramètres de protection anti brute-force
    $maxAttempts = 5;
    $lockoutTime = 30; // Temps de blocage en secondes

    // Initialisation des variables de session si elles n'existent pas
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['lockout_time'] = 0;
    }

    // Vérification du blocage temporaire
    $isLocked = false;
    if ($_SESSION['login_attempts'] >= $maxAttempts) {
        $timeLeft = ($_SESSION['lockout_time'] + $lockoutTime) - time();
        if ($timeLeft > 0) {
            $isLocked = true;
        } else {
            // Le délai est passé, on réinitialise
            $_SESSION['login_attempts'] = 0;
            $_SESSION['lockout_time'] = 0;
        }
    }

    $errorMsg = '';
    if (!empty($_REQUEST['go']) && !$isLocked) {
        if (Auth::getInstance()->tryLog($_REQUEST['login'], $_REQUEST['pwd'])) {
            // Connexion réussie : on remet le compteur à zéro
            $_SESSION['login_attempts'] = 0;
            header('Location: index.php?sid=' . Auth::getInstance()->getSid());
            exit;
        } else {
            // Échec : on incrémente le compteur d'erreurs
            $_SESSION['login_attempts']++;
            if ($_SESSION['login_attempts'] >= $maxAttempts) {
                $_SESSION['lockout_time'] = time();
                $isLocked = true;
            } else {
                $errorMsg = "Erreur d'identifiants ! (Tentative " . $_SESSION['login_attempts'] . "/" . $maxAttempts . ")";
            }
        }
    }
?>
<form method="post">
    <div>
        <h2>Connexion</h2>
        <?php if ($isLocked): ?>
            <p style="color: red; font-weight: bold;">
                Trop de tentatives infructueuses. Compte temporairement bloqué. Veuillez patienter 30 secondes.
            </p>
        <?php else: ?>
            <?php if (!empty($errorMsg)): ?>
                <p style="color: red;"><?php echo $errorMsg; ?></p>
            <?php endif; ?>
            <p>
                <label>
                    Login
                    <input type="text"
                           required="required"
                           name="login" />
                </label>
            </p>
            <p>
                <label>
                    Mot de passe
                    <input type="password"
                           required="required"
                           name="pwd" />
                </label>
            </p>
            <input type="submit" name="go" value="Se connecter" />
        <?php endif; ?>
    </div>
</form>
<p>
    Mot de passe oublié ?
    Envoyez un mail à
    <a href="mailto:<?php echo ADMIN_EMAIL; ?>"><?php echo ADMIN_EMAIL; ?></a>
    avec votre login
    pour recevoir un lien
    de reset de mot de passe !
</p>