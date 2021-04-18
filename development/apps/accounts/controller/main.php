<?php
namespace Controller;
class Main
{
	use \Library\Shared;

	private $model;

	public function exec():?array {
		$result = null;
		$url = $this->getVar('REQUEST_URI', 'e');
		$path = explode('/', $url);

		if (isset($path[2]) && !strpos($path[1], '.')) { // Disallow directory changing
			//подключаем форму запроса
			$file = ROOT . 'model/config/methods/' . $path[1] . '.php';
			if (file_exists($file)) {
				include $file;
			}
			else {
				throw new \Exception("REQUEST_UNKNOWN");
			}
			
			$file = ROOT . 'model/config/patterns/patterns.php';//подключаем паттерны для запроса
			if (file_exists($file)) {
				include $file;
			}
			else {
				throw new \Exception("REQUEST_UNKNOWN");
			}

			
			if (isset($methods[$path[2]])) {
				$details = $methods[$path[2]];
				$request = [];
				
				foreach ($details['params'] as $param) {
					$var = $this->getVar($param['name'], $param['source']);
					
					if ($param['required'] === true) { //проверка присутствия запроса
						
						//проверка обязательного поля
						if (isset($var)) {
							
							//проверка на соответствие шаблону
							if (preg_match($patterns[$param['pattern']]['regular'], $var) == 0) {
								throw new \Exception("REQUEST_INCORRECT, {$param['name']}");
							}

							//throw new \Exception($patterns[$param['pattern']]['function']);
							//приводим к виду (+380)
							if (isset($patterns[$param['pattern']]['function'])) {
								$var = call_user_func($patterns[$param['pattern']]['function'], $var);
														
							}
								
						}
						else {
							throw new \Exception("REQUEST_INCOMPLETE, {$param['name']}");
						}

					}

					//заполняем массив запроса
					if ($var) {
						$request[$param['name']] = $var;
					}
				}

				//form submitAmbassador - новая заявка
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
		}
		else {
			throw new \Exception("REQUEST_UNKNOWN");
		}
		return $result;
	}

	public function __construct() {
		// CORS configuration
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
