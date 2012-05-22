<?php
/*
Author: gaby 

modificada por
$Author: gaby $
$Revision: 1.0 $
$Date: 2006/07/20 15:22:40 $
*/
require_once("../../config.php");


echo $html_header;?>

<form name=form1 action="puco_listado_express.php" method=POST>
<table cellspacing=2 cellpadding=2 border=0 width=100% align=center>
     <tr>
      <td align=center>
          <input type="text" size="30" value="" name="documento" >
	    &nbsp;&nbsp;<input type=submit name="buscar" value='Buscar'>

	  </td>
     </tr>
</table>

<table border=0 width=100% cellspacing=2 cellpadding=2 bgcolor='<?=$bgcolor3?>' align=center>
  
  <tr>
    <td id=mo>DOCUMENTO</td>      	
  	<td Id=mo>NOMBRE</td>      	
    <td id=mo>OBRA SOCIAL</td>  
  </tr>
    <? if ($_POST['buscar']){
    	$documento=$_POST['documento'];
    	if(strlen($documento)>=7){
    			$sql_tmp="SELECT puco.documento,puco.nombre,obras_sociales.nombre AS obra_social
				FROM puco.puco
				INNER JOIN puco.obras_sociales ON (puco.puco.cod_os = puco.obras_sociales.cod_os)
				WHERE (puco.puco.documento ='$documento')";
				$query=sql($sql_tmp,"ERROR al realizar la consulta")or fin_pagina();
    					
    			if($query->recordCount()==0){?> 
    				   	<tr>   
						     <td align="center" colspan="3">NO SE ENCONTRARON DATOS</td> 						      
						</tr> 
				<?}
				else{ 
					while (!$query->EOF) {?>
						    <tr <?=atrib_tr()?>>   
						     <td><?=$query->fields['documento']?></td>      
						     <td><?=$query->fields['nombre']?></td>
						     <td ><?=$query->fields['obra_social']?></td> 
						    </tr>    
							<?$query->MoveNext();
					}//FIN WHILE
			  	}//fin else  
    	 }  
    	 else{?>
    	 	<tr>  
    	 		<td align="center" colspan="3">DEBE INGRESAR A MENOS 7 NUMEROS</td> 						      
			</tr>    	 	
    	 <?}
     	}//fin IF
     ?>
  	
</table>
</form>
</body>
</html>

<?echo fin_pagina();// aca termino ?>