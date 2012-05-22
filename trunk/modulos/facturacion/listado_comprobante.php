<?php
require_once("../../config.php");

require_once("../../lib/funciones_misiones.php");

$func_nroFactura = nro_factura_misiones();

if ($func_nroFactura) {
  $nroF = NRO_FACTURA_MISIONES;
} else {
  $nroF = '';
}

variables_form_busqueda("listado_comprobante");

if ($cmd == "")
  $cmd = "F";


$orden = array(
    "default" => "8",
    "default_up" => "0",
    "1" => "afiapellido",
    "2" => "afinombre",
    "3" => "afidni",
    "4" => "afitipocategoria",
    "5" => "nombreefector",
    "6" => "activo",
    "8" => "id_comprobante",
    "9" => "id_factura",
    "10" => "periodo",
    "11" => "comprobante.fecha_comprobante"
);
$filtro = array(
    "afidni" => "DNI",
    "afiapellido" => "Apellido",
    "afinombre" => "Nombre",
    "descripcion" => "Tipo Afiliado",
    "nombreefector" => "Nombre Efector",
    "activo" => "Activo",
    "id_comprobante" => "Nro. Comprobante",
    "id_factura" => "Nro. Factura",
    "factura.periodo" => "Periodo",
);

if ($func_nroFactura) {
  $filtro = array(
      "afidni" => "DNI",
      "afiapellido" => "Apellido",
      "afinombre" => "Nombre",
      "descripcion" => "Tipo Afiliado",
      "nombreefector" => "Nombre Efector",
      "nacer.smiafiliados.activo" => "Activo",
      "cast(id_comprobante as text)" => "Nro. Comprobante",
      "(case when facturacion.factura.nro_fact_offline <> '' 
        then cast(facturacion.factura.nro_fact_offline as text) 
        else cast(facturacion.factura.id_factura as text) end)" =>  "Nro. Factura",
      "factura.periodo" => "Periodo",
  );
}

$datos_barra = array(
    array(
        "descripcion" => "Facturados",
        "cmd" => "F"
    ),
    array(
        "descripcion" => "No Facturados",
        "cmd" => "NF"
    ),
    array(
        "descripcion" => "Todos",
        "cmd" => "todos"
    )
);

generar_barra_nav($datos_barra);

$sql_tmp = "select $nroF * 
	 from facturacion.comprobante	 
	 left join nacer.smiafiliados using (id_smiafiliados)
     left join nacer.smitiposcategorias on (afitipocategoria=codcategoria)
	 left join facturacion.smiefectores using (cuie)
	 left join facturacion.factura using (id_factura)";

if ($cmd == "F")
  $where_tmp = " (comprobante.id_factura is not null)";


if ($cmd == "NF")
  $where_tmp = " (comprobante.id_factura is null)";

if ($_POST['muestra'] == "Muestra") {

  $fecha_desde = $_POST['fecha_desde'];
  $fecha_hasta = $_POST['fecha_hasta'];
  $link = encode_link("comprobante_excel.php", array("fecha_desde" => $fecha_desde, "fecha_hasta" => $fecha_hasta));
  ?>
  <script>
    window.open('<?= $link ?>')
  </script>	
<?
}

