<?php
namespace App\Controllers;


use App\Libraries\Resources;


/**
* @api Ejemplo sencillo de api rest PHP con escritura en archivo json.
* @category Programación con PHP
* @author Ing. Alfonso Chávez Baquero <alfonso.chb@gmail.com>
* @since Creado: 2021-05-10
*/
class UsersClass
{
	# Estado del proceso.
	private $status = "succes"; // succes, error


	# Mensaje del proceso.
	private $message;


	# Variable para asignar recurso externo;
	private $resources;


	# Ruta de archivo dode se almacena la información.
	private $file_data;


	# Ruta donde se almacenan los logs.
	private $logs;


    /**
     * @method __construct - Método constructor de la clase.
     * @author Ing. Alfonso Chávez Baquero <alfonso.chb@gmail.com>
     * @return void - Retorna la inicialización del objeto.
    */
	public function __construct()
	{
		$this->resources = new Resources;
		$this->logs = APP_ROOT.DIRECTORY_SEPARATOR."logs";
		//$this->file_data = APP_ROOT.DIRECTORY_SEPARATOR."data".DIRECTORY_SEPARATOR."database.json";
		$this->file_data = join(DIRECTORY_SEPARATOR, [APP_ROOT, "data", "database.json"]);
	}


    /**
     * @method findAll - Método todos los registros de la base de datos.
     * @author Ing. Alfonso Chávez Baquero <alfonso.chb@gmail.com>
     * @return (array) - Retorna arreglo con los datos de la consulta.
    */
	public function findAll()
	{
		$database = [];
		try{
			if ( !file_exists($this->file_data) or !is_file($this->file_data) ) {
				throw new \Exception("Base de datos no encontrada.", 1);
			}
			$database = json_decode( file_get_contents( $this->file_data ), true );
			
			$this->message = !empty($database)
				? "Proceso de consulta múltiple finalizada correctamente."
				: "Proceso de consulta múltiple, no se encontraron registros.";
			
		} catch (Exception $e) {
			$this->status = "error";
			$this->message = "Ha ocurrido un fallo: ".$e->getMessage();
		}
		$this->resources->registerLogs( $this->logs, $this->message );
		return [ "status" => $this->status, "message" => $this->message, "data" => $database ];
	}


    /**
     * @method findAll - Método todos los registros de la base de datos.
     * @author Ing. Alfonso Chávez Baquero <alfonso.chb@gmail.com>
     * @param (string) $id - Identificador del registro único.
     * @return (array) - Retorna arreglo con los datos de la consulta.
    */
    public function find( $id=0 )
    {
		$aux = [];
		try{
			if ( !file_exists($this->file_data) or !is_file($this->file_data) ) {
				throw new \Exception("Base de datos no encontrada.", 1);
			}
			$database = json_decode( file_get_contents( $this->file_data ), true );
			foreach ($database as $key => $row) {
				if ( !empty($row) and $row['id']==$id ) {
					$aux = $row;
					break;
				}
			}
			$this->message = !empty($aux) 
				? "Proceso de consulta individual finalizada correctamente."
				: "Proceso de consulta individual, no se encontraron registros.";

		} catch (Exception $e) {
			$this->status = "error";
			$this->message = "Ha ocurrido un fallo: ".$e->getMessage();
		}
		$this->resources->registerLogs( $this->logs, $this->message );
		return [ "status" => $this->status, "message" => $this->message, "data" => $aux ];
    }


