<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

chdir(dirname(__FILE__) . '/..');

require_once 'include/utils/utils.php';
global $adb;



$result = $adb->pquery(
"SELECT lab_facturas.id,
lab_facturas.cliente_id,
lab_facturas.fecha,
lab_facturas.estado,
lab_clientes.nombre FROM lab_facturas INNER JOIN lab_clientes ON
lab_facturas.cliente_id = lab_clientes.id",
[]
);

$facturas=[];

while ($row = $adb->fetchByAssoc($result)) {
$facturas[]=$row;
};


$detalles_factura=$adb->pquery(
"SELECT 
lab_factura_detalle.id,
lab_factura_detalle.factura_id,
lab_productos.nombre AS producto,
lab_factura_detalle.cantidad,
lab_factura_detalle.precio_unitario
FROM lab_factura_detalle
INNER JOIN lab_productos
ON lab_factura_detalle.producto_id = lab_productos.id",
[]
);

$detalles =[];
while ($row=$adb->fetchByAssoc($detalles_factura)){
$detalles[]=$row;
};


foreach ($facturas as &$factura) {
$factura['detalles'] = [];
foreach ($detalles as $detalle) {
if ($detalle['factura_id'] == $factura['id']) 
$factura['detalles'][] = $detalle;
}
}
header('Content-Type: application/json');

echo json_encode($facturas);