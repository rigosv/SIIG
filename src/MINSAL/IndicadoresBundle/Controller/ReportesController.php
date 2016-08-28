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
        
        //Obtener todos los datos del webform 12
        $Frm = $em->find('GridFormBundle:Formulario', 12);
        $datosFrm = $em->getRepository('GridFormBundle:Formulario')->getDatosRAW($Frm);
        
        //Parámetros fijos por el momento, luego hacer el formulario para perdir periodo de inicio y fin del reporte
        $params = array('2015'=>array('01', '02', '03'), '2016'=>array('01','02','03','04','05'));
        $params2 = array();
//        foreach ($params)
        
        $anios_ = array();
        
        //Información de los datos del NUMERADOR, obtenidos de orígenes de datos del etab
        $idOrigenesR = array(array('id'=>131, 'descripcion'=>'Embarazadas captadas antes 12 semanas del total esperado', 
                                    'codigo'=>'emb_cap_12_sema_tot_espe', 'acumular'=>true),
                            array('id'=>193, 'descripcion'=>'# de parto institucional atendidos por todos los prestadores de salud en los 14 municipios (medico/enfermera)', 
                                    'codigo'=>'partos_por_personal_calificado', 'acumular'=>true)
                            );
        foreach ($idOrigenesR as $varR){
            $anios_[] = $this->getDatosFormateados($varR, 'real');
        }
        
        //Información de los datos del DENOMINADOR, obtenidos de orígenes de datos del etab
        $idOrigenesP = array(array('id'=>192, 'descripcion'=>'# de parto institucional atendidos por todos los prestadores de salud en los 14 municipios (medico/enfermera)', 
                                    'codigo'=>'partos_por_personal_calificado', 'acumular'=>true)
                            );
        foreach ($idOrigenesP as $varP){
            $anios_[] = $this->getDatosFormateados($varP, 'planificado');
        }
        
        //Datos fijos
        $idOrigenesFijosP = array(
                                array('descripcion'=>'Embarazadas captadas antes 12 semanas del total esperado', 
                                        'codigo'=>'emb_cap_12_sema_tot_espe',
                                        'datos'=>array(
                                                    array('anio'=>2016, 'mes'=> 'cant_mensual_calidad_01_p', 'calculo'=>200),
                                                    array('anio'=>2016, 'mes'=> 'cant_mensual_calidad_02_p', 'calculo'=>415),
                                                    array('anio'=>2016, 'mes'=> 'cant_mensual_calidad_03_p', 'calculo'=>646),
                                                    array('anio'=>2016, 'mes'=> 'cant_mensual_calidad_04_p', 'calculo'=>895),
                                                    array('anio'=>2016, 'mes'=> 'cant_mensual_calidad_05_p', 'calculo'=>1162),
                                                    array('anio'=>2016, 'mes'=> 'cant_mensual_calidad_06_p', 'calculo'=>1449),
                                                    array('anio'=>2016, 'mes'=> 'cant_mensual_calidad_07_p', 'calculo'=>1757),
                                                    array('anio'=>2016, 'mes'=> 'cant_mensual_calidad_08_p', 'calculo'=>2089),
                                                    array('anio'=>2016, 'mes'=> 'cant_mensual_calidad_09_p', 'calculo'=>2446),
                                                    array('anio'=>2016, 'mes'=> 'cant_mensual_calidad_10_p', 'calculo'=>2829),
                                                    array('anio'=>2016, 'mes'=> 'cant_mensual_calidad_11_p', 'calculo'=>3242),
                                                    array('anio'=>2016, 'mes'=> 'cant_mensual_calidad_12_p', 'calculo'=>3620)                                            
                                                )
                                    ),
                                array('descripcion'=>'Número de usuarias activas captadas para métodos de PF (anual)', 
                                        'codigo'=>'usu_act_captadas_pf',
                                        'datos'=>array(
                                                    array('anio'=>2016, 'mes'=> 'cant_mensual_calidad_01_p', 'calculo'=>2154),
                                                    array('anio'=>2016, 'mes'=> 'cant_mensual_calidad_02_p', 'calculo'=>4154),
                                                    array('anio'=>2016, 'mes'=> 'cant_mensual_calidad_03_p', 'calculo'=>6154),
                                                    array('anio'=>2016, 'mes'=> 'cant_mensual_calidad_04_p', 'calculo'=>8654),
                                                    array('anio'=>2016, 'mes'=> 'cant_mensual_calidad_05_p', 'calculo'=>11654),
                                                    array('anio'=>2016, 'mes'=> 'cant_mensual_calidad_06_p', 'calculo'=>15154),
                                                    array('anio'=>2016, 'mes'=> 'cant_mensual_calidad_07_p', 'calculo'=>19154),
                                                    array('anio'=>2016, 'mes'=> 'cant_mensual_calidad_08_p', 'calculo'=>23654),
                                                    array('anio'=>2016, 'mes'=> 'cant_mensual_calidad_09_p', 'calculo'=>28654),
                                                    array('anio'=>2016, 'mes'=> 'cant_mensual_calidad_10_p', 'calculo'=>34154),
                                                    array('anio'=>2016, 'mes'=> 'cant_mensual_calidad_11_p', 'calculo'=>37154),
                                                    array('anio'=>2016, 'mes'=> 'cant_mensual_calidad_12_p', 'calculo'=>40531)                                            
                                                )
                                    )
                            );
        											

        foreach ($idOrigenesFijosP as $var){
            $anios_[] = $this->formatearDatos($var['datos'], $var); 
        }
        
        foreach ($anios_ as $aa){
            foreach ($aa as $a){
                array_push($datosFrm, $a);
            }
        }
        
        
        $datos_ = array();
        foreach ($datosFrm as $f){
            if (array_key_exists($f['anio'], $params)){
                
                foreach ($f as $k => $sf){
                    $mesVarR = str_replace('cant_mensual_calidad_', '', $k);
                    if (in_array($mesVarR, $params[$f['anio']])){
                        $datos_[$f['codigo_variable']]['real'][$mesVarR.'/'.$f['anio']] = $sf;
                    } else {
                        $mesVarP = str_replace('_p', '', str_replace('cant_mensual_calidad_', '', $k));
                        if (in_array($mesVarP, $params[$f['anio']])){
                            $datos_[$f['codigo_variable']]['planificado'][$mesVarP.'/'.$f['anio']] = $sf;
                        }
                    }
                }
                if (array_key_exists('planificado', $datos_[$f['codigo_variable']])){
                    foreach ($datos_[$f['codigo_variable']]['planificado'] as $k=>$v){
                        $datos_[$f['codigo_variable']]['estatus'][$k] = ($v > 0) ? 
                        array_key_exists('real', $datos_[$f['codigo_variable']]) ? 
                                number_format(($datos_[$f['codigo_variable']]['real'][$k] / $v) * 100,0): null : 
                                null; 
                    }
                    $datosFrmFormat[$f['codigo_variable']]['datos']= $datos_[$f['codigo_variable']];
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
    
    protected function getDatosFormateados($var,  $tipo = null) {
        $em = $this->getDoctrine()->getManager();
        
        $planf = ($tipo=='planificado') ? "||'_p'" : '';
        
        $sql = "SELECT anio::integer, mes::varchar, id_mes::integer, SUM(calculo::numeric) AS calculo FROM 
                       (SELECT datos->'anio' as anio, datos->'id_mes' AS id_mes,
                            'cant_mensual_calidad_'||lpad(datos->'id_mes', 2, '0')$planf AS mes, 
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

        return $this->formatearDatos($datos, $var);
    }
    
    private function formatearDatos($datos, $var) {
        $resp = array();
        foreach ($datos as $d){
            $resp[$d['anio']]['anio'] = $d['anio'];
            $resp[$d['anio']]['codigo_variable'] = $var['codigo'];
            $resp[$d['anio']]['descripcion_variable'] = $var['descripcion'];
            $resp[$d['anio']][$d['mes']] = $d['calculo'];
        }
        
        return $resp;
    }
}
