<?php

namespace MINSAL\GridFormBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * MINSAL\GridFormBundle\Entity\ParametrosIngresoDatos
 *
 * @ORM\Table(name="parametros_ingreso_datos")
 * @UniqueEntity(fields="{codigo}", message="Registro ya existe")
 * @ORM\Entity
 */
class ParametrosIngresoDatos
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
     *
     * @ORM\Column(name="anio", type="integer", nullable=false)
     */
    private $anio;
    
    /**
     * @var string $mes
     *
     * @ORM\Column(name="mes", type="integer", nullable=false)
     */
    private $mes;
    
    /**
     * @var string $cantidadExpedienteReportar
     *
     * @ORM\Column(name="cantidad_expedientes_reportar", type="integer", nullable=false)
     */
    private $cantidadExpedienteReportar;
        

    /**
     * @var string $nombreResponsable
     *
     * @ORM\Column(name="nombre_responsable", type="string", length=20, nullable=false)
     */
    private $nombreResponsable;
    
    /**
     * @var string $fechaEvaluacion
     *
     * @ORM\Column(name="fecha_evaluacion", type="date", nullable=false)
     */
    private $fechaEvaluacion;
    
    /**
     * @var string $observaciones
     *
     * @ORM\Column(name="observaciones", type="text", nullable=true)
     */
    private $observaciones;
    
    /**
     * @ORM\ManyToOne(targetEntity="MINSAL\CostosBundle\Entity\Estructura")     
     * */
    private $establecimiento;
    
    /**
     * @ORM\ManyToOne(targetEntity="Formulario")
     * */
    private $formulario;
    
    public function __toString()
    {
        //return $this->descripcion ? : '';
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
     * Set anio
     *
     * @param integer $anio
     * @return ParametrosIngresoDatos
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
     * Set mes
     *
     * @param integer $mes
     * @return ParametrosIngresoDatos
     */
    public function setMes($mes)
    {
        $this->mes = $mes;

        return $this;
    }

    /**
     * Get mes
     *
     * @return integer 
     */
    public function getMes()
    {
        return $this->mes;
    }

    /**
     * Set nombreResponsable
     *
     * @param string $nombreResponsable
     * @return ParametrosIngresoDatos
     */
    public function setNombreResponsable($nombreResponsable)
    {
        $this->nombreResponsable = $nombreResponsable;

        return $this;
    }

    /**
     * Get nombreResponsable
     *
     * @return string 
     */
    public function getNombreResponsable()
    {
        return $this->nombreResponsable;
    }

    /**
     * Set fechaEvaluacion
     *
     * @param \DateTime $fechaEvaluacion
     * @return ParametrosIngresoDatos
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
     * Set observaciones
     *
     * @param string $observaciones
     * @return ParametrosIngresoDatos
     */
    public function setObservaciones($observaciones)
    {
        $this->observaciones = $observaciones;

        return $this;
    }

    /**
     * Get observaciones
     *
     * @return string 
     */
    public function getObservaciones()
    {
        return $this->observaciones;
    }

    /**
     * Set establecimiento
     *
     * @param \MINSAL\CostosBundle\Entity\Estructura $establecimiento
     * @return ParametrosIngresoDatos
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
     * Set formulario
     *
     * @param \MINSAL\CostosBundle\Entity\Formulario $formulario
     * @return ParametrosIngresoDatos
     */
    public function setFormulario(\MINSAL\CostosBundle\Entity\Formulario $formulario = null)
    {
        $this->formulario = $formulario;

        return $this;
    }

    /**
     * Get formulario
     *
     * @return \MINSAL\CostosBundle\Entity\Formulario 
     */
    public function getFormulario()
    {
        return $this->formulario;
    }

    /**
     * Set cantidadExpedienteReportar
     *
     * @param integer $cantidadExpedienteReportar
     * @return ParametrosIngresoDatos
     */
    public function setCantidadExpedienteReportar($cantidadExpedienteReportar)
    {
        $this->cantidadExpedienteReportar = $cantidadExpedienteReportar;

        return $this;
    }

    /**
     * Get cantidadExpedienteReportar
     *
     * @return integer 
     */
    public function getCantidadExpedienteReportar()
    {
        return $this->cantidadExpedienteReportar;
    }
}
