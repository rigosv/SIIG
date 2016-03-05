<?php

namespace MINSAL\GridFormBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * MINSAL\GridFormBundle\Entity\GrupoColumnas
 *
 * @ORM\Table(name="costos.grupo_columnas")
 * @UniqueEntity(fields="codigo", message="Código ya existe")
 * @ORM\Entity
 */
class GrupoColumnas
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="costos.grupo_columnas_id_seq")
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
     * @ORM\ManyToOne(targetEntity="Formulario", inversedBy="gruposColumnas")
     * @ORM\JoinColumn(name="id_formulario", referencedColumnName="id")
     * */
    private $formulario;
    
    /**
     * @ORM\OneToMany(targetEntity="GrupoColumnas", mappedBy="grupoPadre")
     **/
    private $subgrupos;

    /**
     * @ORM\ManyToOne(targetEntity="GrupoColumnas", inversedBy="subgrupos")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     **/
    private $grupoPadre;
        
    

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
     * @return GrupoColumnas
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
        $this->subgrupos = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set codigo
     *
     * @param string $codigo
     * @return GrupoColumnas
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;

        return $this;
    }

    /**
     * Set formulario
     *
     * @param \MINSAL\GridFormBundle\Entity\Formulario $formulario
     * @return GrupoColumnas
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
     * Add subgrupos
     *
     * @param \MINSAL\GridFormBundle\Entity\GrupoColumnas $subgrupos
     * @return GrupoColumnas
     */
    public function addSubgrupo(\MINSAL\GridFormBundle\Entity\GrupoColumnas $subgrupos)
    {
        $this->subgrupos[] = $subgrupos;

        return $this;
    }

    /**
     * Remove subgrupos
     *
     * @param \MINSAL\GridFormBundle\Entity\GrupoColumnas $subgrupos
     */
    public function removeSubgrupo(\MINSAL\GridFormBundle\Entity\GrupoColumnas $subgrupos)
    {
        $this->subgrupos->removeElement($subgrupos);
    }

    /**
     * Get subgrupos
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSubgrupos()
    {
        return $this->subgrupos;
    }

    /**
     * Set grupoPadre
     *
     * @param \MINSAL\GridFormBundle\Entity\GrupoColumnas $grupoPadre
     * @return GrupoColumnas
     */
    public function setGrupoPadre(\MINSAL\GridFormBundle\Entity\GrupoColumnas $grupoPadre = null)
    {
        $this->grupoPadre = $grupoPadre;

        return $this;
    }

    /**
     * Get grupoPadre
     *
     * @return \MINSAL\GridFormBundle\Entity\GrupoColumnas 
     */
    public function getGrupoPadre()
    {
        return $this->grupoPadre;
    }
}
