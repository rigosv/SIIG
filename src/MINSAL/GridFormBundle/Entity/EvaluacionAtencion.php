<?php

namespace MINSAL\GridFormBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * MINSAL\GridFormBundle\Entity\EvaluacionAtencion
 *
 * @ORM\Table(name="evaluacion_atencion")
 * @UniqueEntity(fields="codigo", message="Código ya existe")
 * @ORM\Entity
 */
class EvaluacionAtencion
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
     * @ORM\ManyToOne(targetEntity="Formulario", inversedBy="areasEvaluacion")
     * */
    private $formulario;
    
    /**
     * @ORM\ManyToOne(targetEntity="MINSAL\CostosBundle\Entity\Estructura")
     * */
    private $establecimiento;
    
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="MINSAL\EstructuraOrganizativaBundle\Entity\Atencion")
     **/
    private $areasAtencion;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->areasAtencion = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set formulario
     *
     * @param \MINSAL\GridFormBundle\Entity\Formulario $formulario
     * @return EvaluacionAtencion
     */
    public function setFormulario(\MINSAL\GridFormBundle\Entity\Formulario $formulario = null)
    {
        $this->formulario = $formulario;

        return $this;
    }

    /**
     * Get formulario
     *
     * @return \MINSAL\GridFormBundle\Entity\Formulario 
     */
    public function getFormulario()
    {
        return $this->formulario;
    }

    /**
     * Set establecimiento
     *
     * @param \MINSAL\CostosBundle\Entity\Estructura $establecimiento
     * @return EvaluacionAtencion
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
     * Add areasAtencion
     *
     * @param \MINSAL\EstructuraOrganizativaBundle\Entity\Atencion $areasAtencion
     * @return EvaluacionAtencion
     */
    public function addAreasAtencion(\MINSAL\EstructuraOrganizativaBundle\Entity\Atencion $areasAtencion)
    {
        $this->areasAtencion[] = $areasAtencion;

        return $this;
    }

    /**
     * Remove areasAtencion
     *
     * @param \MINSAL\EstructuraOrganizativaBundle\Entity\Atencion $areasAtencion
     */
    public function removeAreasAtencion(\MINSAL\EstructuraOrganizativaBundle\Entity\Atencion $areasAtencion)
    {
        $this->areasAtencion->removeElement($areasAtencion);
    }

    /**
     * Get areasAtencion
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAreasAtencion()
    {
        return $this->areasAtencion;
    }
}
