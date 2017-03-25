<?php

namespace MINSAL\CalidadBundle\Repository;

use Doctrine\ORM\EntityRepository;
use MINSAL\CalidadBundle\Entity\PlanMejora;
use MINSAL\CalidadBundle\Entity\Criterio;
use MINSAL\CalidadBundle\Entity\Prioridad;

/**
 * PlanMejoraRepository
 *
 */
class PlanMejoraRepository extends EntityRepository {

    /**
     * 
     * @param PlanMejora $planMejora
     * @param mixed $criterios, criterios a agregar al plan de mejora
     */
    public function agregarCriterios(PlanMejora $planMejora, $criterios) {
        $em = $this->getEntityManager();
        
        foreach ($criterios as $c){
            //Verificar si el criterio ya fue agregado
            $variableCaptura = $em->getRepository('GridFormBundle:VariableCaptura')->findOneBy(array('codigo'=>$c['codigo_variable']));
            $criterio_check = $em->getRepository("CalidadBundle:Criterio")->findOneBy(array('planMejora'=>$planMejora, 'variableCaptura' =>$variableCaptura));

            if ($criterio_check === null){
                //Si no está agregarlo
                $nCriterio = new Criterio();
                $nCriterio->setPlanMejora($planMejora);
                $nCriterio->setVariableCaptura($variableCaptura);
                
                $criterio_check = $nCriterio;
            }
            
            $criterio_check->setBrecha($c['brecha']);
            //Calcular la prioridad de acuerdo a la brecha
            $sql = "SELECT numero_rango FROM rangos_alertas_generales WHERE  $c[brecha] BETWEEN limite_inferior AND limite_superior ";
            $prioridadEval = $em->getConnection()->executeQuery($sql)->fetchAll();
            
            //Prioridad baja por defecto
            $codigoPrioridad = 'alta';
            if (count($prioridadEval) > 0 ){
                switch ($prioridadEval[0]['numero_rango']) {
                    case '1':
                        $codigoPrioridad = 'baja';
                    break;
                    case '2':
                        $codigoPrioridad = 'media';
                    break;
                }
            }
                
            $prioridad = $em->getRepository('CalidadBundle:Prioridad')->findOneBy(array('codigo'=>$codigoPrioridad));
            $criterio_check->setPrioridad($prioridad);
            $em->persist($criterio_check);
            
        }
        
        $em->flush();
        
    }
    
    public function getCriteriosOrden(PlanMejora $planMejora) {
        
        $ind = '';
        $ord = '';
        if ($planMejora->getEstandar()->getFormaEvaluacion() == 'rango_colores'){
            $ind = ' INNER JOIN V.area AR ';
            $ord = ' V.area, ';
        }
        $criterios =  $this->getEntityManager()
            ->createQuery(
                "SELECT C
                    FROM CalidadBundle:Criterio C
                    INNER JOIN C.variableCaptura V
                    $ind
                    WHERE C.planMejora = :plan
                    ORDER BY $ord V.posicion ASC
                    "
            )
            ->setParameters(array('plan'=>$planMejora))
            ->getResult();
        
        return $criterios;
                
    }

}
