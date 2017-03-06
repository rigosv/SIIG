<?php

namespace MINSAL\CalidadBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * MINSAL\GridFormBundle\Entity\Criterio
 *
 * @ORM\Table(name="calidad.criterio")
 * @ORM\Entity
 */
class Criterio
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="calidad.criterio_id_seq")
     */
    private $id;
    
    /**
     * @var string $causaBrecha
     *
     * @ORM\Column(name="causa_brecha", type="text", nullable=true)
     */
    private $causaBrecha;
    
    /**
     * @var string $oportunidadMejora
     *
     * @ORM\Column(name="oportunidad_mejora", type="text", nullable=true)
     */
    private $oportunidadMejora;
    
    /**
     * @var string $factoresMejoramiento
     *
     * @ORM\Column(name="factores_mejoramiento", type="text", nullable=true)
     */
    private $factoresMejoramiento;
    
        
    /**
     * @ORM\ManyToOne(targetEntity="MINSAL\GridFormBundle\Entity\VariableCaptura")
     * */
    private $variableCaptura;
    
    /**
     * @ORM\ManyToOne(targetEntity="TipoIntervencion")
     * */
    private $tipoIntervencion;
    
    /**
     * @ORM\ManyToOne(targetEntity="Prioridad")
     * */
    private $prioridad;
    
    /**
     * @ORM\ManyToOne(targetEntity="PlanMejora", inversedBy="criterios")
     * */
    private $planMejora;
    
    /**
    * @ORM\OneToMany(targetEntity="Actividad", mappedBy="criterio", cascade={"all"}, orphanRemoval=true)
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
     * Set causaBrecha
     *
     * @param string $causaBrecha
     *
     * @return Criterio
     */
    public function setCausaBrecha($causaBrecha)
    {
        $this->causaBrecha = $causaBrecha;

        return $this;
    }

    /**
     * Get causaBrecha
     *
     * @return string
     */
    public function getCausaBrecha()
    {
        return $this->causaBrecha;
    }

    /**
     * Set oportunidadMejora
     *
     * @param string $oportunidadMejora
     *
     * @return Criterio
     */
    public function setOportunidadMejora($oportunidadMejora)
    {
        $this->oportunidadMejora = $oportunidadMejora;

        return $this;
    }

    /**
     * Get oportunidadMejora
     *
     * @return string
     */
    public function getOportunidadMejora()
    {
        return $this->oportunidadMejora;
    }

    /**
     * Set factoresMejoramiento
     *
     * @param string $factoresMejoramiento
     *
     * @return Criterio
     */
    public function setFactoresMejoramiento($factoresMejoramiento)
    {
        $this->factoresMejoramiento = $factoresMejoramiento;

        return $this;
    }

    /**
     * Get factoresMejoramiento
     *
     * @return string
     */
    public function getFactoresMejoramiento()
    {
        return $this->factoresMejoramiento;
    }

    /**
     * Set variableCaptura
     *
     * @param \MINSAL\GridFormBundle\Entity\VariableCaptura $variableCaptura
     *
     * @return Criterio
     */
    public function setVariableCaptura(\MINSAL\GridFormBundle\Entity\VariableCaptura $variableCaptura = null)
    {
        $this->variableCaptura = $variableCaptura;

        return $this;
    }

    /**
     * Get variableCaptura
     *
     * @return \MINSAL\GridFormBundle\Entity\VariableCaptura
     */
    public function getVariableCaptura()
    {
        return $this->variableCaptura;
    }

    /**
     * Set tipoIntervencion
     *
     * @param \MINSAL\CalidadBundle\Entity\TipoIntervencion $tipoIntervencion
     *
     * @return Criterio
     */
    public function setTipoIntervencion(\MINSAL\CalidadBundle\Entity\TipoIntervencion $tipoIntervencion = null)
    {
        $this->tipoIntervencion = $tipoIntervencion;

        return $this;
    }

    /**
     * Get tipoIntervencion
     *
     * @return \MINSAL\CalidadBundle\Entity\TipoIntervencion
     */
    public function getTipoIntervencion()
    {
        return $this->tipoIntervencion;
    }

    /**
     * Set prioridad
     *
     * @param \MINSAL\CalidadBundle\Entity\Prioridad $prioridad
     *
     * @return Criterio
     */
    public function setPrioridad(\MINSAL\CalidadBundle\Entity\Prioridad $prioridad = null)
    {
        $this->prioridad = $prioridad;

        return $this;
    }

    /**
     * Get prioridad
     *
     * @return \MINSAL\CalidadBundle\Entity\Prioridad
     */
    public function getPrioridad()
    {
        return $this->prioridad;
    }

    /**
     * Set planMejora
     *
     * @param \MINSAL\CalidadBundle\Entity\PlanMejora $planMejora
     *
     * @return Criterio
     */
    public function setPlanMejora(\MINSAL\CalidadBundle\Entity\PlanMejora $planMejora = null)
    {
        $this->planMejora = $planMejora;

        return $this;
    }

    /**
     * Get planMejora
     *
     * @return \MINSAL\CalidadBundle\Entity\PlanMejora
     */
    public function getPlanMejora()
    {
        return $this->planMejora;
    }

    /**
     * Add actividade
     *
     * @param \MINSAL\CalidadBundle\Entity\Actividad $actividade
     *
     * @return Criterio
     */
    public function addActividade(\MINSAL\CalidadBundle\Entity\Actividad $actividade)
    {
        $this->actividades[] = $actividade;

        return $this;
    }

    /**
     * Remove actividade
     *
     * @param \MINSAL\CalidadBundle\Entity\Actividad $actividade
     */
    public function removeActividade(\MINSAL\CalidadBundle\Entity\Actividad $actividade)
    {
        $this->actividades->removeElement($actividade);
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
}
