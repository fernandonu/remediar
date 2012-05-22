<?php

function excepcion($m) {
  throw new Exception($m);
}

function calcular_estimulos_2010($anop, $mesp, $verifica, $liquid = 'n') {

  $idc = 0;
  $exp = 'vacio';

  $CON1 = "select expe FROM tmp_infometa where mes='$mesp' and ano='$anop' and cerrado='S' and cuie='$verifica'";
  $resuCON1 = sql($CON1) or excepcion("Error, vuelva a intentarlo.");
  if ($resuCON1->RecordCount() > 0) {
    excepcion("El expediente esta cerrado. Imposible generar estimulos");
  } else {
    if ($liquid != 's') {
      $CONer = "select expe,idc FROM tmp_infometa where mes='$mesp' and ano='$anop' and cuie='$verifica'";
      $resuCONer = sql($CONer) or excepcion("Error, vuelva a intentarlo.");
      if ($resuCONer->RecordCount() > 0) {
        $resuCONer->MoveFirst();
        $exp = $resuCONer->fields["expe"];
        $idc = $resuCONer->fields["idc"];
      } else {
        //error porque no existe
      }
    }
    limpia_estimulos($mesp, $anop, $verifica);
    $SQLef = "SELECT tipoefector FROM nacer.efe_conv where cuie='$verifica'";
    $result = sql($SQLef) or excepcion("Error, vuelva a intentarlo.");
    if ($result->RecordCount() > 0) {
      $result->MoveFirst();
      $tipoefector = $result->fields["tipoefector"];
    }
    objetivos_1_2010($anop, $mesp, $verifica, $tipoefector, $exp, $idc);
    objetivos_2_2010($anop, $mesp, $verifica, $tipoefector, $exp, $idc);
    objetivos_3_2010($anop, $mesp, $verifica, $tipoefector, $exp, $idc);
    objetivos_4_2010($anop, $mesp, $verifica, $tipoefector, $exp, $idc);
    objetivos_5_2010($anop, $mesp, $verifica, $tipoefector, $exp, $idc);
    objetivos_6_2010($anop, $mesp, $verifica, $tipoefector, $exp, $idc);
    objetivos_7_2010($anop, $mesp, $verifica, $tipoefector, $exp, $idc);
    objetivos_8_2010($anop, $mesp, $verifica, $tipoefector, $exp, $idc);
    objetivos_9_2010($anop, $mesp, $verifica, $tipoefector, $exp, $idc);
    objetivos_10_2010($anop, $mesp, $verifica, $tipoefector, $exp, $idc);
  }
}

function limpia_estimulos($mesp, $anop, $verifica) {
  $sql = "delete from tmp_infometa where mes='$mesp' and ano='$anop' and cuie='$verifica'";
  sql($sql) or excepcion("Error, vuelva a intentarlo.");
}

function objetivos_1_2010($anop, $mesp, $verifica, $tipoefector, $exp, $idc) {
  if ($tipoefector == 'PSB' || $tipoefector == 'CSA' || $tipoefector == 'ALD' || $tipoefector ==
    'HOS' || $tipoefector == 'HOS3') {
    $rembar1 = "select numerador,denominador from objetivos where mes='$mesp' and ano='$anop' and obj='1' and cuie='$verifica'";
    $resas1 = sql($rembar1) or excepcion("Error, vuelva a intentarlo.");
    if ($resas1->RecordCount() > 0) {
      $resas1->MoveFirst();
      $numerador = $resas1->fields['numerador'];
      $denominador = $resas1->fields['denominador'];
    } else {
      $numerador = 0;
      $denominador = 0;
    }
    if ($numerador != 0) {
      $total_perc = ($numerador * 100) / $denominador;
    } else {
      $total_perc = 0;
      if ($verifica == 'N05435' || $verifica == 'N20032') {
        $total_perc = 100;
      }
    }
    $meta = '60';
    if ($total_perc < 60) {
      $puntos = 0;
      $cumplido = 'NO';
    } else {
      $cumplido = 'SI';
      if ($total_perc >= 60 && $total_perc < 80) {
        if ($tipoefector == 'PSB' || $tipoefector == 'CSA' || $tipoefector == 'ALD') {
          $puntos = 6;
        }
        if ($tipoefector == 'HOS' || $tipoefector == 'HOS3') {
          $puntos = 1;
        }
      }
      if ($total_perc >= 80 && $total_perc < 100) {
        if ($tipoefector == 'PSB' || $tipoefector == 'CSA' || $tipoefector == 'ALD') {
          $puntos = 8;
        }
        if ($tipoefector == 'HOS' || $tipoefector == 'HOS3') {
          $puntos = 3;
        }
      }
      if ($total_perc >= 100) {
        if ($tipoefector == 'PSB' || $tipoefector == 'CSA' || $tipoefector == 'ALD') {
          $puntos = 10;
        }
        if ($tipoefector == 'HOS' || $tipoefector == 'HOS3') {
          $puntos = 5;
        }
      }
    }
    $SQLu2 = "insert into tmp_infometa (cuie,objetivo,asignado,informado,cumplido,puntos,mes,ano,total,expe,idc,orden,perc,meta) values('$verifica',1,$denominador,$numerador,'$cumplido',$puntos,'$mesp','$anop',$denominador,'$exp',$idc,1,$total_perc,$meta)";
    sql($SQLu2) or excepcion("Error, vuelva a intentarlo.");
  }
}