if ($_POST['importar'] == "Importar") {

  $fecha_desde = $_POST['fecha_desde'];
  $fecha_hasta = $_POST['fecha_hasta'];

  $filename = 'comprobante.txt';
  if (!$handle = fopen($filename, 'a')) {
    echo "No se Puede abrir ($filename)";
    exit;
  }
  $sql1 = "SELECT 
          $nroF
				  facturacion.factura.cuie,
				  facturacion.factura.fecha_factura::date,
				  'FC' AS FC,
				  facturacion.factura.id_factura,
				  facturacion.factura.periodo,
				  facturacion.factura.fecha_carga::date,
				  facturacion.factura.nro_exp,
				  facturacion.factura.monto_prefactura,
				  facturacion.factura.fecha_control::date,
				  contabilidad.ingreso.monto_factura,
				  contabilidad.ingreso.fecha_deposito,
				  contabilidad.ingreso.comentario
				FROM
				  facturacion.factura
				  left JOIN contabilidad.ingreso ON (facturacion.factura.id_factura = contabilidad.ingreso.numero_factura)
				 Where
				 (factura.fecha_factura between '$fecha_desde' and '$fecha_hasta') and factura.estado='C'";

  $result1 = sql($sql1) or die;
  $result1->movefirst();

  while (!$result1->EOF) {

    $id_factura = $result1->fields['id_factura'];
    $sql2 = "SELECT date (log_factura.fecha) as fechaliquidacion
				FROM
				  facturacion.log_factura				  
				 Where
				  id_factura='$id_factura' and tipo='Cerrar Factura'
				 ORDER BY fechaliquidacion DESC";
    $result2 = sql($sql2) or die;
    $result2->movefirst();

    $contenido = $result1->fields['cuie'];
    $contenido.=chr(9);
    $contenido.=$result1->fields['fecha_factura'];
    $contenido.=chr(9);
    $contenido.=$result1->fields['fc'];
    $contenido.=chr(9);
    $contenido.= $func_nroFactura ? $result1->fields['numero_factura'] : $result1->fields['id_factura'];
    $contenido.=chr(9);
    $contenido.=substr($result1->fields['periodo'], 5, 2) . "/" . substr($result1->fields['periodo'], 0, 4);
    $contenido.=chr(9);
    $contenido.=$result1->fields['fecha_carga'];
    $contenido.=chr(9);
    $contenido.=$result1->fields['nro_exp'];
    $contenido.=chr(9);
    $contenido.=number_format($result1->fields['monto_prefactura'], 2, '.', '');
    $contenido.=chr(9);
    $contenido.=number_format($result1->fields['monto_factura'], 2, '.', '');
    $contenido.=chr(9);
    $contenido.=$result1->fields['fecha_deposito'];
    $contenido.=chr(9);
    $contenido.=$result1->fields['fecha_deposito'];
    $contenido.=chr(9);
    $contenido.=$result2->fields['fechaliquidacion'];
    $contenido.=chr(9);
    $cadena = $result1->fields['comentario'];
    $contenido.=str_replace('\n', "", $cadena);

    $contenido.="\n";

    if (fwrite($handle, $contenido) === FALSE) {
      echo "No se Puede escribir  ($filename)";
      exit;
    }
    $result1->MoveNext();
  }
  echo "El Archivo ($filename) se genero con exito <br>";
  fclose($handle);


  $filename = 'prestaciones.txt';

  if (!$handle = fopen($filename, 'a')) {
    echo "No se Puede abrir ($filename)";
    exit;
  }
  $sql1 = "SELECT 
          $nroF
				  comprobante.cuie,
  				  'FC' AS FC,
 				  facturacion.comprobante.id_factura,
 				  facturacion.nomenclador.codigo,
				  facturacion.comprobante.fecha_comprobante::date,
				  nacer.smiafiliados.clavebeneficiario,
				  nacer.smiafiliados.afitipodoc,
				  nacer.smiafiliados.aficlasedoc,
				  nacer.smiafiliados.afidni,
				  facturacion.prestacion.precio_prestacion*facturacion.prestacion.cantidad as precio_total,
				  facturacion.prestacion.peso,
				  facturacion.prestacion.tension_arterial,
				  facturacion.prestacion.precio_prestacion, 
				  anexo.numero
				FROM
				  facturacion.comprobante
				  left JOIN facturacion.prestacion ON (facturacion.comprobante.id_comprobante = facturacion.prestacion.id_comprobante)
				  left JOIN facturacion.nomenclador ON (facturacion.prestacion.id_nomenclador = facturacion.nomenclador.id_nomenclador)
				  left JOIN nacer.smiafiliados ON (facturacion.comprobante.id_smiafiliados = nacer.smiafiliados.id_smiafiliados)
				  left JOIN facturacion.factura ON (comprobante.id_factura = factura.id_factura)
				  left join facturacion.anexo on (prestacion.id_anexo = anexo.id_anexo)
				 Where
				 (factura.fecha_factura between '$fecha_desde' and '$fecha_hasta') and factura.estado='C'";

  $result1 = sql($sql1) or die;
  $result1->movefirst();

  while (!$result1->EOF) {

    $id_factura = $result1->fields['id_factura'];
    $sql2 = "SELECT date (log_factura.fecha) as fechaliquidacion
				FROM
				  facturacion.log_factura				  
				 Where
				  id_factura='$id_factura' and tipo='Cerrar Factura'
				 ORDER BY fechaliquidacion DESC";
    $result2 = sql($sql2) or die;
    $result2->movefirst();

    $contenido = $result1->fields['cuie'];
    $contenido.=chr(9);
    $contenido.=$result1->fields['fc'];
    $contenido.=chr(9);
    $contenido.=$func_nroFactura ? $result1->fields['numero_factura'] : $result1->fields['id_factura'];
    $contenido.=chr(9);
    $contenido.=$result1->fields['codigo'];
    $contenido.=chr(9);
    $contenido.=$result1->fields['numero'];
    $contenido.=chr(9);
    $contenido.=$result1->fields['fecha_comprobante'];
    $contenido.=chr(9);
    $contenido.=$result1->fields['clavebeneficiario'];
    $contenido.=chr(9);
    $contenido.=$result1->fields['afitipodoc'];
    $contenido.=chr(9);
    $contenido.="P";
    $contenido.=chr(9);
    $contenido.=$result1->fields['afidni'];
    $contenido.=chr(9);
    $contenido.=number_format($result1->fields['precio_total'], 2, '.', '');
    $contenido.=chr(9);
    $contenido.="S";
    $contenido.=chr(9);
    $contenido.="N";
    $contenido.=chr(9);
    $contenido.=number_format($result1->fields['peso'], 3, '.', '');
    $contenido.=chr(9);
    $contenido.=$result1->fields['tension_arterial'];
    $contenido.=chr(9);
    $contenido.=$result2->fields['fechaliquidacion'];
    $contenido.="\n";
    if (fwrite($handle, $contenido) === FALSE) {
      echo "No se Puede escribir  ($filename)";
      exit;
    }
    $result1->MoveNext();
  }
  echo "El Archivo ($filename) se genero con exito <br>";

  fclose($handle);



  $filename = 'fondos.txt';

  if (!$handle = fopen($filename, 'a')) {
    echo "No se Puede abrir ($filename)";
    exit;
  }
  $sql1 = "SELECT 
				  contabilidad.egreso.cuie,
				  contabilidad.egreso.fecha_egreso::date,
				  contabilidad.egreso.monto_egreso,				  
				  contabilidad.egreso.id_inciso,				  
				  contabilidad.inciso.ins_nombre
				FROM
				  contabilidad.egreso
				  left JOIN contabilidad.inciso ON (contabilidad.egreso.id_inciso = contabilidad.inciso.id_inciso)
				where
				 (fecha_egreso between '$fecha_desde' and '$fecha_hasta')";

  $result1 = sql($sql1) or die;
  $result1->movefirst();
  while (!$result1->EOF) {
    $contenido = $result1->fields['cuie'];
    $contenido.=chr(9);
    $contenido.=$result1->fields['fecha_egreso'];
    $contenido.=chr(9);
    $contenido.=substr(Fecha($result1->fields['fecha_egreso']), 3, 7);
    $contenido.=chr(9);
    ($result1->fields['id_inciso'] == 1) ? $contenido.=number_format($result1->fields['monto_egreso'], 2, '.', '') : $contenido.=0;
    $contenido.=chr(9);
    ($result1->fields['id_inciso'] == 10) ? $contenido.=number_format($result1->fields['monto_egreso'], 2, '.', '') : $contenido.=0;
    $contenido.=chr(9);
    ($result1->fields['id_inciso'] == 11) ? $contenido.=number_format($result1->fields['monto_egreso'], 2, '.', '') : $contenido.=0;
    $contenido.=chr(9);
    ($result1->fields['id_inciso'] == 3) ? $contenido.=number_format($result1->fields['monto_egreso'], 2, '.', '') : $contenido.=0;
    $contenido.=chr(9);
    ($result1->fields['id_inciso'] == 13) ? $contenido.=number_format($result1->fields['monto_egreso'], 2, '.', '') : $contenido.=0;
    $contenido.=chr(9);
    ($result1->fields['id_inciso'] == 14) ? $contenido.=number_format($result1->fields['monto_egreso'], 2, '.', '') : $contenido.=0;
    $contenido.=chr(9);
    ($result1->fields['id_inciso'] == 4) ? $contenido.=number_format($result1->fields['monto_egreso'], 2, '.', '') : $contenido.=0;
    $contenido.=chr(9);
    ($result1->fields['id_inciso'] == 12) ? $contenido.=number_format($result1->fields['monto_egreso'], 2, '.', '') : $contenido.=0;
    $contenido.=chr(9);
    ($result1->fields['id_inciso'] == 8) ? $contenido.=number_format($result1->fields['monto_egreso'], 2, '.', '') : $contenido.=0;
    $contenido.=chr(9);
    ($result1->fields['id_inciso'] == 9) ? $contenido.=number_format($result1->fields['monto_egreso'], 2, '.', '') : $contenido.=0;
    $contenido.=chr(9);
    ($result1->fields['id_inciso'] == 6) ? $contenido.=number_format($result1->fields['monto_egreso'], 2, '.', '') : $contenido.=0;
    $contenido.=chr(9);
    ($result1->fields['id_inciso'] == 5) ? $contenido.=number_format($result1->fields['monto_egreso'], 2, '.', '') : $contenido.=0;
    $contenido.=chr(9);
    ($result1->fields['id_inciso'] == 15) ? $contenido.=number_format($result1->fields['monto_egreso'], 2, '.', '') : $contenido.=0;
    $contenido.=chr(9);
    ($result1->fields['id_inciso'] == 16) ? $contenido.=number_format($result1->fields['monto_egreso'], 2, '.', '') : $contenido.=0;
    $contenido.=chr(9);
    ($result1->fields['id_inciso'] == 17) ? $contenido.=number_format($result1->fields['monto_egreso'], 2, '.', '') : $contenido.=0;
    $contenido.="\n";
    if (fwrite($handle, $contenido) === FALSE) {
      echo "No se Puede escribir  ($filename)";
      exit;
    }
    $result1->MoveNext();
  }
  echo "El Archivo ($filename) se genero con exito <br>";

  fclose($handle);



  $filename = 'nomenclador.txt';

  if (!$handle = fopen($filename, 'a')) {
    echo "No se Puede abrir ($filename)";
    exit;
  }
  $sql1 = "SELECT 
				  facturacion.nomenclador.codigo,
				  facturacion.anexo.prueba,
				  facturacion.nomenclador.descripcion,
				  facturacion.nomenclador.precio
				FROM
				  facturacion.nomenclador
				  LEFT OUTER JOIN facturacion.anexo ON (facturacion.nomenclador.id_nomenclador = facturacion.anexo.id_nomenclador)
				  INNER JOIN facturacion.nomenclador_detalle ON (facturacion.nomenclador.id_nomenclador_detalle = facturacion.nomenclador_detalle.id_nomenclador_detalle)
				WHERE
				  (facturacion.nomenclador_detalle.descripcion = 'Periodo Anual 2009') and (tipo_nomenclador='NORMAL')
				  and (codigo<>'DEB-CRED')and (codigo<>'DIFERENCIA DE NOMENCLADOR')
				ORDER BY
				  facturacion.nomenclador.codigo";

  $result1 = sql($sql1) or die;
  $result1->movefirst();
  //FORMATO TABLA QUE IMPORTA SIRGe OK
  while (!$result1->EOF) {
    $contenido = str_replace('\n', "", $result1->fields['codigo']);
    $contenido.=chr(9);
    $contenido.=" "; //anexo
    $contenido.=chr(9);
    $contenido.=str_replace('\n', "", $result1->fields['descripcion']);
    $contenido.=chr(9);
    $contenido.=number_format($result1->fields['precio'], 2, '.', '');
    $contenido.=chr(9);
    $contenido.="\n";
    if (fwrite($handle, $contenido) === FALSE) {
      echo "No se Puede escribir  ($filename)";
      exit;
    }
    $result1->MoveNext();
  }
  echo "El Archivo ($filename) se genero con exito <br>";

  fclose($handle);
}


