<?php

include_once(dirname(__FILE__)."/csrfClass.php");

class EmailGuy
{

  private static $settings = [

    "status_required" => "",
    "status_email" => "",
    "status_token" => "",
    "status_success" => "",

    "mail_to" => "",
    "mail_from" => "",
    "mail_reply_to" => "",
    "mail_subject" => "",

    "db_host" => "",
    "db_name" => "",
    "db_user" => "",
    "db_pass" => ""

  ];

  private static $requiredFields = [

    "fullName",
    "email",
    "message"

  ];

  /* *
   * Main function
   * @param array
   * @param array
   * @returns status message
   */
  public static function process($fields, $session){

    $status = "";

    self::$settings = parse_ini_file(dirname(__FILE__)."/../settings/settings.ini");

    $status = self::compareTokens($fields, $session);

    if($status){

      return self::$settings[$status];

    }

    $status = self::validateFields($fields);

    if(!$status){

      $status = self::emailMessage($fields);

      $emailSuccess = false;

      if(!$status){
        $emailSuccess = true;
      }

      // try to save the email, even if sending failed
      self::saveToDB($fields, $emailSuccess);

    }

    if(!$status){

      $status = "status_success";

    }

    return self::$settings[$status];

  }

  /* *
   * Compares POST token to SESSION token
   * @param array
   * @param array
   * @returns status message
   */
  public static function compareTokens($fields, $session){

    $status = "";

    if(!CSRF::compareTokens($fields, $session)){

      $status = "status_token";

    }

    return $status;

  }

  /* *
   * Checks for value
   * @param string
   * @returns status message
   */
  public static function required($val){

    if($val){
      return "";
    } else {
      return "status_required";
    }

  }

  /* *
   * Checks for valid email format
   * @param string
   * @returns status message
   */
  public static function validateEmail($val){

    if (filter_var($val, FILTER_VALIDATE_EMAIL)) {
        return "";
    }

    return "status_email";

  }

  /* *
   * Loops through fields to validate
   * @param array
   * @returns status message
   */
  public static function validateFields($fields){

    $status = "";

    foreach(self::$requiredFields as $val){

      if(array_key_exists($val, $fields)){
        $status = self::required($fields[$val]);

      } else {
        $status = "status_required";

      }

      if(!$status && $val === 'email'){

        $status = self::validateEmail($fields[$val]);

      }

      if($status){

        break;

      }

    }

    return $status;

  }

  /* *
   * Sends Email
   * @param array
   * @returns status message
   */
  public static function emailMessage($fields){

    $status = "";

    $message = "";

    foreach($fields as $key => $val){

      $message .= "$key: $val\r\n";


    }

    // (for Windows) When PHP is talking to an SMTP server directly, if a full stop is
    // found on the start of a line, it is removed. To counter-act this, replace these
    // occurrences with a double dot.
    $message = str_replace("\n.", "\n..", $message);

    // In case any of our lines are larger than 70 characters
    $message = wordwrap($message, 70, "\r\n");

    $to      = self::$settings["mail_to"];
    $subject = self::$settings["mail_subject"];

    $headers = "From: ". self::$settings["mail_from"] . "\r\n" .
        "Reply-To: " . self::$settings["mail_reply_to"] . "\r\n";

    // Send
    if(!mail($to, $subject, $message, $headers)){

      $status = "email_sent";

    }

    return $status;

  }

  /* *
   * Saves copy of message to database
   * @param array
   * @param boolean
   */
  public static function saveToDb($fields, $emailSuccess){

    $status = "";

    // Attempt MySQL server connection.
    $host = self::$settings["db_host"];
    $user = self::$settings["db_user"];
    $pass = self::$settings["db_pass"];
    $name = self::$settings["db_name"];
    $port = (int)self::$settings["db_port"];
    $socket = self::$settings["db_socket"];

    error_log("$host, $user, $pass, $name, $port, $socket");

    $mysqli = new mysqli($host, $user, $pass, $name, $port, $socket);

    //$mysqli = new mysqli('localhost', 'root', 'tmp', "php_contact_form", 3306, '/private/tmp/mysql.sock');

    if ($mysqli->connect_errno) {
       error_log("Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
    }

    // Prepared statement, stage 1: prepare
    $sql = "INSERT INTO contacts (fullName, email, message, phone, sent)
      VALUES (?, ?, ?, ?, ?)";

    if (!(
      $stmt = $mysqli->prepare($sql)
      )) {
      error_log("Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error);
    }

    // Prepared statement, stage 2: bind and execute
    $fullName = $fields['fullName'];
    $email = $fields['email'];
    $message = $fields['message'];
    $phone = "";

    if(array_key_exists('phone', $fields)){
      $phone = $fields['phone'];
    }

    // Record if the email was successfully sent
    $sent = 0;

    if($emailSuccess){
      $sent = 1;
    }

    if (!$stmt->bind_param("ssssi", $fullName, $email, $message, $phone, $sent)) {
        echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
    }

    if (!$stmt->execute()) {
        error_log("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
    }

    // close connection
    $mysqli->close();

  }

  /* *
   * Returns success message
   * @returns status message
   */
  public static function success(){

    return self::settings["success"];

  }

}
