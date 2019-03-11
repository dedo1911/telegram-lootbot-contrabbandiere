<?php

// TODO: spostare su functions
function extract_username ($text) {
  $taglioBenvenuto = substr($text, 10);
  $dalPuntoEsclamativo = explode('!', $taglioBenvenuto);
  return $dalPuntoEsclamativo[0];
}

function filter_infoline ($line) {
  return strpos($line, 'al prezzo di');
}

// TODO: spostare su functions
function extract_item ($text) {
  $rows = split("\n", $text);
  $rows = array_filter($rows, "filter_infoline");
  $infoline = explode(' al prezzo di ', $rows[0]);
  return $infoline[0];
}

// TODO: spostare su functions
function extract_price ($text) {
  $rows = split("\n", $text);
  $rows = array_filter($rows, "filter_infoline");
  $infoline = explode(' al prezzo di ', $rows[0]);
  $price = trim($infoline[1]);
  $sanitizedPrice = (float) filter_var($price, FILTER_SANITIZE_NUMBER_INT);
  return number_format($sanitizedPrice, 0, ",", ".");
  // TODO: controllo "dimezzato" o "malus" e aggiungere Ⓜ️ al prezzo
}

// TODO: Spostare su functions
function get_hashtag ($test) {
  $hashtag = mb_substr($test, 0, 8);
  return is_numeric($hashtag) ? $hashtag."x" : $hashtag;
}

// TODO: Spostare su functions
// TODO: Se non lo trovo, lo richiedo all'api di loot
function get_item ($searchItem) {
  $slices = explode(' (', $searchItem);
  $searchItem = $slices[0].trim();
  $stmt = $GLOBALS['db']->prepare('SELECT * FROM items WHERE name=? LIMIT 1'); 
  $stmt->execute(array($searchItem));
  return $stmt->fetch(PDO::FETCH_ASSOC);
}

function handle_forward () {
  $update = $GLOBALS['telegram']->getData();
  // TODO: controllo che il messaggio inoltrato sia di lootbot, altrimenti ignoro
  // TODO: controllo che sia un messaggio proveniente dal contrabbandiere
  // TODO: controllo che il messaggio sia di oggi
  $owner = extract_username($update["message"]['text']);
  if ($update["message"]["from"]["username"] != $owner) {
    $GLOBALS['telegram']->sendMessage(array(
      'chat_id' => $update['message']['chat']['id'],
      'text' => message_forward_not_owner($update["message"]["from"]["username"], $owner),
      'parse_mode' => $GLOBALS['config']["formattazione_predefinita"]
    ));
  } else {
    $price = extract_price($update["message"]['text']);
    $itemName = extract_item($update["message"]['text']);
    // Chiave univoca offerta (owner, item, price)
    $test = digest($update["message"]["from"]["username"].$itemName.$price);
    $q = $GLOBALS['db']->prepare('SELECT * FROM contrabbandi WHERE test=? OR test=?');
    $q->execute(array($test, '*' . $test));
    if ($q->rowCount() > 0) {
      // Offerta già postata
      // Propongo repost, attendo, ed elimino.
      $sentMessage = $GLOBALS['telegram']->sendMessage(array(
        'chat_id' => $update['message']['chat']['id'],
        'text' => message_already_forwarded($update["message"]["from"]["username"]),
        'parse_mode' => $GLOBALS['config']["formattazione_predefinita"]
      ));
      sleep(5);
      $GLOBALS['telegram']->deleteMessage(array(
        'chat_id' => $update['message']['chat']['id'],
        'message_id' => $sentMessage['result']['message_id']
      ));
    } else {
      // Invio messaggio con tastiera
      $time = time();
      $q2 = $GLOBALS['db']->prepare('INSERT INTO contrabbandi (time, test, nome, owner_id, item, prezzo, chat_id, creation) VALUES(?, ?, ?, ?, ?, ?, ?, ?)');
      $q2->execute(array(
        $time,
        $test,
        $update["message"]["from"]["username"],
        $update["message"]["from"]["id"],
        $itemName,
        $price,
        $update['message']['chat']['id'],
        $time
      ));
      $hashtag = get_hashtag($test);
      $item = get_item($itemName);
      $GLOBALS['telegram']->sendMessage(array(
        'chat_id' => $update['message']['chat']['id'],
        'text' => message_offer($update["message"]["from"]["id"], $update["message"]["from"]["username"], $item, $price, $hashtag),
        'parse_mode' => $GLOBALS['config']["formattazione_predefinita"],
        'reply_markup' => array('inline_keyboard' => menu_offer($test))
      ));
      // Aggiorno db per segnarmi id del messaggio
      $uq = $GLOBALS['db']->prepare("UPDATE contrabbandi SET message_id=? WHERE test=?");
      $uq->execute(array($k, $test));
      // Aggiorno prezzo dell'offerta su secret_price dell'item
      $uprice = $GLOBALS['db']->prepare('UPDATE items SET secret_price=? WHERE id=?');
      $uprice->execute(array(
        $price,
        $item['id']
      ));
    }
  }
  $GLOBALS['telegram']->deleteMessage(array(
    'chat_id' => $update['message']['chat']['id'],
    'message_id' => $update['message']['message_id']
  ));
}

