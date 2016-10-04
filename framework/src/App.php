<?php
namespace Zardak;
use App\Controller\PageNotFound;
class App
{
    /** @var null The controller path */
    private $url_controller_path = null;

    /** @var null The url controller class */
    private $url_controller_class = null;

    /** @var null The method (of the above controller), often also named "action" */
    private $url_action = null;

    /** @var null Parameter one */
    private $url_parameter_1 = null;

    /** @var null Parameter two */
    private $url_parameter_2 = null;

    /** @var null Parameter three */
    private $url_parameter_3 = null;

    /**
     * "Start" the application:
     * Analyze the URL elements and calls the according controller/method or the fallback
     */
    public function __construct()
    {
        // create array with URL parts in $url
        $this->findControllerPath(isset($_GET['url']) ? $_GET['url'] : null);
        $this->findActionAndParams();

        // check for controller: does such a controller exist ?
        $controller_path = 'app/Controller/' . ucwords(strtolower($this->url_controller_path), '/') . '.php';
        if (file_exists($controller_path)) {

            // if so, then load this file and create this controller
            // example: if controller would be "car", then this line would translate into: $this->car = new car();
            // require $controller;
            $controller = '\\' . ucwords(str_replace('.php', '', str_replace('/', '\\', $controller_path)));
            $this->url_controller_class = new $controller();

            // check for method: does such a method exist in the controller ?
            if (method_exists($this->url_controller_class, $this->url_action)) {

                // call the method and pass the arguments to it
                if (isset($this->url_parameter_3)) {
                    // will translate to something like $this->home->method($param_1, $param_2, $param_3);
                    $this->url_controller_class->{$this->url_action}($this->url_parameter_1, $this->url_parameter_2, $this->url_parameter_3);
                } elseif (isset($this->url_parameter_2)) {
                    // will translate to something like $this->home->method($param_1, $param_2);
                    $this->url_controller_class->{$this->url_action}($this->url_parameter_1, $this->url_parameter_2);
                } elseif (isset($this->url_parameter_1)) {
                    // will translate to something like $this->home->method($param_1);
                    $this->url_controller_class->{$this->url_action}($this->url_parameter_1);
                } else {
                    // if no parameters given, just call the method without parameters, like $this->home->method();
                    $this->url_controller_class->{$this->url_action}();
                }
            } else {
                $not_found_page = new PageNotFound();
                $not_found_page->index();
            }
        } else {
            $not_found_page = new PageNotFound();
            $not_found_page->index();
        }
    }

    /**
     * Get and split the URL
     */
    private function findActionAndParams()
    {
        if (isset($_GET['url'])) {
            $url = filter_var($_GET['url'], FILTER_SANITIZE_URL);
            $url = str_replace($this->url_controller_path, '', $url);
            $is_slash = (strlen($url) == 1 && $url == '/') ? true : false;

            $url = trim($url, '/');
            $url = explode('/', $url);

            if (!$is_slash && !empty($url[0]))
                $this->url_action = $url[0];
            elseif ($is_slash)
                $this->url_action = null;
            elseif (empty($url[0]))
                $this->url_action = 'index';

            $this->url_parameter_1 = (!empty($url[1]) ? $url[1] : null);
            $this->url_parameter_2 = (!empty($url[2]) ? $url[2] : null);
            $this->url_parameter_3 = (!empty($url[3]) ? $url[3] : null);
        }
    }

    private function findControllerPath($url)
    {
        if (isset($url)) {
            $url = rtrim($url, '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);

            if (file_exists('app/Controller/' . ucwords(strtolower($url), '/') . '.php')) {
                $this->url_controller_path = $url;
                $this->url_controller_class = substr($url, strrpos($url, "/") === false ? 0 : strrpos($url, "/") + 1);
                return;
            }

            if (strlen($url) > 0 && strpos($url, '/') !== false) {
                $this->findControllerPath(substr($url, 0, strrpos($url, "/")));
            }
            else
                return;
        }
        else{
            $this->url_controller_path = 'home';
            $this->url_controller_class = 'home';
            $this->url_action = 'index';
        }
    }
}
