<?php
namespace Zardak;
use PDO;

/** special thanks to : http://requiremind.com/a-most-simple-php-mvc-beginners-tutorial/ - Ehsan */
class DB {
  private static $instance = NULL;

  private function __construct() {}

  private function __clone() {}

  public static function getInstance() {
      if (!isset(self::$instance)) {
          $pdo_options = array(
            PDO::ATTR_DEFAULT_FETCH_MODE  => PDO::FETCH_OBJ,
            PDO::ATTR_ERRMODE                        => PDO::ERRMODE_WARNING,
            PDO::MYSQL_ATTR_INIT_COMMAND  => 'SET NAMES utf8'
          );
          self::$instance = new PDO(getenv('DB_TYPE') . ':host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_NAME'), getenv('DB_USER'), getenv('DB_PASS'), $pdo_options);
      }
      return self::$instance;
  }
}