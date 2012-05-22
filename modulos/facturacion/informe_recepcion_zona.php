<?php
//ob_start();
require_once ("../../config.php");
require_once ("../../lib/funciones_misiones.php");
?>

 




<script language="JavaScript">
  function cualzona(f)
  {
    var zon;
    //zon=document.forms.f_inmunizacion.zona.value;
    zon=f.zona.value;
    location.href="informe_recepcion_zona.php?zona="+zon;
  }
</script>

<form action="informe_recepcion_zona.php" method="post" name="f_inmunizacion" >

  <table style="text-align:center;width:100%;padding:10px;" ><tr>
      <td><b>Zona:</b>&nbsp;


        <?php
        $SLef = "SELECT idzona, nombre FROM uad.zonas"; // WHERE idzona = $zona";
        $buss = sql($SLef, "Error en zona") or excepcion("Error en zona");
        ?>
        <SELECT NAME="zona" style="font-size:13px;" onChange="cualzona(this.form)" >
          <OPTION VALUE="0" <?
        if ($_GET['zona'] == 0) {
          echo'selected';
        }
        ?>>Todas</OPTION>  
                  <?php
                  if ($buss->RecordCount() > 0) {
                    $buss->MoveFirst();
                    while (!$buss->EOF) {
                      if ($_GET['zona'] == $buss->fields["idzona"]) {
                        $selected = 'selected';
                      } if ($_GET['zona'] != $buss->fields["idzona"]) {
                        $selected = '';
                      }
                      $nombre = $buss->fields["nombre"];
                      ?>
              <OPTION VALUE="<?php echo $buss->fields["idzona"]; ?>"
                      <?php echo $selected; ?> >
                        <?php echo $nombre; ?>
              </OPTION>
              <?php
              $buss->MoveNext();
            }
          }
          ?>
          <table style="text-align:center;width:100%;padding:10px;" ><tr>
              <td><b>Municipio:</b>&nbsp;    

                <?php
                if ($_GET['zona'] != 0) {
                  $consultamuni = ' where id_zona=' . $zona;
                }
                $SLef = "SELECT idmuni_provincial, nombre FROM uad.municipios $consultamuni";
                $buss = sql($SLef, "Error en municipio") or excepcion("Error en municipio");
                ?>  

                <SELECT NAME="municipio" style="font-size:13" >
                  <Option Value="0" <? if ($_GET['idmunicipio'] == 0)
                  echo'selected'; ?>>- - - - - - - - - - - - - - - - - </option>

                  <?php
                  if ($buss->RecordCount() > 0) {
                    $buss->MoveFirst();
                    while (!$buss->EOF) {
                      if ($_GET['idmunicipio'] == $buss->fields["idmuni_provincial"]) {
                        $selected = 'selected';
                      } if ($_GET['idmunicipio'] != $buss->fields["idmuni_provincial"]) {
                        $selected = '';
                      }

                      $nombre = $buss->fields["nombre"];
                      ?>


                      <OPTION VALUE="<?php echo $buss->fields["idmuni_provincial"]; ?>"
                              <?php echo $selected; ?> >
                                <?php echo $nombre; ?>
                      </OPTION>



                      <?php
                      $buss->MoveNext();
                    }
                  }
                  ?>
                </select>
              </td>
            </tr>
            <tr>
            <table style="text-align:center;width:80%;padding:10px;" ><tr>
                <td><b>Efector:</b>&nbsp; 

                  <?php
                  ///////////////// ////////////efector//////////////////////////////
                  if ($_GET['zona'] != 0) {
                    $consulefe = ' where id_zona_sani=' . $_GET['zona'];
                  }
                  $SLe = "SELECT id_efe_conv,  nombre FROM nacer.efe_conv $consulefe";
                  $bus = sql($SLe, "Error en efector") or excepcion("Error en efector");
                  ?>  

                  <SELECT NAME="efector" style="font-size:13" >
                    <Option Value="0" <? if ($_GET['efector'] == 0)
                    echo'selected'; ?>>- - - - - - - - - - - -  </option>

                    <?php
                    if ($bus->RecordCount() > 0) {
                      $bus->MoveFirst();
                      while (!$bus->EOF) {
                        if ($_GET['efector'] == $bus->fields["id_efe_conv"]) {
                          $selected = 'selected';
                        } if ($_GET['efector'] != $bus->fields["id_efe_conv"]) {
                          $selected = '';
                        }

                        $nombre = $bus->fields["nombre"];
                        ?>


                        <OPTION VALUE="<?php echo $bus->fields["id_efe_conv"]; ?>"
                                <?php echo $selected; ?> >
                                  <?php echo $nombre; ?>
                        </OPTION>     

                        <?php
                        $bus->MoveNext();
                      }
                    }
                    ?>
                  </select>
                </td>
              </tr>
              <tr>




                <td>Desde: <input type="text" name="fecha_desde" id="fecha_entrada" size="10" maxlength="10" onKeyUp="mascara(this,'/',patron,true);" onblur="esFechaValida(this);"/>&nbsp;&nbsp;&nbsp;<?=
                    link_calendario('fecha_desde');
                    ?>--- Hasta: <input type="text" name="fecha_hasta" id="fecha_entrada" size="10" maxlength="10" onKeyUp="mascara(this,'/',patron,true);" onblur="esFechaValida(this);"/>&nbsp;&nbsp;&nbsp;<?=
                  link_calendario('fecha_hasta');
                    ?></td>
              </tr>
              <tr>
                <td><input type="submit" name="enviar" value="Enviar" /></td>
              </tr>
            </table>


            </form>


            <?php if ($_POST['enviar'] == "Enviar") { ?>

              <?php //print_r($_POST); ?>

              <table style="text-align:center;width:100%;padding:10px;" ><tr>
                  <td>&nbsp;


                    <?php
                    $idmunicipio = $_POST['municipio'];
                    $idzona = $_POST['zona'];
                    $fechadesde = $_POST ['fecha_desde'];
                    $fechahasta = $_POST ['fecha_hasta'];
                    $idefe = $_POST ['efector'];

                    if ($idzona == 0) {
                      $consulta = 'true'; //nada
                    } else {
                      if ($idmunicipio == 0) {
                        $consulta = "id_zona_sani= $idzona"; //solo zona
                      } else {
                        $consulta = "municipio= $idmunicipio"; //municipio
                      }
                    }

                    ////////////////si se selecciona solo municipio////////////////////////

                    if ($idmunicipio != 0) {
                      $consulta = "municipio= $idmunicipio";
                    } else {
                      if ($idzona == 0) {
                        $consulta = 'true';
                      } else {
                        $consulta = "id_zona_sani= $idzona";
                      }
                    }


////////////////fecha desde ---- fecha hasta///////////////////////
                    if ($fechadesde != '') {
                      $fechadesde = Fecha_db($fechadesde);
                      $consulta.= " and fechavacunacion >= '$fechadesde'";
                    }

                    if ($fechahasta != '') {
                      $fechahasta = Fecha_db($fechahasta);
                      $consulta.= " and fechavacunacion <= '$fechahasta'";
                    }

                    if ($idefe != 0) {
                      $consulta.= " and id_efe_conv = $idefe ";
                      
                    }

////// ///////////////////fin municipio////////////////////////////

                    if ($idzona != 0) {
                      $SQLef = "SELECT nombre FROM uad.zonas where idzona=$idzona";
                      $e_busq = sql($SQLef, "Error en nombre de archivo") or excepcion("Error en nombre de archivo");

                      if ($e_busq->RecordCount() > 0) {
                        $zona = $e_busq->fields["idzona"];
                        $nombre = $e_busq->fields["nombre"];
                      } else {
                        echo 'No se encuentra archivo!';
                      }
                    } else {
                      $nombre = 'Todas';
                    }

                    echo '<b>Segun rendición de Zona: </b>' . $nombre . ' - ';

                    /* ++++++++++++++++++++++++++++ busqueda de zona, muestra municipio ++++++++++++++++++++++++++++++++++ */
                    if ($idmunicipio != 0) {

                      $Slf = "SELECT nombre FROM uad.municipios 
                    where idmuni_provincial=$idmunicipio";
                    
                      $e_busqda = sql($Slf, "Error en municipio") or excepcion("Error en municipio");
                      if ($e_busqda->RecordCount() > 0) {
                        $nombre = $e_busqda->fields["nombre"];
                      } else {
                        echo 'No se encuentra municipio';
                      }
                    } else {
                      $nombre = 'Todos';
                    }

                    echo '<b>Segun rendición de Municipio: </b>' . $nombre . ' - ';
                    /* ++++++++++++++++++++++++++++ fin busqueda++++++++++++++++++++++++++++++++++ */

                    ////////////////////select efector///////////////////////////////////////////
                    if ($idefe != 0) {

                      $Sll = "SELECT  id_efe_conv, nombre FROM nacer.efe_conv 
                    where id_efe_conv=$idefe";
                    
                      $e_busq = sql($Sll, "Error en efector") or excepcion("Error en efector");
                      if ($e_busq->RecordCount() > 0) {
                        $nombre = $e_busq->fields["nombre"];
                      } else {
                        echo 'No se encuentra efector';
                      }
                    } else {
                      $nombre = 'Todos';
                    }

                    echo '<b>Segun rendición de Efector: </b>' . $nombre . ' - ';
/////////////////////////////fin select//////////////////////////////

////////////////fecha//////////////////////////////////////////////////////

                    $consu = "SELECT cast(min(fechavacunacion) as varchar) as t1, max(fechavacunacion) as t2
                FROM inmunizacion.benefinmunizacion a 
                  inner join uad.municipios b on a.municipio= b.idmuni_provincial 
                  inner join  nacer.efe_conv c on a.cuie= c.cuie where $consulta";
                  $busdd = sql($consu, "Error fecha elegida") or excepcion("Error en fecha elegida");

                    if ($busdd->RecordCount() > 0) {
                      echo '<center><H3><u><b>Desde: ' . Fecha($busdd->fields["t1"]) . ' Hasta: ' . Fecha($busdd->fields["t2"]) . '</b></u></H3></center>';
                    } else {
                      echo '<center><H3><u><b>No hay resultados para los parametros elegidos</b></u></H3></center>';
                    }
                    ?>
                  </td>
                </tr>
              </table>
              <?php
              /* +++++++++++++++muestra vacuna, grupo y cantidad+++++++++++++++++++++++++++++++++++++ */
              $consu227 = "SELECT a.idvacuna,vacuna FROM inmunizacion.benefinmunizacion a 
inner join inmunizacion.vacunas b on a.idvacuna=b.idvacuna 
           inner join uad.municipios c on a.municipio=idmuni_provincial 
           inner join  nacer.efe_conv d on a.cuie= d.cuie  where  $consulta group by a.idvacuna,vacuna order by a.idvacuna";

              $busc = sql($consu227, "Error fecha 1 ") or excepcion("Error en fecha ");

              if ($busc->RecordCount() > 0) {
                $busc->moveFirst();
                while (!$busc->EOF) {

                  $vacuna = $busc->fields["idvacuna"];
                  $nombre_vacuna = $busc->fields["vacuna"];

                  echo'<table align="center"  cellpadding="0" cellspacing="0" border="0" width="50%">
 <tr ><td><b>' . $nombre_vacuna . '</b></td> </tr>
	</table>';
                  echo '<table align="center"  cellpadding="0" cellspacing="0" border="1" width="40%">';

                  $consu = "SELECT count(*) as cantidad,a.idrangovacuna,a.idvacuna 
        FROM inmunizacion.benefinmunizacion a
	inner join inmunizacion.vacunas b on a.idvacuna=b.idvacuna
        inner join uad.municipios c on a.municipio=idmuni_provincial
       inner join  nacer.efe_conv d on a.cuie= d.cuie 
	where  ( $consulta ) and a.idvacuna=$vacuna		
	group by a.idrangovacuna,a.idvacuna";

                  $bus = sql($consu, "Error fecha 2 ") or excepcion("Error en fecha ");
                  if ($bus->RecordCount() > 0) {
                    $bus->moveFirst();
                    while (!$bus->EOF) {

                      $cantidad = $bus->fields["cantidad"];
                      $idrangovacuna = $bus->fields["idrangovacuna"];
                      $consu2 = "SELECT descripcion FROM inmunizacion.vacunasrangos where idrangosvacunas ='$idrangovacuna'";
                      $bus2 = sql($consu2, "Error en vacuna") or excepcion("Error en vacuna");

                      echo '<tr align="center" width="80%"><td>' . $bus2->fields["descripcion"] . '</td><td width="20%">' . $cantidad . '</td></tr>';
                      $bus->moveNext();
                    }
                  }
                  echo "</table>";
                  $busc->moveNext();
                }
              }
            }
            fin_pagina();
            ?>