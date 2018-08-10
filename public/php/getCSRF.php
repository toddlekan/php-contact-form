<?php

include_once(dirname(__FILE__)."/../../classes/csrfClass.php");

print CSRF::getToken();
