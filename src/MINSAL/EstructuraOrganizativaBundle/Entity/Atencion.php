<?php

namespace MINSAL\EstructuraOrganizativaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * MINSAL\EstructuraOrganizativaBundle\Entity\Atencion
 *
 * @ORM\Table(name="atencion")
 * @UniqueEntity(fields="codigo", message="Código ya existe")
 * @ORM\Entity
 */
class Atencion
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
     * @ORM\Column(name="codigo", type="string", length=20, nullable=false)
     */
    private $codigo;
        

    /**
     * @var string $descripcion
     *
     * @ORM\Column(name="descripcion", type="text", nullable=true)
     */
    private $descripcion;
    
    /**
     * @ORM\ManyToOne(targetEntity="TipoAtencion")
     * */
    private $tipoAtencion;
    

    public function __toString()
    {
        return $this->descripcion ? : '';
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
     * @return Alineacion
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
     * Set descripcion
     *
     * @param string $descripcion
     * @return Alineacion
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
     * Set tipoAtencion
     *
     * @param \MINSAL\EstructuraOrganizativaBundle\Entity\TipoAtencion $tipoAtencion
     * @return Atencion
     */
    public function setTipoAtencion(\MINSAL\EstructuraOrganizativaBundle\Entity\TipoAtencion $tipoAtencion = null)
    {
        $this->tipoAtencion = $tipoAtencion;

        return $this;
    }

    /**
     * Get tipoAtencion
     *
     * @return \MINSAL\EstructuraOrganizativaBundle\Entity\TipoAtencion 
     */
    public function getTipoAtencion()
    {
        return $this->tipoAtencion;
    }
}