echo $html_header;
?>
<form name=form1 action="listado_comprobante.php" method=POST>
<? echo "<center><b><font size='+1' color='red'>$accion</font></b></center>"; ?>
  <table cellspacing=2 cellpadding=2 border=0 width=100% align=center>
    <tr>
      <td align=center>
<? list($sql, $total_muletos, $link_pagina, $up) = form_busqueda($sql_tmp, $orden, $filtro, $link_tmp, $where_tmp, "buscar"); ?>
        &nbsp;&nbsp;<input type=submit name="buscar" value='Buscar'>

<? if (permisos_check('inicio', 'importa_rendicion_cuentas')) { ?>
          <b>
            &nbsp;&nbsp;&nbsp; || &nbsp;&nbsp;&nbsp;
  	    Desde: <input type="text" name="fecha_desde" value="aaaa-mm-dd" maxlength="10" size="12">
  	    Hasta: <input type="text" name="fecha_hasta" value="aaaa-mm-dd" maxlength="10" size="12">
            <input type="submit" name="importar" value='Importar'>
            &nbsp;&nbsp;&nbsp;
            <input type="submit" name="muestra" value='Muestra'>
          </b>
<? } ?>
      </td>
    </tr>
  </table>

<? $result = sql($sql) or die; ?>

  <table border=0 width=100% cellspacing=2 cellpadding=2 bgcolor='<?= $bgcolor3 ?>' align=center>
    <tr>
      <td colspan=15 align=left id=ma>
        <table width=100%>
          <tr id=ma>
            <td width=30% align=left><b>Total:</b> <?= $total_muletos ?></td>       
            <td width=40% align=right><?= $link_pagina ?></td>
          </tr>
        </table>
      </td>
    </tr>


    <tr>
      <td id=mo width=1%>&nbsp;</td>
      <td align=right id=mo><a id=mo href='<?= encode_link("listado_comprobante.php", array("sort" => "8", "up" => $up)) ?>' >Nro Comp</a></td>      	
      <td align=right id=mo><a id=mo href='<?= encode_link("listado_comprobante.php", array("sort" => "11", "up" => $up)) ?>' >Fecha Comp</a></td>      	
