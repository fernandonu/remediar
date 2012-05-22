<?

require_once ("../../config.php");
include_once('lib_inscripcion.php');
	
if(isset($_POST["id_pais"]))
	{
		$opciones6 = '<option value="-1"> Seleccione Provincia</option>';
		
		$strConsulta = "select id_provincia, nombre from uad.provincias where id_pais = '".$_POST['id_pais']."' order by nombre";
		$result =  @pg_exec($strConsulta);
		

		while( $fila = pg_fetch_array ($result) )
		{
			$opciones6.='<option value="'.$fila["nombre"].'">'.$fila["nombre"].'</option>';
		}

		echo $opciones6;
	}
	
if(isset($_POST["id_provincia"]))
	{
		$opciones7 = '<option value="-1"> Seleccione Localidad</option>';
		
		$strConsulta = "select l.id_localidad, l.nombre from uad.localidades l,uad.provincias p, uad.departamentos d 
						where p.nombre = '".$_POST['id_provincia']."' and d.id_provincia = p.id_provincia and 
						l.id_departamento = d.id_departamento order by nombre";
		$result =  @pg_exec($strConsulta);
		

		while( $fila = pg_fetch_array ($result) )
		{
			$opciones7.='<option value="'.$fila["nombre"].'">'.$fila["nombre"].'</option>';
		}

		echo $opciones7;
	}
	
