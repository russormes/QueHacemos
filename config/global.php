<?php

include('vendor/autoload.php');

spl_autoload_register(function ($class){
    $parts = explode('\\', $class);
    $class_url = array_pop($parts) . '.php';
    while ($next_part = array_pop($parts)) {
      $class_url = $next_part . '/' . $class_url;
    }
    require  'lib/' . $class_url;
  });

use Facebook\FacebookSession;

define('__ROOT__', dirname(dirname(__FILE__)));

define('APP_ID', '695815533812637');
define('APP_SECRET', '18e348c6ccc95c747147fa7887b8c913');
define('REDIRECT_URL', 'http://www.quehacemos.pe/fblogin.php');


FacebookSession::setDefaultApplication(APP_ID, APP_SECRET);

// Debugging with Firephp. Set this to false to turn of reporting to the
// firebug console.
FB::setEnabled(true);

?>