// if ($cbid) and (mb_strpos($msg, "p")===0) 
function handle_callback_reserve () { // mi prenoto
  $text = $GLOBALS['telegram']->Callback_Message()['text'];
  if (strpos($text, '👤 '.$GLOBALS['telegram']->Username()) === 0) {
    // Ti stai prenotando per la tua stessa offerta
    $GLOBALS['telegram']->answerCallbackQuery(array(
      'callback_query_id' => $GLOBALS['telegram']->Callback_ID(),
      'text' => messages_reserve_denied(),
      'show_alert' => false
    ));
  } else {
    // Procedo con la prenotazione
    $request = explode('_', $GLOBALS['telegram']->Callback_Data());
    $test = $request[1];
    $stmt = $GLOBALS['db']->prepare('SELECT * FROM contrabbandi WHERE test=?');
    $stmt->execute(array($test));
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($res) === 1) {
      $contrabbando = $res[0];
      // TODO: aggiungo * davanti a test su db
      // Rispondo alla callback query
      $GLOBALS['telegram']->answerCallbackQuery(array(
        'callback_query_id' => $GLOBALS['telegram']->Callback_ID(),
        'text' => messages_reserve_confirm(),
        'show_alert' => false
      ));
      // Modifico messaggio scrivendo chi si è prenotato
      $GLOBALS['telegram']->editMessageText(array(
        'chat_id' => $contrabbando['chat_id'],
        'message_id' => $contrabbando['message_id'],
        'text' => message_offer_reserved(
          $contrabbando['owner_id'],
          $contrabbando['nome'],
          $contrabbando['item'],
          $contrabbando['prezzo'],
          $GLOBALS['telegram']->UserID(),
          $GLOBALS['telegram']->Username()
        ),
        'parse_mode' => $GLOBALS['config']["formattazione_predefinita"],
        'reply_markup' => array('inline_keyboard' => menu_reserved($test))
      ));
      // sendMessage: al chi ha postato, dicendo chi si è prenotato
      $privMess = $GLOBALS['telegram']->sendMessage(array(
        'chat_id' => $contrabbando['owner_id'],
        'text' => message_reserved($GLOBALS['telegram']->Username(), $contrabbando),
        'parse_mode' => $GLOBALS['config']["formattazione_predefinita"]
      ));
      //sendMessage: a chi si è prenotato, dicendogli che si è prenotato
      $GLOBALS['telegram']->sendMessage(array(
        'chat_id' => $GLOBALS['telegram']->UserID(),
        'text' => message_reserved_confirm($contrabbando, $privMess['ok']),
        'parse_mode' => $GLOBALS['config']["formattazione_predefinita"]
      ));
    }
  }
}

function handle_callback_complete () {
  // TODO: solo admin/creatore gruppo può lanciare questo comando
}

function handle_callback_resign () {
  $text = $GLOBALS['telegram']->Callback_Message()['text'];
  $prenotato = explode('\n\n❗️ ', $text);
  $nick = substr($prenotato[1], 0);
  $nick = str_replace('@', '', $nick);

  if ($GLOBALS['telegram']->Username() == $nick) {
    $request = explode('_', $GLOBALS['telegram']->Callback_Data());
    $test = $request[1];
    $stmt = $GLOBALS['db']->prepare('SELECT * FROM contrabbandi WHERE test=?');
    $stmt->execute(array($test));
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($res) === 1) {
      $contrabbando = $res[0];
      // TODO: Tolgo * da contrabbando "test"
      // TODO: Aggiorno messaggio con nuovi tasti
      // TODO: Rispondo alla callback query dicendo di aver rinunciato
      // TODO: Invio messaggio privato a chi ha postato l'offerta dicendo che e' stata rifiutata
      // TODO: Invio messaggio privato a chi si era offerto, dicendo che si e' rifiutato
      // TODO: Aggiorno il time dell'offerta sul db
    }
  } else {
    $GLOBALS['telegram']->answerCallbackQuery(array(
      'callback_query_id' => $GLOBALS['telegram']->Callback_ID(),
      'text' => message_resign_failed(),
      'show_alert' => false
    ));
  }
}

function handle_answer () {
  // TODO: Verifico che la risposta sia effettivamente una risposta ad un messaggio del bot
}
