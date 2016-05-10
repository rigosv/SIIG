<?php

namespace MINSAL\GridFormBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * MINSAL\GridFormBundle\Entity\EvaluacionExterna
 *
 * @ORM\Table(name="evaluacion_externa")
 * @UniqueEntity(fields={"establecimiento", "tipoEvaluacion", "anio"}, message="Código ya existe")
 * @ORM\Entity
 */
class EvaluacionExterna
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @var string $anio
     * @ORM\Column(name="anio", type="integer",  nullable=false)
     */
    private $anio;
        

    /**
     * @var string $valor
     *
     * @ORM\Column(name="valor", type="float", nullable=false)
     */
    private $valor;
    
    /**
     * @ORM\ManyToOne(targetEntity="EvaluacionExternaTipo")
     * */
    private $tipoEvaluacion;

    /**
     * @ORM\ManyToOne(targetEntity="MINSAL\CostosBundle\Entity\Estructura")
     * */
    private $establecimiento;

    /**
     * Set anio
     *
     * @param integer $anio
     * @return EvaluacionExterna
     */
    public function setAnio($anio)
    {
        $this->anio = $anio;

        return $this;
    }

    /**
     * Get anio
     *
     * @return integer 
     */
    public function getAnio()
    {
        return $this->anio;
    }

    /**
     * Set valor
     *
     * @param float $valor
     * @return EvaluacionExterna
     */
    public function setValor($valor)
    {
        $this->valor = $valor;

        return $this;
    }

    /**
     * Get valor
     *
     * @return float 
     */
    public function getValor()
    {
        return $this->valor;
    }

    /**
     * Set tipoEvaluacion
     *
     * @param \MINSAL\GridFormBundle\Entity\EvaluacionExternaTipo $tipoEvaluacion
     * @return EvaluacionExterna
     */
    public function setTipoEvaluacion(\MINSAL\GridFormBundle\Entity\EvaluacionExternaTipo $tipoEvaluacion = null)
    {
        $this->tipoEvaluacion = $tipoEvaluacion;

        return $this;
    }

    /**
     * Get tipoEvaluacion
     *
     * @return \MINSAL\GridFormBundle\Entity\EvaluacionExternaTipo 
     */
    public function getTipoEvaluacion()
    {
        return $this->tipoEvaluacion;
    }

    /**
     * Set establecimiento
     *
     * @param \MINSAL\CostosBundle\Entity\Estructura $establecimiento
     * @return EvaluacionExterna
     */
    public function setEstablecimiento(\MINSAL\CostosBundle\Entity\Estructura $establecimiento = null)
    {
        $this->establecimiento = $establecimiento;

        return $this;
    }

    /**
     * Get establecimiento
     *
     * @return \MINSAL\CostosBundle\Entity\Estructura 
     */
    public function getEstablecimiento()
    {
        return $this->establecimiento;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
}
