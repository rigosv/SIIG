<?php

namespace MINSAL\GridFormBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * MINSAL\GridFormBundle\Entity\PlanIntervencion
 *
 * @ORM\Table(name="plan_intervencion")
 * @UniqueEntity(fields={"codigo"}, message="Plan ya existe")
 * @ORM\Entity
 */
class PlanIntervencion
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
     * @var string $codigo
     *
     * @ORM\Column(name="codigo", type="string", length=50, nullable=false)
     */
    private $codigo;

    /**
     * @var string $fecha_evaluacion
     *
     * @ORM\Column(name="fecha_evaluacion", type="date", nullable=false)
     */
    private $fechaEvaluacion;
        

    /**
     * @var string $situacionEncontrada
     *
     * @ORM\Column(name="situacion_encontrada", type="text", nullable=true)
     */
    private $situacionEncontrada;
    
    /**
     * @ORM\ManyToOne(targetEntity="Formulario")
     * */
    private $estandar;
    
    /**
     * @ORM\ManyToOne(targetEntity="MINSAL\CostosBundle\Entity\Estructura")
     * */
    private $establecimiento;
    
    /**
    * @var \Doctrine\Common\Collections\ArrayCollection
    * @ORM\OneToMany(targetEntity="PlanIntervencionActividad", mappedBy="planIntervencion", cascade={"all"}, orphanRemoval=true)
    * @ORM\OrderBy({"fechaCumplimiento" = "ASC"})
    */
    private $actividades;
  
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->actividades = new \Doctrine\Common\Collections\ArrayCollection();
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

    /**
     * Set fechaEvaluacion
     *
     * @param \DateTime $fechaEvaluacion
     * @return PlanIntervencion
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
     * Set situacionEncontrada
     *
     * @param string $situacionEncontrada
     * @return PlanIntervencion
     */
    public function setSituacionEncontrada($situacionEncontrada)
    {
        $this->situacionEncontrada = $situacionEncontrada;

        return $this;
    }

    /**
     * Get situacionEncontrada
     *
     * @return string 
     */
    public function getSituacionEncontrada()
    {
        return $this->situacionEncontrada;
    }

    /**
     * Set estandar
     *
     * @param \MINSAL\GridFormBundle\Entity\Formulario $estandar
     * @return PlanIntervencion
     */
    public function setEstandar(\MINSAL\GridFormBundle\Entity\Formulario $estandar = null)
    {
        $this->estandar = $estandar;

        return $this;
    }

    /**
     * Get estandar
     *
     * @return \MINSAL\GridFormBundle\Entity\Formulario 
     */
    public function getEstandar()
    {
        return $this->estandar;
    }

    /**
     * Set establecimiento
     *
     * @param \MINSAL\CostosBundle\Entity\Estructura $establecimiento
     * @return PlanIntervencion
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
     * Add actividades
     *
     * @param \MINSAL\GridFormBundle\Entity\PlanIntervencionActividad $actividades
     * @return PlanIntervencion
     */
    public function addActividade(\MINSAL\GridFormBundle\Entity\PlanIntervencionActividad $actividades)
    {
        $this->actividades[] = $actividades;

        return $this;
    }

    /**
     * Remove actividades
     *
     * @param \MINSAL\GridFormBundle\Entity\PlanIntervencionActividad $actividades
     */
    public function removeActividade(\MINSAL\GridFormBundle\Entity\PlanIntervencionActividad $actividades)
    {
        $this->actividades->removeElement($actividades);
    }

    /**
     * Get actividades
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getActividades()
    {
        return $this->actividades;
    }
    
    public function __toString() {
        return $this->codigo;
    }

    /**
     * Set codigo
     *
     * @param string $codigo
     * @return PlanIntervencion
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;

        return $this;
    }

    /**
     * Get codigo
     *
     * @return string 
     */
    public function getCodigo()
    {
        return $this->codigo;
    }
}
