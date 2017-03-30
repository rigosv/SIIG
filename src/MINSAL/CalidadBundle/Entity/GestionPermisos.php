<?php

namespace MINSAL\CalidadBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MINSAL\GridFormBundle\Entity\GestionPermisos
 *
 * @ORM\Table(name="calidad.gestion_permisos")
 * @ORM\Entity()
 */
class GestionPermisos
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="calidad.gestion_permisos_id_seq")
     */
    private $id;
    
    
    /**
     * @var \Doctrine\Common\Collections\Collection|Estandar[]
     *
     * @ORM\ManyToMany(targetEntity="Estandar")
     * @ORM\JoinTable(name="calidad.permisos_estandares")
     */
    private $estandares;
    
    /**
     * @ORM\ManyToOne(targetEntity="MINSAL\CostosBundle\Entity\Estructura")
     * */
    private $establecimiento;
    
    
    /**
     * @ORM\ManyToOne(targetEntity="MINSAL\IndicadoresBundle\Entity\User")
     * */
    private $usuario;
    
    /**
     * @var string $codigo
     *
     * @ORM\Column(name="elemento", type="string", length=100, nullable=false)
     */
    private $elemento;
    
    /**
     * @var string $codigo
     *
     * @ORM\Column(name="accion", type="string", length=30, nullable=false)
     */
    private $accion;

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
     * Set elemento
     *
     * @param string $elemento
     *
     * @return GestionPermisos
     */
    public function setElemento($elemento)
    {
        $this->elemento = $elemento;

        return $this;
    }

    /**
     * Get elemento
     *
     * @return string
     */
    public function getElemento()
    {
        return $this->elemento;
    }

    /**
     * Set accion
     *
     * @param string $accion
     *
     * @return GestionPermisos
     */
    public function setAccion($accion)
    {
        $this->accion = $accion;

        return $this;
    }

    /**
     * Get accion
     *
     * @return string
     */
    public function getAccion()
    {
        return $this->accion;
    }

    /**
     * Set establecimiento
     *
     * @param \MINSAL\CostosBundle\Entity\Estructura $establecimiento
     *
     * @return GestionPermisos
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
     * Set usuario
     *
     * @param \MINSAL\IndicadoresBundle\Entity\User $usuario
     *
     * @return GestionPermisos
     */
    public function setUsuario(\MINSAL\IndicadoresBundle\Entity\User $usuario = null)
    {
        $this->usuario = $usuario;

        return $this;
    }

    /**
     * Get usuario
     *
     * @return \MINSAL\IndicadoresBundle\Entity\User
     */
    public function getUsuario()
    {
        return $this->usuario;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->estandares = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add estandare
     *
     * @param \MINSAL\CalidadBundle\Entity\Estandar $estandar
     *
     * @return GestionPermisos
     */
    public function addEstandare(\MINSAL\CalidadBundle\Entity\Estandar $estandar)
    {
        $this->estandares[] = $estandar;

        return $this;
    }

    /**
     * Remove estandare
     *
     * @param \MINSAL\CalidadBundle\Entity\Estandar $estandar
     */
    public function removeEstandare(\MINSAL\CalidadBundle\Entity\Estandar $estandar)
    {
        $this->estandares->removeElement($estandar);
    }

    /**
     * Get estandares
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEstandares()
    {
        return $this->estandares;
    }
}