if(isset($_POST["id_departamento"]))
	{
	
		$opciones2 = '<option value="-1"> Seleccione Localidad</option>';
		
		if(strtoupper ($_POST['provincia'])=='MISIONES'){
			$strConsulta = "select idloc_provincial as id_localidad, nombre from uad.localidades where id_departamento = '".$_POST['id_departamento']."' or nombre = '".$_POST['id_departamento']."' order by nombre";
		}else{
			$strConsulta = "select id_localidad, nombre from uad.localidades where id_departamento = '".$_POST['id_departamento']."' or nombre order by nombre";
		}
		$result =  @pg_exec($strConsulta);
		

		while( $fila = pg_fetch_array ($result) )
		{
			$opciones2.='<option value="'.$fila["nombre"].'">'.$fila["nombre"].'</option>';
					
		}
		echo $opciones2;
		
	}

	if(isset($_POST["id_localidad"]))
	{
		$id_localidad=$_POST['id_localidad'];
		$query="select idloc_provincial as id_localidad, nombre from uad.localidades  where nombre='".$_POST['id_localidad']."'";

		$res_factura=sql($query, "Error al traer el Comprobantes") or fin_pagina();
			if ($res_factura->recordcount()>0){
		$id_localidad=$res_factura->fields['id_localidad'];
		}
			$opciones5 = '<option> Codigo Postal </option>';

		$strConsulta = "select id_codpos, codigopostal from uad.codpost where id_localidad = '".$id_localidad."'";
		
		$result =  @pg_exec($strConsulta);
		

		while( $fila = pg_fetch_array ($result) )
		{
		$opciones5.='<option value="'.$fila["codigopostal"].'">'.$fila["codigopostal"].'</option>';
					
		}
			
		echo $opciones5;
		
		
	}
	if (isset($_POST["id_codpos"]))	
	{
		$id_localidad=$_POST['localidad'];
		$query="select idloc_provincial as id_localidad, nombre from uad.localidades  where nombre='".$_POST['localidad']."'";

		$res_factura=sql($query, "Error al traer el Comprobantes") or fin_pagina();
			if ($res_factura->recordcount()>0){
		$id_localidad=$res_factura->fields['id_localidad'];
		}
		
		$id_codpos=$_POST['id_codpos'];
		$query="select id_codpos, codigopostal from uad.codpost where codigopostal='".$_POST['id_codpos']."' and id_localidad='".$id_localidad."'";

		$res_factura=sql($query, "Error al traer el Comprobantes") or fin_pagina();
			if ($res_factura->recordcount()>0){
		$id_codpos=$res_factura->fields['id_codpos'];
		}
	
		$opciones3 = '<option value="-1"> Seleccione Municipio</option>';
						
		if(strtoupper ($_POST['provincia'])=='MISIONES'){
			$strConsulta = "select idmuni_provincial as id_municipio, nombre from uad.municipios where id_codpos = '".$id_codpos."' order by nombre";
		}else{				
			$strConsulta = "select id_municipio, nombre from uad.municipios where id_codpos = '".$id_codpos."' order by nombre";
		 }
		$result =  @pg_exec($strConsulta);
				
		while( $fila = pg_fetch_array ($result) )
		{
			$opciones3.='<option value="'.$fila["nombre"].'">'.$fila["nombre"].'</option>';
		}
		
		echo $opciones3;	
		
	}
	
	
	if(isset($_POST["id_municipio"]))
	{
		if(is_numeric($_POST['id_municipio'])==1){
			$id_municipio=$_POST['id_municipio'];
		}else{
			$q="select idmuni_provincial from uad.municipios  
				where nombre='".$_POST['id_municipio']."'";
				$barriosql=sql($q) or fin_pagina();
				$id_municipio=$barriosql->fields['idmuni_provincial'];
		}
		$barrio=$_POST["barrio"];
		$opciones4 = '<option value="-1"> Seleccione Barrio</option>
					 <option value="S/D" '; if("S/D"==$barrio){$opciones4.=' selected ';} $opciones4.='> S/D</option>';

		
		$strConsulta = "select id_barrio, nombre from uad.barrios where id_municipio = '".$id_municipio."' order by nombre";
		$result =  @pg_exec($strConsulta);
		

		while( $fila = pg_fetch_array ($result) )
		{
			$opciones4.='<option value="'.$fila["nombre"].'"'; if($fila["nombre"]==$barrio || $fila["id_barrio"]==$barrio){$opciones4.=' selected ';} $opciones4.='>'.utf8_encode($fila["nombre"]).'</option>';
		}

		echo $opciones4;
		
	}
	if(isset($_POST["nro_doc_madre"]))
	{
				/* $campo=" responsable,tipo_doc_madre as tipodoc,apellido_madre as apellido,nombre_madre as nombre,alfabeta_madre as alfabeta
		 ,estudios_madre as estudio,estadoest_madre as estadoest,anio_mayor_nivel_madre as anio_mayor_nivel ";
		
		
			$campo=" tipo_doc_padre as tipodoc,apellido_padre as apellido,nombre_padre as nombre,alfabeta_padre as alfabeta
		 	,estudios_padre as estudio,estadoest_padre as estadoest,anio_mayor_nivel_padre as anio_mayor_nivel ";
			*/
				$retorno='-1*DNI***N**C*0';
		if($_POST["nro_doc_madre"]){		
				$q="select COALESCE(responsable,'-1') as responsable,COALESCE(tipo_doc_madre,'DNI') as tipo_doc_madre,COALESCE(apellido_madre,'') as apellido_madre
					,COALESCE(nombre_madre,'') as nombre_madre, COALESCE(alfabeta_madre,'N') as alfabeta_madre,COALESCE(estudios_madre,'') as estudios_madre
					,COALESCE(estadoest_madre,'C') as estadoest_madre,COALESCE(anio_mayor_nivel_madre,'0') as anio_mayor_nivel_madre
					
				,COALESCE(tipo_doc_padre,'DNI') as tipo_doc_padre ,COALESCE(apellido_padre,'') as apellido_padre,COALESCE(nombre_padre,'') as nombre_padre
					,COALESCE(alfabeta_padre,'N') as alfabeta_padre,COALESCE(estudios_padre,'') as estudios_padre,COALESCE(estadoest_padre,'C') as estadoest_padre
					,COALESCE(anio_mayor_nivel_padre,'0') as anio_mayor_nivel_padre 
					
				,COALESCE(tipo_doc_tutor,'DNI') as tipo_doc_tutor,COALESCE(apellido_tutor,'') as apellido_tutor,COALESCE(apellido_tutor,'') as nombre_tutor
					,COALESCE(alfabeta_tutor,'N') as alfabeta_tutor,COALESCE(estudios_tutor,'') as estudios_tutor,COALESCE(estadoest_tutor,'C') as estadoest_tutor
					,COALESCE(anio_mayor_nivel_tutor,'0') as anio_mayor_nivel_tutor 
					
					from uad.beneficiarios  
					where nro_doc_padre='".$_POST["nro_doc_madre"]."' or  nro_doc_madre='".$_POST["nro_doc_madre"]."' or  nro_doc_tutor='".$_POST["nro_doc_madre"]."'
					group by COALESCE(responsable,'-1'),COALESCE(tipo_doc_madre,'DNI'),COALESCE(apellido_madre,'')
					,COALESCE(nombre_madre,'') , COALESCE(alfabeta_madre,'N'),COALESCE(estudios_madre,'')
					,COALESCE(estadoest_madre,'C') ,COALESCE(anio_mayor_nivel_madre,'0') 
					
				,COALESCE(tipo_doc_padre,'DNI') ,COALESCE(apellido_padre,''),COALESCE(nombre_padre,'')
					,COALESCE(alfabeta_padre,'N') ,COALESCE(estudios_padre,'') ,COALESCE(estadoest_padre,'C')
					,COALESCE(anio_mayor_nivel_padre,'0')  
					
				,COALESCE(tipo_doc_tutor,'DNI') ,COALESCE(apellido_tutor,'') ,COALESCE(apellido_tutor,'') 
					,COALESCE(alfabeta_tutor,'N') ,COALESCE(estudios_tutor,''),COALESCE(estadoest_tutor,'C')
					,COALESCE(anio_mayor_nivel_tutor,'0')";
						$barriosql=sql($q) or fin_pagina();
						
						if($barriosql->recordcount()>0){
							$retorno=$_POST['responsable'].'*';
							if($barriosql->fields['responsable']=='MADRE' || $barriosql->fields['apellido_madre']!=''){
									$retorno.=$barriosql->fields['tipo_doc_madre'].'*'.$barriosql->fields['apellido_madre'].'*'.$barriosql->fields['nombre_madre'].'*'.$barriosql->fields['alfabeta_madre'].'*'.$barriosql->fields['estudios_madre'].'*'.$barriosql->fields['estadoest_madre'].'*'.$barriosql->fields['anio_mayor_nivel_madre'];
							}elseif($barriosql->fields['responsable']=='PADRE' || $barriosql->fields['apellido_padre']!=''){
									$retorno.=$barriosql->fields['tipo_doc_padre'].'*'.$barriosql->fields['apellido_padre'].'*'.$barriosql->fields['nombre_padre'].'*'.$barriosql->fields['alfabeta_padre'].'*'.$barriosql->fields['estudios_padre'].'*'.$barriosql->fields['estadoest_padre'].'*'.$barriosql->fields['anio_mayor_nivel_padre'];
							}elseif($barriosql->fields['responsable']=='TUTOR' || $barriosql->fields['apellido_tutor']!=''){
									$retorno.=$barriosql->fields['tipo_doc_tutor'].'*'.$barriosql->fields['apellido_tutor'].'*'.$barriosql->fields['nombre_tutor'].'*'.$barriosql->fields['alfabeta_tutor'].'*'.$barriosql->fields['estudios_tutor'].'*'.$barriosql->fields['estadoest_tutor'].'*'.$barriosql->fields['anio_mayor_nivel_tutor'];	
							}else{
									$retorno.='DNI***N**C*0';	
							}
						}
			}				
				
				echo $retorno;
	}
	?>