    /**
     * @method findAll - Método todos los registros de la base de datos.
     * @author Ing. Alfonso Chávez Baquero <alfonso.chb@gmail.com>
     * @param (arra) $inputs - Los datos de inserción.
     * @return (array) - Retorna arreglo con los datos de la consulta.
    */
    public function insert( Array $inputs )
    {
		try{
			$id = 1;
			$database = [];

			// Lectura de la base existente.
			if ( file_exists($this->file_data) and is_file($this->file_data) ) {
				$database = json_decode( file_get_contents( $this->file_data ), true );
			}

			// Último elemento en el arreglo.
			$row_end = !empty($database) ? end($database) : [];
			if ( !empty($row_end) and isset($row_end['id'])) {
				$id = $row_end['id']+1;
			}

			// Nuevo elemento en el arreglo.
			$database[] = [
				'id' => $id,
			    'nombre' => $inputs['nombre'],
			    'apellido' => $inputs['apellido'],
			    'email'=> $inputs['email'],
			    'edad'=> $inputs['edad']
			];

			// Escritura del nuevo registro.
			$write = fopen( $this->file_data, "w" );
			fwrite($write, json_encode($database, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));
			fclose($write);
			$this->message = "Proceso de adición finalizado exitosamente.";
		} catch (Exception $e) {
			$this->status = "error";
			$this->message = "Ha ocurrido un fallo: ".$e->getMessage();
		}
		$this->resources->registerLogs( $this->logs, $this->message );
		return [ "status" => $this->status, "message" => $this->message ];
    }


    /**
     * @method findAll - Método todos los registros de la base de datos.
     * @author Ing. Alfonso Chávez Baquero <alfonso.chb@gmail.com>
     * @param (arra) $inputs - Los datos de actualización.
     * @return (array) - Retorna arreglo con los datos de la consulta.
    */
    public function update( Array $inputs )
    {
		$flag = false;
		try{
			if ( !file_exists($this->file_data) or !is_file($this->file_data) ) {
				throw new \Exception("Base de datos no encontrada.", 1);
			}

			if ( !isset($inputs['id']) or !is_numeric($inputs['id']) ) {
				throw new \Exception("El identificador del registro no es válido.", 1);
			}

			$database = json_decode( file_get_contents( $this->file_data ), true );
			$this->message = "Proceso de actualización, registro no encontrado.";
			foreach ($database as $key => $row) {
				if ( !empty($row) and $row['id']==$inputs['id'] ) {
					$database[$key] = [
						'id' => $inputs['id'],
					    'nombre' => $inputs['nombre'],
					    'apellido' => $inputs['apellido'],
					    'email'=> $inputs['email'],
					    'edad'=> $inputs['edad']
					];
					$this->message = "Proceso de actualización finalizado exitosamente.";
					break;
				}
			}

			// Re escritura de los datos.
			$write = fopen( $this->file_data, "w" );
			fwrite($write, json_encode($database, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));
			fclose($write);
		} catch (Exception $e) {
			$this->status = "error";
			$this->message = "Ha ocurrido un fallo: ".$e->getMessage();
		}
		$this->resources->registerLogs( $this->logs, $this->message );
		return [ "status" => $this->status, "message" => $this->message ];
    }


    /**
     * @method findAll - Método todos los registros de la base de datos.
     * @author Ing. Alfonso Chávez Baquero <alfonso.chb@gmail.com>
     * @param (string) $id - Identificador del registro único.
     * @return (array) - Retorna arreglo con los datos de la consulta.
    */
    public function delete( $id=0 )
    {
		try{
			if ( !file_exists($this->file_data) or !is_file($this->file_data) ) {
				throw new \Exception("Base de datos no encontrada.", 1);
			}
			$database = json_decode( file_get_contents( $this->file_data ), true );

			$flag = false;
			foreach ($database as $key => $row) {
				if ( !empty($row) and $row['id']==$id ) {
					array_splice($database, $key, 1);
					$flag = true;
					break;
				}
			}

			$this->message = !$flag
				? "Proceso de eliminación, registro no encontrado."
				: "Proceso de eliminación id:$id finalizado exitosamente.";

			// Re escritura de los datos.
			$write = fopen( $this->file_data, "w" );
			fwrite($write, json_encode($database, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));
			fclose($write);
		} catch (Exception $e) {
			$this->status = "error";
			$this->message = "Ha ocurrido un fallo: ".$e->getMessage();
		}
		$this->resources->registerLogs( $this->logs, $this->message );
		return [ "status" => $this->status, "message" => $this->message ];
    }


}