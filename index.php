<?php
    ob_start();
    require_once 'config.php';
?><!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Cracks are forming</title>
        <script type="text/javascript" src="script.js"></script>
        <link rel="stylesheet" type="text/css" href="style.css" />
    </head>
    <body>
        <header>
            <menu>
                <li>
                    <a href="./">
                        Accueil
                    </a>
                </li>
                <li>
                    <a href="./?inc=search">
                        Cracks
                    </a>
                </li>
                <li>
                    <a href="./?inc=add">
                        Nouveau
                    </a>
                </li><?php if(!Auth::getInstance()->isLogged()) { ?>
                <li>
                    <a href="./?inc=login">
                        Connexion
                    </a>
                </li>
                <li>
                    <a href="./?inc=sub">
                        Inscription
                    </a>
                </li>
                <?php } else { ?>
                <li>
                    <form method="post" action="./?inc=logoff">
                        <input type="hidden" name="csrf"
                               value="<?php echo htmlspecialchars(Auth::getInstance()->csrfToken(), ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit">Déconnexion</button>
                    </form>
                </li><?php } ?>
            </menu>
        </header>
        <main>
            <?php
                $pages_autorisees = ['login', 'sub', 'logoff', 'add', 'search'];

                if(!empty($_REQUEST['inc']) && in_array($_REQUEST['inc'], $pages_autorisees)) {
                    include './inc/'.$_REQUEST['inc'].'.php';
                } else {
                    include './inc/last.php';
                }
            ?>
        </main>
        <footer>
            
        </footer>
    </body>
</html>