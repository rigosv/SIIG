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
     * @ORM\ManyToOne(targetEntity="Estandar", inversedBy="planesMejora")
     * 
     * */
    private $estandar;
    
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
     * Set estandar
     *
     * @param \MINSAL\CalidadBundle\Entity\Estandar $estandar
     *
     * @return GestionPermisos
     */
    public function setEstandar(\MINSAL\CalidadBundle\Entity\Estandar $estandar = null)
    {
        $this->estandar = $estandar;

        return $this;
    }

    /**
     * Get estandar
     *
     * @return \MINSAL\CalidadBundle\Entity\Estandar
     */
    public function getEstandar()
    {
        return $this->estandar;
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
}
