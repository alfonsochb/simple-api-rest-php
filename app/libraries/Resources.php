<?php 
namespace App\Libraries;


/**
* @api Ejemplo sencillo de api rest PHP con escritura en archivo json.
* @category Programación con PHP
* @author Ing. Alfonso Chávez Baquero <alfonso.chb@gmail.com>
* @since Creado: 2021-05-10
*/
class Resources
{


    /**
     * @method registerLogs - Método para guardar log cuando ocurren errores.
     * @author Ing. Alfonso Chávez Baquero <alfonso.chb@gmail.com>
     * @param (string) $path_logs - Ruta del directorio donde se guardan los logs y se crear los archivos JSON.
     * @param (string or array) $messages - Mensajes a ser guardados.
     * @param (array) $array_data - Datos del objeto JSON.
     * @return void - Devuelve continuación del proceso.
    */
    public function registerLogs( $path_logs=null, $messages='' )
    {
        if ( $path_logs and file_exists($path_logs) and is_dir($path_logs) ) {
            $name_file = "LOG".date("Ymd").".log";
            $moment = date( "Y-m-d H:i:s" );

            $file_log = fopen($path_logs.DIRECTORY_SEPARATOR.$name_file, 'a+');
            if ( $file_log ){
                if ( !empty(trim($messages)) ) {
                    if ( (is_array($messages) or is_object($messages)) ) {
                        $messages = @array_map('trim', $messages);
                        foreach ($messages as $key => $message) {
                            $write = "$moment - $message".PHP_EOL;
                            fwrite($file_log, $write);  
                        }
                    }else{
                        $message =@trim($messages);
                        $write = "$moment - $message".PHP_EOL;
                        fwrite($file_log, $write);
                    }
                }else{
                    fwrite($file_log, PHP_EOL);
                }
                fclose($file_log);
            }
        }
        return;
    }


    /**
     * @method - Método para guardar los datos.
     * @author Ing. Alfonso Chávez Baquero <alfonso.chavezb@softtek.com>
     * @param (string) $data_path - Directorio donde estan los archivos json.
     * @param (array) $data - Datos del objeto JSON.
     * @return void - Devuelve continuación del proceso.
    */
    public function createFileJson( $path_data=null, $data=array() )
    {
        if ( $path_data and file_exists($path_data) and is_dir($path_data) ) {
            if ( (is_array($data) or is_object($data)) and !empty($data) ){
                $file_name = "database.json";
                $file = $path_data.DIRECTORY_SEPARATOR.$file_name;
                $json_data = json_encode( $data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT ).PHP_EOL;
                if (file_put_contents($file, $json_data, FILE_APPEND|LOCK_EX)) {
                    return $file_name;
                }
            }
        }
        return;
    }


    public static function invalidJsonFormat(int $error)
    {
        switch( $error ) {
            case JSON_ERROR_NONE:
                echo ' - Sin errores';
            break;
            case JSON_ERROR_DEPTH:
                echo ' - Excedido tamaño máximo de la pila';
            break;
            case JSON_ERROR_STATE_MISMATCH:
                echo ' - Desbordamiento de buffer o los modos no coinciden';
            break;
            case JSON_ERROR_CTRL_CHAR:
                echo ' - Encontrado carácter de control no esperado';
            break;
            case JSON_ERROR_SYNTAX:
                echo ' - Error de sintaxis, JSON mal formado';
            break;
            case JSON_ERROR_UTF8:
                echo ' - Caracteres UTF-8 malformados, posiblemente codificados de forma incorrecta';
            break;
            default:
                echo ' - Error desconocido';
            break;
        }
        echo PHP_EOL;
    }



}