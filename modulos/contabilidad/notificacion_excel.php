<?php

require_once ("../../config.php");

require_once("../../lib/funciones_misiones.php");

$func_nroFactura = nro_factura_misiones();

if ($func_nroFactura) {
  $nroF = NRO_FACTURA_MISIONES;
} else {
  $nroF = '';
}

extract($_POST,EXTR_SKIP);
if ($parametros) extract($parametros,EXTR_OVERWRITE);


$color1="#5090C0";
  $color2="#D5D5D5";
  $ret = "";
    
$sql= "SELECT 
  $nroF
	ingreso.id_ingreso,
  facturacion.factura.id_factura,
  facturacion.factura.cuie,
  facturacion.factura.fecha_carga,
  facturacion.factura.fecha_factura,
  facturacion.factura.periodo,
  facturacion.factura.estado,
  facturacion.factura.observaciones,
  facturacion.factura.id_factura,
  facturacion.factura.online,
  facturacion.factura.nro_exp_ext,
  facturacion.factura.fecha_exp_ext,
  facturacion.factura.periodo_contable,
  facturacion.factura.monto_prefactura,
  facturacion.smiefectores.nombre
FROM
  facturacion.factura  
left join facturacion.smiefectores using (cuie)
  left join contabilidad.ingreso on factura.id_factura=ingreso.numero_factura 
  
WHERE  (factura.estado='C') and (factura.cuie='$cuie') and (facturacion.factura.nro_exp_ext is not null) and (ingreso.id_ingreso is not null) 
ORDER BY ingreso.id_ingreso DESC";
  
$res_factura=sql($sql,"no se puede ejecutar");
$res_factura->movefirst();

$nombre_efector=$res_factura->fields['nombreefector'];
$nro_factura=$res_factura->fields['id_factura'];
$exp_externo=$res_factura->fields['nro_exp_ext'];
$monto_prefactura=number_format($res_factura->fields['monto_prefactura'],2,',','.');
$periodo_contable=$res_factura->fields['periodo_contable'];
$id_factura=$res_factura->fields['id_factura'];

$sql= "SELECT * FROM nacer.efe_conv where cuie = '$cuie'";  
$res_efector=sql($sql,"no se puede ejecutar");
$referente=$res_efector->fields['referente'];

$query_t="SELECT sum 
			(facturacion.prestacion.cantidad*facturacion.prestacion.precio_prestacion) as total
			FROM
			  facturacion.factura
			  INNER JOIN facturacion.comprobante ON (facturacion.factura.id_factura = facturacion.comprobante.id_factura)
			  INNER JOIN facturacion.prestacion ON (facturacion.comprobante.id_comprobante = facturacion.prestacion.id_comprobante)
			  INNER JOIN facturacion.nomenclador ON (facturacion.prestacion.id_nomenclador = facturacion.nomenclador.id_nomenclador)
			  INNER JOIN nacer.smiafiliados ON (facturacion.comprobante.id_smiafiliados = nacer.smiafiliados.id_smiafiliados)
			  INNER JOIN facturacion.smiefectores ON (facturacion.comprobante.cuie = facturacion.smiefectores.cuie)
			  where factura.id_factura=$id_factura";
$total=sql($query_t,"NO puedo calcular el total");
$monto_factura=$total->fields['total'];
$monto_factura=number_format($monto_factura,2,',','.');

			$query=" SELECT sum(cantidad*monto) as total FROM
  			facturacion.debito  			
  			where id_factura='$id_factura'";
			$result_t_debitado=$db->Execute($query) or die($db->ErrorMsg());
			$debito=number_format($result_t_debitado->fields['total'],2,',','.');

			$query=" SELECT sum(cantidad*monto) as total FROM
  			facturacion.credito  			  
  			where id_factura='$id_factura'";
			$result_t_acreditado=$db->Execute($query) or die($db->ErrorMsg());
			$credito=number_format($result_t_acreditado->fields['total'],2,',','.');