function objetivos_2_2010($anop, $mesp, $verifica, $tipoefector, $exp, $idc) {
  if ($tipoefector == 'HOS' || $tipoefector == 'HOS1' || $tipoefector == 'HOS3') {
    $rembar1 = "select numerador, denominador	from objetivos where mes='$mesp' and ano='$anop' and obj='2' and cuie='$verifica'";
    $resas1 = sql($rembar1) or excepcion("Error, vuelva a intentarlo.");
    if ($resas1->RecordCount() > 0) {
      $resas1->MoveFirst();
      $numerador = $resas1->fields['numerador'];
      $denominador = $resas1->fields['denominador'];
    } else {
      $numerador = 0;
      $denominador = 0;
    }

    if ($numerador != 0) {
      $total_perc = ($numerador * 100) / $denominador;
    } else {
      $total_perc = 0;
      if ($verifica == 'N05435' || $verifica == 'N20032') {
        $total_perc = 100;
      }
    }
    $meta = '70';
    if ($total_perc < 70) {
      $puntos = 0;
      $cumplido = 'NO';
    } else {
      $cumplido = 'SI';
      if ($tipoefector == 'HOS1' || $tipoefector == 'HOS3') {
        if ($total_perc >= 70 && $total_perc < 80) {
          $puntos = 2;
        }
        if ($total_perc >= 80 && $total_perc < 90) {
          $puntos = 3;
        }
        if ($total_perc >= 90 && $total_perc < 100) {
          $puntos = 4;
        }
        if ($total_perc >= 100) {
          $puntos = 5;
        }
      } else {
        if ($total_perc >= 60 && $total_perc < 80) {
          $puntos = 1;
        }
        if ($total_perc >= 80 && $total_perc < 100) {
          $puntos = 2;
        }
        if ($total_perc >= 100) {
          $puntos = 2.5;
        }
      }
    }
    $SQLu2 = "insert into tmp_infometa (cuie,objetivo,asignado,informado,cumplido,puntos,mes,ano,total,expe,idc,orden,perc,meta) values('$verifica',2,$denominador,$numerador,'$cumplido',$puntos,'$mesp','$anop',$denominador,'$exp',$idc,2,$total_perc,$meta)";
    sql($SQLu2) or excepcion("Error, vuelva a intentarlo.");
  }
}

function objetivos_3_2010($anop, $mesp, $verifica, $tipoefector, $exp, $idc) {
  if ($tipoefector == 'HOS' || $tipoefector == 'HOS1' || $tipoefector == 'HOS3') {
    $rembar1 = "select numerador, denominador from objetivos where mes='$mesp' and ano='$anop' and obj='3' and cuie='$verifica'";
    $resas1 = sql($rembar1) or excepcion("Error, vuelva a intentarlo.");
    if ($resas1->RecordCount() > 0) {
      $resas1->MoveFirst();
      $numerador = $resas1->fields['numerador'];
      $denominador = $resas1->fields['denominador'];
    } else {
      $numerador = 0;
      $denominador = 0;
    }
    if ($numerador != 0) {
      $total_perc = ($numerador * 100) / $denominador;
    } else {
      $total_perc = 0;
      if ($verifica == 'N05435' || $verifica == 'N20032') {
        $total_perc = 100;
      }
    }
    $meta = '70';
    if ($total_perc < 70) {
      $puntos = 0;
      $cumplido = 'NO';
    } else {
      $cumplido = 'SI';
      if ($tipoefector == 'HOS1' || $tipoefector == 'HOS3') {
        if ($total_perc >= 70 && $total_perc < 80) {
          $puntos = 2;
        }
        if ($total_perc >= 80 && $total_perc < 90) {
          $puntos = 3;
        }
        if ($total_perc >= 90 && $total_perc < 100) {
          $puntos = 4;
        }
        if ($total_perc >= 100) {
          $puntos = 5;
        }
      } else {
        if ($total_perc >= 60 && $total_perc < 80) {
          $puntos = 1;
        }
        if ($total_perc >= 80 && $total_perc < 100) {
          $puntos = 2;
        }
        if ($total_perc >= 100) {
          $puntos = 2.5;
        }
      }
    }
    $SQLu2 = "insert into tmp_infometa (cuie,objetivo,asignado,informado,cumplido,puntos,mes,ano,total,expe,idc,orden,perc,meta) values('$verifica',3,$denominador,$numerador,'$cumplido',$puntos,'$mesp','$anop',$denominador,'$exp',$idc,3,$total_perc,$meta)";
    sql($SQLu2) or excepcion("Error, vuelva a intentarlo.");
  }
}

