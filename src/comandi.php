<?php

function digest($string) {
    return md5($string . $GLOBALS['config']['salt']);
}


if (strpos($msg, "/ping") === 0) {
    $inizio = (int) $datemsg;
    $msg_r = smReturn($chatID, "<i>Pong</i>", $idcmd);
    $id_r = $msg_r['message_id'];
    $date_r = (int) $msg_r['date'];
    $seconds_delay = $date_r - $inizio;
    editm($chatID, $id_r, "<i>Delay: $seconds_delay seconds.</i>");
    sleep(5);
    delm($chatID, $id_r);
    delm($chatID, $idmsg);
}

//COMANDI
if ($chatID == $userID) {
    if ($msg == "/start") {
        sm($chatID, "In privato posso dirti se qualcuno si prenota ad una tua offerta.\n\nSorgenti: https://github.com/ldeluigi/telegram-lootbot-contrabbandiere");
    } elseif ($msg == "/clear") {
        clear();
    } elseif ($msg == "/search") {
        search();
    } elseif ($msg == "/repost") {
        sm($chatID, "$username, questo comando funziona solo nei gruppi in cui è presente il bot.");
    } elseif (($msg=="/reset") and (in_array($userID, $admins))) {
        sm($chatID, '🚽 Rimuoverò tutto nei prossimi minuti.');
        dbreset();
    } elseif ($msg == "/culo") {
        sm($chatID, json_encode(is_between_times('00:28', '22:44')));
    }
    return;
} else if (!in_array($chatID, $groups) and $GLOBALS['config']["allow_all_groups"] !== true) {
    sm($chatID, "Questo gruppo [ID:<code>$chatID</code>] non è abilitato.\n\n<i>Grazie e arrivederci</i>");
    leave();
    return;
}

//171514820 è l'id di loot bot
if (($inoltrato) and ($inoltrato_id==171514820) and (mb_strpos($msg, 'Benvenut')===0) and (mb_strpos($msg, "Puoi creare oggetti per il Contrabbandiere")>0) and (date("z Y", time())===date("z Y", $inoltrato_time))) {
    $taglioBenvenuto = mb_substr($msg, 10);
    $dalPuntoEsclamativo = mb_split("!", $taglioBenvenuto);
    $nome = $dalPuntoEsclamativo[0];
    if ($nome!=$username) {
        sm($chatID, "⚠️ $username, inoltra solo le offerte che ti appartengono, o invita @$nome nel gruppo.");
    } else {
        $info_line = mb_split("\n", mb_split("affari diversi.\n\n", $dalPuntoEsclamativo[1])[1])[0];
        $info = mb_split(" al prezzo di ", $info_line);
        $item = $info[0];
        $prezzo = number_format((float) filter_var(trim($info[1]), FILTER_SANITIZE_NUMBER_INT), 0, ",", ".");
        if (prezzoFreeAbilitato()) {
            $prezzo = liberaPrezzo($prezzo);
        }
        if (negPvtAbilitato() && is_between_times('09:00', '22:44')) {
            $prezzo = negPvtPrezzo($prezzo);
        }
        if (mb_strpos($info[1], "dimezzato")!== false and mb_strpos($info[1], "malus")!== false) {
            $prezzo = $prezzo." Ⓜ️";
        }
        //nome item prezzo
        $test = digest($nome.$item.$prezzo);
        $q = $GLOBALS['db']->prepare("SELECT * FROM contrabbandi WHERE test=? OR test=?");
        $q->execute(array($test, '*' . $test));
        if ($q->rowCount() === 0) {
            $t = time();
            $q2 = $GLOBALS['db']->prepare("INSERT INTO contrabbandi (time, test, nome, item, prezzo, chat_id, creation) VALUES(?, ?, ?, ?, ?, ?, ?)");
            $q2->execute(array($t, $test, $nome, $item, $prezzo, $chatID, $t));
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
            $k = sm($chatID, "👤 <a href=\"tg://user?id=$userID\">$nome</a>\n🛠 <b>$item</b>\n📦 ".getPC($item)." pc\n💰 $prezzo §\n🏷 #".((is_numeric($c)) ? $c."x" : $c), $menu, 'pred', false);
            $uq = $GLOBALS['db']->prepare("UPDATE contrabbandi SET message_id=? WHERE test=?");
            $uq->execute(array($k, $test));

            //AGGIUNTA A items
            $item_1 = trim(substr($item, 0, strpos($item, "(")));
            $price_1 = abs((int) filter_var($prezzo, FILTER_SANITIZE_NUMBER_INT));
            $i_query = $GLOBALS['db']->prepare("SELECT * FROM items WHERE name LIKE ?");
            $i_query->execute(array($item_1));
            if ($i_query->rowCount() == 0) {
                $items = richiestaAPI("http://fenixweb.net:3300/api/v2/$token/items");
                $dec = json_decode($items, true);
                $arr = $dec["res"];
                if ($arr[0]["id"]) {
                    foreach ($arr as $i) {
                        $t = $GLOBALS['db']->prepare("REPLACE INTO items (id, name, rarity, description, value, estimate, craftable, searchable, reborn, power, power_armor, power_shield, dragon_power, critical, allow_sell) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $t->execute(array(
                            (string) $i['id'],
                            $i['name'],
                            $i['rarity'],
                            $i['description'],
                            (string) $i['value'],
                            (string) $i['estimate'],
                            (string) $i['craftable'],
                            (string) $i['reborn'],
                            (string) $i['power'],
                            (string) $i['power_armor'],
                            (string) $i['power_shield'],
                            (string) $i['dragon_power'],
                            (string) $i['critical'],
                            (string) $i['category'],
                            (string) $i['allow_sell']
                        ));
                        if ($i['name'] == $item_1) {
                            $ut = $GLOBALS['db']->prepare("UPDATE items SET secret_price=? WHERE id=?");
                            $ut->execute(array($price_1, (string) $i['id']));
                        }
                    }
                }
            } else {
                $uq = $GLOBALS['db']->prepare("UPDATE items SET secret_price=? WHERE name LIKE ?");
                $uq->execute(array($price_1, $item_1));
            }
        } else {
            $m = sm($chatID, "⚠️ $username, questa offerta è già stata inoltrata, usa /repost se vuoi riportarla in primo piano.");
            sleep(5);
            delm($chatID, $m);
        }
    }
    delm($chatID, $idmsg);
}


