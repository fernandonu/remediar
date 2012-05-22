<?
require_once ("../../config.php");

require_once("../../lib/funciones_misiones.php");

$func_nroFactura = nro_factura_misiones();

if ($func_nroFactura) {
  $nroF = NRO_FACTURA_MISIONES;
} else {
  $nroF = '';
}

extract($_POST, EXTR_SKIP);
if ($parametros)
  extract($parametros, EXTR_OVERWRITE);
cargar_calendario();

if ($_POST['notificar'] == "Notificar Via Mail") {
  $color1 = "#5090C0";
  $color2 = "#D5D5D5";
  $ret = "";

  $sql = "SELECT 
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
  facturacion.smiefectores.nombreefector
FROM
  facturacion.factura  
left join facturacion.smiefectores using (cuie)
  left join contabilidad.ingreso on factura.id_factura=ingreso.numero_factura 
  
WHERE  (factura.estado='C') and (factura.cuie='$cuie') and (facturacion.factura.nro_exp_ext is not null) and (ingreso.id_ingreso is not null) 
ORDER BY ingreso.id_ingreso DESC";

  $res_factura = sql($sql, "no se puede ejecutar");
  $res_factura->movefirst();

  $nombre_efector = $res_factura->fields['nombreefector'];
  $nro_factura = $res_factura->fields['id_factura'];
  $exp_externo = $res_factura->fields['nro_exp_ext'];
  $monto_prefactura = number_format($res_factura->fields['monto_prefactura'], 2, ',', '.');
  $periodo_contable = $res_factura->fields['periodo_contable'];
  $id_factura = $res_factura->fields['id_factura'];

  $sql = "SELECT * FROM facturacion.smiefectores where cuie = '$cuie'";
  $res_efector = sql($sql, "no se puede ejecutar");
  $referente = $res_efector->fields['referente'];

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
  $monto_factura = $total->fields['total'] + $total1->fields['total1'];
  $monto_factura = number_format($monto_factura, 2, ',', '.');

  $query = " SELECT sum(cantidad*monto) as total FROM
  			facturacion.debito  			
  			where id_factura='$id_factura'";
  $result_t_debitado = $db->Execute($query) or die($db->ErrorMsg());
  $debito = number_format($result_t_debitado->fields['total'], 2, ',', '.');

  $query = " SELECT sum(cantidad*monto) as total FROM
  			facturacion.credito  			  
  			where id_factura='$id_factura'";
  $result_t_acreditado = $db->Execute($query) or die($db->ErrorMsg());
  $credito = number_format($result_t_acreditado->fields['total'], 2, ',', '.');


  date_default_timezone_set('Europe/Madrid');
  setlocale(LC_TIME, 'spanish');
  $dia_hoy = strftime("%A %d de %B de %Y");

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
  $ret .= "<b>Efector: $nombre_efector. CUIE: $cuie. Número de Factura: $nro_factura_tmp</b>\n";
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

  $saldo_real = number_format($saldo_real, 2, ',', '.');
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
  $ret .= "<font color=white><b>Se ruega contestar este mail al mail Oficial del Plan Nacer, para ser tenido en cuenta como acuse de recibo.</b></font>\n";
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
  $ret .= "<tr bgcolor='$color1'>\n";
  $ret .= "<td width='20%' align='left'>\n";
  $ret .= "<b>ID Ingreso";
  $ret .= "</b></td>";
  $ret .= "<td>";
  $ret .= "<b>Nro Factura";
  $ret .= "</b></td>\n";
  $ret .= "<td>";
  $ret .= "<b>Fecha Factura";
  $ret .= "</b></td>\n";
  $ret .= "<td width='10%'>";
  $ret .= "<b>Monto Deposito";
  $ret .= "</b></td>\n";
  $ret .= "<td width='15%'>";
  $ret .= "<b>Fecha de Deposito";
  $ret .= "</b></td>\n";
  $ret .= "<td width='15%'>";
  $ret .= "<b>Fecha de Notificacion";
  $ret .= "</b></td>\n";
  $ret .= "<td width='15%'>";
  $ret .= "<b>Numero de Expediente";
  $ret .= "</b></td>\n";
  $ret .= "</tr>";

  $sql = "SELECT 
  $nroF
  *
FROM
  contabilidad.ingreso  
  left join facturacion.factura on ingreso.numero_factura=factura.id_factura 
  where  
  (factura.estado='C') and (factura.cuie='$cuie') and (facturacion.factura.nro_exp_ext is not null) 
  ORDER BY id_ingreso DESC
  LIMIT 5 OFFSET 0";
  $res = sql($sql, 'error');

  while (!$res->EOF) {
    $id_ingreso = $res->fields['id_ingreso'];
    $id_factura = $res->fields['id_factura'];
    $fecha_factura = fecha($res->fields['fecha_factura']);
    $monto_deposito = number_format($res->fields['monto_deposito'], 2, ',', '.');
    $fecha_deposito = fecha($res->fields['fecha_deposito']);
    $fecha_notificacion = fecha($res->fields['fecha_notificacion']);
    $nro_exp_ext = $res->fields['nro_exp_ext'];


    $ret .= "<tr bgcolor='$color2'>\n";
    $ret .= "<td>";
    $ret .= "<b>" . $id_ingreso;
    $ret .= "</b></td>\n";
    $ret .= "<td>";
    $nro_factura_tmp = $func_nroFactura ? $res->fields['numero_factura'] : $id_factura;
    $ret .= "<b>" . $nro_factura_tmp;
    $ret .= "</b></td>\n";
    $ret .= "<td>";
    $ret .= "<b>" . $fecha_factura;
    $ret .= "</b></td>\n";
    $ret .= "<td>";
    $ret .= "<b>" . $monto_deposito;
    $ret .= "</b></td>\n";
    $ret .= "<td align='right'>";
    $ret .= "<b>" . $fecha_deposito;
    $ret .= "</b></td>";
    $ret .= "<td align='right'>";
    $ret .= "<b>" . $fecha_notificacion;
    $ret .= "</b></td>";
    $ret .= "<td align='right'>";
    $ret .= "<b>" . $nro_exp_ext;
    $ret .= "</b></td>";
    $ret .= "</tr>\n";

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
  $ret .= "<tr bgcolor='$color1'>\n";
  $ret .= "<td width='10%' align='left'>\n";
  $ret .= "<b>ID Egreso";
  $ret .= "</b></td>\n";
  $ret .= "<td>";
  $ret .= "<b>Inciso";
  $ret .= "</b></td>\n";
  $ret .= "<td>";
  $ret .= "<b>Monto Egreso";
  $ret .= "</b></td>\n";
  $ret .= "<td width='10%'>";
  $ret .= "<b>Fecha Egreso";
  $ret .= "</b></td>\n";
  $ret .= "</tr>";

  $sql = "SELECT 
  *
FROM
  contabilidad.egreso  
  left join contabilidad.inciso using (id_inciso) 
  where cuie='$cuie' and monto_egreso<>0
  order by id_egreso DESC
  LIMIT 5 OFFSET 0";
  $res = sql($sql, 'error');

  while (!$res->EOF) {
    $id_egreso = $res->fields['id_egreso'];
    $ins_nombre = $res->fields['ins_nombre'];
    $monto_egreso = number_format($res->fields['monto_egreso'], 2, ',', '.');
    $fecha_egreso = fecha($res->fields['fecha_egreso']);

    $ret .= "<tr bgcolor='$color2'>\n";
    $ret .= "<td>";
    $ret .= "<b>" . $id_egreso;
    $ret .= "</b></td>\n";
    $ret .= "<td>";
    $ret .= "<b>" . $ins_nombre;
    $ret .= "</b></td>\n";
    $ret .= "<td>";
    $ret .= "<b>" . $monto_egreso;
    $ret .= "</b></td>\n";
    $ret .= "<td>";
    $ret .= "<b>" . $fecha_egreso;
    $ret .= "</b></td>\n";
    $ret .= "</tr>\n";

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
  $sql = "select * from nacer.mail_efe_conv where cuie='$cuie'";
  $res_mail = sql($sql, "no se puede ejecutar");
  $res_mail->movefirst();
  while (!$res_mail->EOF) {
    $para = $res_mail->fields['mail'];
    $ret .= "<tr bgcolor='$color1' align='left'>\n";
    $ret .= "<td align='left'>\n";
    $ret .= "<b>Mail: $para.</b>\n";
    $ret .= "</td>\n";
    $ret .= "</tr>\n";
    $res_mail->movenext();
  }
  $ret .= "</table>\n";

  echo $ret;

  $res_mail->movefirst();
  while (!$res_mail->EOF) {
    $para = $res_mail->fields['mail'];
    enviar_mail_html($para, 'Notificacion de Fondos', $ret, 0, 0, 0);
    $res_mail->movenext();
  }
  enviar_mail_html('', 'Notificacion de Fondos', $ret, 0, 0, 0);
  $ref = encode_link("notificacion_excel.php", array("cuie" => $cuie, "id_factura" => $id_factura, "saldo_real" => $saldo_real));
  ?>
  <script>
    window.open('<?= $ref ?>')
  </script>
  <?
}



if ($marcar1 == "True") {
  $db->StartTrans();
  $query = "delete from contabilidad.ingreso
             where id_ingreso=$id_ingreso";

  sql($query, "Error al eliminar") or fin_pagina();
  $accion = "Se elimino el Ingreso Numero: $id_ingreso.";
  $db->CompleteTrans();
}

if ($marcar2 == "True") {
  $db->StartTrans();
  $query = "delete from contabilidad.egreso
             where id_egreso=$id_egreso";

  sql($query, "Error al eliminar") or fin_pagina();
  $accion = "Se elimino el Egreso Numero: $id_egreso.";
  $db->CompleteTrans();
}

if ($_POST['guardar'] == "Guardar Ingreso") {

  $cuie = $_POST['cuie'];
  $fecha_prefactura = Fecha_db($_POST['fecha_prefactura']);
  $comentario = $_POST['comentario'];
  $usuario = $_ses_user['name'];
  $fecha = date("Y-m-d");
  $numero_factura = $_POST['numero_factura'];
  $id_servicio = $_POST['servicio'];
  $expediente_externo = $_POST['expediente_externo'];
  $fecha_exp_ext = $_POST['fecha_exp_ext'];
  $fecha_exp_ext = Fecha_db($fecha_exp_ext);

  $query_dupli = "select * 
					from contabilidad.ingreso 
					where cuie='$cuie' and numero_factura in ('$numero_factura')";
  $res_dupli = sql($query_dupli, "no se puede ejecurar duplicado");

  if ($res_dupli->recordCount() == 0) {

    $query_t = "SELECT sum 
			(facturacion.prestacion.cantidad*facturacion.prestacion.precio_prestacion) as total
			FROM
			  facturacion.factura
			  INNER JOIN facturacion.comprobante ON (facturacion.factura.id_factura = facturacion.comprobante.id_factura)
			  INNER JOIN facturacion.prestacion ON (facturacion.comprobante.id_comprobante = facturacion.prestacion.id_comprobante)
			  where factura.id_factura=$numero_factura";
    $total = sql($query_t, "NO puedo calcular el total");
    $query_t1 = "SELECT sum 
			(nomenclador.prestaciones_n_op.precio) as total1
			FROM
			  facturacion.factura
			  INNER JOIN facturacion.comprobante ON (facturacion.factura.id_factura = facturacion.comprobante.id_factura)
			  INNER JOIN nomenclador.prestaciones_n_op using (id_comprobante)
			  where factura.id_factura=$numero_factura";
    $total1 = sql($query_t1, "NO puedo calcular el total");
    $monto_factura = $total->fields['total'] + $total1->fields['total1'];

    ($monto_factura == '') ? $monto_factura = 0 : $monto_factura = $monto_factura;

    $query_t = "SELECT monto_prefactura, fecha_factura
			FROM
			  facturacion.factura			  
			  where factura.id_factura=$numero_factura";
    $total = sql($query_t, "NO puedo calcular el total");
    $monto_prefactura = $total->fields['monto_prefactura'];

    if ($monto_prefactura == '') {
      $monto_prefactura = $monto_factura;
      $query = "update facturacion.factura set 
	    			monto_prefactura='$monto_prefactura'   			
	    			where id_factura='$numero_factura'";
      sql($query, "Error al actualizar factura") or fin_pagina();
    }
    $fecha_factura = $total->fields['fecha_factura'];


    $db->StartTrans();
    $q = "select nextval('contabilidad.ingreso_id_ingreso_seq') as id_comprobante";
    $id_comprobante = sql($q) or fin_pagina();
    $id_comprobante = $id_comprobante->fields['id_comprobante'];
    $query = "insert into contabilidad.ingreso
	             (id_ingreso,cuie,monto_prefactura,fecha_prefactura,monto_factura,fecha_factura,comentario,usuario,fecha,numero_factura,id_servicio)
	             values
	             ($id_comprobante,'$cuie','$monto_prefactura','$fecha_prefactura','$monto_factura','$fecha_factura','$comentario','$usuario','$fecha','$numero_factura','$id_servicio')";
    sql($query, "Error al insertar el comprobante") or fin_pagina();


    $query = "select periodo_actual from facturacion.factura where id_factura='$numero_factura'";
    $res = sql($query, 'error al traer factura');
    $periodo_actual = $res->fields['periodo_actual'];



    $query = "update facturacion.factura set 
	    			nro_exp_ext='$expediente_externo',
	    			periodo_contable='$periodo_actual',
	    			fecha_exp_ext='$fecha_exp_ext'    			
	    			where id_factura='$numero_factura'";
    sql($query, "Error al actualizar factura") or fin_pagina();

    $accion = "Se guardo el Ingreso Numero: $id_comprobante";


    $db->CompleteTrans();
  }
  else
    $accion="ERROR!!: No se puede Guardar, ya se genero un ingreso con la FACTURA: $numero_factura";
}//de if ($_POST['guardar']=="Guardar nuevo Muleto")

if ($_POST['guardar'] == "Guardar Egreso") {

  $cuie = $_POST['cuie'];
  $monto_egreso = $_POST['monto_egreso'];
  $fecha_egreso = Fecha_db($_POST['fecha_egreso']);
  $monto_egre_comp = $_POST['monto_egre_comp'];
  $fecha_egre_comp = Fecha_db($_POST['fecha_egre_comp']);
  $comentario = $_POST['comentario1'];
  $usuario = $_ses_user['name'];
  $fecha = date("Y-m-d");
  $numero_factura = $_POST['numero_factura'];
  $id_servicio1 = $_POST['servicio1'];
  $id_inciso = $_POST['ins_nombre'];

  $db->StartTrans();
  $q = "select nextval('contabilidad.egreso_id_egreso_seq') as id_comprobante";
  $id_comprobante = sql($q) or fin_pagina();
  $id_comprobante = $id_comprobante->fields['id_comprobante'];
  $query = "insert into contabilidad.egreso
	             (id_egreso,cuie,monto_egreso,fecha_egreso,comentario,usuario,fecha,id_servicio,id_inciso,monto_egre_comp,fecha_egre_comp)
	             values
	             ($id_comprobante,'$cuie','$monto_egreso','$fecha_egreso','$comentario','$usuario','$fecha','$id_servicio1','$id_inciso','$monto_egre_comp','$fecha_egre_comp')";
  sql($query, "Error al insertar el comprobante") or fin_pagina();
  $accion = "Se guardo el Ingreso Numero: $id_comprobante";
  $db->CompleteTrans();
}//de if ($_POST['guardar']=="Guardar nuevo Muleto")

$sql = "select * from facturacion.smiefectores
	 where cuie='$cuie'";
$res_comprobante = sql($sql, "Error al traer los Comprobantes") or fin_pagina();

$nombre = $res_comprobante->fields['nombreefectores'];
$domicilio = $res_comprobante->fields['domicilio'];
$ciudad = $res_comprobante->fields['ciudad'];

echo $html_header;
?>
<script>
  //controlan que ingresen todos los datos necesarios par el muleto
  function control_nuevos_ingresos()
  {
    if(document.all.servicio.value=="-1"){
      alert('Debe Seleccionar un Servicio');
      return false;
    }
    if(document.all.numero_factura.value=="-1"){
      alert('Debe Vincular una Factura')
      return false;
    }
    if(document.all.expediente_externo.value==""){
      alert('Debe Ingresar un Expediente Externo');
      return false;
    }
    if(document.all.fecha_exp_ext.value==""){
      alert('Debe Ingresar una Fecha de Expediente Externo');
      return false;
    }
    if (confirm('Esta Seguro que Desea Agregar Ingreso?'))return true;
    else return false;	
  }


  function control_nuevos_egresos()
  {
    if(document.all.servicio1.value=="-1"){
      alert('Debe Seleccionar un Servicio');
      return false;
    }
  
    if(document.all.ins_nombre.value=="-1"){
      alert('Debe Seleccionar un Inciso');
      return false;
    }
	
    if(document.all.monto_egre_comp.value==""){
      alert('Debe Ingresar un monto egreso COMPROMETIDO');
      return false;
    }
    if(document.all.monto_egreso.value==""){
      alert('Debe Ingresar un monto egreso (0 si no hay monto)');
      return false;
    } 
    if (confirm('Esta Seguro que Desea Agregar Egreso?'))return true;
    else return false;	
  }//de function control_nuevos()

  var img_ext='<?= $img_ext = '../../imagenes/rigth2.gif' ?>';//imagen extendido
  var img_cont='<?= $img_cont = '../../imagenes/down2.gif' ?>';//imagen contraido
  function muestra_tabla(obj_tabla,nro){
    oimg=eval("document.all.imagen_"+nro);//objeto tipo IMG
    if (obj_tabla.style.display=='none'){
      obj_tabla.style.display='inline';
      oimg.show=0;
      oimg.src=img_ext;
    }
    else{
      obj_tabla.style.display='none';
      oimg.show=1;
      oimg.src=img_cont;
    }
  }
</script>

<form name='form1' action='ingre_egre_admin.php' method='POST'>

  <? echo "<center><b><font size='+2' color='red'>$accion</font></b></center>"; ?>
  <input type="hidden" name="cuie" value="<?= $cuie ?>">
  <?
  $sql = "select monto_egreso from contabilidad.egreso
		where cuie='$cuie'";
  $res_egreso = sql($sql, "no puede calcular el saldo");

  if ($res_egreso->recordCount() == 0) {
    $sql = "select ingre as total, ingre,egre,deve,egre_comp from
		(select sum (monto_deposito)as ingre from contabilidad.ingreso
		where cuie='$cuie') as ingreso,
		(select sum (monto_egreso)as egre from contabilidad.egreso
		where cuie='$cuie') as egreso,
		(select sum (monto_factura)as deve from contabilidad.ingreso
		where cuie='$cuie') as devengado,
		(select sum (monto_egre_comp)as egre_comp from contabilidad.egreso
		where cuie='$cuie') as egre_comp";
  } else {
    $sql = "select ingre-egre as total, ingre,egre,deve,egre_comp from
		(select sum (monto_deposito)as ingre from contabilidad.ingreso
		where cuie='$cuie') as ingreso,
		(select sum (monto_egreso)as egre from contabilidad.egreso
		where cuie='$cuie') as egreso,
		(select sum (monto_factura)as deve from contabilidad.ingreso
		where cuie='$cuie') as devengado,
		(select sum (monto_egre_comp)as egre_comp from contabilidad.egreso
		where cuie='$cuie') as egre_comp";
  }
  $res_saldo = sql($sql, "no puede calcular el saldo")
  ?>
  <table width="95%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor='<?= $bgcolor_out ?>' class="bordes">
    <tr id="mo">
      <td>
        <font size=+1><b>Ingreso / Egreso</b></font>    
      </td>
    </tr>
    <tr><td>
        <table width=70% align="center" class="bordes">
          <tr>
            <td id=mo colspan="2">
              <b> Descripción del Efector</b>
            </td>
          </tr>
          <tr>
            <td>
              <table>
                <tr>	           
                  <td align="center" colspan="2">
                    <b> CUIE: <font size="+1" color="Red"><?= $cuie ?></font> </b>
                  </td>
                </tr>
                <tr>
                  <td align="right">
                    <b>Nombre:
                  </td>         	
                  <td align='left'>
                    <input type='text' name='nombre' value='<?= $nombre; ?>' size=60 align='right' readonly></b>
                  </td>
                </tr>
                <tr>
                  <td align="right">
                    <b> Domicilio:
                  </td>   
                  <td  >
                    <input type='text' name='domicilio' value='<?= $domicilio; ?>' size=60 align='right' readonly></b>
                  </td>
                </tr>
                <tr>
                  <td align="right">
                    <b> Ciudad:
                  </td> 
                  <td >
                    <input type='text' name='ciudad' value='<?= $ciudad; ?>' size=60 align='right' readonly></b>
                  </td>
                </tr>
                <tr>
                  <td align="right"><b>Saldo:</b></td>
                  <td align="left">		          			
                    <b><font size="+1" color="Blue"><?= number_format($res_saldo->fields['total'], 2, ',', '.') ?></font></b>
                  </td>
                </tr>  
                <tr>	           
                  <td align="center" colspan="2">
                    <b><font size="2" color="Red">Nota: Los valores numericos se ingresan SIN separadores de miles, y con "." como separador DECIMAL</font> </b>
                  </td>
                </tr> 
                <tr>	           
                  <td align="center" colspan="2">
                    <? $ref = encode_link("detalle_servicio.php", array("cuie" => $cuie, "nombre" => $nombre));
                    $onclick_elegir = "window.open('$ref')"; ?>
                    <input type="button" name="detalle_servicio" value="Detalle por Servicio" onclick="(<?= $onclick_elegir ?>)" style="width:250px">
                    <input type="submit" name="notificar" value="Notificar Via Mail" onclick="return confirm('Se Notificara por Mail Movimiento Bancarios del CAPS. ¿Esta Seguro?');" style="width:250px">
                  </td>
                </tr>          
              </table>
            </td>      
          </tr>
        </table>     
        <table class="bordes" align="center" width="70%">
          <tr align="center" id="sub_tabla">
            <td colspan="2">	
		 		Nuevo Ingreso
            </td>
          </tr>
          <tr><td class="bordes"><table>
                <tr>
                  <td>
                <tr>
                  <td align="right">
                    <b>Número de Factura:</b>
                  </td>
                  <td align="left">		          			
                    <select name=numero_factura Style="width:550px"
                            onKeypress="buscar_combo(this);"
                            onblur="borrar_buffer();"
                            onchange="borrar_buffer();"
                            > 
                      <option value=-1 selected>Seleccione</option>
                      <?
                      $sql = "select $nroF * from facturacion.factura
									left join facturacion.smiefectores using (cuie)
									where factura.cuie='$cuie'
			                 		order by id_factura DESC";
                      $res_efectores = sql($sql) or fin_pagina();
                      while (!$res_efectores->EOF) {
                        $id_factura = $res_efectores->fields['id_factura'];
                        $nombreefector = $res_efectores->fields['nombreefector'];
                        $periodo_actual = $res_efectores->fields['periodo_actual'];
                        $periodo = $res_efectores->fields['periodo'];
                        $monto_prefactura = number_format($res_efectores->fields['monto_prefactura'], 2, ',', '.');
                        $fecha_factura = fecha($res_efectores->fields['fecha_factura']);
                        ?>
                        <?php $nro_factura_tmp = $func_nroFactura ? $res_efectores->fields['numero_factura'] : $id_factura; ?>
                        <option value=<?= $id_factura; ?>><?= "N°:" . $nro_factura_tmp . " - Periodo Prestaciones:" . $periodo_actual . " - Monto Cuasi:" . $monto_prefactura ?></option>

                        <?
                        $res_efectores->movenext();
                      }
                      ?>
                    </select><font size="2" color="Red"></font>
                  </td>

                </tr>

                <tr>
                  <td align="right">
                    <b>Servicio:</b>
                  </td>
                  <td align="left">		          			
                    <select name=servicio Style="width:450px"
                            onKeypress="buscar_combo(this);"
                            onblur="borrar_buffer();"
                            onchange="borrar_buffer();"
                            >
                      <option value=-1>Seleccione</option>
                      <?
                      $sql = "select * from facturacion.servicio order by descripcion";
                      $res_efectores = sql($sql) or fin_pagina();
                      while (!$res_efectores->EOF) {
                        $id_servicio = $res_efectores->fields['id_servicio'];
                        $descripcion = $res_efectores->fields['descripcion'];
                        ?>
                        <option <?= ($res_efectores->fields['descripcion'] == "No Corresponde") ? "selected" : "" ?> value=<?= $id_servicio; ?>><?= $descripcion ?></option>
                        <?
                        $res_efectores->movenext();
                      }
                      ?>
                    </select>
                  </td>
                </tr>

                <tr>
                  <td align="right">
                    <b>Fecha de la Prefactura:</b>
                  </td>
                  <td align="left">

                    <? $fecha_prefactura = date("d/m/Y"); ?>
                    <input type=text id=fecha_prefactura name=fecha_prefactura value='<?= $fecha_prefactura; ?>' size=15 readonly>
                    <?= link_calendario("fecha_prefactura"); ?>					    	 
                  </td>		    
                </tr>



                <tr>
                  <td align="right">
                    <b>Expediente Externo:</b>
                  </td>
                  <td align="left">		          			
                    <input type="text" name="expediente_externo" value="" size=30 align="right"><font color="Red"> Ej: 2010-000003, 2009-012345</font>
                  </td>
                </tr>

                <tr>
                  <td align="right"><b>Fecha de Expediente:</b></td>
                  <td align="left">
                    <input type=text id=fecha_exp_ext name=fecha_exp_ext value='<?= $fecha_exp_ext; ?>' size=15 readonly>
                    <?= link_calendario("fecha_exp_ext"); ?>					    	 
                  </td>		    
                </tr>

                <tr>
                  <td align="right">
                    <b>Comentario:</b>
                  </td>         	
                  <td align='left'>
                    <textarea cols='70' rows='3' name='comentario' ></textarea>
                  </td>
                </tr>         			 					 
            </td>
          </tr>
        </table></td></tr>	 
    <tr>
      <td align="center" colspan="2" class="bordes">		      
        <input type="submit" name="guardar" value="Guardar Ingreso" title="Guardar Ingreso" Style="width:300px" onclick="return control_nuevos_ingresos()">
      </td>
    </tr> 
  </table>	
</td></tr>

<?
//tabla de comprobantes
$query = "SELECT 
  $nroF
  *
FROM
  contabilidad.ingreso  
  left join facturacion.factura on ingreso.numero_factura=factura.id_factura 
  left join facturacion.servicio using (id_servicio)
  where ingreso.cuie='$cuie' 
  order by id_ingreso DESC";
$res_comprobante = sql($query, "<br>Error al traer los comprobantes<br>") or fin_pagina();
?>
<tr><td><table width="100%" class="bordes" align="center">
      <tr align="center" id="mo">
        <td align="center" width="3%">
          <img id="imagen_2" src="<?= $img_ext ?>" border=0 title="Mostrar Ingresos" align="left" style="cursor:hand;" onclick="muestra_tabla(document.all.prueba_vida,2);" >
        </td>
        <td align="center">
          <b>Ingresos</b>&nbsp; (Total Depositado: <?= number_format($res_saldo->fields['ingre'], 2, ',', '.') ?>
          &nbsp; Total Devengado: <?= number_format($res_saldo->fields['deve'], 2, ',', '.') ?>)
          <? $total_depositado = $res_saldo->fields['ingre'] //lo uso en ecuacion mas adelante ?>
        </td>
      </tr>
    </table></td></tr>
<tr><td><table id="prueba_vida" border="1" width="100%" style="display:none;border:thin groove">
      <? if ($res_comprobante->RecordCount() == 0) { ?>
        <tr>
          <td align="center">
            <font size="3" color="Red"><b>No existen Ingresos para este Efector</b></font>
          </td>
        </tr>
        <?
      } else {
        ?>
        <tr id="sub_tabla">		 	    
          <td width="5%">ID</td>
          <td width="15%">Numero Factura</td>
          <td width="15%">Monto Pre Factura</td>
          <td width="15%">Fecha Pre Factura</td>
          <td width="15%">Monto Factura</td>
          <td width="15%">Fecha Factura</td>
          <td width="15%">Monto Deposito</td>
          <td width="15%">Fecha Deposito</td>
          <td width="15%">Fecha Notificacion</td>
          <td width="15%">Comentario</td>
          <td width="10%">Usuario</td>
          <td width="10%">Fecha</td>
          <td width="10%">Servicio</td>
          <td width="10%">Borrar</td>
        </tr>
        <?
        $res_comprobante->movefirst();
        while (!$res_comprobante->EOF) {
          $ref = encode_link("carga_deposito.php", array("id_ingreso" => $res_comprobante->fields['id_ingreso'], "pagina" => "ingre_egre_admin.php", "cuie" => $cuie, "numero_factura" => $res_comprobante->fields['numero_factura']));
          $onclick_elegir = "location.href='$ref'";

          $ref1 = encode_link("ingre_egre_admin.php", array("id_ingreso" => $res_comprobante->fields['id_ingreso'], "marcar1" => "True", "cuie" => $cuie));
          $id_ingreso = $res_comprobante->fields['id_ingreso'];
          $onclick_eliminar = "if (confirm('Esta Seguro que Desea Eliminar Ingreso $id_ingreso ?')) location.href='$ref1'
            						else return false;	";
          ?>
          <tr <?= atrib_tr() ?>>	 			
            <td onclick="<?= $onclick_elegir ?>"><?= $res_comprobante->fields['id_ingreso'] ?></td>
            <?php $nro_factura_tmp = $func_nroFactura ? $res_comprobante->fields['numero_factura'] : $res_comprobante->fields['id_factura']; ?>
            <td onclick="<?= $onclick_elegir ?>" align="center"><b><?= number_format($nro_factura_tmp, 0, '', '.') ?></b></td>
            <td onclick="<?= $onclick_elegir ?>"><?= number_format($res_comprobante->fields['monto_prefactura'], 2, ',', '.') ?></td>
            <td onclick="<?= $onclick_elegir ?>"><?= fecha($res_comprobante->fields['fecha_prefactura']) ?></td>
            <td onclick="<?= $onclick_elegir ?>"><?= number_format($res_comprobante->fields['monto_factura'], 2, ',', '.') ?></td>
            <td onclick="<?= $onclick_elegir ?>"><?= fecha($res_comprobante->fields['fecha_factura']) ?></td>
            <td onclick="<?= $onclick_elegir ?>"><?= number_format($res_comprobante->fields['monto_deposito'], 2, ',', '.') ?></td>
            <td onclick="<?= $onclick_elegir ?>"><?= fecha($res_comprobante->fields['fecha_deposito']) ?></td>
            <td onclick="<?= $onclick_elegir ?>"><?= fecha($res_comprobante->fields['fecha_notificacion']) ?></td>
            <td onclick="<?= $onclick_elegir ?>"><?= $res_comprobante->fields['comentario'] ?></td>
            <td onclick="<?= $onclick_elegir ?>"><?= $res_comprobante->fields['usuario'] ?></td>		 		
            <td onclick="<?= $onclick_elegir ?>"><?= fecha($res_comprobante->fields['fecha']) ?></td>		 		
            <td onclick="<?= $onclick_elegir ?>"><?= $res_comprobante->fields['descripcion'] ?></td>		 		
            <td onclick="<?= $onclick_eliminar ?>" align="center"><img src='../../imagenes/salir.gif' style='cursor:hand;'></td>		 		
          </tr>	

          <?
          $res_comprobante->movenext();
        }
      }
      ?>
    </table></td></tr>

<tr><td>
    <table class="bordes" align="center" width="70%">
      <tr align="center" id="sub_tabla">
        <td colspan="2">	
		 		Nuevo Egreso
        </td>
      </tr>
      <tr><td class="bordes"><table>
            <tr>
              <td>				 
            <tr>
              <td align="right">
                <b>Rubro:</b>
              </td>
              <td align="left">		          			
                <select name=ins_nombre Style="width:450px"
                        onKeypress="buscar_combo(this);"
                        onblur="borrar_buffer();"
                        onchange="borrar_buffer();"
                        >
                  <option value=-1>Seleccione</option>
                  <?
                  $sql = "select * from contabilidad.inciso order by id_inciso";
                  $res_efectores = sql($sql) or fin_pagina();
                  while (!$res_efectores->EOF) {
                    $id_servicio = $res_efectores->fields['id_inciso'];
                    $descripcion = $res_efectores->fields['ins_nombre'];
                    ?>
                    <option value='<?= $id_servicio; ?>'><?= $descripcion ?></option>
                    <?
                    $res_efectores->movenext();
                  }
                  ?>
                </select>
              </td>
            </tr>

            <tr>
              <td align="right">
                <b>Servicio:</b>
              </td>
              <td align="left">		          			
                <select name=servicio1 Style="width:450px"
                        onKeypress="buscar_combo(this);"
                        onblur="borrar_buffer();"
                        onchange="borrar_buffer();"
                        >
                  <option value=-1>Seleccione</option>
                  <?
                  $sql = "select * from facturacion.servicio order by descripcion";
                  $res_efectores = sql($sql) or fin_pagina();
                  while (!$res_efectores->EOF) {
                    $id_servicio = $res_efectores->fields['id_servicio'];
                    $descripcion = $res_efectores->fields['descripcion'];
                    ?>
                    <option <?= ($res_efectores->fields['descripcion'] == "No Corresponde") ? "selected" : "" ?> value=<?= $id_servicio; ?>><?= $descripcion ?></option>
                    <?
                    $res_efectores->movenext();
                  }
                  ?>
                </select>
              </td>
            </tr>

            <tr>
              <td align="right">
                <b>Monto del Egreso Comprometido:</b>
              </td>
              <td align="left">		          			
                <input type="text" name="monto_egre_comp" value="" size=30 align="right">
              </td>
            </tr>
            <tr>
              <td align="right">
                <b>Fecha del egreso Comprometido:</b>
              </td>
              <td align="left">

                <? $fecha_egre_comp = date("d/m/Y"); ?>
                <input type=text id=fecha_egre_comp name=fecha_egre_comp value='<?= $fecha_egre_comp; ?>' size=15 readonly>
                <?= link_calendario("fecha_egre_comp"); ?>					    	 
              </td>		    
            </tr>


            <tr>
              <td align="right">
                <b>Monto del Egreso:</b>
              </td>
              <td align="left">		          			
                <input type="text" name="monto_egreso" value="" size=30 align="right">
              </td>
            </tr>
            <tr>
              <td align="right">
                <b>Fecha del egreso:</b>
              </td>
              <td align="left">

                <? $fecha_egreso = date("d/m/Y"); ?>
                <input type=text id=fecha_egreso name=fecha_egreso value='<?= $fecha_egreso; ?>' size=15 readonly>
                <?= link_calendario("fecha_egreso"); ?>					    	 
              </td>		    
            </tr>

            <tr>
              <td align="right">
                <b>Comentario:</b>
              </td>         	
              <td align='left'>
                <textarea cols='70' rows='3' name='comentario1' ></textarea>
              </td>
            </tr>   					 
        </td>
      </tr>
    </table></td></tr>	 
<tr>
  <td align="center" colspan="2" class="bordes">		      
    <input type="submit" name="guardar" value="Guardar Egreso" title="Guardar Ingreso" Style="width:300px" onclick="return control_nuevos_egresos()">
  </td>
</tr> 
</table>	
</td></tr>

<?
//tabla de comprobantes
$query = "SELECT 
  *
FROM
  contabilidad.egreso  
  left join facturacion.servicio using (id_servicio) 
  left join contabilidad.inciso using (id_inciso) 
  where cuie='$cuie' 
  order by id_egreso DESC";
$res_comprobante = sql($query, "<br>Error al traer los comprobantes<br>") or fin_pagina();
?>
<tr><td><table width="100%" class="bordes" align="center">
      <tr align="center" id="mo">
        <td align="center" width="3%">
          <img id="imagen_2" src="<?= $img_ext ?>" border=0 title="Mostrar egresos" align="left" style="cursor:hand;" onclick="muestra_tabla(document.all.prueba_vida1,2);" >
        </td>
        <td align="center">
          <b>Egresos</b>&nbsp; (Total de Egresos:<?= number_format($res_saldo->fields['egre'], 2, ',', '.') ?> <!--Comprometido:--><? //number_format($res_saldo->fields['egre_comp'],2,',','.')  ?>
	   Comprometido NO Pagado: <?= number_format($res_saldo->fields['egre_comp'] - $res_saldo->fields['egre'], 2, ',', '.') ?>) // <font color=#F781F3>Saldo Real= <?= number_format($total_depositado - $res_saldo->fields['egre'] - ($res_saldo->fields['egre_comp'] - $res_saldo->fields['egre']), 2, ',', '.') ?></font></b>
          <? $saldo_real = $total_depositado - $res_saldo->fields['egre'] - ($res_saldo->fields['egre_comp'] - $res_saldo->fields['egre']) ?>
          <input type="hidden" value="<?= $saldo_real ?>" name="saldo_real">
        </td>
      </tr>
    </table></td></tr>
<tr><td><table id="prueba_vida1" border="1" width="100%" style="display:none;border:thin groove">
      <? if ($res_comprobante->RecordCount() == 0) { ?>
        <tr>
          <td align="center">
            <font size="3" color="Red"><b>No existen Egresos para este Efector</b></font>
          </td>
        </tr>
        <?
      } else {
        ?>
        <tr id="sub_tabla">		 	    
          <td width="5%">ID</td>
          <td width="15%">Rubro</td>
          <td width="15%">Monto Egre COMPROMETIDO</td>	 		
          <td width="15%">Fecha Egre COMPROMETIDO</td>
          <td width="15%">Monto Egre</td>	 		
          <td width="15%">Fecha Egre</td>
          <td width="15%">Comentario</td>
          <td width="15%">Fecha Deposito</td>
          <td width="10%">Usuario</td>
          <td width="10%">Fecha</td>
          <td width="10%">Servicio</td>
          <td width="10%">Borrar</td>
        </tr>
        <?
        $res_comprobante->movefirst();
        while (!$res_comprobante->EOF) {
          $ref = encode_link("modifica_egreso.php", array("id_egreso" => $res_comprobante->fields['id_egreso'], "pagina" => "ingre_egre_admin.php", "cuie" => $cuie, "monto_egreso" => $res_comprobante->fields['monto_egreso'], "monto_egreso_comp" => $res_comprobante->fields['monto_egre_comp']));
          $onclick_elegir = "location.href='$ref'";

          $ref1 = encode_link("ingre_egre_admin.php", array("id_egreso" => $res_comprobante->fields['id_egreso'], "marcar2" => "True", "cuie" => $cuie));
          $id_egreso = $res_comprobante->fields['id_egreso'];
          $onclick_eliminar = "if (confirm('Esta Seguro que Desea Eliminar Egreso $id_egreso ?')) location.href='$ref1'
            						else return false;	";
          ?>
          <tr <?= atrib_tr() ?>>	 			
            <td onclick="<?= $onclick_elegir ?>"><?= $res_comprobante->fields['id_egreso'] ?></td>
            <td onclick="<?= $onclick_elegir ?>"><?= $res_comprobante->fields['ins_nombre'] ?></td>
            <td onclick="<?= $onclick_elegir ?>"><?= number_format($res_comprobante->fields['monto_egre_comp'], 2, ',', '.') ?></td>
            <td onclick="<?= $onclick_elegir ?>"><?= fecha($res_comprobante->fields['fecha_egre_comp']) ?></td>
            <td onclick="<?= $onclick_elegir ?>"><?= number_format($res_comprobante->fields['monto_egreso'], 2, ',', '.') ?></td>
            <td onclick="<?= $onclick_elegir ?>"><?= fecha($res_comprobante->fields['fecha_egreso']) ?></td>		 		
            <td onclick="<?= $onclick_elegir ?>"><?= $res_comprobante->fields['comentario'] ?></td>
            <td onclick="<?= $onclick_elegir ?>"><?= fecha($res_comprobante->fields['fecha_deposito']) ?></td>
            <td onclick="<?= $onclick_elegir ?>"><?= $res_comprobante->fields['usuario'] ?></td>	
            <td onclick="<?= $onclick_elegir ?>"><?= fecha($res_comprobante->fields['fecha']) ?></td>		 
            <td onclick="<?= $onclick_elegir ?>"><?= $res_comprobante->fields['descripcion'] ?></td>		 
            <td onclick="<?= $onclick_eliminar ?>" align="center"><img src='../../imagenes/salir.gif' style='cursor:hand;'></td>		 					 		
          </tr>	

          <?
          $res_comprobante->movenext();
        }
      }
      ?>
    </table></td></tr>



<tr><td><table width=100% align="center" class="bordes">
      <tr align="center">
        <td>
          <input type=button name="volver" value="Volver" onclick="document.location='ing_egre_listado.php'"title="Volver al Listado" style="width:150px">     
        </td>
      </tr>
    </table></td></tr>

</table>

</form>
<?= fin_pagina(); // aca termino  ?>
