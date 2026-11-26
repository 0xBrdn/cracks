<?php

/**
 * Description of Auth
 *
 * @author 
 */
class Auth {
    use tSingleton;
    
    protected function __construct(){
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
            session_start();
        }
        // Suppression du cookie non sécurisé authbypass
    }
    
    public function subscribe($login, $pwd) {
        global $db;
        $q = 'insert into users values(null, :login, :pwd, 0)';
        $stmt = $db->prepare($q);
        $stmt->execute([
            'login' => $login,
            'pwd' => md5($pwd)
        ]);
    }
    
    public function tryLog($login, $pwd): bool {
        global $db;
        $q = 'select * from users where login = :login and pwd = :pwd';
        $found = null;
        $ls = $db->prepare($q);
        $ls->execute([
            'login' => $login,
            'pwd' => md5($pwd)
        ]);
        $ls->setFetchMode(PDO::FETCH_ASSOC);
        if(!empty($ls)) {
            foreach($ls as $l) { $found = $l; }
        }
        if($found) {
            $this->log($found['id']);
            return true;
        } else {
            return false;
        }
    }
    
    public function log($id) {
        session_regenerate_id(true);
        $_SESSION['userid'] = $id;
    }

    public function logoff() {
        $_SESSION['userid'] = null;
        session_regenerate_id(true);
    }
    
    public function isLogged() {
        return !empty($_SESSION['userid']);
    }
    
    public function isAdmin(): bool {
        global $db;
        if(!$this->isLogged()) { return false; }
        $s = $db->prepare('select isadmin from users where id = :id');
        $s->execute(['id' => $_SESSION['userid']]);
        return (bool) $s->fetchColumn();
    }
    
    public function getSid() {
        return session_id();
    }
    
    public function getCodeFromLogin($login) {
        global $db;
        $q = 'select id, pwd from users where login = :login';
        $stmt = $db->prepare($q);
        $stmt->execute(['login' => $login]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function resetPwd($id, $code, $newPwd) {
        global $db;
        $q = 'update users set pwd = :newPwd where id = :id and pwd = :code';
        $stmt = $db->prepare($q);
        $stmt->execute([
            'newPwd' => md5($newPwd),
            'id' => $id,
            'code' => $code
        ]);
    }
}