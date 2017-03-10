<?php

namespace MINSAL\CalidadBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * MINSAL\GridFormBundle\Entity\Estandar
 *
 * @ORM\Table(name="calidad.estandar")
 * @UniqueEntity(fields="codigo", message="Código ya existe")
 * @ORM\Entity(repositoryClass="MINSAL\CalidadBundle\Repository\EstandarRepository")
 */
class Estandar
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="calidad.estandar_id_seq")
     */
    private $id;
    
    /**
     * @var string $codigo
     *
     * @ORM\Column(name="codigo", type="string", length=40, nullable=false)
     */
    private $codigo;

    /**
     * @var string $nombre
     *
     * @ORM\Column(name="nombre", type="string", length=100, nullable=false)
     */
    private $nombre;

    /**
     * @var string $descripcion
     *
     * @ORM\Column(name="descripcion", type="text", nullable=false)
     */
    private $descripcion;
    
    /**
     * @ORM\ManyToOne(targetEntity="MINSAL\GridFormBundle\Entity\Formulario", inversedBy="estandares")
     * */
    private $formularioCaptura;
    
     /**
     * @var string $formaEvaluacion
     *
     * @ORM\Column(name="forma_evaluacion", type="string", length=50, nullable=true)
     */
    private $formaEvaluacion;
    
    /**
     * @var string $evaluacionPorExpedientes
     *
     * @ORM\Column(name="evaluacion_por_expedientes", type="boolean", nullable=true)
     */
    private $evaluacionPorExpedientes;
    
    /**
     * @var string $meta
     *
     * @ORM\Column(name="meta", type="float", nullable=true)
     */
    private $meta;
    
    /**
     * @ORM\ManyToOne(targetEntity="Proceso")
     * */
    private $proceso;
    
    /**
     * @ORM\OneToMany(targetEntity="PlanMejora", mappedBy="estandar", cascade={"all"}, orphanRemoval=true)
     * 
     */
    private $planesMejora;

    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->planesMejora = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    public function __toString() {
        return $this->getNombre();
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
     * Set codigo
     *
     * @param string $codigo
     *
     * @return Estandar
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

    /**
     * Set nombre
     *
     * @param string $nombre
     *
     * @return Estandar
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
     * Set descripcion
     *
     * @param string $descripcion
     *
     * @return Estandar
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
     * Set formularioCaptura
     *
     * @param \MINSAL\GridFormBundle\Entity\Formulario $formularioCaptura
     *
     * @return Estandar
     */
    public function setFormularioCaptura(\MINSAL\GridFormBundle\Entity\Formulario $formularioCaptura = null)
    {
        $this->formularioCaptura = $formularioCaptura;

        return $this;
    }

    /**
     * Get formularioCaptura
     *
     * @return \MINSAL\GridFormBundle\Entity\Formulario
     */
    public function getFormularioCaptura()
    {
        return $this->formularioCaptura;
    }

    /**
     * Set proceso
     *
     * @param \MINSAL\CalidadBundle\Entity\Proceso $proceso
     *
     * @return Estandar
     */
    public function setProceso(\MINSAL\CalidadBundle\Entity\Proceso $proceso = null)
    {
        $this->proceso = $proceso;

        return $this;
    }

    /**
     * Get proceso
     *
     * @return \MINSAL\CalidadBundle\Entity\Proceso
     */
    public function getProceso()
    {
        return $this->proceso;
    }

    /**
     * Add planesMejora
     *
     * @param \MINSAL\CalidadBundle\Entity\PlanMejora $planesMejora
     *
     * @return Estandar
     */
    public function addPlanesMejora(\MINSAL\CalidadBundle\Entity\PlanMejora $planesMejora)
    {
        $this->planesMejora[] = $planesMejora;

        return $this;
    }

    /**
     * Remove planesMejora
     *
     * @param \MINSAL\CalidadBundle\Entity\PlanMejora $planesMejora
     */
    public function removePlanesMejora(\MINSAL\CalidadBundle\Entity\PlanMejora $planesMejora)
    {
        $this->planesMejora->removeElement($planesMejora);
    }

    /**
     * Get planesMejora
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPlanesMejora()
    {
        return $this->planesMejora;
    }

    /**
     * Set formaEvaluacion
     *
     * @param string $formaEvaluacion
     *
     * @return Estandar
     */
    public function setFormaEvaluacion($formaEvaluacion)
    {
        $this->formaEvaluacion = $formaEvaluacion;

        return $this;
    }

    /**
     * Get formaEvaluacion
     *
     * @return string
     */
    public function getFormaEvaluacion()
    {
        return $this->formaEvaluacion;
    }

    /**
     * Set evaluacionPorExpedientes
     *
     * @param boolean $evaluacionPorExpedientes
     *
     * @return Estandar
     */
    public function setEvaluacionPorExpedientes($evaluacionPorExpedientes)
    {
        $this->evaluacionPorExpedientes = $evaluacionPorExpedientes;

        return $this;
    }

    /**
     * Get evaluacionPorExpedientes
     *
     * @return boolean
     */
    public function getEvaluacionPorExpedientes()
    {
        return $this->evaluacionPorExpedientes;
    }

    /**
     * Set meta
     *
     * @param float $meta
     *
     * @return Estandar
     */
    public function setMeta($meta)
    {
        $this->meta = $meta;

        return $this;
    }

    /**
     * Get meta
     *
     * @return float
     */
    public function getMeta()
    {
        return $this->meta;
    }
}
