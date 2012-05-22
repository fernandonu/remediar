<?
require_once ("../../config.php");

require_once("../../lib/funciones_misiones.php");

extract($_POST, EXTR_SKIP);
if ($parametros)
  extract($parametros, EXTR_OVERWRITE);
  
echo ' PROCESANDO CONTROL......ESTA OPERACION PUEDE TARDAR VARIOS MINUTOS....AGUARDE2!!!';


/*1_INICIO DE LOS CONTROL*/
$sql3= " SELECT  prestacion.id_nomenclador,  precio,smiafiliados.afidni, smiafiliados.afiapellido, smiafiliados.afinombre 
			, nomenclador.codigo,comprobante.id_smiafiliados
			,factura.cuie,comprobante.clavebeneficiario,prestacion.id_prestacion
			,validacion_prestacion_mns.control,validacion_prestacion_mns.periodicidad,validacion_prestacion_mns.maxefector,validacion_prestacion_mns.maxprovincial
			,validacion_prestacion_mns.tipope,validacion_prestacion_mns.tipoef,validacion_prestacion_mns.tipopr
			,comprobante.fecha_comprobante, date_part('month',comprobante.fecha_comprobante) as mes,date_part('year',comprobante.fecha_comprobante) as ano
					--,convert(nvarchar,fechacontrolprenatal,103) as primerctrl
					FROM facturacion.factura
					inner join facturacion.comprobante on factura.id_factura=comprobante.id_factura
					inner join facturacion.prestacion on comprobante.id_comprobante=prestacion.id_comprobante
					inner join nacer.smiafiliados on smiafiliados.id_smiafiliados=comprobante.id_smiafiliados
					inner join facturacion.nomenclador on nomenclador.id_nomenclador=prestacion.id_nomenclador
					left join facturacion.validacion_prestacion_mns on trim(nomenclador.codigo)=trim(validacion_prestacion_mns.codnomenclador)
					where factura.id_factura=$id_factura";
			 $sql3.= " and not exists(select id_debito from facturacion.debito where id_factura=$id_factura and documento_deb=smiafiliados.afidni and codigo_deb=codigo)";
			  $sql3.= " and validacion_prestacion_mns.control in ('provincial','efector')";
		/*	 $controlcual=  "SELECT a.idBenefPrestacion,a.claveBeneficiario,a.tipoDoc,a.nroDoc,a.apellido,a.nombre,a.codNomenclador,
CONVERT ( varchar ( 10 ), a.fechaActual ,103 ) as fechaActual,b.cuieEfector,a.idbenefrecepcion,x.control,x.periodicidad,
x.maxefector,x.maxprovincial,tipope,tipoef,tipopr,month(a.fechaActual) as mes,year(a.fechaActual) as ano,
convert(nvarchar,fechacontrolprenatal,103) as primerctrl
FROM [20BEnefPrestacion]a INNER JOIN [20EfectoresInforme] b ON a.idEfectorInforme=b.idEfectorInforme
INNER JOIN [20Nomencladores] x on x.codnomenclador=a.codnomenclador
left JOIN trzembarazadas r ON a.idbenefrecepcion=r.idbenefrecepcion
WHERE ((idCaratula='$idc') and (x.control='provincial' or  x.control='efector') and (a.debitoFinan='0' and a.debitoMedi='0'))
group by a.idBenefPrestacion,a.claveBeneficiario,a.tipoDoc,a.nroDoc,a.apellido,a.nombre,a.codNomenclador,
CONVERT ( varchar ( 10 ), a.fechaActual ,103),b.cuieEfector,a.idbenefrecepcion,x.control,x.periodicidad,x.maxefector,
x.maxprovincial,tipope,tipoef,tipopr,datepart(month,a.fechaActual),datepart(year,a.fechaActual),convert(nvarchar,fechacontrolprenatal,103)";*/

		 $res_sql3 = sql($sql3) or fin_pagina();
		 while (!$res_sql3->EOF) {
					$consulta1="";
					$consulta2="";
					$consulta3="";
					$mjs_provef="";
					$cuantos=0;
					$idBenefPrestacion=$res_sql3->fields['id_prestacion'];
					$idbenefdebito=$idBenefPrestacion;
					$claveBeneficiario=trim($res_sql3->fields['clavebeneficiario']);
					$id_smiafiliados=$res_sql3->fields['id_smiafiliados'];
					$nroDoc=$res_sql3->fields['afidni'];
					$codigo=$res_sql3->fields['codigo'];
					$id_nomenclador=$res_sql3->fields['id_nomenclador'];
					$precio=$res_sql3->fields['precio'];
					$apellido=$res_sql3->fields['afiapellido'];
					$nombre=$res_sql3->fields['afinombre'];
					$codNomenclador_benef=trim($res_sql3->fields['codigo']);
					$fechaActual=$res_sql3->fields['fecha_comprobante'];
					$cuieEfector=$res_sql3->fields['cuie'];
					$control=trim($res_sql3->fields['control']);
					$periodo=trim($res_sql3->fields['periodicidad']);	
					$maxefector=trim($res_sql3->fields['maxefector']);	
					$maxprovincial=trim($res_sql3->fields['maxprovincial']);
					$tipope=trim($res_sql3->fields['tipope']);	
					$tipoef=trim($res_sql3->fields['tipoef']);	
					$tipopr=trim($res_sql3->fields['tipopr']);	
					$mes=trim($res_sql3->fields['mes']);
					$ano=trim($res_sql3->fields['ano']);
					//$primerctrl=$res_sql3->fields['primerctrl'];		
					$debitar='n';
					
					
				if ($control=='efector')
				{
					$q='E';
					$consulta1="factura.cuie='$cuieEfector' and ";
				}
				if ($control=='provincial')
				{	
					$q='P';	
				}
		
		$maximus=0;
				
		if (substr($tipope,1,1)!='c' && $tipope!='v'){
		$contar=1;
		$prov= " SELECT  prestacion.id_nomenclador,  comprobante.id_smiafiliados,comprobante.fecha_comprobante,prestacion.id_prestacion
					,factura.nro_fact_offline,factura.id_factura
					FROM facturacion.factura
					inner join facturacion.comprobante on factura.id_factura=comprobante.id_factura
					inner join facturacion.prestacion on comprobante.id_comprobante=prestacion.id_comprobante
					where $consulta1 comprobante.id_smiafiliados=$id_smiafiliados";
			 $prov.= " and not exists(select id_debito from facturacion.debito where id_factura=factura.id_factura and documento_deb='$nroDoc' and codigo_deb='$codigo')";
			  $prov.= " and prestacion.id_nomenclador='$id_nomenclador'";
			   $prov.= " and prestacion.id_prestacion<>$idBenefPrestacion";
			  $prov.= " and comprobante.fecha_comprobantes>DATE '$fechaActual' - ($periodo*30) and comprobante.fecha_comprobante<DATE '$fechaActual' + ($periodo*30)";
			  
	/*	$prov=  "SELECT  c.codexpediente,CONVERT ( varchar ( 10 ), a.fechaActual ,103 ) as fechaActual,a.idBenefPrestacion
		,nrocuerpoexp
		FROM [20BenefPrestacion]a INNER JOIN [20EfectoresInforme] b ON a.idEfectorInforme=b.idEfectorInforme
		INNER JOIN [20CaratulaInforme] c ON b.idCaratula=c.idCaratula
		WHERE $consulta1 ((a.tipoDoc='$tipoDoc' and a.nroDoc='$nroDoc' and a.apellido='$apellido' and a.nombre='$nombre') 
		$clave ) and a.idBenefPrestacion<>'$idBenefPrestacion' and 
		(a.codNomenclador='$codNomenclador_benef' and a.debitoFinan='0' and a.debitoMedi='0') and 
		(a.fechaActual>DATEADD (Month,-$periodo,'$fechaActual') and a.fechaActual<DATEADD (Month,$periodo,'$fechaActual')) 
		group by c.codexpediente,CONVERT ( varchar ( 10 ), a.fechaActual ,103 ),a.idBenefPrestacion,nrocuerpoexp";*/
		 
			 $res_prov = sql($prov) or fin_pagina();
		 while (!$res_prov->EOF) {
					$nro_fact_offline=trim($res_prov->fields['nro_fact_offline']);
					$fechaActual_comp=$res_prov->fields['fecha_comprobante'];
					$idBenefPrestacion_comp=$res_prov->fields['id_prestacion'];
					$contar++;
					
				$res_sql3->movenext();
				}
				
				if ($contar>1)
				{
					$mjs_provef='Prestacion no cumple con la periodicidad del control';
					$debitar=='s';
				}
		
			if ($debitar=='s')
			{

					$SQLbenef = "insert into facturacion.debito (id_factura, id_nomenclador, cantidad, id_motivo_d, monto, documento_deb, apellido_deb, nombre_deb, codigo_deb, observaciones_deb, mensaje_baja)
				 values (" .$id_factura . "," . $id_nomenclador . ", 1, 65, " . $precio . ", '" . $nroDoc . "', '" . $apellido . "', '" . $nombre . "', '" . $codigo. "', '', '$mjs_provef')";
				/////////////////////////////////////////error/////////////////////////////////
				sql($SQLbenef, "Error al insertar débito", 0) or excepcion("Error al insertar débito");
			}
		}
	$res_sql3->movenext();
	}/*1_ FIN DE CONTROL*/


		
		
		
		$SQLfin = "update facturacion.factura set ctrl='S' where factura.id_factura=$id_factura";
				/////////////////////////////////////////error/////////////////////////////////
				sql($SQLfin, "Error al marcar control", 0) or excepcion("Error al marcar control");
				

				
		echo ' <SCRIPT Language="Javascript">
		alert("Fin de Controles Automaticos");
		 window.opener.location.reload();
		 window.close();
		</SCRIPT>';	
		
?>