function objetivos_4_2010($anop, $mesp, $verifica, $tipoefector, $exp, $idc) {
  if ($tipoefector == 'PSB' || $tipoefector == 'CSA' || $tipoefector == 'ALD' || $tipoefector ==
    'HOS' || $tipoefector == 'HOS1' || $tipoefector == 'HOS3') {
    $rembar1 = "select numerador, denominador from objetivos where mes='$mesp' and ano='$anop' and obj='4' and cuie='$verifica'";
    $resas1 = sql($rembar1) or excepcion("Error, vuelva a intentarlo.");
    if ($resas1->RecordCount() > 0) {
      $resas1->MoveFirst();
      $numerador = $resas1->fields['numerador'];
      $denominador = $resas1->fields['denominador'];
    } else {
      $numerador = 0;
      $denominador = 0;
    }
    if ($numerador != 0) {
      $total_perc = ($numerador * 100) / $denominador;
    } else {
      $total_perc = 0;
      if ($verifica == 'N05435' || $verifica == 'N20032') {
        $total_perc = 100;
      }
    }
    $meta = '60';
    if ($total_perc < 60) {
      $puntos = 0;
      $cumplido = 'NO';
    } else {
      $cumplido = 'SI';
      if ($total_perc >= 60 && $total_perc < 80) {
        if ($tipoefector == 'HOS1') {
          $puntos = 3;
        }
        if ($tipoefector == 'PSB' || $tipoefector == 'CSA' || $tipoefector == 'ALD') {
          $puntos = 6;
        }
        if ($tipoefector == 'HOS' || $tipoefector == 'HOS3') {
          $puntos = 1;
        }
      }
      if ($total_perc >= 80 && $total_perc < 100) {
        if ($tipoefector == 'HOS1') {
          $puntos = 4;
        }
        if ($tipoefector == 'PSB' || $tipoefector == 'CSA' || $tipoefector == 'ALD') {
          $puntos = 8;
        }
        if ($tipoefector == 'HOS' || $tipoefector == 'HOS3') {
          $puntos = 2;
        }
      }
      if ($total_perc >= 100) {
        if ($tipoefector == 'HOS1') {
          $puntos = 5;
        }
        if ($tipoefector == 'PSB' || $tipoefector == 'CSA' || $tipoefector == 'ALD') {
          $puntos = 10;
        }
        if ($tipoefector == 'HOS' || $tipoefector == 'HOS3') {
          $puntos = 2.5;
        }
      }
    }
    $SQLu2 = "insert into tmp_infometa (cuie,objetivo,asignado,informado,cumplido,puntos,mes,ano,total,expe,idc,orden,perc,meta) values('$verifica',4,$denominador,$numerador,'$cumplido',$puntos,'$mesp','$anop',$denominador,'$exp',$idc,4,$total_perc,$meta)";
    sql($SQLu2) or excepcion("Error, vuelva a intentarlo.");
  }
}