<? if ($cmd != "NF") { ?>
        <td align=right id=mo><a id=mo href='<?= encode_link("listado_comprobante.php", array("sort" => "9", "up" => $up)) ?>' >Nro Factura</a></td>      	
        <td align=right id=mo><a id=mo href='<?= encode_link("listado_comprobante.php", array("sort" => "10", "up" => $up)) ?>' >Per. Fact.</a></td>      	
<? } ?>
      <td align=right id=mo><a id=mo href='<?= encode_link("listado_comprobante.php", array("sort" => "1", "up" => $up)) ?>' >Apellido</a></td>      	
      <td align=right id=mo><a id=mo href='<?= encode_link("listado_comprobante.php", array("sort" => "2", "up" => $up)) ?>'>Nombre</a></td>
      <td align=right id=mo><a id=mo href='<?= encode_link("listado_comprobante.php", array("sort" => "3", "up" => $up)) ?>'>DNI</a></td>
      <td align=right id=mo><a id=mo href='<?= encode_link("listado_comprobante.php", array("sort" => "4", "up" => $up)) ?>'>Tipo Beneficiario</a></td>
      <td align=right id=mo><a id=mo href='<?= encode_link("listado_comprobante.php", array("sort" => "5", "up" => $up)) ?>'>Nombre Efector</a></td>       
      <td align=right id=mo><a id=mo href='<?= encode_link("listado_comprobante.php", array("sort" => "6", "up" => $up)) ?>'>Activo</a></td>    
      <td align=right id=mo>Clave Beneficiario</td>
      <td align=right id=mo>Total Prestaciones</td>
    </tr>
