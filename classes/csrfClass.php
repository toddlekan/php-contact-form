<?php

class CSRF
{

  /* *
   * Generates token
   * @returns token string
   */
  public static function getToken(){

    if (!headers_sent()) {
        session_start();
    }

    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    $token = $_SESSION['csrf'];

    return $token;

  }

  /* *
   * Compares post token to session token
   * @param array
   * @param array
   * @returns boolean
   */
  public static function compareTokens($fields, $session){

    $fieldToken = "";
    $sessonToken = "";

    if(array_key_exists('csrf', $fields)){
      $fieldToken = $fields['csrf'];
    }

    if(array_key_exists('csrf', $session)){
      $sessionToken = $session['csrf'];
    }

    if (!empty($sessionToken) && !empty($fieldToken)) {

      if (hash_equals($sessionToken, $fieldToken)) {
        return true;
      }

    }

    return false;

  }

}
