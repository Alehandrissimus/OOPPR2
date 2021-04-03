<?php
/**
 * User Controller
 *
 * @author Serhii Shkrabak
 * @global object $CORE
 * @package Controller\Main
 */
namespace Controller;
class Main
{
	use \Library\Shared;

	private $model;

	public function exec():?array {
		$result = null;
		$url = $this->getVar('REQUEST_URI', 'e');

		$path = explode('/', $url);

		if (!isset($path[2]) || strpos($path[1], '.')) { // check for url correctness
			throw new \Exception("REQUEST_UNKNOWN");
		}

		$file = ROOT . 'model/config/methods/' . $path[1] . '.php';
		if (!file_exists($file)) { // check for methods availability
			throw new \Exception("REQUEST_UNKNOWN");
		}
		include $file;

		$file = ROOT . 'model/config/patterns/patterns.php';
		if (!file_exists($file)) { // check for patterns availability
			throw new \Exception("REQUEST_UNKNOWN");
		}	
		include $file;

		if (isset($methods[$path[2]])) { // check for current method availability
			$details = $methods[$path[2]];
			$request = [];

			foreach ($details['params'] as $param) {
				$var = $this->getVar($param['name'], $param['source']);
				
				if ($param['required'] === true) { // checking for required parameter

					if (isset($var)) { // checking parameter availability
						if (preg_match($patterns["{$param['name']}"], $var) == 0) { // check for pattern match
							throw new \Exception("REQUEST_INCORRECT, {$param['name']}");
						}

						//forming correct phone number
						if ($param['name'] == 'phone') {
							$var = '+380'.substr($var, strlen($var) - 9);
						}
					}
					else {
						throw new \Exception("REQUEST_INCOMPLETE, {$param['name']}");
					}

				}
				
				if ($var) {
					$request[$param['name']] = $var;
				}
			}

			//forming result
			if (method_exists($this->model, $path[1] . $path[2])) {
				$method = [$this->model, $path[1] . $path[2]];
				$result = $method($request);
			}
			else {
				throw new \Exception("REQUEST_UNKNOWN");
			}
		}
		else {
			throw new \Exception("REQUEST_UNKNOWN");
		}

		return $result;
	}

	public function __construct() {
		$origin = $this -> getVar('HTTP_ORIGIN', 'e');
		$front = $this -> getVar('FRONT', 'e');

		foreach ( [$front] as $allowed )
			if ( $origin == "https://$allowed") {
				header( "Access-Control-Allow-Origin: $origin" );
				header( 'Access-Control-Allow-Credentials: true' );
			}
		$this->model = new \Model\Main;
	}
}