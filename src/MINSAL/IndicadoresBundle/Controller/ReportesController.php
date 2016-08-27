<?php

namespace MINSAL\IndicadoresBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


/**
* @Route("/reportes")
*/
class ReportesController extends Controller {

    /**
     * @Route("/matriz_seguimiento", name="matriz-seguimiento")
     */
    public function matrizSeguimientoAccion() {
        $admin_pool = $this->get('sonata.admin.pool');
        
        $em = $this->getDoctrine()->getManager();
        
        $Frm = $em->find('GridFormBundle:Formulario', 12);
        $datosFrm = $em->getRepository('GridFormBundle:Formulario')->getDatosRAW($Frm);

        $params = array('2015'=>array('01', '02', '03'), '2016'=>array('01','02','03','04','05'));
        $anios_ = array();
        $idOrigenesR = array(array('id'=>131, 'descripcion'=>'Embarazadas captadas antes 12 semanas del total esperado', 
                                                'codigo'=>'emb_cap_12_sema_tot_espe', 'acumular'=>true
                                    )
                            );
        foreach ($idOrigenesR as $var){
            $sql = "SELECT anio::integer, mes::varchar, id_mes::integer, SUM(calculo::numeric) as calculo FROM 
                       (SELECT datos->'anio' as anio, datos->'id_mes' as id_mes,
                            'cant_mensual_calidad_'||lpad(datos->'id_mes', 2, '0') mes, 
                            datos->'calculo' AS calculo 
                        FROM origenes.fila_origen_dato_$var[id] 
                        WHERE datos->'calculo' != ''
                        ) AS A  
                    GROUP BY anio::integer, mes::varchar, id_mes::integer ";
            if ($var['acumular']){
                $sql = "SELECT anio, mes, (SELECT SUM(calculo) FROM ($sql) AS BB WHERE BB.id_mes <= AC.id_mes and BB.anio = AC.anio) AS calculo
                            FROM ($sql) AS AC ";
            }
            $datos = $em->getConnection()->executeQuery($sql)->fetchAll();
            
            foreach ($datos as $d){                
                $anios_[$d['anio']]['anio'] = $d['anio'];
                $anios_[$d['anio']]['codigo_variable'] = $var['codigo'];
                $anios_[$d['anio']]['descripcion_variable'] = $var['descripcion'];
                $anios_[$d['anio']][$d['mes']] = $d['calculo'];
            }
            
        }
        
        //Datos fijos
        $idOrigenesFijosP = array(
                                array('descripcion'=>'Embarazadas captadas antes 12 semanas del total esperado', 'codigo'=>'emb_cap_12_sema_tot_espe',
                                        'datos'=>array(
                                                    array('anio'=>2016, 'mes'=> 'cant_mensual_calidad_01_p', 'calculo'=>200),
                                                    array('anio'=>2016, 'mes'=> 'cant_mensual_calidad_02_p', 'calculo'=>415),
                                                    array('anio'=>2016, 'mes'=> 'cant_mensual_calidad_03_p', 'calculo'=>646)
                                                )
                                    )
                            );
        foreach ($idOrigenesFijosP as $var){
            foreach ($var['datos'] as $d){                
                $anios_[$d['anio']]['anio'] = $d['anio'];
                $anios_[$d['anio']]['codigo_variable'] = $var['codigo'];
                $anios_[$d['anio']]['descripcion_variable'] = $var['descripcion'];
                $anios_[$d['anio']][$d['mes']] = $d['calculo'];
            }
        }
        
        
        foreach ($anios_ as $a){
            array_push($datosFrm, $a);
        }
        $datos_ = array();
        foreach ($datosFrm as $f){
            if (array_key_exists($f['anio'], $params)){
                
                foreach ($f as $k => $sf){
                    $mesVarR = str_replace('cant_mensual_calidad_', '', $k);
                    if (in_array($mesVarR, $params[$f['anio']])){
                        $datos_['real'][$mesVarR.'/'.$f['anio']] = $sf;
                    } else {
                        $mesVarP = str_replace('_p', '', str_replace('cant_mensual_calidad_', '', $k));
                        if (in_array($mesVarP, $params[$f['anio']])){
                            $datos_['planificado'][$mesVarP.'/'.$f['anio']] = $sf;
                        }
                    }
                }
                if (count($datos_['planificado']) > 0){
                    foreach ($datos_['planificado'] as $k=>$v){
                        $datos_['estatus'][$k] = ($v > 0) ? number_format(($datos_['real'][$k] / $v) * 100,0) : null; 
                    }
                    $datosFrmFormat[$f['codigo_variable']]['datos']= $datos_;
                    $datosFrmFormat[$f['codigo_variable']]['descripcion'] = $f['descripcion_variable'];
                    //$datosFrmFormat[$f['codigo_variable']]['categoria'] = $f['descripcion_categoria_variable'];
                }
            }
        }

        return $this->render('IndicadoresBundle:Reportes:matrizSeguimiento.html.twig', 
                                array(
                                    'admin_pool' => $admin_pool,
                                    'datosFrm' => $datosFrmFormat,
                                    'parms' => $params
                                ));
    }
}
