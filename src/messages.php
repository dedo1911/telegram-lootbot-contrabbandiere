<?php

function message_pong () {
  return '<i>Pong</i>';
}

function message_delay ($seconds) {
  return '<i>Delay: '.$seconds.' seconds.</i>';
}

function message_group_not_enabled ($id) {
  return 'Questo gruppo [ID:<code>' . $id . '</code>] non è abilitato.\n\n'.
  '<i>Grazie e arrivederci</i>';
}

function message_start_private () {
  return 'In privato posso dirti se qualcuno si prenota ad una tua offerta.\n\n'.
  'Sorgenti: https://github.com/ldeluigi/telegram-lootbot-contrabbandiere';
}

function message_start_group () {
  return 'Sono operativo, potete inoltrare le vostre richieste.';
}

function message_repost_private ($username) {
  return  $username . ' questo comando funziona solo nei gruppi in cui è presente il bot.';
}

function message_open ($chiudere, $prenotate) {
  return '📈 ' . $chiudere . ' offerte da chiudere/eliminare\n'.
  '📬 ' . $prenotate . ' rimaste prenotate.';
}

function message_forward_not_owner ($username, $realOwner) {
  return '⚠️ ' . $username . ', inoltra solo le offerte che ti appartengono, o invita @ ' . $realOwner . ' nel gruppo.';
}

function message_already_forwarded ($username) {
  return '⚠️ ' . $username . ', questa offerta è già stata inoltrata, usa /repost se vuoi riportarla in primo piano.';
}

function messages_reserve_denied () {
  return 'Tu non puoi!';
}

function messages_reserve_confirm () {
  return 'Prenotato!';
}

function message_offer ($tgUserId, $tgUsername, $item, $price, $hashtag) {
  return '👤 <a href="tg://user?id='.$tgUserId.'">'.$tgUsername.'</a>\n'.
  '🛠 <b>'.$item['name'].'</b>\n'.
  '📦 '.$item['craft_pnt'].' pc\n'.
  '💰 '.$price.' §\n'.
  '🏷 #'.$hashtag;
}

function message_offer_reserved ($tgUserId, $tgUsername, $item, $price, $resTgUserId, $resTgUsername) {
  return '👤 <a href="tg://user?id='.$tgUserId.'">'.$tgUsername.'</a>\n'.
  '🛠 <b>'.$item['name'].'</b>\n'.
  '📦 '.$item['craft_pnt'].' pc\n'.
  '💰 '.$price.' §\n'.
  '\n❗️ <a href="tg://user?id='.$resTgUserId.'">'.$resTgUsername.'</a>';
}

function message_reset () {
  return '🚽 Rimosse tutte le offerte nel database.';
}

function message_reserved ($username, $contrabbando) {
  return '@'.$username.' si è prenotato per la tua offerta!\n'.
    'Oggetto: '.$contrabbando['item'].'\n'.
    'Prezzo: '.$contrabbando['prezzo'].' §';
}

function message_reserved_confirm ($contrabbando, $sentPvt) {
  $itemObj = get_item($contrabbando['item']);
  $item = $itemObj['name'];
  return 'Ti sei prenotato per '.$contrabbando['item'].' di @'.$contrabbando['nome'].' a '.$contrabbando['prezzo'].' §\n'.
    ( $sentPvt
        ? '\n@' . $contrabbando['nome'] . ' ha il bot attivo, non serve taggarlo.\n'
        : '\n@' . $contrabbando['nome'] . ' non ha il bot attivo, consiglio di taggarlo.\n'
    ).
    '\n@LootGameBot:\n'.
      '<code>Cerca *'.$item.'</code>\n'.
    '\n@Craftlootbot:\n'.
      '<code>/lista '.$item.'</code>\n'.
      '<code>/craft '.$item.'</code>\n'.
    '\n@ToolsForLootBot:\n'.
      '@ToolsForLootBot '.$item;
}

function message_resign_failed () {
  return 'Non sei tu ad essere prenotato!';
}
