<?php

namespace MINSAL\CalidadBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MINSAL\GridFormBundle\Entity\Estandar
 *
 * @ORM\Table(name="calidad.estandar")
 * @ORM\Entity(repositoryClass="MINSAL\GridFormBundle\Repository\FormularioRepository")
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
     * @ORM\ManyToOne(targetEntity="Proceso")
     * */
    private $proceso;
    
    /**
     * @ORM\OneToMany(targetEntity="Criterio", mappedBy="estandar", cascade={"all"}, orphanRemoval=true)
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
     * Add planesMejora
     *
     * @param \MINSAL\CalidadBundle\Entity\Criterio $planesMejora
     *
     * @return Estandar
     */
    public function addPlanesMejora(\MINSAL\CalidadBundle\Entity\Criterio $planesMejora)
    {
        $this->planesMejora[] = $planesMejora;

        return $this;
    }

    /**
     * Remove planesMejora
     *
     * @param \MINSAL\CalidadBundle\Entity\Criterio $planesMejora
     */
    public function removePlanesMejora(\MINSAL\CalidadBundle\Entity\Criterio $planesMejora)
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
    
    public function __toString() {
        return $this->getNombre();
    }
}
