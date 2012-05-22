<?php
//ob_start();
try {
  require_once ("../../config.php");
  require_once ("../../lib/funciones_estimulos.php");
  sql("BEGIN");

  $id_factura = $parametros["id_factura"];
  //buscar expediente, efector, mes y año
  $sql = "SELECT * FROM facturacion.factura ff INNER JOIN
  facturacion.recepcion fr ON(ff.recepcion_id = fr.idrecepcion)
  WHERE id_factura = $id_factura";
  $r_sql = sql($sql) or excepcion("Error al buscar expediente");
  if ($r_sql->recordCount() > 0) {
    $cuie = $r_sql->fields['cuie'];
    $periodo = explode('/', $r_sql->fields['periodo']);
    $ano = $periodo[0];
    $mes = $periodo[1];
    $cod_org = $r_sql->fields['cod_org'];
    $no_correlativo = $r_sql->fields['no_correlativo'];
    $ano_exp = $r_sql->fields['ano_exp'];
    $cuerpo = $r_sql->fields['cuerpo'];
  } else {
    excepcion("Error al buscar expediente");
  }
?>
  <center><u><H2>Estimulos</H2></u></center>
  <center><font size="3"><b>Expediente nº:&nbsp;</b>
<?php echo $cod_org . '-' . $no_correlativo . '-' . $ano_exp . '-' . $cuerpo; ?>
    </font></center><br /><br />
    <?php
    $sql = "SELECT ff.id_factura, ff.monto_prefactura FROM facturacion.factura ff INNER JOIN
  facturacion.recepcion fr ON(ff.recepcion_id = fr.idrecepcion)
  WHERE ff.cuie = '$cuie' AND fr.cod_org = $cod_org
  AND fr.no_correlativo = $no_correlativo AND fr.ano_exp = $ano_exp
  AND fr.cuerpo = $cuerpo AND ff.estado = 'A'";
    $r_sql = sql($sql) or excepcion("Error al buscar expediente");
    if ($r_sql->recordCount() > 0) {
      excepcion("No se puede calcular los estímulos debido a que existen facturas abiertas.");
    }
    //sumar el monto de todas las facturas con ese expediente y de ese efector
    $sql = "SELECT ff.id_factura, ff.monto_prefactura FROM facturacion.factura ff INNER JOIN
  facturacion.recepcion fr ON(ff.recepcion_id = fr.idrecepcion)
  WHERE ff.cuie = '$cuie' AND fr.cod_org = $cod_org
  AND fr.no_correlativo = $no_correlativo AND fr.ano_exp = $ano_exp
  AND fr.cuerpo = $cuerpo";
    $r_sql = sql($sql) or excepcion("Error al buscar expediente");
    if ($r_sql->recordCount() > 0) {
      $r_sql->moveFirst();
      $total = 0;
      $debito = 0;

      while (!$r_sql->EOF) {
        $total += $r_sql->fields['monto_prefactura'];
        $query = "SELECT sum(cantidad*monto) as total FROM facturacion.debito where id_factura=" . $r_sql->fields['id_factura'];
        $d_sql = sql($query);
        $debito += $d_sql->fields['total'];
        $r_sql->moveNext();
      }
      $monto = $total - $debito;
    } else {
      excepcion("Error al buscar expediente");
    }


    /* if (isset($_GET['mes']) && isset($_GET['ano']) && isset($_GET['cuie'])) {
      $mes = $_GET['mes'];
      $ano = $_GET['ano'];
      $cuie = $_GET['cuie'];
      } else {
      //exit;
      }
      $mes = '01';
      $ano = '2010';
      $cuie = 'N05441'; */
    calcular_estimulos_2010($ano, $mes, $cuie);
    mostrar_estimulos($ano, $mes, $cuie, $monto);
    sql("COMMIT");
  } catch (exception $e) {
    sql("ROLLBACK", "Error en rollback", 0);
    echo "Error: " . $e->getMessage() . "<br /><br /><br />";
  }
  fin_pagina();
    ?>