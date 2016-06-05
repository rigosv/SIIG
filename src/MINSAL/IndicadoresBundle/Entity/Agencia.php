<?php

namespace MINSAL\IndicadoresBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * MINSAL\IndicadoresBundle\Entity\Agencia
 *
 * @ORM\Table(name="agencia",uniqueConstraints={@ORM\UniqueConstraint(name="codigo_idx", columns={"codigo"})})
 * @UniqueEntity(fields="codigo", message="Código ya existe")
 * @ORM\Entity
 */
class Agencia
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
     * @var string $nombre
     *
     * @ORM\Column(name="nombre", type="string", length=200, nullable=false)
     */
    private $nombre;
    
    /**
     * @ORM\ManyToMany(targetEntity="MINSAL\GridFormBundle\Entity\Formulario")
     * @ORM\JoinTable(name="indicador_formulario",
     *      joinColumns={@ORM\JoinColumn(name="id_agencia", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="id_formulario", referencedColumnName="id")}
     *      )
     * @ORM\OrderBy({"nombre" = "ASC"})
     **/
    protected $formularios;
    
    /**
     * @ORM\ManyToMany(targetEntity="FichaTecnica")
     * @ORM\JoinTable(name="indicador_agencia",
     *      joinColumns={@ORM\JoinColumn(name="id_agencia", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="id_indicador", referencedColumnName="id")}
     *      )
     * @ORM\OrderBy({"nombre" = "ASC"})
     **/
    protected $indicadores;

    

    public function __toString()
    {
        return $this->codigo ? : '';
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
     * @return Agencia
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
     * Set nombre
     *
     * @param string $nombre
     * @return Agencia
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string 
     */
    public function getNombre()
    {
        return $this->nombre;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->indicadores = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add indicadores
     *
     * @param \MINSAL\IndicadoresBundle\Entity\FichaTecnica $indicadores
     * @return Agencia
     */
    public function addIndicadore(\MINSAL\IndicadoresBundle\Entity\FichaTecnica $indicadores)
    {
        $this->indicadores[] = $indicadores;

        return $this;
    }

    /**
     * Remove indicadores
     *
     * @param \MINSAL\IndicadoresBundle\Entity\FichaTecnica $indicadores
     */
    public function removeIndicadore(\MINSAL\IndicadoresBundle\Entity\FichaTecnica $indicadores)
    {
        $this->indicadores->removeElement($indicadores);
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

    /**
     * Add formularios
     *
     * @param \MINSAL\GridFormBundle\Entity\Formulario $formularios
     * @return Agencia
     */
    public function addFormulario(\MINSAL\GridFormBundle\Entity\Formulario $formularios)
    {
        $this->formularios[] = $formularios;

        return $this;
    }

    /**
     * Remove formularios
     *
     * @param \MINSAL\GridFormBundle\Entity\Formulario $formularios
     */
    public function removeFormulario(\MINSAL\GridFormBundle\Entity\Formulario $formularios)
    {
        $this->formularios->removeElement($formularios);
    }

    /**
     * Get formularios
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFormularios()
    {
        return $this->formularios;
    }
}
