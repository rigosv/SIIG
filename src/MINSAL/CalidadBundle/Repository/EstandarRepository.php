<?php

namespace MINSAL\CalidadBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * EstandarRepository
 *
 */
class EstandarRepository extends EntityRepository {
    /**
     * 
     * @param text $establecimiento, código de estructura
     * @param text $periodo, anio_mes
     * @param type $formulario, codigo del formulario
     * @return type
     */
    public function getCriterios($establecimiento, $periodo, $formulario) {
        $em = $this->getEntityManager();        
        return $em->getRepository('GridFormBundle:Formulario')->getCriterios($establecimiento, $periodo, $formulario);
    }

}
