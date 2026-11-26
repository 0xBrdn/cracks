<?php
    Auth::getInstance()->logoff();
    header('Location: index.php', true, 303);
    exit;
