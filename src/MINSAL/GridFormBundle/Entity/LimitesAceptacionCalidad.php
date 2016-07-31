<?php

namespace MINSAL\GridFormBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * MINSAL\GridFormBundle\Entity\LimitesAceptacionCalidad
 *
 * @ORM\Table(name="limites_aceptacion_calidad")
 * @UniqueEntity(fields="codigo", message="Código ya existe")
 * @ORM\Entity
 */
class LimitesAceptacionCalidad
{
    /**
     * @var string $codigo
     * @ORM\Id
     * @ORM\Column(name="codigo", type="string", length=100, nullable=false)
     */
    private $codigo;
    
    /**
     * @var string $descripcion
     *
     * @ORM\Column(name="descripcion", type="text", nullable=true)
     */
    private $descripcion;
        

    /**
     * @var string $valor
     *
     * @ORM\Column(name="valor", type="float", nullable=true)
     */
    private $valor;
    

    public function __toString()
    {
        return $this->codigo ? : '';
    }


    /**
     * Set codigo
     *
     * @param string $codigo
     *
     * @return LimitesAceptacionCalidad
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
     * Set valor
     *
     * @param float $valor
     *
     * @return LimitesAceptacionCalidad
     */
    public function setValor($valor)
    {
        $this->valor = $valor;

        return $this;
    }

    /**
     * Get valor
     *
     * @return float
     */
    public function getValor()
    {
        return $this->valor;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     *
     * @return LimitesAceptacionCalidad
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
}
