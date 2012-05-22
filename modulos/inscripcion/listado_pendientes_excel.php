<?php
require_once ("../../config.php");
if ($_POST['generarpendiente']){
	$sql="select id_beneficiarios,tipo_transaccion,apellido_benef||' '||apellido_benef_otro as apellido_benef,nombre_benef||' '||nombre_benef_otro as nombre_benef,clase_documento_benef,tipo_documento,clase_documento_benef,numero_doc,sexo,CAST(fecha_nacimiento_benef AS CHAR(10))as fecha_nacimiento_benef,pais_nac
			,tipo_doc_madre,nro_doc_madre,apellido_madre,nombre_madre,tipo_doc_padre,nro_doc_padre,apellido_padre
			,nombre_padre,tipo_doc_tutor,nro_doc_tutor,apellido_tutor,nombre_tutor,cuie_ea||'-'||nombreefector as  cuie_ea,calle,numero_calle,departamento,localidad,municipio,barrio,CAST(fecha_inscripcion AS CHAR(10)) as fecha_inscripcion,responsable
			,CAST(fecha_diagnostico_embarazo AS CHAR(10))as fecha_diagnostico_embarazo,semanas_embarazo,CAST(fecha_probable_parto AS CHAR(10))as fecha_probable_parto
			,CAST(fecha_efectiva_parto AS CHAR(10))as fecha_efectiva_parto,cast(beneficiarios.usuario_carga as int) as usuario_carga
			from uad.beneficiarios
			left join facturacion.smiefectores on beneficiarios.cuie_ea=smiefectores.cuie
			where beneficiarios.estado_envio='p' and tipo_ficha in ('2','3') and beneficiarios.fecha_carga between '".Fecha_db($_POST['f_desde'])."' and '".Fecha_db($_POST['f_hasta'])."' ";
			
		
	$id_usuario_conect=$_ses_user['id']; $admin='s';
	 $queryCategoria="SELECT upper(grupos.uname)as uname
			FROM permisos.grupos
			left join permisos.grupos_usuarios on grupos.id_grupo=grupos_usuarios.id_grupo 
  	where id_usuario=$id_usuario_conect and upper(uname) like '%ADMIN %'";
	$resultado=sql($queryCategoria, "Error al traer el Comprobantes") or fin_pagina();
	if ($resultado->recordcount()>0) {
	$uname=$resultado->fields['uname'];
	$uname_w= substr($uname,6,strlen($uname));
	
	$sql.= " and  cast(beneficiarios.usuario_carga as int) in (select id_usuario 
														FROM permisos.grupos
														left join permisos.grupos_usuarios on grupos.id_grupo=grupos_usuarios.id_grupo  
															where upper(uname)='$uname_w')";
	
	}else{
		 $queryCategoria="SELECT upper(grupos.uname)as uname
			FROM permisos.grupos
			left join permisos.grupos_usuarios on grupos.id_grupo=grupos_usuarios.id_grupo 
		where id_usuario=$id_usuario_conect and upper(uname) like '%ADMIN%'";
		$resultado=sql($queryCategoria, "Error al traer el Comprobantes") or fin_pagina();
		if ($resultado->recordcount()==0) {
			$sql.= " and  cast(beneficiarios.usuario_carga as int)=$id_usuario_conect"; $admin='n';
		}
	}	
		$result=sql($sql) or fin_pagina();	
		if($result->recordcount()>0){
		excel_header("listado_pendientes.xls");
		?>
		 <br>
		 <table width="100%" align=center border=1 bordercolor=#585858 cellspacing="0" cellpadding="5" style="font-size:8px"> 
		  <tr bgcolor=#C0C0FF align="center" style="font:bold">
			<td  id=mo>Id</td>
			<td  id=mo>Tipo</td>
			<td  id=mo width="60">Apellido Benef</td>
			<td  id=mo width="60">Nombre Benef</td>
			<td  id=mo width="40">Clase Doc</td>
			<td  id=mo>Tipo Doc</td>
			<td  id=mo>Nº Doc</td>
			<td  id=mo width="20">Sexo</td>
			<td  id=mo>Fecha Nac</td>
			<td  id=mo>Pais Nac</td>
			<td  id=mo width="50">Diagnostico Embarazo</td>
			<td  id=mo width="40">Semanas Gestacion</td>
			<td  id=mo width="50">FUM</td>
			<td  id=mo width="50">FPP</td>
			<td  id=mo width="40">Responsable</td>
			<td  id=mo width="40">Tipo Doc Responsable</td>
			<td  id=mo width="50">Nº Doc Responsable</td>
			<td  id=mo width="60">Apellido Responsable</td>
			<td  id=mo width="60">Nombre Responsable</td>
			<td  id=mo width="100">Efector</td>
			<td  id=mo>Calle</td>
			<td  id=mo width="30">Nº Calle</td>
			<td  id=mo width="70">Departamento</td>
			<td  id=mo width="70">Localidad</td>
			<td  id=mo width="70">Municipio</td>
			<td  id=mo width="60">Barrio</td>
			<td  id=mo>Inscripcion</td>
			<?if($admin=='s'){?>
			<td  id=mo>Usuario Carga</td>
			<?}?>
		  </tr>
		  <?   
		  while (!$result->EOF) {
		  if($admin=='s'){
			   $sq2="select upper(nombre||' '||apellido) as nomus
				from sistema.usuarios
				where id_usuario=".$result->fields['usuario_carga'];
			   $resulta = sql($sq2) or die;
			   if($resulta->recordcount()>0){
			   $usuario_carga=$resulta->fields['nomus'];
			   }
		   }?>
			<tr>     
			 <td>&nbsp;<?=$result->fields['id_beneficiarios']?>&nbsp;</td>
			 <td>&nbsp;<?=$result->fields['tipo_transaccion']?></td>
			 <td>&nbsp;<?=$result->fields['apellido_benef']?></td>
			 <td>&nbsp;<?=$result->fields['nombre_benef']?></td>
			 <td>&nbsp;<?=$result->fields['clase_documento_benef']?></td>
			 <td>&nbsp;<?=$result->fields['tipo_documento']?></td>
			 <td>&nbsp;<?=$result->fields['numero_doc']?></td>
			 <td>&nbsp;<?=$result->fields['sexo']?></td>
			 <td>&nbsp;<?=$result->fields['fecha_nacimiento_benef']?></td>
			 <td>&nbsp;<?=$result->fields['pais_nac']?></td>
			 
			  <td><?=str_replace('1899-12-30','',$result->fields['fecha_diagnostico_embarazo'])?></td>
			 <td><?=$result->fields['semanas_embarazo']?></td>
			 <td><?=str_replace('1899-12-30','',$result->fields['fecha_probable_parto'])?></td>
			 <td><?=str_replace('1899-12-30','',$result->fields['fecha_efectiva_parto'])?></td>
			 
			 <td>&nbsp;<?=$result->fields['responsable']?></td>
		<?	 if($result->fields['responsable']=='MADRE'){?>
			 <td>&nbsp;<?=$result->fields['tipo_doc_madre']?></td>
			 <td>&nbsp;<?=$result->fields['nro_doc_madre']?></td>
			 <td>&nbsp;<?=$result->fields['apellido_madre']?></td>
			 <td>&nbsp;<?=$result->fields['nombre_madre']?></td>
			 <?	}elseif($result->fields['responsable']=='PADRE'){?>
			 <td>&nbsp;<?=$result->fields['tipo_doc_padre']?></td>
			 <td>&nbsp;<?=$result->fields['nro_doc_padre']?></td>
			 <td>&nbsp;<?=$result->fields['apellido_padre']?></td>
			 <td>&nbsp;<?=$result->fields['nombre_padre']?></td>
			  <?	}else{?>
			 <td>&nbsp;<?=$result->fields['tipo_doc_tutor']?></td>
			 <td>&nbsp;<?=$result->fields['nro_doc_tutor']?></td>
			 <td>&nbsp;<?=$result->fields['apellido_tutor']?></td>
			 <td>&nbsp;<?=$result->fields['nombre_tutor']?></td>
			 <? }?>
			 <td>&nbsp;<?=$result->fields['cuie_ea']?></td>
			 <td>&nbsp;<?=$result->fields['calle']?></td>
			 <td>&nbsp;<?=$result->fields['numero_calle']?></td>
			 <td>&nbsp;<?=$result->fields['departamento']?></td>
			 <td>&nbsp;<?=$result->fields['localidad']?></td>
			 <td>&nbsp;<?=$result->fields['municipio']?></td>
			 <td>&nbsp;<?=$result->fields['barrio']?></td>
			 <td>&nbsp;<?=$result->fields['fecha_inscripcion']?></td>
			  <? if($admin=='s'){?>
			 <td>&nbsp;<?=$usuario_carga?></td>
			 <?}?>
			</tr>
			<?$result->MoveNext();
			}?>
		 </table>
<?  }else{ 
			$ref = encode_link("pendiente_listado.php",array("mjs"=>'No hay pendientes para generar archivos'));
							echo "<SCRIPT Language='Javascript'> location.href='$ref'</SCRIPT>";
		}
}?>