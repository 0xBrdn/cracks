<?php
    session_start();
    ob_start();
    require_once '../config.php';
    
    function countVotes(int $cid, int $uid) {
        global $db;
        $returns = ['votes' => 0, 'voted' => 0,];
        $q = 'select sum(v.val) as tot, case when u.id is not null then 1 else 0 end as voted '
                . ' from votes v '
                . ' left join cracks c on c.id=v.crack '
                . " left join users u on u.id=v.voter and u.id=$uid "
                . " where c.id=$cid "
                . " group by c.id";
        $ls = $db->query($q, PDO::FETCH_ASSOC);
        if(!empty($ls) && ($rw = $ls->fetch(PDO::FETCH_ASSOC))) {
            $returns['votes'] = $rw['tot'];
            $returns['voted'] = $rw['voted'];
        }
        return $returns;
    }
    
    function doVote(int $cid, int $uid, int $val) {
        global $db;
        $q = 'insert into votes (crack, voter, val) values(:c, :u, :v)';
        $s = $db->prepare($q);
        $s->execute(['c' => $cid, 'u' => $uid, 'v' => $val,]);
    }
    
    $result = ['errors' => [],];
    
    try {
        if(!empty($_SERVER['REQUEST_METHOD'])) {
            if('GET' === strtoupper($_SERVER['REQUEST_METHOD'])) {
                if(!empty($_REQUEST['cid'])) {
                    $v = countVotes(intval($_REQUEST['cid']), $_REQUEST['uid']);
                    $result['val'] = $v['votes'];
                    $result['voted'] = $v['voted'];
                } else {
                    $result['errors'][] = 'Not found';
                }
            } else if('POST' === strtoupper($_SERVER['REQUEST_METHOD'])) {
                $decoded = json_decode(file_get_contents('php://input'));
                $cid = intval($decoded->cid ?? 0);
                $val = intval($decoded->val ?? 0);
                $uid = intval($_SESSION['userid'] ?? 0);
                
                if($uid <= 0) {
                    $result['errors'][] = 'Connexion requise';
                } else if($val !== 1 && $val !== -1) {
                    $result['errors'][] = 'Vote invalide';
                } else {
                    $s = $db->prepare('select count(*) from votes where crack = ? and voter = ?');
                    $s->execute([$cid, $uid]);
                    if($s->fetchColumn() > 0) {
                        $result['errors'][] = 'Déjà voté';
                    } else {
                        doVote($cid, $uid, $val);
                        $result['ok'] = 'ok';
                    }
                }
            } else {
                $result['errors'][] = 'Invalid method';
            }
        } else {
            $result['errors'][] = 'Empty method';
        }
    } catch(Exception $e) {
        $result['errors'][] = $e->getMessage();
    }
    
    ob_end_clean();
    header('Content-Type:application/json');
    echo json_encode($result);
