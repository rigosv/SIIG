<?php

namespace MINSAL\GridFormBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * MINSAL\GridFormBundle\Entity\PlanIntervencionActividad
 *
 * @ORM\Table(name="plan_intervencion_actividad")
 * @ORM\Entity
 */
class PlanIntervencionActividad
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
     * @var string $fechaCumplimiento
     *
     * @ORM\Column(name="fecha_cumplimiento", type="date", nullable=false)
     */
    private $fechaCumplimiento;
        

    /**
     * @var string $situacionEncontrada
     *
     * @ORM\Column(name="descripcion", type="text", nullable=false)
     */
    private $descripcion;
    
    /**
     * @var string $responsable
     *
     * @ORM\Column(name="responsable", type="string", length=250, nullable=false)
     */
    private $responsable;
    
    /**
     * @var string $fecha_evaluacion
     *
     * @ORM\Column(name="fecha_evaluacion", type="date", nullable=true)
     */
    private $fechaEvaluacion;
    
    /**
     * @var string $resultadoEvaluacion
     *
     * @ORM\Column(name="resultado_evaluacion", type="text", nullable=true)
     */
    private $resultadoEvaluacion;
    
    /**
     * @var string $medidas
     *
     * @ORM\Column(name="medidas", type="text", nullable=true)
     */
    private $medidas;
    
    /**
    * @ORM\ManyToOne(targetEntity="PlanIntervencion", inversedBy="actividades")
    */
    private $planIntervencion;
  

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set fechaCumplimiento
     *
     * @param \DateTime $fechaCumplimiento
     * @return PlanIntervencionActividad
     */
    public function setFechaCumplimiento($fechaCumplimiento)
    {
        $this->fechaCumplimiento = $fechaCumplimiento;

        return $this;
    }

    /**
     * Get fechaCumplimiento
     *
     * @return \DateTime 
     */
    public function getFechaCumplimiento()
    {
        return $this->fechaCumplimiento;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     * @return PlanIntervencionActividad
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string 
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set responsable
     *
     * @param string $responsable
     * @return PlanIntervencionActividad
     */
    public function setResponsable($responsable)
    {
        $this->responsable = $responsable;

        return $this;
    }

    /**
     * Get responsable
     *
     * @return string 
     */
    public function getResponsable()
    {
        return $this->responsable;
    }

    /**
     * Set fechaEvaluacion
     *
     * @param \DateTime $fechaEvaluacion
     * @return PlanIntervencionActividad
     */
    public function setFechaEvaluacion($fechaEvaluacion)
    {
        $this->fechaEvaluacion = $fechaEvaluacion;

        return $this;
    }

    /**
     * Get fechaEvaluacion
     *
     * @return \DateTime 
     */
    public function getFechaEvaluacion()
    {
        return $this->fechaEvaluacion;
    }

    /**
     * Set resultadoEvaluacion
     *
     * @param string $resultadoEvaluacion
     * @return PlanIntervencionActividad
     */
    public function setResultadoEvaluacion($resultadoEvaluacion)
    {
        $this->resultadoEvaluacion = $resultadoEvaluacion;

        return $this;
    }

    /**
     * Get resultadoEvaluacion
     *
     * @return string 
     */
    public function getResultadoEvaluacion()
    {
        return $this->resultadoEvaluacion;
    }

    /**
     * Set medidas
     *
     * @param string $medidas
     * @return PlanIntervencionActividad
     */
    public function setMedidas($medidas)
    {
        $this->medidas = $medidas;

        return $this;
    }

    /**
     * Get medidas
     *
     * @return string 
     */
    public function getMedidas()
    {
        return $this->medidas;
    }

    /**
     * Set planIntervencion
     *
     * @param \MINSAL\GridFormBundle\Entity\PlanIntervencion $planIntervencion
     * @return PlanIntervencionActividad
     */
    public function setPlanIntervencion(\MINSAL\GridFormBundle\Entity\PlanIntervencion $planIntervencion = null)
    {
        $this->planIntervencion = $planIntervencion;

        return $this;
    }

    /**
     * Get planIntervencion
     *
     * @return \MINSAL\GridFormBundle\Entity\PlanIntervencion 
     */
    public function getPlanIntervencion()
    {
        return $this->planIntervencion;
    }
}
