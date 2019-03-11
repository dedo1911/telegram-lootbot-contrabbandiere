<?php

function menu_offer ($test) {
  return array(
    array(
      array(
        'text' => 'Mi prenoto',
        'callback_data' => 'p_'.$test
      ),
      array(
        'text' => 'Concludi richiesta',
        'callback_data' => 'c_'.$test
      )
    )
  );
}

function menu_reserved ($test) {
  return array(
    array(
      array(
        'text' => 'Rinuncio',
        'callback_data' => 'r_'.$test
      ),
      array(
        'text' => 'Concludi richiesta',
        'callback_data' => 'c_'.$test
      )
    )
  );
}