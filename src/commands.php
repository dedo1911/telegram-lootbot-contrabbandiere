<?php

// TODO: Spostare su functions
function digest ($string) {
    return md5($string . $GLOBALS['config']['salt']);
}

// TODO: Spostare su functions
function is_group_enabled () {
    $update = $GLOBALS['telegram']->getData();
    if (in_array($update["message"]["chat"]["id"], $GLOBALS['config']['groups']) ||
        $GLOBALS['config']["allow_all_groups"] === true) return true;
    
    $GLOBALS['telegram']->sendMessage(array(
        'chat_id' => $update['message']['chat']['id'],
        'text' => message_group_not_enabled($update['message']['chat']['id']),
        'parse_mode' => $GLOBALS['config']["formattazione_predefinita"]
    ));
    $GLOBALS['telegram']->leaveChat(array(
        'chat_id' => $update['message']['chat']['id']
    ));
    return false;
}

// TODO: Spostare su functions
function is_admin () {
    $update = $GLOBALS['telegram']->getData();
    return in_array($update["message"]["chat"]["id"], $GLOBALS['config']['admins']);
}

// /ping (uguale sia gruppo che privato)
function command_ping () {
    $update = $GLOBALS['telegram']->getData();
    $result = $GLOBALS['telegram']->sendMessage(array(
        'chat_id' => $update['message']['chat']['id'],
        'text' => message_pong(),
        'parse_mode' => $GLOBALS['config']["formattazione_predefinita"]
    ));
    $message = $result['result'];
    $delay = $update['message']['date'] - $message['date'];
    $GLOBALS['telegram']->editMessageText(array(
        'chat_id' => $update['message']['chat']['id'],
        'message_id' => $message['message_id'],
        'text' => message_delay($delay),
        'parse_mode' => $GLOBALS['config']["formattazione_predefinita"]
    ));
    sleep(5);
    $GLOBALS['telegram']->deleteMessage(array(
        'chat_id' => $update['message']['chat']['id'],
        'message_id' => $message['message_id']
    ));
    $GLOBALS['telegram']->deleteMessage(array(
        'chat_id' => $update['message']['chat']['id'],
        'message_id' => $update['message']['message_id']
    ));
}

// /start (gestisce sia gruppo che privato)
function command_start () {
    $update = $GLOBALS['telegram']->getData();
    if ($update["message"]["chat"]["id"] == $update["message"]["from"]["id"]) { // In privato
        $GLOBALS['telegram']->sendMessage(array(
            'chat_id' => $update['message']['chat']['id'],
            'text' => message_start_private(),
            'parse_mode' => $GLOBALS['config']["formattazione_predefinita"]
        ));
    } else { // In gruppo
        if (!is_group_enabled()) return;
        $GLOBALS['telegram']->sendMessage(array(
            'chat_id' => $update['message']['chat']['id'],
            'text' => message_start_group(),
            'parse_mode' => $GLOBALS['config']["formattazione_predefinita"]
        ));
    }
}

// /clear (uguale sia gruppo che privato)
function command_clear () {
    $update = $GLOBALS['telegram']->getData();
    clear(); // TODO: rifattorizzare l'esecuzine del comando
}

// /search (uguale sia gruppo che privato)
function command_search () {
    $update = $GLOBALS['telegram']->getData();
    search(); // TODO: rifattorizzare l'esecuzine del comando
}

// /repost (gestisce solo gruppo)
function command_repost () {
    $update = $GLOBALS['telegram']->getData();
    if ($update["message"]["chat"]["id"] == $update["message"]["from"]["id"]) { // In privato
        $GLOBALS['telegram']->sendMessage(array(
            'chat_id' => $update['message']['chat']['id'],
            'text' => message_repost_private($update["message"]["from"]["username"]),
            'parse_mode' => $GLOBALS['config']["formattazione_predefinita"]
        ));
    } else { // In gruppo
        repost(); // TODO: rifattorizzare l'esecuzine del comando
    }
}

// /reset (solo admin)
function command_reset () {
    $update = $GLOBALS['telegram']->getData();
    if (!is_admin()) return;
    $GLOBALS['telegram']->sendMessage(array(
        'chat_id' => $update['message']['chat']['id'],
        'text' => message_reset(),
        'parse_mode' => $GLOBALS['config']["formattazione_predefinita"]
    ));
    dbreset(); // TODO: rifattorizzare l'esecuzione del comando
}

