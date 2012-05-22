<?php

define('FPDF_FONTPATH', 'font/');

require_once("../../config.php");
include_once("factura_clasepdf.php");

require_once("../../lib/funciones_misiones.php");

$func_nroFactura = nro_factura_misiones();

if ($func_nroFactura) {
  $nroF = NRO_FACTURA_MISIONES;
} else {
  $nroF = '';
}

//generacion de pdf
$pdf = new orden_compra();
if ($parametros['id_factura'])
  $id_factura = $parametros['id_factura'];

$query1 = "SELECT 
  facturacion.factura.id_factura,
  nacer.smiafiliados.afiapellido,
  nacer.smiafiliados.afinombre,
  nacer.smiafiliados.afidni,
  nomenclador.prestaciones_n_op.tema,
  nomenclador.prestaciones_n_op.patologia,
  nomenclador.prestaciones_n_op.precio,
  facturacion.smiefectores.nombreefector,
  facturacion.smiefectores.cuie,
  facturacion.factura.periodo,
  facturacion.factura.periodo_actual,
  facturacion.factura.observaciones,
  facturacion.factura.fecha_carga,
  facturacion.factura.fecha_factura
FROM
  facturacion.factura
  INNER JOIN facturacion.comprobante ON (facturacion.factura.id_factura = facturacion.comprobante.id_factura)
  INNER JOIN nomenclador.prestaciones_n_op using (id_comprobante)
  INNER JOIN nacer.smiafiliados ON (facturacion.comprobante.id_smiafiliados = nacer.smiafiliados.id_smiafiliados)
  INNER JOIN facturacion.smiefectores ON (facturacion.comprobante.cuie = facturacion.smiefectores.cuie)
  where factura.id_factura=$id_factura";
$f_res1 = $db->Execute($query1) or die($db->ErrorMsg());

$query = "select categoria from nomenclador.grupo_prestacion where codigo='" . $f_res1->fields['tema'] . "';";
$tema = sql($query) or fin_pagina();
$query = "select descripcion from nomenclador.patologias where codigo='" . $f_res1->fields['patologia'] . "';";
$patologia = sql($query) or fin_pagina();

$query = "SELECT 
  $nroF
  facturacion.factura.id_factura,
  nacer.smiafiliados.afiapellido,
  nacer.smiafiliados.afinombre,
  nacer.smiafiliados.afidni,
  facturacion.prestacion.cantidad,
  facturacion.prestacion.precio_prestacion,
  facturacion.nomenclador.descripcion,
  facturacion.nomenclador.codigo,
  facturacion.smiefectores.nombreefector,
  facturacion.smiefectores.cuie,
  facturacion.factura.periodo,
  facturacion.factura.periodo_actual,
  facturacion.factura.observaciones,
  facturacion.factura.fecha_carga,
  facturacion.factura.fecha_factura
FROM
  facturacion.factura
  INNER JOIN facturacion.comprobante ON (facturacion.factura.id_factura = facturacion.comprobante.id_factura)
  INNER JOIN facturacion.prestacion ON (facturacion.comprobante.id_comprobante = facturacion.prestacion.id_comprobante)
  INNER JOIN facturacion.nomenclador ON (facturacion.prestacion.id_nomenclador = facturacion.nomenclador.id_nomenclador)
  INNER JOIN nacer.smiafiliados ON (facturacion.comprobante.id_smiafiliados = nacer.smiafiliados.id_smiafiliados)
  INNER JOIN facturacion.smiefectores ON (facturacion.comprobante.cuie = facturacion.smiefectores.cuie)
  where factura.id_factura=$id_factura
  order by codigo";

$f_res = $db->Execute($query) or die($db->ErrorMsg());

$pdf->dibujar_planilla();
if ($f_res->recordcount() > 0) {
  $pdf->nro_orden_compra($f_res->fields['periodo']);
  $pdf->nro_orden_compra1($f_res->fields['periodo_actual']);
  $pdf->proveedor($f_res->fields['nombreefector']);
  $pdf->fecha(Fecha($f_res->fields['fecha_factura']));
  $pdf->fecha_carga(Fecha($f_res->fields['fecha_carga']));
  $pdf->vendedor($f_res->fields['cuie']);
  $pdf->pasa_id_licitacion($func_nroFactura ? $f_res->fields['numero_factura'] : $id_factura);
  $pdf->lugar_entrega($f_res->fields['observaciones']);
}
if ($f_res1->recordcount() > 0) {
  $pdf->nro_orden_compra($f_res1->fields['periodo']);
  $pdf->nro_orden_compra1($f_res1->fields['periodo_actual']);
  $pdf->proveedor($f_res1->fields['nombreefector']);
  $pdf->fecha(Fecha($f_res1->fields['fecha_factura']));
  $pdf->fecha_carga(Fecha($f_res1->fields['fecha_carga']));
  $pdf->vendedor($f_res1->fields['cuie']);
  $pdf->pasa_id_licitacion($func_nroFactura ? $f_res->fields['numero_factura'] : $id_factura);
  $pdf->lugar_entrega($f_res1->fields['observaciones']);
}


//traemos los productos para agregar al pdf desde la tabla filas
$total = 0;
while (!$f_res1->EOF) {
  $pdf->producto($f_res1->fields['afiapellido'] . " " . substr($f_res1->fields['afinombre'], 0, 4) . ". DNI: " . $f_res1->fields['afidni'] . "-" . $tema->fields['categoria'] . " " . $patologia->fields['descripcion'], '1', $f_res1->fields['precio'], "$");
  $f_res1->MoveNext();
}
while (!$f_res->EOF) {
  $pdf->producto($f_res->fields['afiapellido'] . ", " . $f_res->fields['afinombre'] . "  |  DNI: " . $f_res->fields['afidni'] . "     --  Descripcion: " . $f_res->fields['descripcion'] . "  |  Codigo: " . $f_res->fields['codigo'], $f_res->fields['cantidad'], $f_res->fields['precio_prestacion'], "$");
  $f_res->MoveNext();
}

$query_t = "SELECT sum 
			(facturacion.prestacion.cantidad*facturacion.prestacion.precio_prestacion) as total
			FROM
			  facturacion.factura
			  INNER JOIN facturacion.comprobante ON (facturacion.factura.id_factura = facturacion.comprobante.id_factura)
			  INNER JOIN facturacion.prestacion ON (facturacion.comprobante.id_comprobante = facturacion.prestacion.id_comprobante)
			  where factura.id_factura=$id_factura";
$total = sql($query_t, "NO puedo calcular el total");
$query_t1 = "SELECT sum 
			(nomenclador.prestaciones_n_op.precio) as total1
			FROM
			  facturacion.factura
			  INNER JOIN facturacion.comprobante ON (facturacion.factura.id_factura = facturacion.comprobante.id_factura)
			  INNER JOIN nomenclador.prestaciones_n_op using (id_comprobante)
			  where factura.id_factura=$id_factura";
$total1 = sql($query_t1, "NO puedo calcular el total");
$total = $total->fields['total'] + $total1->fields['total1'];


$pdf->_final($total, "$", $_ses_user['name'], $firma2, $firma3);
$pdf->Footer();

$id_factura = $func_nroFactura ? $f_res->fields['numero_factura'] : $id_factura;
$pdf->guardar_servidor("factura_$id_factura.pdf");
?>