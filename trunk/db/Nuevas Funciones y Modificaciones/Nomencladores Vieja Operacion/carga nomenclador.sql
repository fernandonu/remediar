/*cod*/
select  case when substring(a.codnomenclador,8,1) in ('A','B','C','D','E','F') or a.codnomenclador like  'Pago Dif%'
		or substring(a.codnomenclador,7,1)='.'	or
(len(rtrim(a.codnomenclador)) >=7 and substring(a.codnomenclador,7,1) not in ('-')
and substring(a.codnomenclador,8,1) not in ('A','B','C','D','E','F','G')
and substring(a.codnomenclador,7,1)<>'.' and a.codnomenclador not like 'Pago Dif%') then rtrim(a.codnomenclador)
	else substring(a.codnomenclador,1,6) end,grupo,subgrupo 
,case when substring(a.codnomenclador,1,6)in ('LMI 46') then 'Laboratorio Materno Infantil  (1 prueba de Anexo I)'
  when substring(a.codnomenclador,1,6)in ('LMI 47') then 'Laboratorio Materno Infantil  (1 prueba de Anexo II)'
  when substring(a.codnomenclador,1,6)in ('LMI 48') then 'Laboratorio Materno Infantil  (1 prueba de Anexo III)'
  when substring(a.codnomenclador,1,6)in ('LMI 49') then 'Laboratorio Materno Infantil  (1 prueba de Anexo IV)'
else  rtrim(descripcion) end descr
,left(costo,7),case when substring(a.codnomenclador,1,1)<>'R' then 'NORMAL'else 'RURAL' end,numvigencia
,a.codnomenclador
from [20nomencladores]a 
inner join [20vigencianomencladores]b on a.codnomenclador=b.codnomenclador
group by  case when substring(a.codnomenclador,8,1) in ('A','B','C','D','E','F','G') or a.codnomenclador  like 'Pago Dif%'
			or substring(a.codnomenclador,7,1)='.'	
or (len(rtrim(a.codnomenclador)) >=7 and substring(a.codnomenclador,7,1) not in ('-')
and substring(a.codnomenclador,8,1) not in ('A','B','C','D','E','F','G')
and substring(a.codnomenclador,7,1)<>'.' and a.codnomenclador not like 'Pago Dif%') then rtrim(a.codnomenclador)
	else substring(a.codnomenclador,1,6) end,grupo,subgrupo,case when substring(a.codnomenclador,1,6)in ('LMI 46') then 'Laboratorio Materno Infantil  (1 prueba de Anexo I)'
  when substring(a.codnomenclador,1,6)in ('LMI 47') then 'Laboratorio Materno Infantil  (1 prueba de Anexo II)'
  when substring(a.codnomenclador,1,6)in ('LMI 48') then 'Laboratorio Materno Infantil  (1 prueba de Anexo III)'
  when substring(a.codnomenclador,1,6)in ('LMI 49') then 'Laboratorio Materno Infantil  (1 prueba de Anexo IV)'
else  rtrim(descripcion) end,left(costo,7),numvigencia,substring(a.codnomenclador,1,1)
, a.codnomenclador

/*anexo*/
select  substring(a.codnomenclador,8,2),rtrim(descripcion),'',left(costo,7),id_nomenclador,numvigencia
from [20nomencladores]a 
inner join [20vigencianomencladores]b on a.codnomenclador=b.codnomenclador
where substring(a.codnomenclador,7,1) in ('-')
group by  substring(a.codnomenclador,8,2),rtrim(descripcion),left(costo,7),id_nomenclador,numvigencia



/*prueba*/
select *
from [20vigencianomencladores]
where numvigencia=7

/*saca de postgres*/
select 'update [20vigencianomencladores] set id_nomenclador='||id_nomenclador||' where codnomenclador like '''||trim(codigo)||'%'' and numvigencia='''||id_nomenclador_detalle||''''
from facturacion.nomenclador
where trim(codigo) in ('LMI 46','LMI 47','LMI 48','LMI 49')