<?
while (!$result->EOF) {
  $id_tabla = "tabla_" . $result->fields['id_comprobante'];
  $onclick_check = " javascript:(this.checked)?Mostrar('$id_tabla'):Ocultar('$id_tabla')";

  //consulta para saber si tiene pretaciones el comprobante
  $sql = " select count(id_prestacion) as cant_prestaciones from facturacion.prestacion 								
			where id_comprobante=" . $result->fields['id_comprobante'];
  $cant_prestaciones = sql($sql, "no se puede traer la contidad de prestaciones") or die();
  $cant_prestaciones = $cant_prestaciones->fields['cant_prestaciones'];
  if ($result->fields['marca'] == "1")
    $color_fondo = "AA888";
  else
    $color_fondo="";
  ?>

      <tr <?= atrib_tr() ?>>
        <td>
          <input type=checkbox name=check_prestacion value="" onclick="<?= $onclick_check ?>" class="estilos_check">
        </td>     
        <td bgcolor='<?= $color_fondo ?>' ><?= $result->fields['id_comprobante'] ?></td>
        <td ><?= Fecha($result->fields['fecha_comprobante']) ?></td>
        <? if ($cmd != "NF") { ?>
          <td ><?= $func_nroFactura ? $result->fields['numero_factura'] : $result->fields['id_factura']; ?></td>
          <td ><?= $result->fields['periodo'] ?></td>
        <? } ?>
        <td ><?= $result->fields['afiapellido'] ?></td>
        <td ><?= $result->fields['afinombre'] ?></td>
        <td ><?= $result->fields['afidni'] ?></td>     
        <td ><?= $result->fields['descripcion'] ?></td> 
        <td ><?= $result->fields['nombreefector'] ?></td>             
        <td ><?= $result->fields['activo'] ?></td>       
        <td ><?= $result->fields['clavebeneficiario'] ?></td> 
        <td ><?= $cant_prestaciones ?></td> 
      </tr>    
      <tr>
        <td colspan=10>

          <?
          $sql = " select *
								from facturacion.prestacion 
								left join facturacion.nomenclador using (id_nomenclador)							
								where id_comprobante=" . $result->fields['id_comprobante'] . " order by id_prestacion DESC";
          $result_items = sql($sql) or fin_pagina();
          ?>
          <div id=<?= $id_tabla ?> style='display:none'>
            <table width=90% align=center class=bordes>
              <?
              $cantidad_items = $result_items->recordcount();
              if ($cantidad_items == 0) {
                ?>
                <tr>
                  <td colspan="10" align="center">
                    <b><font color="Red" size="+1">NO HAY PRESTACIONES PARA ESTE COMPROBANTE</font></b>
                  </td>	                                
                </tr>	                               
              <? } else { ?>
                <tr id=ma>		                               
                  <td>Cantidad</td>
                  <td>Codigo</td>
                  <td>Descripción</td>
                  <td>Precio</td>
                  <td>Total</td>	                               
                </tr>
                <? while (!$result_items->EOF) { ?>
                  <tr>
                    <td class="bordes"><?= $result_items->fields["cantidad"] ?></td>			                                 
                    <td class="bordes"><?= $result_items->fields["codigo"] ?></td>
                    <td class="bordes"><?= $result_items->fields["descripcion"] ?></td>
                    <td class="bordes"><?= number_format($result_items->fields["precio_prestacion"], 2, ',', '.') ?></td>
                    <td class="bordes"><?= number_format($result_items->fields["cantidad"] * $result_items->fields["precio_prestacion"], 2, ',', '.') ?></td>
                  </tr>
                  <?
                  $result_items->movenext();
                }//del while
              }//del else
              ?>

            </table>
          </div>

        </td>
      </tr>  	
  <? $result->MoveNext();
} ?>

  </table>
  <br>
  <table align='center' border=1 bordercolor='#000000' bgcolor='#FFFFFF' width='80%' cellspacing=0 cellpadding=0>
    <tr>
      <td colspan=10 bordercolor='#FFFFFF'><b>Colores de Referencia para la Columna Número de Comprobante:</b></td>
    <tr>
      <td width=30% bordercolor='#FFFFFF'>
        <table border=1 bordercolor='#FFFFFF' cellspacing=0 cellpadding=0 width=100%>

          <tr>        
            <td width=30 bgcolor='AA888' bordercolor='#000000' height=30>&nbsp;</td>
            <td bordercolor='#FFFFFF'>Anulado</td>
          </tr>
        </table>
      </td>
  </table>
</form>
</body>
</html>
<?
echo fin_pagina(); // aca termino ?>