<?php
    // On s'assure que la session est active et que l'utilisateur est connecté
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!empty($_POST['val']) && !empty($_SESSION['userid'])) {
        // On prépare la requête pour éviter l'injection SQL
        $stmt = $db->prepare('INSERT INTO cracks (content, owner, datesend) VALUES (?, ?, ?)');
        
        // On exécute en passant les données séparément
        $stmt->execute([
            nl2br($_POST['content']), 
            $_SESSION['userid'], // On sécurise la récupération côté serveur, impossible à modifier par l'utilisateur
            time()
        ]);
        
        // On redirige vers le nouveau crack
        header('Location: index.php?inc=search&cid=' . $db->lastInsertId());
        exit;
    }
?>
<form method="post">
    <div>
        <h2>Ajouter un crack</h2>
        <p>
            <label for="content">
                Contenu
            </label>
            <textarea name="content"
                      id="content"
                      required="required"></textarea>
            <input type="hidden"
                   name="owner"
                   value="<?php echo htmlspecialchars($_SESSION['userid'] , ENT_QUOTES, 'UTF-8'); ?>" />
            <input type="submit" name="val" value="Ajouter ce crack" />
        </p>
    </div>
</form>