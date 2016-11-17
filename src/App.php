<?php
namespace Zardak;

use \ReflectionMethod;
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
        $url = isset($_GET['url']) ? $_GET['url'] : null;
        $this->redirectToCorrectUrl($url);
        $this->findControllerPath($url);
        $this->findActionAndParams($url);
    }

    /**
     * Get and split the URL
     */
    private function findActionAndParams($url)
    {
        $url = str_replace($this->url_controller_path, '', $url);
        $url = trim($url, '/');
        $url = explode('/', $url);

        $controller_path = 'app/Controller/' . ucwords(strtolower($this->url_controller_path), '/') . '.php';
        
        if (file_exists($controller_path)) {
            $controller = '\\' . ucwords(str_replace('.php', '', str_replace('/', '\\', $controller_path)));
            $this->url_controller_class = new $controller();

            if (empty($url[0]))
                unset($url);

            $number_of_input_params     = 0;
            $number_of_method_params    = 0;

            if (isset($url) && method_exists($this->url_controller_class, $url[0])) {
                $this->url_action       = $url[0];
                $reflection             = new ReflectionMethod($this->url_controller_class, $this->url_action);
                $number_of_method_params= count($reflection->getParameters());
                $this->url_parameter_1  = (!empty($url[1]) ? $url[1] : null);
                $this->url_parameter_2  = (!empty($url[2]) ? $url[2] : null);
                $this->url_parameter_3  = (!empty($url[3]) ? $url[3] : null);
                $number_of_input_params = count($url) - 1;
            }
            else if (method_exists($this->url_controller_class, 'index')) {
                $this->url_action       = 'index';
                $reflection             = new ReflectionMethod($this->url_controller_class, $this->url_action);
                $number_of_method_params= count($reflection->getParameters());
                $this->url_parameter_1  = (!empty($url[0]) ? $url[0] : null);
                $this->url_parameter_2  = (!empty($url[1]) ? $url[1] : null);
                $this->url_parameter_3  = (!empty($url[2]) ? $url[2] : null);
                $number_of_input_params = isset($url) ? count($url) : 0;
            }

            if ($number_of_input_params > $number_of_method_params) {
                $not_found_page = new PageNotFound();
                $not_found_page->index();
            }
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
        }
    }

    private function findControllerPath($url)
    {
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
        else {
            $this->url_controller_path  = 'home';
            $this->url_controller_class = 'home';
            return;
        }
    }

    private function redirectToCorrectUrl($url) {
        if ($url[strlen($url) - 1] == '/') {
            $url = rtrim($url, '/');
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: " . getenv('URL') . $url);
            exit();
        }
    }
}