// /free (sia gruppo che privato)
function command_free () {
    free(); // TODO: rifattorizzare l'esecuzione del comando
}
// /open (sia gruppo che privato)
function command_open () {
    // TODO: sistemare query. si può fare con una query sola.
    $q = $GLOBALS['db']->prepare("SELECT COUNT(*) as count FROM contrabbandi");
    $q->execute();
    $rows = $q->fetchAll(PDO::FETCH_ASSOC);
    $n = $rows[0]['count'];
    $q = $GLOBALS['db']->prepare("SELECT COUNT(*) as count FROM contrabbandi WHERE test LIKE ?");
    $q->execute(array("*%"));
    $rows = $q->fetchAll(PDO::FETCH_ASSOC);
    $m = $rows[0]['count'];
    $GLOBALS['telegram']->sendMessage(array(
        'chat_id' => $update['message']['chat']['id'],
        'text' => message_open($n, $m),
        'parse_mode' => $GLOBALS['config']["formattazione_predefinita"]
    ));
}

//171514820 è l'id di loot bot
if (($inoltrato) and ($inoltrato_id==171514820) and (mb_strpos($msg, 'Benvenut')===0) and (mb_strpos($msg, "Puoi creare oggetti per il Contrabbandiere")>0) and (date("z Y", time())===date("z Y", $inoltrato_time))) {

}


if (($cbid) and (mb_strpos($msg, "p")===0) and (mb_strpos($cbmtext, "👤 ".$username)!==0)) {

}

if (($cbid) and (mb_strpos($msg, "r")===0)) {
    $prenotato = mb_split("\n\n❗️ ", $cbmtext);
    $nick = mb_substr($prenotato[1], 0);
    $nick = mb_ereg_replace("@", "", $nick);
    if ($username==$nick) {
        $pezzi = mb_split("\n", $prenotato[0]);
        $nome = mb_substr($pezzi[0], 2);
        $item = mb_substr($pezzi[1], 2);
        $pc = mb_substr($pezzi[2], 2);
        $prezzo = mb_substr($pezzi[3], 2, (mb_strpos($pezzi[3], " ")>0) ? mb_strpos($pezzi[3], " ") -3 : 0);
        $test = mb_split("_", $msg)[1];
        $q = $GLOBALS['db']->prepare("SELECT * FROM contrabbandi WHERE test=?");
        $q->execute(array($test));
        if ($q->rowCount() === 0) {
            $iq = $GLOBALS['db']->prepare("INSERT INTO contrabbandi (time, test, nome, item, prezzo, chat_id, creation) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $iq->execute(array(
                (string) time(),
                $test,
                $nome,
                $item,
                $prezzo,
                $chatID,
                (string) time()
            ));
            $dq = $GLOBALS['db']->prepare("DELETE FROM contrabbandi WHERE test=?");
            $dq->execute(array('*' . $test));
            $menu[] = array(
                array(
                    "text" => "Mi prenoto",
                    "callback_data" => "p_$test"
                )
            );
            $menu[] = array(
                array(
                    "text" => "Concludi richiesta",
                    "callback_data" => "c_$test"
                )
            );
            $c = mb_substr($test, 0, 8);
            cb_reply($cbid, 'Hai rinunciato.', false, $cbmid, "🗣 $username\n👤 $nome\n🛠 <b>$item</b>\n📦 $pc\n💰 $prezzo §\n\n🙌");
            $k = sm($chatID, mb_split("\n\n❗️ ", restoreEntities($cbmtext, $entities))[0]."🏷 #".((is_numeric($c)) ? $c."x" : $c), $menu, 'pred', false, $cbmid);
            if ($entities[0]['user']['id']) {
                sm($entities[0]['user']['id'], "@".$username." ha rinunciato alla tua offerta. (Oggetto: ".$item.", Prezzo: ".$prezzo.")");
            }
            sm($userID, "Hai rinunciato all'offerta di $nome. (Oggetto: <b>$item</b>, Prezzo:$prezzo)");
            $uq2 = $GLOBALS['db']->prepare("UPDATE contrabbandi SET time=?, message_id=? WHERE test=?");
            $uq2->execute(array(
                (string) time(),
                $k,
                $test
            ));
        }
    }
}

