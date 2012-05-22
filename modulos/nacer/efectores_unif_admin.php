<?
require_once ("../../config.php");

extract($_POST, EXTR_SKIP);
if ($parametros)
    extract($parametros, EXTR_OVERWRITE);
cargar_calendario();

if ($_POST['guardar_editar'] == "Guardar") {
    $db->StartTrans();

    $fecha_modificacion = date("Y-m-d");
    $usuario = $_ses_user['id'];
    $fecha_comp_ges_db = Fecha_db($fecha_comp_ges);
    $fecha_fin_comp_ges_db = Fecha_db($fecha_fin_comp_ges);
    $fecha_tercero_admin_db = Fecha_db($fecha_tercero_admin);
    $fecha_fin_tercero_admin_db = Fecha_db($fecha_fin_tercero_admin);

    if ($convenioselect != '-1') {
        $query = "update nacer.efe_conv set                             
             com_gestion='$com_gestion',
             com_gestion_firmante='$com_gestion_firmante',
             tercero_admin='$tercero_admin',
             tercero_admin_firmante='$tercero_admin_firmante',
             com_gestion_firmante_actual='$com_gestion_firmante_actual',
             dni_firmante_actual='$dni_firmante_actual',
             fecha_modificacion='$fecha_modificacion',
             usuario='$usuario',
             fecha_comp_ges='$fecha_comp_ges_db',
             fecha_fin_comp_ges='$fecha_fin_comp_ges_db',
             com_gestion_pago_indirecto='$com_gestion_pago_indirecto',
             fecha_tercero_admin='$fecha_tercero_admin_db',
             fecha_fin_tercero_admin='$fecha_fin_tercero_admin_db',
             id_nomenclador_detalle='$nomenclador_detalle',
             id_zona_sani='$id_zona_sani'
             
             where id_efe_conv=$convenioselect";
    } else {
        $query = "insert into nacer.efe_conv (com_gestion,com_gestion_firmante,tercero_admin,tercero_admin_firmante,com_gestion_firmante_actual,dni_firmante_actual,
        fecha_modificacion,cuie,usuario,fecha_comp_ges,fecha_fin_comp_ges,com_gestion_pago_indirecto,fecha_tercero_admin,
        fecha_fin_tercero_admin,id_nomenclador_detalle,id_zona_sani) ";
        $query.="values ('$com_gestion',$com_gestion_firmante,$tercero_admin,$tercero_admin_firmante,
        $com_gestion_firmante_actual,$dni_firmante_actual,'$fecha_modificacion','$cuie','$usuario','$fecha_comp_ges_db','$fecha_fin_comp_ges_db','$com_gestion_pago_indirecto',
        '$fecha_tercero_admin_db','$fecha_fin_tercero_admin_db',$nomenclador_detalle,$id_zona_sani)";
    }

    /* $query = "update nacer.efe_conv set    
      domicilio='$domicilio',
      cod_pos='$cod_pos',
      cuidad='$cuidad',
      referente='$referente',
      tel='$tel',
      com_gestion='$com_gestion',
      com_gestion_firmante='$com_gestion_firmante',
      tercero_admin='$tercero_admin',
      tercero_admin_firmante='$tercero_admin_firmante',
      com_gestion_firmante_actual='$com_gestion_firmante_actual',
      dni_firmante_actual='$dni_firmante_actual',
      fecha_modificacion='$fecha_modificacion',
      usuario='$usuario',
      fecha_comp_ges='$fecha_comp_ges_db',
      fecha_fin_comp_ges='$fecha_fin_comp_ges_db',
      com_gestion_pago_indirecto='$com_gestion_pago_indirecto',
      fecha_tercero_admin='$fecha_tercero_admin_db',
      fecha_fin_tercero_admin='$fecha_fin_tercero_admin_db',
      n_2008='$n_2008',
      n_2009='$n_2009',
      id_nomenclador_detalle='$nomenclador_detalle',
      id_zona_sani='$id_zona_sani'

      where id_efe_conv=$id_efe_conv"; */

    sql($query, "Error al insertar/actualizar el efector") or fin_pagina();

    $db->CompleteTrans();
    $fechacompromiso = $fecha_comp_ges_db . " - " . $fecha_fin_comp_ges_db;

    $accion = "Se Grabo el Convenio: $fechacompromiso.";

    /* $para = '';
      $paracc = '';
      $parabcc = '';
      $asunto = 'MODIFICACION EFECTOR';
      $contenido = "El Efector: $nombre. CUIE: $cuie fue Modificado por el Usuario: $usuario. Por favor revisar y actualizar en la tabla de Efectores";
      enviar_mail($para, $paracc, $parabcc, $asunto, $contenido, '', '');

      $accion = "Los datos se actualizaron se Envio mail"; */
}

