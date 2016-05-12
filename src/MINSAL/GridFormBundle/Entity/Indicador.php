<?php

namespace MINSAL\GridFormBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * MINSAL\GridFormBundle\Entity\Indicador
 *
 * @ORM\Table(name="indicador")
 * @UniqueEntity(fields="codigo", message="Código ya existe")
 * @ORM\Entity(repositoryClass="MINSAL\GridFormBundle\Entity\IndicadorRepository")
 */
class Indicador
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="costos.alineacion_id_seq")
     */
    private $id;

    /**
     * @var string $codigo
     *
     * @ORM\Column(name="codigo", type="string", length=60, nullable=false)
     */
    private $codigo;
        

    /**
     * @var string $descripcion
     *
     * @ORM\Column(name="descripcion", type="text", nullable=true)
     */
    private $descripcion;
    
    /**
     * @ORM\ManyToOne(targetEntity="Formulario", inversedBy="indicadores")
     * */
    private $estandar;
    
    /**
     * @var string $formaEvaluacion
     *
     * @ORM\Column(name="forma_evaluacion", type="string", length=50, nullable=false)
     */
    private $formaEvaluacion;
    
    /**
     * @var string $porcentajeAceptacion
     *
     * @ORM\Column(name="porcentaje_aceptacion", type="float", nullable=true)
     */
    private $porcentajeAceptacion;
    
    
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="VariableCaptura", inversedBy="indicadores")
     **/
    private $criterios;
    

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
     * Constructor
     */
    public function __construct()
    {
        $this->criterios = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set estandar
     *
     * @param \MINSAL\GridFormBundle\Entity\Formulario $estandar
     * @return Indicador
     */
    public function setEstandar(\MINSAL\GridFormBundle\Entity\Formulario $estandar = null)
    {
        $this->estandar = $estandar;

        return $this;
    }

    /**
     * Get estandar
     *
     * @return \MINSAL\GridFormBundle\Entity\Formulario 
     */
    public function getEstandar()
    {
        return $this->estandar;
    }

    /**
     * Add criterios
     *
     * @param \MINSAL\GridFormBundle\Entity\VariableCaptura $criterios
     * @return Indicador
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

    /**
     * Set formaEvaluacion
     *
     * @param string $formaEvaluacion
     * @return Indicador
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
     * Set porcentajeAceptacion
     *
     * @param float $porcentajeAceptacion
     * @return Indicador
     */
    public function setPorcentajeAceptacion($porcentajeAceptacion)
    {
        $this->porcentajeAceptacion = $porcentajeAceptacion;

        return $this;
    }

    /**
     * Get porcentajeAceptacion
     *
     * @return float 
     */
    public function getPorcentajeAceptacion()
    {
        return $this->porcentajeAceptacion;
    }
}