function objetivos_5_2010($anop, $mesp, $verifica, $tipoefector, $exp, $idc) {
  if ($tipoefector == 'HOS' || $tipoefector == 'HOS1' || $tipoefector == 'HOS3') {
    $rembar1 = "select numerador, denominador from objetivos where mes='$mesp' and ano='$anop' and obj='5' and cuie='$verifica'";
    $resas1 = sql($rembar1) or excepcion("Error, vuelva a intentarlo.");
    if ($resas1->RecordCount() > 0) {
      $resas1->MoveFirst();
      $numerador = $resas1->fields['numerador'];
      $denominador = $resas1->fields['denominador'];
    } else {
      $numerador = 0;
      $denominador = 0;
    }
    if ($numerador != 0) {
      $total_perc = ($numerador * 100) / $denominador;
    } else {
      $total_perc = 0;
      if ($verifica == 'N05435' || $verifica == 'N20032') {
        $total_perc = 100;
      }
    }
    $meta = '80';
    if ($total_perc < 80) {
      $puntos = 0;
      $cumplido = 'NO';
    } else {
      $cumplido = 'SI';
      if ($total_perc >= 80 && $total_perc < 90) {
        if ($tipoefector == 'HOS' || $tipoefector == 'HOS1' || $tipoefector == 'HOS3') {
          $puntos = 1;
        }
      }
      if ($total_perc >= 90 && $total_perc < 100) {
        if ($tipoefector == 'HOS' || $tipoefector == 'HOS3') {
          $puntos = 2;
        }
        if ($tipoefector == 'HOS1') {
          $puntos = 2.5;
        }
      }
      if ($total_perc >= 100) {
        if ($tipoefector == 'HOS' || $tipoefector == 'HOS3') {
          $puntos = 2.5;
        }
        if ($tipoefector == 'HOS1') {
          $puntos = 5;
        }
      }
    }
    $SQLu2 = "insert into tmp_infometa (cuie,objetivo,asignado,informado,cumplido,puntos,mes,ano,total,expe,idc,orden,perc,meta) values('$verifica',5,$denominador,$numerador,'$cumplido',$puntos,'$mesp','$anop',$denominador,'$exp',$idc,5,$total_perc,$meta)";
    sql($SQLu2) or excepcion("Error, vuelva a intentarlo.");
  }
}

function objetivos_6_2010($anop, $mesp, $verifica, $tipoefector, $exp, $idc) {
  if ($tipoefector == 'HOS' || $tipoefector == 'HOS1' || $tipoefector == 'HOS2' ||
    $tipoefector == 'HOS3') {
    $rembar1 = "select numerador, denominador from objetivos where mes='$mesp' and ano='$anop' and obj='6' and cuie='$verifica'";
    $resas1 = sql($rembar1) or excepcion("Error, vuelva a intentarlo.");
    if ($resas1->RecordCount() > 0) {
      $resas1->MoveFirst();
      $numerador = $resas1->fields['numerador'];
      $denominador = $resas1->fields['denominador'];
    } else {
      $numerador = 0;
      $denominador = 0;
    }
    if ($numerador != 0) {
      $total_perc = ($numerador * 100) / $denominador;
    } else {
      $total_perc = 0;
      if ($verifica == 'N05435' || $verifica == 'N20032') {
        $total_perc = 100;
      }
    }
    $meta = '100';
    if ($total_perc < 100) {
      $puntos = 0;
      $cumplido = 'NO';
    } else {
      $cumplido = 'SI';

      if ($total_perc >= 100) {
        if ($tipoefector == 'HOS1' || $tipoefector == 'HOS2' || $tipoefector == 'HOS3') {
          $puntos = 10;
        }
        if ($tipoefector == 'HOS') {
          $puntos = 5;
        }
      }
    }
    $SQLu2 = "insert into tmp_infometa (cuie,objetivo,asignado,informado,cumplido,puntos,mes,ano,total,expe,idc,orden,perc,meta) values('$verifica',6,$denominador,$numerador,'$cumplido',$puntos,'$mesp','$anop',$denominador,'$exp',$idc,6,$total_perc,$meta)";
    sql($SQLu2) or excepcion("Error, vuelva a intentarlo.");
  }
}

