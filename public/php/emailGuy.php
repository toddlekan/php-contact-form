<?php

include_once(dirname(__FILE__)."/../../classes/emailGuyClass.php");

session_start();

print EmailGuy::process($_POST, $_SESSION);
