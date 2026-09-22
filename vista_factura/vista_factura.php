<?php 

error_reporting(E_ALL);
ini_set('display_errors', 1);

chdir(dirname(__FILE__) . '/..');

require_once 'include/utils/utils.php';

global $adb;
$id = $_GET['id'];

$resultado = $adb->pquery(
    "SELECT
    i.invoiceid,
    i.invoice_no,
    i.subject,
    i.invoicedate,
    i.duedate,
    i.invoicestatus,
    i.subtotal,
    i.total,
    i.received,
    i.balance,
    a.accountname,
    p.productname,
    p.productcode,
    r.quantity,
    r.listprice
FROM vtiger_invoice i
LEFT JOIN vtiger_account a
    ON i.accountid = a.accountid
LEFT JOIN vtiger_inventoryproductrel r
    ON i.invoiceid = r.id
LEFT JOIN vtiger_products p
    ON r.productid = p.productid
WHERE i.invoiceid = ?;",
    [$id]
);

$factura =$adb->fetchByAssoc($resultado);

echo "Factura: " . $factura['invoice_no'] . "<br>";
echo "Asunto: " . $factura['subject'] . "<br>";
echo "Cliente: " . $factura['accountname'] . "<br>";
echo "Fecha: " . $factura['invoicedate'] . "<br>";
echo "Vencimiento: " . $factura['duedate'] . "<br>";
echo "Estado: " . $factura['invoicestatus'] . "<br>";

$subtotal = $factura['subtotal'];
$total = $factura['total'];
$recibido = $factura['received'];
$saldo = $factura['balance'];
echo "<hr>";

while ($factura) {

    echo "Producto: " . $factura['productname'] . "<br>";
    echo "Código: " . $factura['productcode'] . "<br>";
    echo "Cantidad: " . $factura['quantity'] . "<br>";
    echo "Precio:" . $factura['listprice'] . "<br>";

    echo "<hr>";

    $factura = $adb->fetchByAssoc($resultado);
}

echo "Subtotal: " . $subtotal . "<br>";
echo "Total: " . $total . "<br>";
echo "Recibido: " . $recibido . "<br>";
echo "Saldo: " . $saldo;

?>
