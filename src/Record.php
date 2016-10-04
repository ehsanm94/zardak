<?php
namespace Zardak;

/**
 * @method bool|string getId()
 * @method bool|string getCookie()
 * @method bool|string getStudent_id()
 * @method bool|string getFirstname()
 * @method bool|string getLastname()
 * @method bool|string getToken()
 * @method bool|string getEmail()
 */
class Record
{
    /* from: https://davidwalsh.name/dynamic-functions [Thank you!] -Ehsan
    /* record information will be held in here */
    private $info;

    /* constructor */
    function __construct($record_array) {
        $this->info = $record_array;
    }

    /* dynamic function server */
    function __call($method,$arguments) {
        $meth = $this->from_camel_case(substr($method,3,strlen($method)-3));
        return array_key_exists($meth,$this->info) ? $this->info[$meth] : false;
    }

    /* uncamelcaser: via http://www.paulferrett.com/2009/php-camel-case-functions/ */
    function from_camel_case($str) {
        $str[0] = strtolower($str[0]);
        $func = create_function('$c', 'return "_" . strtolower($c[1]);');
        return preg_replace_callback('/([A-Z])/', $func, $str);
    }
}