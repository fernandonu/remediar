<?php
require_once ("../../config.php");

require_once("../../lib/funciones_misiones.php");

$func_nroFactura = nro_factura_misiones();

if ($func_nroFactura) {
  $nroF = NRO_FACTURA_MISIONES;
} else {
  $nroF = '';
}

$fecha_desde = $parametros["fecha_desde"];
$fecha_hasta = $parametros["fecha_hasta"];

$query = "SELECT
  $nroF
  facturacion.factura.periodo,
  facturacion.comprobante.id_comprobante,
  facturacion.comprobante.fecha_comprobante,
  facturacion.comprobante.cuie,
  facturacion.smiefectores.nombreefector,
  nacer.smiafiliados.afiapellido,
  nacer.smiafiliados.afinombre,
  nacer.smiafiliados.afidni,
  facturacion.nomenclador.codigo,
  facturacion.nomenclador.descripcion,
  facturacion.prestacion.precio_prestacion,
  facturacion.prestacion.cantidad,
  facturacion.anexo.prueba
FROM
  facturacion.comprobante
  INNER JOIN nacer.smiafiliados ON (facturacion.comprobante.id_smiafiliados = nacer.smiafiliados.id_smiafiliados)
  INNER JOIN facturacion.smiefectores ON (facturacion.comprobante.cuie = facturacion.smiefectores.cuie)
  INNER JOIN facturacion.factura ON (facturacion.comprobante.id_factura = facturacion.factura.id_factura)
  INNER JOIN facturacion.prestacion ON (facturacion.comprobante.id_comprobante = facturacion.prestacion.id_comprobante)
  INNER JOIN facturacion.nomenclador ON (facturacion.prestacion.id_nomenclador = facturacion.nomenclador.id_nomenclador)
  INNER JOIN facturacion.anexo ON (facturacion.prestacion.id_anexo = facturacion.anexo.id_anexo)

where (comprobante.fecha_comprobante between '$fecha_desde' and '$fecha_hasta') and comprobante.id_factura is not null
  order by numero_factura DESC";

$result = $db->Execute($query) or die($db->ErrorMsg());

excel_header("muestra.xls");
?>
<form name=form1 method=post action="comprobante_excel.php">
  <table width="100%" align=center border=1 bordercolor=#585858 cellspacing="0" cellpadding="5">
    <tr bgcolor=#C0C0FF>
      <td align=right >Num Factura</td>
      <td align=right >Periodo</td>
      <td align=right >Num Comprobante</td>
      <td align=right >Fecha Prestación</td>
      <td align=right >CUIE</td>
      <td align=right >Efector</td>
      <td align=right >Apellido</td>
      <td align=right >Nombre</td>
      <td align=right >DNI</td>
      <td align=right >Codigo</td>
      <td align=right >Descripcion</td>
      <td align=right >Precio</td>
      <td align=right >Cantidad</td>
      <td align=right >Prueba</td>
    </tr>
    <? while (!$result->EOF) {
      ?>
      <tr>
        <td><?= $func_nroFactura ? $result->fields['numero_factura'] : $result->fields['id_factura'] ?></td>
        <td><?= $result->fields['periodo'] ?></td>
        <td><?= $result->fields['id_comprobante'] ?></td>
        <td><?= Fecha($result->fields['fecha_comprobante']) ?></td>
        <td><?= $result->fields['cuie'] ?></td>
        <td><?= $result->fields['nombre'] ?></td>
        <td><?= $result->fields['afiapellido'] ?></td>
        <td><?= $result->fields['afinombre'] ?></td>
        <td><?= $result->fields['afidni'] ?></td>
        <td><?= $result->fields['codigo'] ?></td>
        <td><?= $result->fields['descripcion'] ?></td>
        <td><?= number_format($result->fields['precio_prestacion'], 2, ',', '.') ?></td>
        <td><?= $result->fields['cantidad'] ?></td>

      </tr>
      <? $result->MoveNext();
    } ?>
  </table>
</form>