<?php

namespace MINSAL\CalidadBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MINSAL\GridFormBundle\Entity\Actividad
 *
 * @ORM\Table(name="calidad.actividad")
 * @ORM\Entity
 */
class Actividad
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="calidad.actividad_id_seq")
     */
    private $id;
    
    /**
     * @var string $nombre
     *
     * @ORM\Column(name="causa_brecha", type="text", nullable=false)
     */
    private $nombre;
    
    /**
     * @var string $fechaInicio
     *
     * @ORM\Column(name="fecha_inicio", type="date", nullable=false)
     */
    private $fechaInicio;
    
    /**
     * @var string $fechaFinalizacion
     *
     * @ORM\Column(name="fecha_finalizacion", type="date", nullable=false)
     */
    private $fechaFinalizacion;
    
    /**
     * @var string $medioVerificacion
     *
     * @ORM\Column(name="medio_verificacion", type="text", nullable=true)
     */
    private $medioVerificacion;
    
    /**
     * @var string $responsable
     *
     * @ORM\Column(name="responsable", type="string", length=250, nullable=false)
     */
    private $responsable;
    
    /**
     * @var string $porcentajeAvance
     *
     * @ORM\Column(name="porcentajeAvance", type="integer", nullable=true)
     */
    private $porcentajeAvance;
    
        
    /**
     * @ORM\ManyToOne(targetEntity="MINSAL\GridFormBundle\Entity\VariableCaptura", inversedBy="actividades")
     * */
    private $criterio;
       

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
     * Set nombre
     *
     * @param string $nombre
     *
     * @return Actividad
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set fechaInicio
     *
     * @param \DateTime $fechaInicio
     *
     * @return Actividad
     */
    public function setFechaInicio($fechaInicio)
    {
        $this->fechaInicio = $fechaInicio;

        return $this;
    }

    /**
     * Get fechaInicio
     *
     * @return \DateTime
     */
    public function getFechaInicio()
    {
        return $this->fechaInicio;
    }

    /**
     * Set fechaFinalizacion
     *
     * @param \DateTime $fechaFinalizacion
     *
     * @return Actividad
     */
    public function setFechaFinalizacion($fechaFinalizacion)
    {
        $this->fechaFinalizacion = $fechaFinalizacion;

        return $this;
    }

    /**
     * Get fechaFinalizacion
     *
     * @return \DateTime
     */
    public function getFechaFinalizacion()
    {
        return $this->fechaFinalizacion;
    }

    /**
     * Set medioVerificacion
     *
     * @param string $medioVerificacion
     *
     * @return Actividad
     */
    public function setMedioVerificacion($medioVerificacion)
    {
        $this->medioVerificacion = $medioVerificacion;

        return $this;
    }

    /**
     * Get medioVerificacion
     *
     * @return string
     */
    public function getMedioVerificacion()
    {
        return $this->medioVerificacion;
    }

    /**
     * Set responsable
     *
     * @param string $responsable
     *
     * @return Actividad
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
     * Set porcentajeAvance
     *
     * @param integer $porcentajeAvance
     *
     * @return Actividad
     */
    public function setPorcentajeAvance($porcentajeAvance)
    {
        $this->porcentajeAvance = $porcentajeAvance;

        return $this;
    }

    /**
     * Get porcentajeAvance
     *
     * @return integer
     */
    public function getPorcentajeAvance()
    {
        return $this->porcentajeAvance;
    }

    /**
     * Set criterio
     *
     * @param \MINSAL\GridFormBundle\Entity\VariableCaptura $criterio
     *
     * @return Actividad
     */
    public function setCriterio(\MINSAL\GridFormBundle\Entity\VariableCaptura $criterio = null)
    {
        $this->criterio = $criterio;

        return $this;
    }

    /**
     * Get criterio
     *
     * @return \MINSAL\GridFormBundle\Entity\VariableCaptura
     */
    public function getCriterio()
    {
        return $this->criterio;
    }
}
