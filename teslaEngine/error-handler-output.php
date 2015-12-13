<?php
/**
* Custom error handling for hhvm
* Implemented in EPSInventory-API(https://github.com/epsclubs/EPSInventory-API) on Jan 5, 2015
* Referenced from: http://stackoverflow.com/questions/24524222/display-fatal-notice-errors-in-browser
*/
// Usage: Call `set_error_handler(error_handler);` at the top of any php or hh file running on hhvm

function error_handler ($errorNumber, $message, $errfile, $errline) {
  switch ($errorNumber) {
    case E_ERROR :
    $errorLevel = 'Error';
    break;
    case E_WARNING :
    $errorLevel = 'Warning';
    break;
    case E_NOTICE :
    $errorLevel = 'Notice';
    break;
    default :
    $errorLevel = 'Undefined';
  }
  echo '<br/><b>' . $errorLevel . '</b>: ' . $message . ' in <b>'.$errfile . '</b> on line <b>' . $errline . '</b><br/>';
}