$desabilefe = 'disabled';

if ($_POST['editar']) {
    $desabil = '';
    $desabiledit = 'disabled';
} else {
    $desabil = 'disabled';
    $desabiledit = '';
}

if ($_POST['cancelar_editar']) {
    $desabil = 'disabled';
    $desabiledit = '';
}


if ($_POST["convenioselect"]) {
    $unsql = "select * from nacer.efe_conv where id_efe_conv='$convenioselect'";
    $res_conv = sql($unsql, "Error al traer el Convenio") or fin_pagina();

    $com_gestion = $res_conv->fields['com_gestion'];
    $com_gestion_firmante = $res_conv->fields['com_gestion_firmante'];
    $fecha_comp_ges = $res_conv->fields['fecha_comp_ges'];
    $fecha_fin_comp_ges = $res_conv->fields['fecha_fin_comp_ges'];
    $com_gestion_pago_indirecto = $res_conv->fields['com_gestion_pago_indirecto'];
    $tercero_admin = $res_conv->fields['tercero_admin'];
    $tercero_admin_firmante = $res_conv->fields['tercero_admin_firmante'];
    $fecha_tercero_admin = $res_conv->fields['fecha_tercero_admin'];
    $fecha_fin_tercero_admin = $res_conv->fields['fecha_fin_tercero_admin'];
    $com_gestion_firmante_actual = $res_conv->fields['com_gestion_firmante_actual'];
    $dni_firmante_actual = $res_conv->fields['dni_firmante_actual'];
    $n_2008 = $res_conv->fields['n_2008'];
    $n_2009 = $res_conv->fields['n_2009'];
    $id_nomenclador_detalle = $res_conv->fields['id_nomenclador_detalle'];
    $id_zona_sani = $res_conv->fields['id_zona_sani'];
} else {
    $desabil = '';
    $desabiledit = 'disabled';
}

if ($cuie) {
    $query = "SELECT 
  uad.localidades.nombre nlocalidad,
  uad.departamentos.nombre ndepartamento,
  e.nombreefector nefector,
  e.tel,
  e.referente,
  e.cod_pos codpos,
  e.ciudad ciudad,
  e.domicilio domicilio

FROM
  facturacion.smiefectores e inner join uad.departamentos on departamento=id_departamento inner join uad.localidades on cod_pos=codigopostal 
  where e.cuie='$cuie'";

    $res_factura = sql($query, "Error al traer el Efector") or fin_pagina();

    $nombre = $res_factura->fields['nefector'];
    $domicilio = $res_factura->fields['domicilio'];
    $departamento = $res_factura->fields['ndepartamento'];
    $localidad = $res_factura->fields['nlocalidad'];
    $cod_pos = $res_factura->fields['codpos'];
    $cuidad = $res_factura->fields['ciudad'];
    $referente = $res_factura->fields['referente'];
    $tel = $res_factura->fields['tel'];
}


echo $html_header;
?>
<script>

    function control_nuevos()
    { 	 
        if(document.all.fecha_comp_ges.value==""){
            alert('Debe Ingresar una Fecha Compromiso de Gestion');
            return false;
        }
        if(document.all.fecha_fin_comp_ges.value==""){
            alert('Debe Ingresar una Fecha Fin Compromiso de Gestion');
            return false;
        }
        if(document.all.fecha_tercero_admin.value==""){
            alert('Debe Ingresar una Fecha Tercero Administrador');
            return false;
        }
        if(document.all.fecha_fin_tercero_admin.value==""){
            alert('Debe Ingresar una Fecha Fin Tercero Administrador');
            return false;
        }
        if(document.all.id_zona_sani.value=="-1"){
            alert('Debe Seleccionar una zona Sanitaria (Sino figura ninguna agregar en la tabla nacer.zona_sani)');
            return false;
        } 
        return true;
    }

    function editar_campos()
    {
        /*document.all.domicilio.readOnly=false;
        document.all.cod_pos.readOnly=false;
        document.all.cuidad.readOnly=false;
        document.all.referente.readOnly=false;
        document.all.tel.readOnly=false;
        document.all.com_gestion_firmante.readOnly=false;
        document.all.tercero_admin.readOnly=false;
        document.all.tercero_admin_firmante.readOnly=false;
        document.all.com_gestion_firmante_actual.readOnly=false;
        document.all.dni_firmante_actual.readOnly=false;
        document.all.com_gestion.disabled=false;
        document.all.com_gestion_pago_indirecto.disabled=false;
        document.all.nomenclador_detalle.disabled=false;
        document.all.id_zona_sani.disabled=false;
		
        document.all.cancelar_editar.disabled=false;
        document.all.guardar_editar.disabled=false;
        document.all.editar.disabled=true;*/
        //return true;
    }//de function control_nuevos()


