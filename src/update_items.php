<?php

require 'config.php';
require 'database.php';

if (!function_exists("richiesta_API")) {
    function richiesta_API($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, 0);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);
 
        return $result;
    }
}
// CONFIGURAZIONE SCRIPT
$items = richiesta_API("http://fenixweb.net:3300/api/v2/" . $GLOBALS['config']["lootbot_api_token"] . "/items");
$dec = json_decode($items, true);
if (!is_array($dec)) {
    return;
}
$arr = $dec["res"];
if ($dec["code"] == 200 and $arr[0]["id"]) {
    foreach ($arr as $i) {
        $itemID = $i['id'];
        $nq = $GLOBALS['db']->prepare("SELECT * FROM items WHERE id=?");
        $nq->execute(array($itemID));
        $n = $nq->rowCount();
        if ($n>0) {
            $check = $GLOBALS['db']->prepare("UPDATE items SET name=?, rarity=?, description=?, value=?, estimate=?, craftable=?, reborn=?, power=?, power_armor=?, power_shield=?, dragon_power=?, critical=?, allow_sell=?, craft_pnt=? WHERE id=?");
            $check->execute(array(
                $i['name'],
                $i['rarity'],
                $i['description'],
                $i['value'],
                $i['estimate'],
                $i['craftable'],
                $i['reborn'],
                $i['power'],
                $i['power_armor'],
                $i['power_shield'],
                $i['dragon_power'],
                $i['critical'],
                $i['allow_sell'],
                $i['craft_pnt'],
                $itemID
            ));
        } else {
            $check = $GLOBALS['db']->prepare("INSERT INTO items (`id`, `name`, `rarity`, `description`, `value`, `estimate`, `craftable`, `reborn`, `power`, `power_armor`, `power_shield`, `dragon_power`, `critical`, `allow_sell`, `craft_pnt`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $check->execute(array(
                $itemID,
                $i['name'],
                $i['rarity'],
                $i['description'],
                $i['value'],
                $i['estimate'],
                $i['craftable'],
                $i['reborn'],
                $i['power'],
                $i['power_armor'],
                $i['power_shield'],
                $i['dragon_power'],
                $i['critical'],
                $i['allow_sell'],
                $i['craft_pnt']
            ));
            echo("Nuovo item: ".$i["name"]);
        }
    }
}

$items = richiesta_API("http://fenixweb.net:3300/api/v2/" . $GLOBALS['config']["lootbot_api_token"] . "/crafts/id");
$dec = json_decode($items, true);
if (!is_array($dec)) {
    return;
}
$arr = $dec["res"];
if ($dec["code"] == 200 and $arr[0]["id"]) {
    foreach ($arr as $i) {
        $itemID = $i['material_result'];
        $check = $GLOBALS['db']->prepare("UPDATE items SET material_1=?, material_2=?, material_3=? WHERE id=?");
        $check->execute(array(
            $i['material_1'],
            $i['material_2'],
            $i['material_3'],
            $itemID
        ));
    }
}
