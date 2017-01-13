<?php

namespace MINSAL\IndicadoresBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MINSAL\IndicadoresBundle\Entity\Bitacora
 *
 * @ORM\Table(name="bitacora")
 * @ORM\Entity(repositoryClass="MINSAL\IndicadoresBundle\Repository\BitacoraRepository")
 */
class Bitacora
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
     * @ORM\Column(name="id_session", type="string", length=100, nullable=false)
     */
    private $idSession;

    /**
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="gruposIndicadores")
     * @ORM\JoinColumn(name="id_usuario", referencedColumnName="id")
     */
    private $usuario;


    /**
     * @var datetime $fechaHora
     *
     * @ORM\Column(name="fecha_hora", type="datetime", nullable=false)
     */
    private $fechaHora;

    /**
     * @var string $accion
     *
     * @ORM\Column(name="accion", type="string", length=100, nullable=false)
     */
    private $accion;
    
    /**
     * @var string $elemento
     *
     * @ORM\Column(name="elemento", type="text", nullable=true)
     */
    private $elemento;

    

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
     * Set idSession
     *
     * @param string $idSession
     *
     * @return Bitacora
     */
    public function setIdSession($idSession)
    {
        $this->idSession = $idSession;

        return $this;
    }

    /**
     * Get idSession
     *
     * @return string
     */
    public function getIdSession()
    {
        return $this->idSession;
    }

    /**
     * Set fechaHora
     *
     * @param \DateTime $fechaHora
     *
     * @return Bitacora
     */
    public function setFechaHora($fechaHora)
    {
        $this->fechaHora = $fechaHora;

        return $this;
    }

    /**
     * Get fechaHora
     *
     * @return \DateTime
     */
    public function getFechaHora()
    {
        return $this->fechaHora;
    }

    /**
     * Set accion
     *
     * @param string $accion
     *
     * @return Bitacora
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
     * Set usuario
     *
     * @param \MINSAL\IndicadoresBundle\Entity\User $usuario
     *
     * @return Bitacora
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
     * Set elemento
     *
     * @param string $elemento
     *
     * @return Bitacora
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
}