date_default_timezone_set('Europe/Madrid');
setlocale(LC_TIME, 'spanish');
$dia_hoy=strftime("%A %d de %B de %Y");
	
$ret .= "<table width='65%'  bgcolor='$color1' align='center' style='border: 2px solid #000000; font-size=14px;'>\n";
$ret .= "<tr bgcolor='$color1'>\n";
$ret .= "<td align='center'>\n";
$ret .= "<b>FORMULARIO I</b>\n";
$ret .= "</td>\n";
$ret .= "</tr>\n";
$ret .= "<tr bgcolor='$color1' align='right'>\n";
$ret .= "<td align='rigth'>\n";
$ret .= "<b>Plan Nacer, $dia_hoy</b>\n";
$ret .= "</td>\n";
$ret .= "</tr>\n";
$ret .= "<tr bgcolor='$color1' align='left'>\n";
$ret .= "<td align='left'>\n";
$nro_factura_tmp = $func_nroFactura ? $res_factura->fields['numero_factura'] : $id_factura;
$ret .= "<b>Efector: $nombre_efector. CUIE: $cuie. Número de Factura: $id_factura</b>\n";
$ret .= "</td>\n";
$ret .= "</tr>\n";
$ret .= "<tr bgcolor='$color1' align='left'>\n";
$ret .= "<td align='left'>\n";
$ret .= "<b>Referente: $referente.</b>\n";
$ret .= "</td>\n";
$ret .= "</tr>\n";
$ret .= "<tr bgcolor='$color1'>\n";
$ret .= "<td align='justify'>\n";
$ret .= "<b>Por medio de la presente le notifico que se encuentra a disposición del prestador que usted representa
la suma de $ $monto_factura. Monto neto a liquidar que surge luego de haberse detectado en auditoría conceptos
erróneos en la facturación presentada. Por Consiguiente se debito la suma de $ $debito  y se acredito $ $credito.
transferida por el EPCSS en relacion a la cuasi-factura del mes de $periodo_contable, de $ $monto_prefactura. </b>\n";
$ret .= "</td>\n";
$ret .= "</tr>\n";
$ret .= "<tr bgcolor='$color1'>\n";
$ret .= "<td align='justify'>\n";
$ret .= "<b>Asimismo informo a Usted que dicha transferencia se realizo a través del Expediente N°: $exp_externo</b>\n";
$ret .= "</td>\n";
$ret .= "</tr>\n";
$ret .= "</table><br>\n";

$ret .= "<table width=95% align=center style='font-size=10px'>\n";
$ret .= "<tr>\n";
$ret .= "<td align=center>\n";
$ret .= "<b>INFORMACION ANEXA\n";
$ret .= "</td>\n";
$ret .= "</tr>\n";
$ret .= "</table>\n";
$ret .= "<table width='65%'  bgcolor='$color1' align='center' style='border: 2px solid #000000; font-size=14px;'>\n";
$ret .= "<tr bgcolor='$color1'>\n";
$ret .= "<td align='justify'>\n";
$ret .= "<b>Por medio de la presente le informo que su Saldo Real es de $saldo_real. </b>\n";
$ret .= "</td>\n";
$ret .= "</tr>\n";
$ret .= "<tr bgcolor='$color1'>\n";
$ret .= "<td align='justify'>\n";
$ret .= "<b>Queda a su disposicion retirar en nuestras oficinas el informe de DEBITO / CREDITO en el horario de 8hs. a 18hs.</b>\n";
$ret .= "</td>\n";
$ret .= "</tr>\n";
$ret .= "<tr bgcolor='$color1'>\n";
$ret .= "<td align='justify'>\n";
$ret .= "<font color=white><b>Se ruega contestar este mail a la casilla ofial de Plan Nacer, para ser tenido en cuenta como acuse de recibo.</b></font>\n";
$ret .= "</td>\n";
$ret .= "</tr>\n";
$ret .= "</table><br>\n";

