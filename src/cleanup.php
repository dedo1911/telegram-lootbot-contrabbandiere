<?php

if (($cbid) or ($inoltrato) or (strpos($msg, "/")===0)) {
    $p = $GLOBALS['db']->prepare("SELECT * FROM contrabbandi ORDER BY time LIMIT 3");
    $p->execute();
    $n = $p->rowCount();
    $rows = $p->fetchAll(PDO::FETCH_ASSOC);
    if ($n > 0) {
        for ($i=0; ($i<$n) and ($i<3); $i++) {
            $b = $rows[$i];
            if (((time() - $b['time'])>57600) or ((time() - $b['creation'])>86400)) {
                $dq = $GLOBALS['db']->prepare("DELETE FROM contrabbandi WHERE test=?");
                $dq->execute(array($b['test']));
                $oldchatID = $b['chat_id'];
                $oldmessID = $b['message_id'];
                $item = $b['item'];
                $prezzo = $b['prezzo'];
                $nome = $b['nome'];
                if (($oldchatID!=0) and ($oldmessID!=0)) {
                    editm($oldchatID, $oldmessID, "👴🏻 Questa offerta è obsoleta. (Oggetto: <b>$item</b>, Prezzo:$prezzo, Proprietario: $nome)");
                }
                sleep(5);
            }
        }
    }
}
