<?php
namespace Zardak;

class Controller
{
    public function sendHeader($header)
    {
        if (!is_array($header)) {
            header($header);
        }
        else {
            $headers = $header;
            foreach ($headers as $header)
            {
                header($header);
            }
        }
    }

    public function sendJson($data) {
        $this->sendHeader(array(
            'Cache-Control: no-cache, must-revalidate',
            'Expires: Mon, 26 Jul 1997 05:00:00 GMT',
            'Content-type: application/json',
        ));
        echo is_array($data) ? json_encode($data) : $data;
    }

    public function render($view) {
        ob_start();
        $path = str_replace('.', '/', $view);
        if (file_exists('views/' . $path . '.php')) {
            include('views/' . str_replace('.', '/', $path) . '.php');
        }
        return ob_get_clean();
    }

    public function getField($field) {
        return isset($_POST[$field]) && !empty($_POST[$field]) ? $_POST[$field] : false;
    }
}
