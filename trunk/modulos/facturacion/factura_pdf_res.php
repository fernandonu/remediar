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

$query = "SELECT 
  sum(facturacion.prestacion.cantidad) AS cantidad,
  facturacion.nomenclador.codigo,
  facturacion.nomenclador.descripcion,
  facturacion.prestacion.precio_prestacion,
  facturacion.prestacion.precio_prestacion*sum(facturacion.prestacion.cantidad) as precio_total,
  facturacion.smiefectores.nombreefector,
  facturacion.smiefectores.cuie,
  facturacion.factura.periodo,
  facturacion.factura.periodo_actual,
  facturacion.factura.observaciones,
  facturacion.factura.fecha_carga,
  facturacion.factura.fecha_factura
FROM
  facturacion.nomenclador
  INNER JOIN facturacion.prestacion ON (facturacion.nomenclador.id_nomenclador = facturacion.prestacion.id_nomenclador)
  INNER JOIN facturacion.comprobante ON (facturacion.prestacion.id_comprobante = facturacion.comprobante.id_comprobante)
  INNER JOIN facturacion.factura ON (facturacion.comprobante.id_factura = facturacion.factura.id_factura)  
  INNER JOIN facturacion.smiefectores ON (facturacion.comprobante.cuie = facturacion.smiefectores.cuie)
WHERE  (factura.id_factura='$id_factura')
  GROUP BY
  facturacion.nomenclador.id_nomenclador,
  facturacion.nomenclador.codigo,
  facturacion.nomenclador.descripcion,
  facturacion.prestacion.precio_prestacion,
  facturacion.smiefectores.nombreefector,
  facturacion.smiefectores.cuie,
  facturacion.factura.periodo,
  facturacion.factura.periodo_actual,
  facturacion.factura.observaciones,
  facturacion.factura.fecha_carga,
  facturacion.factura.fecha_factura
  order by codigo";

$f_res = $db->Execute($query) or die($db->ErrorMsg());

$query1 = "SELECT 
		  b.categoria as obj_pres,
		  prestaciones_n_op.tema as codigo,
		  count (comprobante.id_comprobante) as cantidad_obj_pres,
		  sum (prestaciones_n_op.precio) precio,
		  facturacion.smiefectores.nombreefector,
		  facturacion.smiefectores.cuie,
		  facturacion.factura.periodo,
		  facturacion.factura.periodo_actual,
		  facturacion.factura.observaciones,
		  facturacion.factura.fecha_carga,
		  facturacion.factura.fecha_factura
		FROM
		  facturacion.comprobante
		INNER JOIN nomenclador.prestaciones_n_op ON (facturacion.comprobante.id_comprobante = nomenclador.prestaciones_n_op.id_comprobante)
		INNER JOIN nomenclador.grupo_prestacion b ON (nomenclador.prestaciones_n_op.tema = b.codigo)
		INNER JOIN facturacion.smiefectores ON (comprobante.cuie = facturacion.smiefectores.cuie)
		INNER JOIN facturacion.factura ON (facturacion.comprobante.id_factura = facturacion.factura.id_factura)  
		WHERE  (factura.id_factura='$id_factura')
		GROUP BY
		  b.categoria,
		  prestaciones_n_op.tema,
		  facturacion.smiefectores.nombreefector,
		  facturacion.smiefectores.cuie,
		  facturacion.factura.periodo,
		  facturacion.factura.periodo_actual,
		  facturacion.factura.observaciones,
		  facturacion.factura.fecha_carga,
		  facturacion.factura.fecha_factura
		ORDER BY 
		  obj_pres";
$f_res1 = $db->Execute($query1) or die($db->ErrorMsg());

$pdf->dibujar_planilla();

if ($func_nroFactura) {
  $query = "SELECT
              ".NRO_FACTURA_MISIONES." 1
            FROM facturacion.factura
            where id_factura='$id_factura'";
  $result_nro_fact = $db->Execute($query) or die($db->ErrorMsg());
}

if ($f_res->recordcount() > 0) {
  $pdf->nro_orden_compra($f_res->fields['periodo']);
  $pdf->nro_orden_compra1($f_res->fields['periodo_actual']);
  $pdf->proveedor($f_res->fields['nombreefector']);
  $pdf->fecha(Fecha($f_res->fields['fecha_factura']));
  $pdf->fecha_carga(Fecha($f_res->fields['fecha_carga']));
  $pdf->vendedor($f_res->fields['cuie']);
  $pdf->pasa_id_licitacion($func_nroFactura ? $result_nro_fact->fields['numero_factura'] : $id_factura);
  $pdf->lugar_entrega($f_res->fields['observaciones']);
}
if ($f_res1->recordcount() > 0) {
  $pdf->nro_orden_compra($f_res1->fields['periodo']);
  $pdf->nro_orden_compra1($f_res1->fields['periodo_actual']);
  $pdf->proveedor($f_res1->fields['nombre']);
  $pdf->fecha(Fecha($f_res1->fields['fecha_factura']));
  $pdf->fecha_carga(Fecha($f_res1->fields['fecha_carga']));
  $pdf->vendedor($f_res1->fields['cuie']);
  $pdf->pasa_id_licitacion($func_nroFactura ? $result_nro_fact->fields['numero_factura'] : $id_factura);
  $pdf->lugar_entrega($f_res1->fields['observaciones']);
}

//traemos los productos para agregar al pdf desde la tabla filas
$total = 0;

if ($f_res->recordcount() > 0) {
  while (!$f_res->EOF) {
    $pdf->producto($f_res->fields['codigo'] . ": " . $f_res->fields['descripcion'], $f_res->fields['cantidad'], $f_res->fields['precio_prestacion'], "$");
    $f_res->MoveNext();
  }
}

if ($f_res1->recordcount() > 0) {
  while (!$f_res1->EOF) {
    $pdf->producto($f_res1->fields['codigo'] . ": " . $f_res1->fields['obj_pres'], $f_res1->fields['cantidad_obj_pres'], $f_res1->fields['precio'], "$");
    $f_res1->MoveNext();
  }
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

$id_factura = $func_nroFactura ? $result_nro_fact->fields['numero_factura'] : $id_factura;
$pdf->guardar_servidor("factura_$id_factura.pdf");
?>