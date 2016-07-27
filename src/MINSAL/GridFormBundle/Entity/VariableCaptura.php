<?php

namespace MINSAL\GridFormBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * MINSAL\GridFormBundle\Entity\VariableCaptura
 *
 * @ORM\Table(name="variable_captura")
 * @UniqueEntity(fields="codigo", message="Código ya existe")
 * @ORM\Entity
 */
class VariableCaptura
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
     * @ORM\Column(name="codigo", type="string", length=200, nullable=false)
     */
    private $codigo;
    
    /**
     * @var string $texto_ayuda
     *
     * @ORM\Column(name="texto_ayuda", type="text", nullable=true)
     */
    private $textoAyuda;
    
    /**
     * @var string $esPoblacion
     *
     * @ORM\Column(name="es_poblacion", type="boolean", nullable=true, options={"default" = false})
     */
    private $esPoblacion;
    
    
    /**
     * @var string $descripcion
     *
     * @ORM\Column(name="descripcion", type="text", nullable=false)
     */
    private $descripcion;
    
    /**
     * @var string $posicion
     *
     * @ORM\Column(name="posicion", type="float", nullable=true)
     */
    private $posicion;
    
    /**
     * @var string $nivelIndentacion
     *
     * @ORM\Column(name="nivel_indentacion", type="integer", nullable=true)
     */
    private $nivelIndentacion;
    
    /**
     * @ORM\ManyToOne(targetEntity="CategoriaVariableCaptura")
     * @ORM\JoinColumn(name="id_categoria_captura", referencedColumnName="id")
     * */
    private $categoria;
    
    /**
     * @ORM\ManyToOne(targetEntity="AreaVariableCaptura")
     * */
    private $area;
    
    /**
     * @ORM\ManyToOne(targetEntity="Formulario", inversedBy="variables")
     * */
    private $formulario;
    
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * 
     * @ORM\ManyToMany(targetEntity="Indicador", mappedBy="criterios")
     * */
    private $indicadores;
    
    /**
     * @var string $regla_validacion
     *
     * @ORM\Column(name="regla_validacion", type="string", length=100, nullable=true)
     */
    private $reglaValidacion;
    
    /**
     * @var string $esSeparador
     *
     * @ORM\Column(name="es_separador", type="boolean", nullable=true, options={"default" = false})
     */
    private $esSeparador;
    
    /**
     * @ORM\ManyToOne(targetEntity="TipoControl")
     * @ORM\JoinColumn(name="id_tipo_control", referencedColumnName="id")
     * */
    private $tipoControl;
    
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="RangoAlerta", inversedBy="criterios")
     * @ORM\OrderBy({"limiteInferior" = "ASC", "limiteSuperior" = "ASC", "color" = "ASC"})
     **/
    private $alertas;
    

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
     * @return VariableCaptura
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
     * @return VariableCaptura
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
     * Set categoria
     *
     * @param \MINSAL\GridFormBundle\Entity\CategoriaVariableCaptura $categoria
     * @return VariableCaptura
     */
    public function setCategoria(\MINSAL\GridFormBundle\Entity\CategoriaVariableCaptura $categoria = null)
    {
        $this->categoria = $categoria;

        return $this;
    }

    /**
     * Get categoria
     *
     * @return \MINSAL\GridFormBundle\Entity\CategoriaVariableCaptura 
     */
    public function getCategoria()
    {
        return $this->categoria;
    }
    
    public function __toString() {
        return '(' . $this->getCodigo() . ')' . $this->descripcion;
    }

    /**
     * Set textoAyuda
     *
     * @param string $textoAyuda
     * @return VariableCaptura
     */
    public function setTextoAyuda($textoAyuda)
    {
        $this->textoAyuda = $textoAyuda;

        return $this;
    }

    /**
     * Get textoAyuda
     *
     * @return string 
     */
    public function getTextoAyuda()
    {
        return $this->textoAyuda;
    }

    /**
     * Set esPoblacion
     *
     * @param boolean $esPoblacion
     * @return VariableCaptura
     */
    public function setEsPoblacion($esPoblacion)
    {
        $this->esPoblacion = $esPoblacion;

        return $this;
    }

    /**
     * Get esPoblacion
     *
     * @return boolean 
     */
    public function getEsPoblacion()
    {
        return $this->esPoblacion;
    }

    

    /**
     * Set formulario
     *
     * @param \MINSAL\GridFormBundle\Entity\Formulario $formulario
     * @return VariableCaptura
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
     * Set reglaValidacion
     *
     * @param string $reglaValidacion
     * @return VariableCaptura
     */
    public function setReglaValidacion($reglaValidacion)
    {
        $this->reglaValidacion = $reglaValidacion;

        return $this;
    }

    /**
     * Get reglaValidacion
     *
     * @return string 
     */
    public function getReglaValidacion()
    {
        return $this->reglaValidacion;
    }
    
    /**
     * Set tipoControl
     *
     * @param \MINSAL\GridFormBundle\Entity\TipoControl $tipoControl
     * @return VariableCaptura
     */
    public function setTipoControl(\MINSAL\GridFormBundle\Entity\TipoControl $tipoControl = null)
    {
        $this->tipoControl = $tipoControl;

        return $this;
    }

    /**
     * Get tipoControl
     *
     * @return \MINSAL\GridFormBundle\Entity\TipoControl 
     */
    public function getTipoControl()
    {
        return $this->tipoControl;
    }

    /**
     * Set posicion
     *
     * @param integer $posicion
     * @return VariableCaptura
     */
    public function setPosicion($posicion)
    {
        $this->posicion = $posicion;

        return $this;
    }

    /**
     * Get posicion
     *
     * @return integer 
     */
    public function getPosicion()
    {
        return $this->posicion;
    }

    /**
     * Set esSeparador
     *
     * @param boolean $esSeparador
     * @return VariableCaptura
     */
    public function setEsSeparador($esSeparador)
    {
        $this->esSeparador = $esSeparador;

        return $this;
    }

    /**
     * Get esSeparador
     *
     * @return boolean 
     */
    public function getEsSeparador()
    {
        return $this->esSeparador;
    }

    /**
     * Set nivelIndentacion
     *
     * @param integer $nivelIndentacion
     * @return VariableCaptura
     */
    public function setNivelIndentacion($nivelIndentacion)
    {
        $this->nivelIndentacion = $nivelIndentacion;

        return $this;
    }

    /**
     * Get nivelIndentacion
     *
     * @return integer 
     */
    public function getNivelIndentacion()
    {
        return $this->nivelIndentacion;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->alertas = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add alertas
     *
     * @param \MINSAL\GridFormBundle\Entity\RangoAlerta $alertas
     * @return VariableCaptura
     */
    public function addAlerta(\MINSAL\GridFormBundle\Entity\RangoAlerta $alertas)
    {
        $this->alertas[] = $alertas;

        return $this;
    }

    /**
     * Remove alertas
     *
     * @param \MINSAL\GridFormBundle\Entity\RangoAlerta $alertas
     */
    public function removeAlerta(\MINSAL\GridFormBundle\Entity\RangoAlerta $alertas)
    {
        $this->alertas->removeElement($alertas);
    }

    /**
     * Get alertas
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAlertas()
    {
        return $this->alertas;
    }


    /**
     * Add indicadores
     *
     * @param \MINSAL\GridFormBundle\Entity\Indicador $indicadores
     * @return VariableCaptura
     */
    public function addIndicadore(\MINSAL\GridFormBundle\Entity\Indicador $indicadores)
    {
        $this->indicadores[] = $indicadores;

        return $this;
    }

    /**
     * Remove indicadores
     *
     * @param \MINSAL\GridFormBundle\Entity\Indicador $indicadores
     */
    public function removeIndicadore(\MINSAL\GridFormBundle\Entity\Indicador $indicadores)
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
     * Set area
     *
     * @param \MINSAL\GridFormBundle\Entity\AreaVariableCaptura $area
     *
     * @return VariableCaptura
     */
    public function setArea(\MINSAL\GridFormBundle\Entity\AreaVariableCaptura $area = null)
    {
        $this->area = $area;

        return $this;
    }

    /**
     * Get area
     *
     * @return \MINSAL\GridFormBundle\Entity\AreaVariableCaptura
     */
    public function getArea()
    {
        return $this->area;
    }
}
