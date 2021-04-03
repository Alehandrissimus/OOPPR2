<?php
/**
 * Core bootloader
 *
 * @author Serhii Shkrabak
 */

/* RESULT STORAGE */
$RESULT = [
	'state' => 0,
	'data' => [],
	'debug' => []
];

/* ENVIRONMENT SETUP */
define('ROOT', $_SERVER['DOCUMENT_ROOT'] . '/'); // Unity entrypoint;

register_shutdown_function('shutdown', 'OK'); // Unity shutdown function

spl_autoload_register('load'); // Class autoloader

set_exception_handler('handler'); // Handle all errors in one function

/* HANDLERS */

/*
 * Class autoloader
 */
function load (String $class):void {
	$class = strtolower(str_replace('\\', '/', $class));
	$file = "$class.php";
	if (file_exists($file))
		include $file;
	}

/*
 * Error logger
 */
function handler (Throwable $e):void {
	$file = ROOT.'data/errorCodes.php';
	if (file_exists($file)) {
		include $file;
	}
	
	global $RESULT;
	//$message = $e -> getMessage();
	$message = explode(', ', $e -> getMessage());

	foreach($codes as $value) {
		if ($message[0] == $value['message']) {
			$state = $value['state'];
			break;
		}
	}

	$RESULT['state'] = $state;
	$RESULT['data'] = isset($message[1]) ? $message[1] : '';
	$RESULT['message'] = $message[0];
	$RESULT[ 'debug' ][] = [
		'type' => get_class($e),
		'details' => $message,
		'file' => $e -> getFile(),
		'line' => $e -> getLine(),
		'trace' => $e -> getTrace()
	];
}

/*
 * Shutdown handler
 */
function shutdown():void {
	global $RESULT;
	$error = error_get_last();
	unset($RESULT['debug']);
	if ( ! $error ) {
		header("Content-Type: application/json");
		echo json_encode($GLOBALS['RESULT'], JSON_UNESCAPED_UNICODE);
	}
}

$CORE = new Controller\Main;
$data = $CORE->exec();

if ($data !== null)
	$RESULT['data'] = $data; 
else { // Error happens
	$RESULT['state'] = 6;
	$RESULT['errors'] = ['INTERNAL_ERROR' . $data];
	unset($RESULT['data']);
}