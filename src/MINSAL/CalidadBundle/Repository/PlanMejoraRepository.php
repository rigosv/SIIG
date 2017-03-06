<?php

namespace MINSAL\IndicadoresBundle\Repository;

use Doctrine\ORM\EntityRepository;
use MINSAL\CalidadBundle\Entity\PlanMejora;

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
        //Crear tabla temporal para agregar los criterios
        $sql = "";
        
        //Guardar los criterios en la tabla temporal
        $sql = "";
        
        //Insertar en la tabla criterios aquellos nuevos y actualizar los que ya existen
        $sql = "";
    }

}