$ret .= "<table width=95% align=center style='font-size=10px'>\n";
$ret .= "<tr>\n";
$ret .= "<td align=center>\n";
$ret .= "<b> ULTIMOS CINCO MOVIMIENTOS INGRESOS\n";
$ret .= "</td>\n";
$ret .= "</tr>\n";
$ret .= "</table>\n";
$ret .= "<table bgcolor='$color1' width='55%' align='center' style='font-size=10px'>\n";
  $ret .= "<tr bgcolor='$color1'>\n" ;
    $ret .= "<td width='20%' align='left'>\n" ;
    $ret .= "<b>ID Ingreso";
    $ret .= "</b></td>" ;
    $ret .= "<td>" ;
    $ret .= "<b>Nro Factura";
    $ret .= "</b></td>\n" ;
    $ret .= "<td>" ;
    $ret .= "<b>Fecha Factura";
    $ret .= "</b></td>\n" ;
    $ret .= "<td width='10%'>" ;
    $ret .= "<b>Monto Deposito";
    $ret .= "</b></td>\n" ;
    $ret .= "<td width='15%'>" ;
    $ret .= "<b>Fecha de Deposito";
    $ret .= "</b></td>\n" ;
    $ret .= "<td width='15%'>" ;
    $ret .= "<b>Fecha de Notificacion";
    $ret .= "</b></td>\n" ;
    $ret .= "<td width='15%'>" ;
    $ret .= "<b>Numero de Expediente";
    $ret .= "</b></td>\n" ;
  $ret .= "</tr>" ;

$sql="SELECT 
  $nroF
  *
FROM
  contabilidad.ingreso  
  left join facturacion.factura on ingreso.numero_factura=factura.id_factura 
  where  
  (factura.estado='C') and (factura.cuie='$cuie') and (facturacion.factura.nro_exp_ext is not null) 
  ORDER BY id_ingreso DESC
  LIMIT 5 OFFSET 0";
$res=sql($sql,'error');

while (!$res->EOF) {
 $id_ingreso = $res->fields['id_ingreso'];
 $id_factura = $func_nroFactura ? $res->fields['numero_factura'] : $res->fields['id_factura'];
 $fecha_factura = fecha($res->fields['fecha_factura']);
 $monto_deposito = number_format($res->fields['monto_deposito'],2,',','.');
 $fecha_deposito = fecha($res->fields['fecha_deposito']);
 $fecha_notificacion = fecha($res->fields['fecha_notificacion']);
 $nro_exp_ext = $res->fields['nro_exp_ext'];
 

   $ret .= "<tr bgcolor='$color2'>\n" ;
    $ret .= "<td>" ;
    $ret .= "<b>".$id_ingreso;
    $ret .= "</b></td>\n" ;
    $ret .= "<td>" ;
    $ret .= "<b>".$id_factura;
    $ret .= "</b></td>\n" ;
    $ret .= "<td>" ;
    $ret .= "<b>".$fecha_factura;
    $ret .= "</b></td>\n" ;
    $ret .= "<td>" ;
    $ret .= "<b>".$monto_deposito;
    $ret .= "</b></td>\n" ;
    $ret .= "<td align='right'>" ;
    $ret .= "<b>".$fecha_deposito;
    $ret .= "</b></td>" ;
    $ret .= "<td align='right'>" ;
    $ret .= "<b>".$fecha_notificacion;
    $ret .= "</b></td>" ;
     $ret .= "<td align='right'>" ;
    $ret .= "<b>".$nro_exp_ext;
    $ret .= "</b></td>" ;
    $ret .= "</tr>\n" ;

 $res->MoveNext();
 
}//del while
$ret .= "</table>\n";

