<?php

namespace MINSAL\GridFormBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * MINSAL\GridFormBundle\Entity\DimensionCalidad
 *
 * @ORM\Table(name="dimension_calidad")
 * @UniqueEntity(fields="codigo", message="Código ya existe")
 * @ORM\Entity
 */
class DimensionCalidad
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
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
    * @var \Doctrine\Common\Collections\ArrayCollection
    * @ORM\OneToMany(targetEntity="Indicador", mappedBy="dimension", cascade={"all"}, orphanRemoval=true)
    * @ORM\OrderBy({"descripcion" = "ASC"})
    */
    private $indicadores;
    

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
     * @return DimensionCalidad
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
     * Constructor
     */
    public function __construct()
    {
        $this->indicadores = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set id
     *
     * @param integer $id
     *
     * @return DimensionCalidad
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Add indicadore
     *
     * @param \MINSAL\GridFormBundle\Entity\Indicador $indicadore
     *
     * @return DimensionCalidad
     */
    public function addIndicadore(\MINSAL\GridFormBundle\Entity\Indicador $indicadore)
    {
        $this->indicadores[] = $indicadore;

        return $this;
    }

    /**
     * Remove indicadore
     *
     * @param \MINSAL\GridFormBundle\Entity\Indicador $indicadore
     */
    public function removeIndicadore(\MINSAL\GridFormBundle\Entity\Indicador $indicadore)
    {
        $this->indicadores->removeElement($indicadore);
    }

    /**
     * Get indicadores
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getIndicadores()
    {
        return $this->indicadores;
    }
}
