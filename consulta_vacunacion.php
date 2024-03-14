<?php
require_once('lib/nusoap.php');

$NAMESPACE = 'http://www.transexpress.com.gt/webTransexpress/';
$server = new soap_server();
$server->configureWSDL('webTransexpress');

$server->register(
    'datosvacunacion',
    array(
        'id_usuario' => 'xsd:int',
    ),
    array('return' => 'xsd:string'),
    $NAMESPACE
);

function datosvacunacion($id_usuario)
{
    $con = mssql_connect("192.168.0.2", "web", "!nf0rm4t!k");

    if (!$con) {
        die('Error de conexión: ' . mssql_get_last_message());
    }

    $db = mssql_select_db('db_trans', $con);

    if (!$db) {
        die('Error al seleccionar la base de datos: ' . mssql_get_last_message());
    }

    $str_sql = "SELECT * FROM sp_datosvacunacion WHERE id_usuario = $id_usuario";
    $rs = mssql_query($str_sql);

    if ($rs) {
        $datos_vacunacion = array();

        while ($registro = mssql_fetch_array($rs)) {
            $datos_vacunacion[] = array(
                'fecha' => $registro['fecha'],
                'vacuna' => $registro['vacuna'],
                'dosis' => $registro['dosis']
            );
        }

        $resultado = json_encode($datos_vacunacion);
    } else {
        $resultado = '{"error":"No se encontraron datos de vacunación para el usuario"}';
    }

    mssql_close($con);
    
    return $resultado;
}

$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);
?>