function objetivos_7_2010($anop, $mesp, $verifica, $tipoefector, $exp, $idc) {
  if ($tipoefector == 'PSB' || $tipoefector == 'CSA' || $tipoefector == 'ALD' || $tipoefector ==
    'HOS' || $tipoefector == 'HOS3') {
    $rembar1 = "select numerador, denominador from objetivos where mes='$mesp' and ano='$anop' and obj='7' and cuie='$verifica'";
    $resas1 = sql($rembar1) or excepcion("Error, vuelva a intentarlo.");
    if ($resas1->RecordCount() > 0) {
      $resas1->MoveFirst();
      $numerador = $resas1->fields['numerador'];
      $denominador = $resas1->fields['denominador'];
    } else {
      $numerador = 0;
      $denominador = 0;
    }
    if ($numerador != 0) {
      $total_perc = ($numerador * 100) / $denominador;
    } else {
      $total_perc = 0;
      if ($verifica == 'N05435' || $verifica == 'N20032') {
        $total_perc = 100;
      }
    }
    $meta = '90';
    if ($total_perc < 90) {
      $puntos = 0;
      $cumplido = 'NO';
    } else {
      $cumplido = 'SI';
      if ($total_perc >= 90 && $total_perc < 100) {
        if ($tipoefector == 'PSB' || $tipoefector == 'CSA' || $tipoefector == 'ALD') {
          $puntos = 5;
        }
        if ($tipoefector == 'HOS' || $tipoefector == 'HOS3') {
          $puntos = 2.5;
        }
      }
      if ($total_perc >= 100) {
        if ($tipoefector == 'PSB' || $tipoefector == 'CSA' || $tipoefector == 'ALD') {
          $puntos = 10;
        }
        if ($tipoefector == 'HOS' || $tipoefector == 'HOS3') {
          $puntos = 5;
        }
      }
    }
    $SQLu2 = "insert into tmp_infometa (cuie,objetivo,asignado,informado,cumplido,puntos,mes,ano,total,expe,idc,orden,perc,meta) values('$verifica',7,$denominador,$numerador,'$cumplido',$puntos,'$mesp','$anop',$denominador,'$exp',$idc,7,$total_perc,$meta)";
    sql($SQLu2) or excepcion("Error, vuelva a intentarlo.");
  }
}

function objetivos_8_2010($anop, $mesp, $verifica, $tipoefector, $exp, $idc) {
  if ($tipoefector == 'HOS' || $tipoefector == 'HOS1' || $tipoefector == 'HOS3') {
    $rembar1 = "select numerador, denominador from objetivos where mes='$mesp' and ano='$anop' and obj='8' and cuie='$verifica'";
    $resas1 = sql($rembar1) or excepcion("Error, vuelva a intentarlo.");
    if ($resas1->RecordCount() > 0) {
      $resas1->MoveFirst();
      $numerador = $resas1->fields['numerador'];
      $denominador = $resas1->fields['denominador'];
    }
  } else {
    $numerador = 0;
    $denominador = 0;
  }
  $meta = '90';
  if ($numerador != 0) {
    $total_perc = ($numerador * 100) / $denominador;
  } else {
    $total_perc = 0;
    if ($verifica == 'N05435' || $verifica == 'N20032') {
      $total_perc = 100;
    }
  }
  if ($total_perc < 90) {
    $puntos = 0;
    $cumplido = 'NO';
  } else {
    $cumplido = 'SI';
    if ($total_perc >= 90 && $total_perc < 100) {
      $puntos = 2.5;
    }
    if ($total_perc >= 100) {
      $puntos = 5;
    }
  }
  $SQLu2 = "insert into tmp_infometa (cuie,objetivo,asignado,informado,cumplido,puntos,mes,ano,total,expe,idc,orden,perc,meta) values('$verifica',8,$denominador,$numerador,'$cumplido',$puntos,'$mesp','$anop',$denominador,'$exp',$idc,8,$total_perc,$meta)";
  sql($SQLu2) or excepcion("Error, vuelva a intentarlo.");
}

