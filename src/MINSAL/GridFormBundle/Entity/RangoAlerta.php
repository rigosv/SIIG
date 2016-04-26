<?php

namespace MINSAL\GridFormBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MINSAL\IndicadoresBundle\Entity\RangoAlerta
 *
 * @ORM\Table(name="rango_alerta")
 * @ORM\Entity
 */
class RangoAlerta
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
     * @var decimal $limiteInferior
     *
     * @ORM\Column(name="limite_inferior", type="float", nullable=false)
     */
    private $limiteInferior;

    /**
     * @var decimal $limiteSuperior
     *
     * @ORM\Column(name="limite_superior", type="float",  nullable=false)
     */
    private $limiteSuperior;

    /**
     * @var string $color
     *
     * @ORM\Column(name="color", type="string", length=50, nullable=false)
     */
    private $color;
    
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * 
     * @ORM\ManyToMany(targetEntity="VariableCaptura", mappedBy="alertas")
     * */
    private $criterios;


    public function __toString()
    {
        return $this->limiteInferior. ' - ' . $this->limiteSuperior . ' - '. $this->color ;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->criterios = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set limiteInferior
     *
     * @param float $limiteInferior
     * @return RangoAlerta
     */
    public function setLimiteInferior($limiteInferior)
    {
        $this->limiteInferior = $limiteInferior;

        return $this;
    }

    /**
     * Get limiteInferior
     *
     * @return float 
     */
    public function getLimiteInferior()
    {
        return $this->limiteInferior;
    }

    /**
     * Set limiteSuperior
     *
     * @param float $limiteSuperior
     * @return RangoAlerta
     */
    public function setLimiteSuperior($limiteSuperior)
    {
        $this->limiteSuperior = $limiteSuperior;

        return $this;
    }

    /**
     * Get limiteSuperior
     *
     * @return float 
     */
    public function getLimiteSuperior()
    {
        return $this->limiteSuperior;
    }

    /**
     * Set color
     *
     * @param string $color
     * @return RangoAlerta
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get color
     *
     * @return string 
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Add criterios
     *
     * @param \MINSAL\GridFormBundle\Entity\VariableCaptura $criterios
     * @return RangoAlerta
     */
    public function addCriterio(\MINSAL\GridFormBundle\Entity\VariableCaptura $criterios)
    {
        $this->criterios[] = $criterios;

        return $this;
    }

    /**
     * Remove criterios
     *
     * @param \MINSAL\GridFormBundle\Entity\VariableCaptura $criterios
     */
    public function removeCriterio(\MINSAL\GridFormBundle\Entity\VariableCaptura $criterios)
    {
        $this->criterios->removeElement($criterios);
    }

    /**
     * Get criterios
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCriterios()
    {
        return $this->criterios;
    }
}