</script>

<form name='form1' action='efectores_unif_admin.php' method='POST'>
    <input type="hidden" value="<?= $id_efe_conv ?>" name="id_efe_conv">
    <input type="hidden" value="<?= $cuie ?>" name="cuie">
    <? echo "<center><b><font size='+1' color='red'>$accion</font></b></center>"; ?>
    <table width="85%" cellspacing=0 border=1 bordercolor=#E0E0E0 align="center" bgcolor='<?= $bgcolor_out ?>' class="bordes">
        <tr id="mo">
            <td>
                <font size=+1><b>Efector</b></font>        
            </td>
        </tr>
        <tr><td>
                <table width=90% align="center" class="bordes">
                    <tr>
                        <td id=mo colspan="2">
                            <b> Descripción del Efector</b>
                        </td>
                    </tr>
                    <tr>	           
                        <td align="center" colspan="2">
                            <b> CUIE: <font size="+1" color="Red"><?= $cuie ?></font> </b>
                        </td>
                    </tr>
                    <tr>	           
                        <td align="center" colspan="2" style="padding-bottom: 10px ">
                            <b><font size="2" color="Red">Nota: Los valores numericos se ingresan SIN separadores de miles, y con "." como separador DECIMAL</font> </b>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table width=90% align="center" class="bordes" style="padding-right: 50px">

                                <tr>
                                    <td align="right">
                                        <b>Nombre:</b>
                                        <input type="text" size="40" value="<?= $nombre ?>" name="nombre" <?= $desabilefe ?>/>
                                    </td> 
                                    <td align="right">
                                        <b>Referente:</b>
                                        <input type="text" size="40" value="<?= $referente ?>" name="referente" <?= $desabilefe ?>/>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right">
                                        <b>Departamento:</b>
                                        <input type="text" size="40" value="<?= $departamento ?>" name="departamento" <?= $desabilefe ?>/>
                                    </td>
                                    <td align="right">
                                        <b>Localidad:</b>
                                        <input type="text" size="40" value="<?= $localidad ?>" name="localidad" <?= $desabilefe ?>/>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right">
                                        <b>Cuidad:</b>
                                        <input type="text" size="40" value="<?= $cuidad ?>" name="cuidad" <?= $desabilefe ?>/>
                                    </td>
                                    <td align="right">
                                        <b>Domicilio:</b>
                                        <input type="text" size="40" value="<?= $domicilio ?>" name="domicilio" <?= $desabilefe ?>/>
                                    </td>  
                                </tr>                                                               
                                <tr>
                                    <td align="right">
                                        <b>Telefono:</b>
                                        <input type="text" size="40" value="<?= $tel ?>" name="tel" <?= $desabilefe ?>/>
                                    </td>
                                    <td align="right">
                                        <b>C.P.:</b>                                                                 
                                        <input type="text" size="40" value="<?= $cod_pos ?>" name="cod_pos" <?= $desabilefe ?>/>
                                    </td> 
                                </tr>

                                <!--tr>
                                    <td align="right">
                                        <b>Mail:</b>
                                    </td>
                                    < ?
                                    $sql = "select * from nacer.mail_efe_conv where cuie = '$cuie'";
                                    $result_mail = sql($sql, 'Error');
                                    $result_mail->movefirst();
                                    $contenido_mail = '';
                                    while (!$result_mail->EOF) {
                                        $contenido_mail.=$result_mail->fields['descripcion'] . ': ' . $result_mail->fields['mail'] . '-';
                                        $result_mail->MoveNext();
                                    }
                                    ?>  
                                    <td align="left">			  		 
                                        <input type="text" size="40" value="< ?= $contenido_mail ?>" name="mail" readonly>
                                        < ? $ref = encode_link("administra_mail.php", array("cuie" => $cuie, "nombre" => $nombre)); ?>
                                        <input type="button" name="mail" value="Mail"  onclick="window.open('<?= $ref ?>')">            
                                    </td>
                                </tr-->
                            </table>

                            <table width=90% align="center" class="bordes"  style="padding-top: 20px">
                                <tr>                        
                                    <td id="mo" colspan="2" >		          			
                                        <b>Datos del Convenio</b>&nbsp;&nbsp; 
                                        
                                            <select name="convenioselect" onChange="this.form.submit()" >
                                                <option value="-1">Nuevo Convenio</option>
                                                <?
                                                $sql2 = "select * from nacer.efe_conv inner join facturacion.smiefectores using (cuie) where cuie ='$cuie' order by nombreefector";
                                                $res_efectores2 = sql($sql2) or fin_pagina();
                                                while (!$res_efectores2->EOF) {
                                                    $id_efe_conv = $res_efectores2->fields['id_efe_conv'];
                                                    $fechacompromiso = $res_efectores2->fields['fecha_comp_ges'] . " - " . $res_efectores2->fields['fecha_fin_comp_ges'];
                                                    ?>
                                                    <option value='<?= $id_efe_conv ?>'<? if ($id_efe_conv == $convenioselect)
                                                    echo "selected" ?>><?= $fechacompromiso ?></option>
                                                    <?
                                                    $res_efectores2->movenext();
                                                 }
                                                        ?>                                                
                                            </select>
                                        
                                    </td>
                                </tr>

                                <tr>

                                    <td align="right" style="padding-top: 10px">
                                        <b>Nomenclador en Uso:</b>
                                    </td>
                                    <td align="left" style="padding-top: 10px">		          			
                                        <select name=nomenclador_detalle Style="width:257px" <?= $desabil ?>>
                                            <option value=-></option>
                                            <?
                                            $sql = "select * from facturacion.nomenclador_detalle";
                                            $res = sql($sql) or fin_pagina();
                                            while (!$res->EOF) {
                                                $id_nomenclador_detalle_1 = $res->fields['id_nomenclador_detalle'];
                                                $descripcion = $res->fields['descripcion'];
                                                ?>
                                                <option value=<?=
                                            $id_nomenclador_detalle_1;
                                            if ($id_nomenclador_detalle == $id_nomenclador_detalle_1)
                                                echo " selected"
                                                    ?> >
                                                <?= $descripcion ?>
                                                </option>
                                                <?
                                                $res->movenext();
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>

                                <tr>

                                <tr>
                                    <td align="right">
                                        <b>Compromiso de Gestion:</b>
                                    </td>
                                    <td align="left">
                                        <select name=com_gestion Style="width:257px" <?= $desabil ?>>
                                            <option value=-></option>
                                            <option value=VERDADERO <? if (trim($com_gestion) == 'VERDADERO')
                                                echo "selected" ?>>VERDADERO</option>
                                            <option value=FALSO <? if (trim($com_gestion) == 'FALSO')
                                                    echo "selected" ?>>FALSO</option>			  
                                        </select>              
                                    </td>
                                </tr>       

                                <tr>
                                    <td align="right">
                                <u><b>Referente con Addenda:</b><u>
                                        </td>
                                        <td align="left">		 
                                            <input type="text" size="40" value="<?= $com_gestion_firmante_actual ?>" name="com_gestion_firmante_actual" <?= $desabil ?>>
                                        </td>
                                        </tr>

                                        <tr>
                                            <td align="right">
                                        <u><b>DNI Referente con Addenda:</b><u>
                                                </td>
                                                <td align="left">		 
                                                    <input type="text" size="40" value="<?= $dni_firmante_actual ?>" name="dni_firmante_actual" <?= $desabil ?>>
                                                </td>
                                                </tr>

                                                <tr>
                                                    <td align="right">
                                                        <b>Compromiso de Gestion Firmante:</b>
                                                    </td>
                                                    <td align="left">		 
                                                        <input type="text" size="40" value="<?= $com_gestion_firmante ?>" name="com_gestion_firmante" <?= $desabil ?>>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td align="right">
                                                        <b>Fecha del Compromiso de Gestion:</b>
                                                    </td>
                                                    <td align="left">		 
                                                        <input id="fecha_comp_ges" type="text" size="35" value="<?= fecha($fecha_comp_ges) ?>" name="fecha_comp_ges" <?= $desabil ?>>
<?= link_calendario("fecha_comp_ges"); ?>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td align="right">
                                                        <b>Fecha Fin del Compromiso de Gestion:</b>
                                                    </td>
                                                    <td align="left">		 
                                                        <input id="fecha_fin_comp_ges" type="text" size="35" value="<?= fecha($fecha_fin_comp_ges) ?>" name="fecha_fin_comp_ges" <?= $desabil ?>>
<?= link_calendario("fecha_fin_comp_ges"); ?>
                                                    </td>
                                                </tr>

                                                <tr>

                                                <tr>
                                                    <td align="right">
                                                        <b>Compromiso de Gestion Pago Indirecto:</b>
                                                    </td>
                                                    <td align="left">
                                                        <select name=com_gestion_pago_indirecto Style="width:257px" <?= $desabil ?>>
                                                            <option value=-></option>
                                                            <option value=VERDADERO <? if (trim($com_gestion_pago_indirecto) == 'VERDADERO')
    echo "selected" ?>>VERDADERO</option>
                                                            <option value=FALSO <? if (trim($com_gestion_pago_indirecto) == 'FALSO')
                                                                    echo "selected" ?>>FALSO</option>			  
                                                        </select>              
                                                    </td>
                                                </tr>  

                                                <tr>
                                                    <td align="right">
                                                        <b>Tercero Administrador:</b>
                                                    </td>
                                                    <td align="left">		 
                                                        <input type="text" size="40" value="<?= $tercero_admin ?>" name="tercero_admin" <?= $desabil ?>>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td align="right">
                                                        <b>Tercero Administrador Firmante:</b>
                                                    </td>
                                                    <td align="left">		 
                                                        <input type="text" size="40" value="<?= $tercero_admin_firmante ?>" name="tercero_admin_firmante" <?= $desabil ?>>
                                                    </td>
                                                </tr>

                                                <tr>

                                                    <td align="right">
                                                        <b>Fecha Tercero Administrador:</b>
                                                    </td>
                                                    <td align="left">		 
                                                        <input id="fecha_tercero_admin" type="text" size="35" value="<?= fecha($fecha_tercero_admin) ?>" name="fecha_tercero_admin" <?= $desabil ?>>
<?= link_calendario("fecha_tercero_admin"); ?>
                                                    </td>
                                                </tr>   

                                                <td align="right">
                                                    <b>Fecha Fin Tercero Administrador:</b>
                                                </td>
                                                <td align="left">		 
                                                    <input id="fecha_fin_tercero_admin" type="text" size="35" value="<?= fecha($fecha_fin_tercero_admin) ?>" name="fecha_fin_tercero_admin" <?= $desabil ?>>
<?= link_calendario("fecha_fin_tercero_admin"); ?>
                                                </td>
                                                </tr> 


                                                <tr>

                                                    <td align="right">
                                                        <b>Zona Sanitaria:</b>
                                                    </td>
                                                    <td align="left">		          			
                                                        <select name=id_zona_sani Style="width:257px" <?= $desabil ?>>
                                                            <option value=-></option>
                                                            <?
                                                            $sql = "select * from nacer.zona_sani";
                                                            $res = sql($sql) or fin_pagina();
                                                            while (!$res->EOF) {
                                                                $id_nomenclador_detalle_1 = $res->fields['id_zona_sani'];
                                                                $descripcion = $res->fields['nombre_zona'];
                                                                ?>                                                            
                                                                <option value=<?=
                                                            $id_nomenclador_detalle_1;
                                                            if ($id_zona_sani == $id_nomenclador_detalle_1)
                                                                echo " selected"
                                                                ?> >
                                                                <?= $descripcion ?>
                                                                </option>
                                                                <?
                                                                $res->movenext();
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                </tr>

                                                </table>
                                                </td>      
                                                </tr> 

                                                </table>           
                                                <br>
<? if ($id_efe_conv) { ?>
                                                    <table class="bordes" align="center" width="100%">
                                                        <tr align="center" id="sub_tabla">
                                                            <td>	
                                                                Editar DATO
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <td align="center">		      
                                                                <input type="submit" name="editar" value="Editar Campos" onClick="this.form.submit()" title="Editar" style="width:130px" <?= $desabiledit ?>/> &nbsp;&nbsp;
                                                                <input type="submit" name="guardar_editar" value="Guardar" onclick="control_nuevos()" title="Guarda Muleto" <?= $desabil ?> style="width:130px" />&nbsp;&nbsp;
                                                                <input type="submit" name="cancelar_editar" value="Cancelar" title="Cancela Edicion de Muletos" <?= $desabil ?> style="width:130px" onclick=""/>		      		      
                                                            </td>
                                                        </tr> 
                                                    </table>	
                                                    <br>
<? } ?>
                                                <tr><td><table width=100% align="center" class="bordes">
                                                            <tr align="center">
                                                                <td>
                                                                    <input type=button name="volver" value="Volver" onclick="document.location='efectores_unif.php'"title="Volver al Listado" style="width:150px">     
                                                                </td>
                                                            </tr>
                                                        </table></td></tr>


                                                </table>
                                                </form>

<?=
fin_pagina(); // aca termino ?>