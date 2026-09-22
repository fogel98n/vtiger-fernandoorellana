<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

chdir(dirname(__FILE__).'/..');
require_once'include/utils/utils.php';
global $adb;

$datos=json_decode(file_get_contents('php://input'),true);

$id=$datos['id'];
$fecha=$datos['fecha'];
$estado=$datos['estado'];
$detalles=$datos['detalles']??[];

$adb->pquery(
    "UPDATE lab_facturas set fecha =?,
    estado=?
    WHERE id =?
    ",
    
    [$fecha,$estado,$id]
);

foreach($detalles as $detalle){
    $adb->pquery(
        "UPDATE lab_factura_detalle SET precio_unitario=?, cantidad=? WHERE id=?",
        [$detalle['precio_unitario'], $detalle['cantidad'], $detalle['id']]
    );
}
header('content-type:aplication/json');
echo  "factura actualizada";
echo $fecha;