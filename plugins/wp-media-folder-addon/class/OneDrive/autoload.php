<?php

/**
 * @internal
 */
  function wpmf_onedrive_api_php_client_autoload($className) {
    $classPath = explode('_', $className);
    if ($classPath[0] != 'OneDrive') {
      return;
    }
    // Drop 'OneDrive', and maximum class Wpmffile path depth in this project is 3.
    $classPath = array_slice($classPath, 1, 2);

    $filePath = dirname(__FILE__) . '/' . implode('/', $classPath) . '.php';
    if (file_exists($filePath)) {
      require_once($filePath);
    }
  }

  spl_autoload_register('wpmf_onedrive_api_php_client_autoload');