function objetivos_9_2010($anop, $mesp, $verifica, $tipoefector, $exp, $idc) {
  if ($tipoefector == 'PSB' || $tipoefector == 'CSA' || $tipoefector == 'ALD' || $tipoefector ==
    'HOS' || $tipoefector == 'HOS2') {
    $rembar1 = "select numerador, denominador from objetivos where mes='$mesp' and ano='$anop' and obj='9' and cuie='$verifica'";
    $resas1 = sql($rembar1) or excepcion("Error, vuelva a intentarlo.");
    if ($resas1->RecordCount() > 0) {
      $resas1->MoveFirst();
      $numerador = $resas1->fields['numerador'];
      $denominador = $resas1->fields['denominador'];
    } else {
      $numerador = 0;
      $denominador = 0;
    }
    if ($numerador != 0) {
      $total_perc = ($numerador * 100) / $denominador;
    } else {
      $total_perc = 0;
      if ($verifica == 'N05435' || $verifica == 'N20032') {
        $total_perc = 100;
      }
    }
    $meta = '60';
    if ($total_perc < 60) {
      $puntos = 0;
      $cumplido = 'NO';
    } else {
      $cumplido = 'SI';
      if ($total_perc >= 60 && $total_perc < 80) {
        if ($tipoefector == 'PSB' || $tipoefector == 'CSA' || $tipoefector == 'ALD' || $tipoefector ==
          'HOS') {
          $puntos = 1.5;
        }
        if ($tipoefector == 'HOS2') {
          $puntos = 5;
        }
      }
      if ($total_perc >= 80 && $total_perc < 100) {
        if ($tipoefector == 'PSB' || $tipoefector == 'CSA' || $tipoefector == 'ALD' || $tipoefector ==
          'HOS') {
          $puntos = 3;
        }
        if ($tipoefector == 'HOS2') {
          $puntos = 10;
        }
      }
      if ($total_perc >= 100) {
        if ($tipoefector == 'PSB' || $tipoefector == 'CSA' || $tipoefector == 'ALD' || $tipoefector ==
          'HOS') {
          $puntos = 5;
        }
        if ($tipoefector == 'HOS2') {
          $puntos = 15;
        }
      }
    }
    $SQLu2 = "insert into tmp_infometa (cuie,objetivo,asignado,informado,cumplido,puntos,mes,ano,total,expe,idc,orden,perc,meta) values('$verifica',9,$denominador,$numerador,'$cumplido',$puntos,'$mesp','$anop',$denominador,'$exp',$idc,9,$total_perc,$meta)";
    sql($SQLu2) or excepcion("Error, vuelva a intentarlo.");
  }
}

function objetivos_10_2010($anop, $mesp, $verifica, $tipoefector, $exp, $idc) {
  if ($tipoefector == 'PSB' || $tipoefector == 'CSA' || $tipoefector == 'ALD' || $tipoefector ==
    'HOS' || $tipoefector == 'HOS2') {
    $rembar1 = "select numerador, denominador from objetivos where mes='$mesp' and ano='$anop' and obj='10' and cuie='$verifica'";
    $resas1 = sql($rembar1) or excepcion("Error, vuelva a intentarlo.");
    ;
    if ($resas1->RecordCount() > 0) {
      $resas1->MoveFirst();
      $numerador = $resas1->fields['numerador'];
      $denominador = $resas1->fields['denominador'];
    } else {
      $numerador = 0;
      $denominador = 0;
    }
    if ($numerador != 0) {
      $total_perc = ($numerador * 100) / $denominador;
    } else {
      $total_perc = 0;
      if ($verifica == 'N05435' || $verifica == 'N20032') {
        $total_perc = 100;
      }
    }
    $meta = '60';
    if ($total_perc < 60) {
      $puntos = 0;
      $cumplido = 'NO';
    } else {
      $cumplido = 'SI';
      if ($total_perc >= 60 && $total_perc < 80) {
        if ($tipoefector == 'PSB' || $tipoefector == 'CSA' || $tipoefector == 'ALD' || $tipoefector ==
          'HOS') {
          $puntos = 1.5;
        }
        if ($tipoefector == 'HOS2') {
          $puntos = 5;
        }
      }
      if ($total_perc >= 80 && $total_perc < 100) {
        if ($tipoefector == 'PSB' || $tipoefector == 'CSA' || $tipoefector == 'ALD' || $tipoefector ==
          'HOS') {
          $puntos = 3;
        }
        if ($tipoefector == 'HOS2') {
          $puntos = 10;
        }
      }
      if ($total_perc >= 100) {
        if ($tipoefector == 'PSB' || $tipoefector == 'CSA' || $tipoefector == 'ALD' || $tipoefector ==
          'HOS') {
          $puntos = 5;
        }
        if ($tipoefector == 'HOS2') {
          $puntos = 15;
        }
      }
    }
    $SQLu2 = "insert into tmp_infometa (cuie,objetivo,asignado,informado,cumplido,puntos,mes,ano,total,expe,idc,orden,perc,meta) values('$verifica',10,$denominador,$numerador,'$cumplido',$puntos,'$mesp','$anop',$denominador,'$exp',$idc,10,$total_perc,$meta)";
    sql($SQLu2) or excepcion("Error, vuelva a intentarlo.");
  }
}

