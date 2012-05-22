<?php

require_once ("../../config.php");

$cmd=$parametros["cmd"];
$estado=$cmd;
if ($cmd=='todos') $cmd='T';
elseif ($cmd=='activos')$cmd='S';
else $cmd='N';

$sql="select * from nacer.smiafiliados
	 left join nacer.smitiposcategorias on (afitipocategoria=codcategoria)
	 left join facturacion.smiefectores on (cuieefectorasignado=cuie)";
if ($cmd!='T') $sql.=" WHERE  (smiafiliados.activo='$cmd')";
$sql.=" Order by smiafiliados.afiapellido";
$result=sql($sql) or fin_pagina();

excel_header("beneficiarios.xls");

?>
<form name=form1 method=post action="listado_beneficiarios_excel.php">
<table width="100%">
  <tr>
   <td>
    <table width="100%">
     <tr>
      <td align=left>
       <b>Total beneficiarios: </b><?=$result->RecordCount();?> 
       </td>       
      </tr>
      <tr>
      <td align=left>
       <b>Estado: <font size="+1" color="Red"><?=$estado;?> </font></b>
       </td>       
      </tr>
    </table>  
   </td>
  </tr>  
 </table> 
 <br>
 <table width="100%" align=center border=1 bordercolor=#585858 cellspacing="0" cellpadding="5"> 
  <tr bgcolor=#C0C0FF>
    <td align=right >Apellido</td>      	
    <td align=right >Nombre</td>
    <td align=right >DNI</td>
    <td align=right >Tipo Beneficiario</td>
    <td align=right >Nombre Efector</td>    
    <td align=right >Activo</td>    
    <td align=right >Motivo Baja</td>
    <td align=right >Mensaje Baja</td>
    <td align=right >Clave Beneficiario</td>
    <td align=right >Sexo</td>
    <td align=right >Fecha Nac</td>
    <td align=right >Departamento</td>
    <td align=right >Localidad</td>
    <td align=right >F.Ins</td>
    <td align=right >F.Carga</td>
  </tr>
  <?   
  while (!$result->EOF) {?>  
    <tr>     
     <td><?=$result->fields['afiapellido']?></td>
     <td><?=$result->fields['afinombre']?></td>
     <td><?=$result->fields['afidni']?></td>     
     <td><?=$result->fields['descripcion']?></td>     
     <td><?=$result->fields['nombreefector']?></td>         
     <td><?=$result->fields['activo']?></td>     
     <td><?=$result->fields['motivobaja']?></td> 
     <td><?=$result->fields['mensajebaja']?></td> 
     <td "<?=excel_style("texto")?>"><?=$result->fields['clavebeneficiario']?></td> 
     <td><?=$result->fields['afisexo']?></td> 
     <td><?=Fecha($result->fields['afifechanac'])?></td> 
     <td><?=$result->fields['afidomdepartamento']?></td> 
     <td><?=$result->fields['afidomlocalidad']?></td> 
     <td><?=Fecha($result->fields['fechainscripcion'])?></td> 
     <td><?=Fecha($result->fields['fechacarga'])?></td> 
    </tr>
	<?$result->MoveNext();
    }?>
 </table>
 </form>