if (($cbid) and (mb_strpos($msg, "p")===0) and (mb_strpos($cbmtext, "👤 ".$username)!==0)) {
    $test = mb_split("_", $msg)[1];
    $q = $GLOBALS['db']->prepare("SELECT * FROM contrabbandi WHERE test=?");
    $q->execute(array($test));
    if ($q->rowCount() === 1) {
        $rows = $q->fetchAll(PDO::FETCH_ASSOC);
        $array = $rows[0];
        $upd = $GLOBALS['db']->prepare("UPDATE contrabbandi SET test=? WHERE test=?");
        $upd->execute(array($array['test'], $test));
        $menu[] = array(
            array(
                "text" => "Rinuncio",
                "callback_data" => "r_$test"
            )
        );
        $menu[] = array(
            array(
                "text" => "Concludi richiesta",
                "callback_data" => "c_$test"
            )
        );
        $tag = mb_strpos($cbmtext, "🏷 #");
        $pure_item = trim(substr($array['item'], 0, strpos($array['item'], "(")));
        cb_reply($cbid, 'Prenotato!', false, $cbmid, restoreEntities(mb_substr($cbmtext, 0, ($tag) ? $tag : mb_strlen($cbmtext)), $entities)."\n\n❗️ <a href=\"tg://user?id=$userID\">".$username."</a>", $menu);
        if ($entities[0]['user']['id']) {
            $c = sm($entities[0]['user']['id'], "@".$username." si è prenotato per la tua offerta! (Oggetto:".$array['item'].", Prezzo: ".$array['prezzo'].")");
        }
        sm($userID, "Ti sei prenotato per ".$array['item']." di @".$array['nome']." a ".$array['prezzo']." §".((($c==false) and ($entities[0]['user']['id'])) ? "\n\n".$array['nome']." non ha il bot attivo, consiglio di taggarlo." : "\n\n".$array['nome']." ha il bot attivo, non serve taggarlo.")."\n\n@LootGameBot:\n<code>Cerca *".$pure_item."</code>\n\n@Craftlootbot:\n<code>/lista ".$pure_item."</code>\n<code>/craft ".$pure_item."</code>\n\n@ToolsForLootBot:\n<code>@ToolsForLootBot $pure_item</code>");//<a href=\"tg://user?id=280391978\">
    }
} elseif (($cbid) and (mb_strpos($msg, "p")===0) and (mb_strpos($cbmtext, "👤 ".$username)===0)) {
    cb_reply($cbid, 'Tu non puoi!', false);
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
    } else {
        cb_reply($cbid, 'Non sei tu ad essere prenotato!', false);
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
if (mb_strpos($msg, "/start@ContrabbAndroideBot")===0) {
    sm($chatID, "Sono operativo, potete inoltrare le vostre richieste.");
}

if ($msg == "/open") {
    $q = $GLOBALS['db']->prepare("SELECT COUNT(*) as count FROM contrabbandi");
    $q->execute();
    $rows = $q->fetchAll(PDO::FETCH_ASSOC);
    $n = $rows[0]['count'];
    $q = $GLOBALS['db']->prepare("SELECT COUNT(*) as count FROM contrabbandi WHERE test LIKE ?");
    $q->execute(array("*%"));
    $rows = $q->fetchAll(PDO::FETCH_ASSOC);
    $m = $rows[0]['count'];
    sm($chatID, "📈 $n offerte da chiudere/eliminare\n📬 $m rimaste prenotate.");
}


if (strpos($msg, "/repost")===0) {
    repost();
}
if (strpos($msg, "/free")===0) {
    free();
}
if (strpos($msg, "/clear")===0) {
    clear();
}
if (strpos($msg, "/search")===0) {
    search();
}

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

if (($msg=="/reset") and (in_array($userID, $admins))) {
    dbreset();

    sm($chatID, '🚽 Rimosse tutte le offerte nel database.');
    return;
}





////////////////////////////////////////////////////////////
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