if (($cbid) and (mb_strpos($msg, "c")===0) and ((mb_strpos($cbmtext, "👤 ".$username)===0) or (($status = memberstatus($chatID, $userID))=='administrator' or $status=='creator'))) {
    $test = mb_split("_", $msg)[1];
    $dc = $GLOBALS['db']->prepare("DELETE FROM contrabbandi WHERE test=? OR test=?");
    $dc->execute(array($test, '*'.$test));
    if (mb_strpos($cbmtext, "\n\n❗️ ")!==false) {
        $prenotato = mb_split("\n\n❗️ ", $cbmtext);
        $nick = mb_substr($prenotato[1], 0);
        $nick = mb_ereg_replace("@", "", $nick);
    } else {
        $nick = false;
        $prenotato[0] = $cbmtext;
    }

    $pezzi = mb_split("\n", $prenotato[0]);
    $nome = mb_substr($pezzi[0], 2);
    $item = mb_substr($pezzi[1], 2);
    $pc = mb_substr($pezzi[2], 2);
    $prezzo = mb_substr($pezzi[3], 2);
    cb_reply($cbid, 'Richiesta conclusa!', false, $cbmid, ($nick!=false)? (($nome==$username)? "👤 $nome\n🗣 $nick\n🛠 <b>$item</b>\n📦 $pc\n💰 $prezzo\n\n✅" : "⚜️ $username\n👤 $nome\n🗣 $nick\n🛠 <b>$item</b>\n📦 $pc\n💰 $prezzo\n\n⛔️") : (($nome==$username)? "👤 $nome\n🛠 <b>$item</b>\n📦 $pc\n💰 $prezzo\n\n❌" : "⚜️ $username\n👤 $nome\n🛠 <b>$item</b>\n📦 $pc\n💰 $prezzo\n\n🛑"));
    if ($entities[0]['user']['id'] and $nome!=$username) {
        sm($entities[0]['user']['id'], "⚜️ @$username ha imposto la chiusura della tua richiesta.");
    }
} elseif (($cbid) and (mb_strpos($msg, "c")===0)) {
    cb_reply($cbid, 'Non sei autorizzato.', false);
}

if ($cbid and $msg == 'del_msg') {
    if ($entities[0]['user']['id'] and $entities[0]['user']['id'] == $userID) {
        cb_reply($cbid, 'Cancello il tag...', false);
        delm($chatID, $cbmid);
    } else {
        cb_reply($cbid, 'Non sei tu ad essere taggato!', false);
    }
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (($risposta) and ($risposta_userID==396023029) and (mb_strpos($risposta_msg, "\n\n❗️ ")!==false)) {
    $prenotato = mb_split("\n\n❗️ ", $risposta_msg);
    $nick = mb_substr($prenotato[1], 0);
    $nick = mb_ereg_replace("@", "", $nick);
    if ($username==$nick) {
        $pezzi = mb_split("\n", $prenotato[0]);
        $nome = mb_substr($pezzi[0], 2);
        $item = mb_substr($pezzi[1], 2);
        $prezzo = mb_substr($pezzi[3], 2);
        if ($risposta_entities[0]['user']['id']) {
            $tagged = false;
            foreach ($entities as $entity) {
                if ($entity['type'] == "mention" and strpos($msg, "@$nome") !== false) {
                    $tagged = true;
                    break;
                }
            }
            $c1 = sm($risposta_entities[0]['user']['id'], "<a href=\"tg://user?id=$userID\">$username</a> ha risposto alla tua offerta (Oggetto:".$item.", Prezzo: ".$prezzo.")...");
            $c2 = fm($risposta_entities[0]['user']['id'], $chatID, $idmsg);
            if (!$tagged and !($c1 and $c2)) {
                //sm($userID, "<a href=\"tg://user?id=".((string) $risposta_entities[0]['user']['id'])."\">$nome</a> non ha il bot attivo, prova a taggarlo.");
                sm($chatID, "<a href=\"tg://user?id=".((string) $risposta_entities[0]['user']['id'])."\">$nome</a> attiva il <a href=\"t.me/$userbot\">bot</a> per essere notificato in privato.", array(array(array('text' => 'OK', 'callback_data' => 'del_msg'))), 'HTML', false, $idmsg);
            } elseif ($tagged) {
                sm($userID, "<i>Quando rispondi a un'offerta ci penso io a contattare/taggare l'interessato.</i>");
            }
        }
    }
}
