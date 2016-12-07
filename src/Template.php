<?php 
namespace Zardak;

class Template {
	private $vars = array();

	private $views_chain;

	public function __construct($views_chain) {
		if (gettype($views_chain) === "array") {
			$this->views_chain = $views_chain;
		}
		else {
			$this->views_chain = array($views_chain);
		}
	}

	public function __get($name) {
		if (isset($this->vars[$name]))
			return $this->vars[$name];
	} 

	public function __set($name, $value) { 
		if($name == 'view_template_files') { 
			throw new Exception("Cannot bind variable named 'view_template_files'"); 
		} 
		$this->vars[$name] = $value; 
	} 

	public function render() {
		if(array_key_exists('views_chain', $this->vars)) { 
			throw new Exception("Cannot bind variable called 'views_chain'"); 
		} 
		foreach ($this->views_chain as $view => $data) {
			if (is_array($data)) {
				foreach ($data as $key => $val) {
					$this->$key = $val;
				}
			}
			if (count($this->vars) > 0) {
				extract($this->vars);
			}
			ob_start();
			$path = str_replace('.', '/', $view);
			if (file_exists('public/views/' . $path . '.php')) {
				include('public/views/' . str_replace('.', '/', $path) . '.php');
			}
			else { // when a template doesnt have any input variable!
				include('public/views/' . str_replace('.', '/', $data) . '.php');
			}
			$this->content = ob_get_clean();
		}
		echo $this->content;
	}
} 