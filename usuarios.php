<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('America/Bogota');
ini_set('error_reporting', E_ALL);
ini_set("display_errors", 1);


require_once 'vendor/autoload.php';
require_once 'config/Constants.php';


use Config\AppConfig;
use App\Libraries\Resources;
use App\Controllers\UsersClass;


/**
* @api Ejemplo sencillo de api rest PHP con escritura en archivo json.
* @category Programación con PHP
* @author Ing. Alfonso Chávez Baquero <alfonso.chb@gmail.com>
* @since Creado: 2021-05-10
* @see -----------------------------------------------------------------
	 ,___,  ,___,
	 [o.o]  [o.o] 
	</)__)  (__(\> 
	 -”–”-  -”–”-
*/
class ApiUsers
{

	# Versión de la API
    const WS_NAME = 'Usuarios';

	# Variable de retorno del servicio.
	private $response;

	# Ruta para almacenar los logs.
	private $config;

	# Variable para asignar recurso externo;
	private $resources;

	# Ruta donde se almacenan los logs.
	private $logs;

	# Tiempo inicial del servicio.
	private $time_ini;

	# Tiempo final del servicio.
	private $time_end;


    /**
     * @method __construct - Método constructor de la clase.
     * @author Ing. Alfonso Chávez Baquero <alfonso.chb@gmail.com>
     * @return void - Retorna la inicialización del objeto.
    */
	function __construct()
	{
		$this->config = new AppConfig;
		$this->resources = new Resources;
		$this->time_ini = microtime(true);
		$this->logs = APP_ROOT.DIRECTORY_SEPARATOR."logs";
		$this->resources->registerLogs( $this->logs, "");
		$this->resources->registerLogs( $this->logs, "Solicitud al servicio ".$this->config->appName.' - '.self::WS_NAME );
		$this->resources->registerLogs( $this->logs, "Proceso ejecutadose en ".APP_ROOT );
	}


    /**
     * @method processRequest - Método para validar procesar la solicitud.
     * @author Ing. Alfonso Chávez Baquero <alfonso.chb@gmail.com>
     * @return void - Retorna la continuación del proceso.
    */
	public function processRequest( $method='', $request=null )
	{
		try {
			$method = !empty($method) ? strtoupper($method) : '';
			$request = is_array($request) ? $request : (array)$request;
			#echo "<pre>"; print_r($request); die;

			if ( !in_array($method, ['POST', 'GET', 'PUT', 'DELETE'])) {
				throw new Exception("Acción no permitida, no se reconoce el método de la petición.", 1);
			}
			
			$controller = new UsersClass();
			switch ( $method ) {
				case 'POST': 	$this->response = $controller->insert( $request ); break;
				case 'GET':
					if ( isset($request['id']) and is_numeric($request['id']) ) {
						$this->response = $controller->find( $request['id'] );
					}else{
						$this->response = $controller->findAll();
					}
				break;				
				case 'PUT': 	$this->response = $controller->update( $request ); break;
				case 'DELETE': 	$this->response = $controller->delete( $request['id'] ); break;
				default: 		throw new Exception("Método no programado.", 1); break;
			}
		} catch (Exception $e) {
			$message = "ERROR: message^".$e->getMessage()."~code^".$e->getCode()."~file^".$e->getFile()."~line^".$e->getLine();
			$this->resources->registerLogs( $this->logs, $message );
			$this->response = [
				"status" => "error",
				"message" => $e->getCode()." ".$e->getMessage()
			];
		}

		return $this->responseRequest();
	}


    /**
     * @method responseRequest - Método para imprimir la respuesta de la solicitud.
     * @author Ing. Alfonso Chávez Baquero <alfonso.chb@gmail.com>
     * @return void - Retorna impresión del proceso.
    */
	public function responseRequest()
	{
		$this->time_end = microtime(true);
		$time = round(( $this->time_end - $this->time_ini ), 3)." segundos.";
		$this->resources->registerLogs( $this->logs, "Duración total del proceso: ".$time );
		echo json_encode( $this->response, JSON_UNESCAPED_UNICODE );
		http_response_code(200);
		exit;
	}


}


/**
* @see: Entrada de datos al servicio.
* (String) $method - Captura el método HTTP: POST, GET, PUT, PATCH, DELETE.
* (String) $request - Captura de la información enviada desde el consumo del servicio.
* (Struct) $obj - Objeto creado para procesar la petición.
*/
$method = strtoupper( $_SERVER['REQUEST_METHOD'] );
$request = !empty($_REQUEST) ? $_REQUEST : json_decode( file_get_contents("php://input"), true );
$obj = new ApiUsers();
$obj->processRequest( $method, $request );
exit(0);