$ret .= "<table width=95% align=center style='font-size=10px'>\n";
$ret .= "<tr>\n";
$ret .= "<td align=center>\n";
$ret .= "<b> ULTIMOS CINCO MOVIMIENTOS EGRESOS\n";
$ret .= "</td>\n";
$ret .= "</tr>\n";
$ret .= "</table>\n";
$ret .= "<table bgcolor='$color1' width='55%' align='center' style='font-size=10px'>\n";
  $ret .= "<tr bgcolor='$color1'>\n" ;
    $ret .= "<td width='10%' align='left'>\n" ;
    $ret .= "<b>ID Egreso";
    $ret .= "</b></td>\n" ;
    $ret .= "<td>" ;
    $ret .= "<b>Inciso";
    $ret .= "</b></td>\n" ;
    $ret .= "<td>" ;
    $ret .= "<b>Monto Egreso";
    $ret .= "</b></td>\n" ;
    $ret .= "<td width='10%'>" ;
    $ret .= "<b>Fecha Egreso";
    $ret .= "</b></td>\n" ;    
  $ret .= "</tr>" ;

$sql="SELECT 
  *
FROM
  contabilidad.egreso  
  left join contabilidad.inciso using (id_inciso) 
  where cuie='$cuie' and monto_egreso<>0
  order by id_egreso DESC
  LIMIT 5 OFFSET 0";
$res=sql($sql,'error');

while (!$res->EOF) {
 $id_egreso = $res->fields['id_egreso'];
 $ins_nombre = $res->fields['ins_nombre'];
 $monto_egreso = number_format($res->fields['monto_egreso'],2,',','.');
 $fecha_egreso = fecha($res->fields['fecha_egreso']);
 
    $ret .= "<tr bgcolor='$color2'>\n" ;
    $ret .= "<td>" ;
    $ret .= "<b>".$id_egreso;
    $ret .= "</b></td>\n" ;
    $ret .= "<td>" ;
    $ret .= "<b>".$ins_nombre;
    $ret .= "</b></td>\n" ;
    $ret .= "<td>" ;
    $ret .= "<b>".$monto_egreso;
    $ret .= "</b></td>\n" ;
    $ret .= "<td>" ;
    $ret .= "<b>".$fecha_egreso;
    $ret .= "</b></td>\n" ;
    $ret .= "</tr>\n" ;

 $res->MoveNext();
 
}//del while
$ret .= "</table>\n";

$ret .= "<table width=95% align=center style='font-size=10px'>\n";
$ret .= "<tr>\n";
$ret .= "<td align=center>\n";
$ret .= "<b> NOTIFICACIONES\n";
$ret .= "</td>\n";
$ret .= "</tr>\n";
$ret .= "</table>\n";	
$ret .= "<table width='65%'  bgcolor='$color1' align='center' style='border: 2px solid #000000; font-size=14px;'>\n";
$ret .= "<tr bgcolor='$color1' align='left'>\n";
$ret .= "<td align='rigth'>\n";
$ret .= "<b>Queda Notificado Equipo de Plan Nacer a través del mail oficial.</b>\n";
$ret .= "</td>\n";
$ret .= "</tr>\n";
$ret .= "<tr bgcolor='$color1' align='left'>\n";
$ret .= "<td align='left'>\n";
$ret .= "<b>Queda Notificado el Efector: $nombre_efector. CUIE: $cuie. A través de los mail declarados.</b>\n";
$ret .= "</td>\n";
$ret .= "</tr>\n";
$sql= "select * from nacer.mail_efe_conv where cuie='$cuie'";
$res_mail=sql($sql,"no se puede ejecutar");
$res_mail->movefirst();
while (!$res_mail->EOF) { 
	$para=$res_mail->fields['mail'];
	$ret .= "<tr bgcolor='$color1' align='left'>\n";
	$ret .= "<td align='left'>\n";
	$ret .= "<b>Mail: $para.</b>\n";
	$ret .= "</td>\n";
	$ret .= "</tr>\n";
	$res_mail->movenext();
}
$ret .= "</table>\n";

excel_header("ingreso_egreso.xls");

?>
<form name=form1 method=post action="notificacion_excel.php">
<?echo $ret;?>
 
 </form>