function mostrar_estimulos($ano, $mes, $cuie, $sum_mont_est) {
  $monto_p = $sum_mont_est;
  if ($tipoefector != 'ADM' && $tipoefector != 'LAB' && $tipoefector != 'TRN') {

    $tef_otrokpz = 0;
    $tes_otrokpz = 0;
    $tes_otrokpz = ($sum_mont_est * 10) / 100;
    $t_paraefectot = ($sum_mont_est * 50) / 100;
    $bandera = 0;
    $sum_ptos = 0;
    $t_sum_ptos = 0;

    if ($ano >= '2010') {
      $titulo_obj = 'Cumplimiento';
      $embarazadas = "SELECT a.objetivo,a.asignado,a.informado,a.cumplido,a.puntos,a.perc as Cumplimiento,a.meta
FROM  facturacion.tmp_infometa a where a.cuie='$cuie' and  a.mes='$mes' and a.ano='$ano' order by orden";
    } else {
      $titulo_obj = 'META';
      $embarazadas = "SELECT a.objetivo,a.asignado,a.informado,a.cumplido,c.punto as puntos,b.meta
FROM  [10tmp_infometa] a
inner join [10metas] b on a.objetivo=b.obj  and b.mes=a.mes and b.ano=a.ano
inner join [10puntos] c on a.objetivo=c.obj and c.mes=a.mes and c.ano=a.ano
where (a.cuie='$cuies' or (a.departamento='$iddepartamento' and a.municipio='$idmunicipio') )
and  a.mes='$mes' and a.ano='$ano' and c.tipo='$tipoefector' 
order by orden";
    }
    $rembarazadas = sql($embarazadas);

    if ($rembarazadas->recordCount() > 0) {
?> 
      <table cellspacing="0" cellpadding="0" width="61%" align="center" border="0">
        <tr align="center">
          <td>
            <font size="1"><b>Metas cumplidas en el mes de&nbsp;<?
      //echo mesletra($mes);
?></b></font></td>
  </tr> </table>
<table cellspacing="0" cellpadding="0" width="61%" align="center" border="0"> 
  <tr align="center">

    <td width="280"><font size="1"><b>&nbsp;OBJETIVOS</b></font></td>

    <td width="50"><font size="1"><b>&nbsp;META</b></font></td>

    <td width="110"><font size="1"><b>&nbsp;<?php
          echo $titulo_obj;
?></b></font></td>

    <td width="80"><font size="1"><b>&nbsp;INFORMADO</b></font></td>

    <td width="80"><font size="1"><b>&nbsp;CUMPLIDO</b></font></td>

    <td width="60"><font size="1"><b>&nbsp;PUNTOS</b></font></td>

  </tr> </table>
<table cellspacing="0" cellpadding="0" width="61%" align="center" border="0"> 
  <tr align="center">

    <td width="290"><font size="1"><b>&nbsp;</b></font></td>

    <td width="50"><font size="1"><b>&nbsp;%</b></font></td>

    <td width="50"><font size="1"><b>&nbsp;%</b></font></td>

    <td width="60"><font size="1"><b>&nbsp;Abs</b></font></td>

    <td width="80"><font size="1"><b>&nbsp;</b></font></td>

    <td width="80"><font size="1"><b>&nbsp;</b></font></td>

    <td width="60"><font size="1"><b>&nbsp;</b></font></td>

  </tr></table>
<table cellspacing="0" cellpadding="0" width="61%" align="center" border="1%"> <tr>
    <?php
          //$fembarazadas = sql($rembarazadas);
          $rembarazadas->moveFirst();
          while (!$rembarazadas->EOF) {
            $monto_estim = 0;
            $bandera = 12;
            //print_r($rembarazadas);
            $obj = $rembarazadas->fields["objetivo"];
            if (($obj == 11 && $tipoefector != 'HOS1' && $tipoefector != 'HOS2' && $tipoefector !=
              'HOS3') || ($obj >= 1 && $obj <= 10)) {
              $asignado = $rembarazadas->fields["asignado"];
              $informado = $rembarazadas->fields["informado"];
              $cumplido = $rembarazadas->fields["cumplido"];
              $puntos = $rembarazadas->fields["puntos"];
              $Cumplimiento = $rembarazadas->fields["cumplimiento"];
              $meta = $rembarazadas->fields["meta"];
              echo '<td width="290"><font size="1"><b>';
              if ($obj == 1 && $ano >= '2010') {
                echo 'Captacion embarazada';
              }
              if ($obj == 2 && $ano >= '2010') {
                echo 'Apgar´>5';
              }
              if ($obj == 3 && $ano >= '2010') {
                echo 'Peso al nacer >2500grs.';
              }
              if ($obj == 4 && $ano >= '2010') {
                echo 'VDRL y ATT en embarazo';
              }
              if ($obj == 5 && $ano >= '2010') {
                echo 'VDRL y ATT previa al parto';
              }
              if ($obj == 6 && $ano >= '2010') {
                echo 'Atencion de muertes materno/infantiles';
              }
              if ($obj == 7 && $ano >= '2010') {
                echo 'Cob. Inmunizaciones';
              }
              if ($obj == 8 && $ano >= '2010') {
                echo 'Conserjeria';
              }
              if ($obj == 9 && $ano >= '2010') {
                echo 'Seguimiento de niño < 1 año';
              }
              if ($obj == 10 && $ano >= '2010') {
                echo 'Seguimiento de niño >= 1 año';
              }

              echo '</b></font></td><td align="center"  width="50"><font size="1">&nbsp;' .
              number_format($meta, 2, ".", ",") . '</font></td>
				<td align="center"  width="50"><font size="1">&nbsp;' . number_format($Cumplimiento,
                2, ".", ",") . '</font></td>
				<td align="center"  width="60"><font size="1">' . $asignado . '</font></td>
				   <td align="center" width="80"><font size="1">' . $informado .
              '</font></td>
				   <td align="center" width="80"><font size="1">' . $cumplido .
              '</font></td>
				   <td align="center" width="60"><font size="1">' . $puntos .
              '</font></td></tr>';
              /* $monto_estim=($sum_mont_est*$puntos)/100;
                $tes_otrokpz=$tes_otrokpz+$monto_estim; */
              if ($cumplido == 'SI') {
                $sum_ptos = $sum_ptos + $puntos;
              }
            }
            $rembarazadas->moveNext();
          }
        }
        //mssql_free_result($rembarazadas);
        if ($bandera != 0) {
    ?>
        <tr><td colspan="6" align="right"><font size="1"><b>Total Puntos&nbsp;&nbsp;&nbsp;&nbsp;</b></font></td><td align="center"><font size="1"><b>
          <?php echo $sum_ptos + 10; ?>
        </b></font></td></tr></table><br><br>
<?php
        }
        if ($sum_ptos < 40) {
          $t_sum_ptos = 40 - $sum_ptos;
        }
        $tes_otrokpz = $tes_otrokpz + (($sum_mont_est * $sum_ptos) / 100);
        $tef_otrokpz = $t_paraefectot + (($sum_mont_est * $t_sum_ptos) / 100);

        if ($bandera > 0) {
          if (rtrim($codExpediente) == '6124-037-11') {
            $tef_otrokpz = $tef_otrokpz - 603.649;
            $tes_otrokpz = $tes_otrokpz + 603.649;
          }
?>
          <CENTER><font size="1"><b>&nbsp;<?php
          //corrije($efectorx);
?></b></font></CENTER>
    <table cellspacing="0" cellpadding="0"  align="center" border="1">
      <tr align="center">
        <td><font size="1"><b>Total a Pagar</b></font></td>
        <td><font size="1"><b>Fondos para el Efector</b></font></td>
        <td><font size="1"><b>Fondos para Estimulos</b></font></td></tr>
      <tr align="center">
        <td><font size="1">$<?
          echo number_format($sum_mont_est, 3, ".", ",");
?></font></td>
      <td><font size="1">$<?
          echo number_format($tef_otrokpz, 3, ".", ",");
?></font></td>
      <td><font size="1">$<?
          echo number_format($tes_otrokpz, 3, ".", ",");
?></font></td>
    </tr></table>

<?php
        }
      } else {
        if ($qvigencia <= 4) {
          $tef_otrokpz = ($monto_p * 50) / 100;
          $tes_otrokpz = ($monto_p * 50) / 100;
?>
          <CENTER><font size="1"><b>&nbsp;<?
          corrije($efectorx);
?></b></font></CENTER>
    <table cellspacing="0" cellpadding="0"  align="center" border="1">
      <tr align="center">
        <td><font size="1"><b>Total a Pagar</b></font></td>
        <td><font size="1"><b>Fondos para el Efector</b></font></td>
        <td><font size="1"><b>Fondos para Estimulos</b></font></td></tr>
      <tr align="center">
        <td><font size="1">$<?
          echo number_format($monto_p, 3, ".", ",");
?></font></td>
      <td><font size="1">$<?
          echo number_format($tef_otrokpz, 3, ".", ",");
?></font></td>
      <td><font size="1">$<?
          echo number_format($tes_otrokpz, 3, ".", ",");
?></font></td>
    </tr></table>
<?
        }
      }
    }
?>