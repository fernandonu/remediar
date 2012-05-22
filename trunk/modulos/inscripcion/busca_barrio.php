<?php
 require_once ("../../config.php");
 extract($_POST,EXTR_SKIP);
 if ($parametros) extract($parametros,EXTR_OVERWRITE);
 echo $html_header;
?>
<script>
function iSubmitEnter(oEvento, oFormulario){
     var iAscii;

     if (oEvento.keyCode)
         iAscii = oEvento.keyCode;
     else if (oEvento.which)
         iAscii = oEvento.which;
     else
         return false;

     /*if (iAscii == 13)*/ oFormulario.submit();

     return true;
}
</script>
<?echo "<center><b><font size='+1' color='red'>$accion</font></b></center>"; ?>
<FORM METHOD="get" ACTION="" name="form1" id="form1">
<font size=2><b>Ingrese Nombre, codigo o palabra clave que identifique al barrio y presiones enter</b> </font>
 &nbsp; <input type="text" style="font-size:9" name="efectores" size="20"   maxlength="40" onkeyup="iSubmitEnter(event, document.form1)">
 <input type="hidden" value="<?=$qkmpo?>" name="qkmpo" />
 <input type="hidden" value="<?=$id_planilla?>" name="id_planilla" />
 <input type="hidden" value="<?=$muni?>" name="muni" />
</FORM>
<script>
document.getElementById('efectores').focus();
if(document.all.muni.value=="-1"){
alert('Antes debe seleccionar 1 municipio');
window.close();
}
</script>
<?
if ($_GET['efectores']){
$muni= $_GET['muni'];
$efectores= $_GET['efectores'];
$id_planilla= $_GET['id_planilla'];
	if(is_numeric($muni)==1){
		$sql_where="b.idmuni_provincial=$muni and";
	}else{
		$sql_where="b.nombre='$muni' and";
	}
	/*if($id_planilla){
		$q="select idmuni_provincial from uad.municipios  
			where nombre='".$_GET['municipio']."'";
			$barrio=sql($q) or fin_pagina();
			$id_municipio=$barrio->fields['idmuni_provincial'];
		$que_va_municipio=;
	}*/
if($_GET['municipio']!=''){
	$nom_barrio=strtoupper($efectores);
	$muni=$_GET['municipio'];
	if(is_numeric($_GET['municipio'])==1){
		$id_municipio=$_GET['municipio'];
	}else{
		$q="select idmuni_provincial from uad.municipios  
			where nombre='".$_GET['municipio']."'";
			$barrio=sql($q) or fin_pagina();
			$id_municipio=$barrio->fields['idmuni_provincial'];
	}
		$sql_where="b.idmuni_provincial=$id_municipio and";
		echo "<script> document.all.muni.value='".$id_municipio."'; </script>";	
		
		$very="SELECT * from uad.barrios
				where nombre='$nom_barrio' and id_municipio=$id_municipio";
			
		$res_very=sql($very, "Error al realizar la verificacion")or fin_pagina();
		
		if($res_very->recordCount()==0){
			
		/*	$q="select nextval('uad.barrios_id_barrio_seq') as id_barrio";
			$barrio=sql($q) or fin_pagina();
			$id_barrio=$barrio->fields['id_barrio']; */
		   
	
		   $query="insert into uad.barrios
					(id_barrio, nombre, id_municipio)
					values
					(nextval('uad.barrios_id_barrio_seq'), '$nom_barrio' , '$id_municipio') RETURNING id_barrio";
			
				$barrio=sql($query, "Error al insertar provincia") or fin_pagina();
				$id_barrio=$barrio->fields['id_barrio'];
			 echo "<script> alert('Los datos se han guardado correctamente'); </script>";	 
			 $db->CompleteTrans();   
		} else {echo "<script> alert('Ya existe un Barrio con ese nombre'); </script>";}
}

   $sql=  "select a.id_barrio,a.nombre,b.nombre as nommunicipio,b.id_municipio,c.nombre as nomdepartamento,c.id_departamento,b.idmuni_provincial
            from uad.barrios a
			inner join uad.municipios b on a.id_municipio=b.idmuni_provincial
			inner join uad.localidades d on  b.idmuni_provincial=d.idloc_provincial
            inner join uad.departamentos c on d.id_departamento=c.id_departamento            
		WHERE  $sql_where
		( upper(a.nombre) = upper('$efectores') or upper(a.nombre) like upper('%$efectores%') )
		order by a.nombre,c.id_departamento,d.id_localidad";

$res_efectores=sql($sql) or fin_pagina();
	 ?>
					<script>
						document.getElementById('efectores').value='<?=$efectores?>';
					</script>
				  <table border=1 cellspacing=0 cellpadding=0 height=10% align="center" width=97%>
				  <caption>&nbsp;<h2><U>Resultados de la Busqueda</U></h2></caption>
				 <tr>
				  <td align="center">&nbsp;<h5>Barrio</h5></td>
				  <td align="center">&nbsp;<h5>Departamento</h5></td>
				  <td align="center">&nbsp;<h5>Municipo</h5></td>
				 </tr>
<?
			while (!$res_efectores->EOF){
			if($id_planilla){
			$nombre_barrio=$res_efectores->fields['nombre'];
			}else{
			$nombre_barrio=$res_efectores->fields['id_barrio'];
			}?>
					<tr>				
					<td><a href="#" onclick="opener.document.forms.form1.barrion.value = '<?=$nombre_barrio?>'; opener.document.forms.form1.b_barrio.focus(); opener.document.forms.form1.barrio.value = '<?=$nombre_barrio?>'; window.close();" style="text-decoration:none;" ><font size=2><?=$res_efectores->fields['nombre']?></font></a></td>
					 <td>&nbsp;<font size=2><? echo $res_efectores->fields['nomdepartamento'].'('.$res_efectores->fields['id_departamento'].')'; ?></font>&nbsp;</td>
                     <td>&nbsp;<font size=2><? echo $res_efectores->fields['nommunicipio'].'('.$res_efectores->fields['id_municipio'].')'; ?></font>&nbsp;</td>
 
			<? $res_efectores->movenext();
			    }?> </table>
			 <BR>  
			 <table width=60% align="center" class="bordes" style="display:none; margin-bottom:30px" id="barrionew_ver" >
		 	 <tr>
		  		<td id=mo colspan="2">
					<b> Nuevo Barrio</b> 
		  		</td>
		 	</tr>
			 <tr>
				<td align="right">
					  <b>Nombre:</b>
				</td>         	
				<td align='left'>
				<FORM METHOD="get" ACTION="" name="form2" id="form2">
						 <input type="hidden" value="<?=$muni?>" name="municipio" /><input type="hidden" value="<?=$id_planilla?>" name="id_planilla" />
						 <input type="text" size="40" value="" name="efectores"> &nbsp;&nbsp;<button onclick="submit();" style="font-size:9">Guardar Barrio</button>&nbsp;&nbsp;<button onclick="document.all.barrionew_ver.style.display='none';"  style="font-size:9; margin-right:-50">Cerrar</button>
				 </FORM>
				 </td>
			</tr>
			</table>
			 <center><button onclick="document.all.barrionew_ver.style.display='block';" >Agregar Barrio</button>
			 &nbsp;&nbsp;&nbsp;
			  <button onclick="window.close();" >Cerrar</button> </center>
<? }
echo fin_pagina();// aca termino?>
