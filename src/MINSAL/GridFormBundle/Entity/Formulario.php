<?php

namespace MINSAL\GridFormBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * MINSAL\GridFormBundle\Entity\Formulario
 *
 * @ORM\Table(name="costos.formulario")
 * @UniqueEntity(fields="codigo", message="Código ya existe")
 * @ORM\Entity(repositoryClass="MINSAL\GridFormBundle\Entity\FormularioRepository")
 */
class Formulario
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="costos.formulario_id_seq")
     */
    private $id;

    /**
     * @var string $codigo
     *
     * @ORM\Column(name="codigo", type="string", length=40, nullable=false)
     */
    private $codigo;

    /**
     * @var string $nombre
     *
     * @ORM\Column(name="nombre", type="string", length=100, nullable=false)
     */
    private $nombre;

    /**
     * @var string $descripcion
     *
     * @ORM\Column(name="descripcion", type="text", nullable=true)
     */
    private $descripcion;

    /**
     * @var string $areaCosteo
     *
     * @ORM\Column(name="area_costeo", type="string", length=50, nullable=true)
     */
    private $areaCosteo;

    /**
     * @var string $columnas_fijas
     *
     * @ORM\Column(name="columnas_fijas", type="integer", nullable=true)
     */
    private $columnasFijas;

    /**
     * @var string $periodoLecturaDatos
     *
     * @ORM\Column(name="periodo_lectura_datos", type="string", length=20, nullable=true)
     */
    private $periodoLecturaDatos;
    
    /**
     * @var string $formaEvaluacion
     *
     * @ORM\Column(name="forma_evaluacion", type="string", length=50, nullable=true)
     */
    private $formaEvaluacion;

    /**
     * @var string $rutaManualUso
     *
     * @ORM\Column(name="ruta_manual_uso", type="string", length=250, nullable=true)
     */
    private $rutaManualUso;

    /**
     * @var string $sql_lectura_datos
     *
     * @ORM\Column(name="sql_lectura_datos", type="text", nullable=true)
     */
    private $sqlLecturaDatos;

    /**
     * @var string $ajustarAltoFila
     *
     * @ORM\Column(name="ajustar_alto_fila", type="boolean", nullable=true)
     */
    private $ajustarAltoFila;

    /**
     * @var string $evaluacionPorExpedientes
     *
     * @ORM\Column(name="evaluacion_por_expedientes", type="boolean", nullable=true)
     */
    private $evaluacionPorExpedientes;

    /**
     * @var string $posicion
     *
     * @ORM\Column(name="posicion", type="float", nullable=true)
     */
    private $posicion;
    
    
    /**
     * @var string $tituloColumnas
     *
     * @ORM\Column(name="titulo_columnas", type="text", nullable=true)
     */
    private $tituloColumnas;

    /**
     * @var string $ocultarNumeroFila
     *
     * @ORM\Column(name="ocultar_numero_fila", type="boolean", nullable=true)
     */
    private $ocultarNumeroFila;

    /**
     * @var string $meta
     *
     * @ORM\Column(name="meta", type="float", nullable=true)
     */
    private $meta;

    /**
     * @var string $noOrdenarPorFila
     *
     * @ORM\Column(name="no_ordenar_fila", type="boolean", nullable=true)
     */
    private $noOrdenarPorFila;
    
    /**
     * @var string $calculoFilas
     *
     * @ORM\Column(name="calculo_filas", type="text", nullable=true)
     */
    private $calculoFilas;
    
    /**
     * @ORM\ManyToOne(targetEntity="MINSAL\IndicadoresBundle\Entity\Conexion")
     * */
    private $conexionOrigenExpedientes;
    
    /**
     * @var string $origenNumerosExpedientes
     *
     * @ORM\Column(name="origen_num_expedientes", type="text", nullable=true)
     */
    private $origenNumerosExpedientes;
        
    
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Campo", inversedBy="formularios")
     * @ORM\JoinTable(name="costos.formulario_campo")
     * @ORM\OrderBy({"posicion" = "ASC"})
     **/
    private $campos;

    /**
    * @var \Doctrine\Common\Collections\ArrayCollection
    * @ORM\OneToMany(targetEntity="GrupoColumnas", mappedBy="formulario", cascade={"all"}, orphanRemoval=true)
    * @ORM\OrderBy({"descripcion" = "ASC"})
    */
    private $gruposColumnas;
        
    /**
    * @var \Doctrine\Common\Collections\ArrayCollection
    * @ORM\OneToMany(targetEntity="Formulario", mappedBy="formularioSup")
    */
    private $grupoFormularios;
    
    /**
     * @ORM\ManyToOne(targetEntity="Formulario", inversedBy="grupoFormularios")
     * @ORM\JoinColumn(name="id_formulario_sup", referencedColumnName="id")
     * */
    private $formularioSup;

    /**
    * @var \Doctrine\Common\Collections\ArrayCollection
    * @ORM\OneToMany(targetEntity="VariableCaptura", mappedBy="formulario", cascade={"all"}, orphanRemoval=true)
    * @ORM\OrderBy({"descripcion" = "ASC"})
    */
    private $variables;
    
    /**
    * @var \Doctrine\Common\Collections\ArrayCollection
    * @ORM\OneToMany(targetEntity="Indicador", mappedBy="formulario", cascade={"all"}, orphanRemoval=true)
    * @ORM\OrderBy({"descripcion" = "ASC"})
    */
    private $indicadores;

    /**
     * @ORM\ManyToOne(targetEntity="MINSAL\IndicadoresBundle\Entity\OrigenDatos")
     * */
    private $origenDatos;


    public function __toString()
    {
        return $this->nombre ? : '';
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

    public function setIdentificador($id)
    {
        return $this->id = $id;
    }

    /**
     * Set codigo
     *
     * @param string $codigo
     * @return Formulario
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
     * @return Formulario
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
     * Set descripcion
     *
     * @param string $descripcion
     * @return Formulario
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
     * Add gruposColumnas
     *
     * @param \MINSAL\GridFormBundle\Entity\GrupoColumnas $gruposColumnas
     * @return Formulario
     */
    public function addGruposColumna(\MINSAL\GridFormBundle\Entity\GrupoColumnas $gruposColumnas)
    {
        $this->gruposColumnas[] = $gruposColumnas;

        return $this;
    }

    /**
     * Remove gruposColumnas
     *
     * @param \MINSAL\GridFormBundle\Entity\GrupoColumnas $gruposColumnas
     */
    public function removeGruposColumna(\MINSAL\GridFormBundle\Entity\GrupoColumnas $gruposColumnas)
    {
        $this->gruposColumnas->removeElement($gruposColumnas);
    }

    /**
     * Get gruposColumnas
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGruposColumnas()
    {
        return $this->gruposColumnas;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->campos = new \Doctrine\Common\Collections\ArrayCollection();
        $this->gruposColumnas = new \Doctrine\Common\Collections\ArrayCollection();
        $this->gruposFormularios = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add campos
     *
     * @param \MINSAL\GridFormBundle\Entity\Campo $campos
     * @return Formulario
     */
    public function addCampo(\MINSAL\GridFormBundle\Entity\Campo $campos)
    {
        $this->campos[] = $campos;

        return $this;
    }

    /**
     * Remove campos
     *
     * @param \MINSAL\GridFormBundle\Entity\Campo $campos
     */
    public function removeCampo(\MINSAL\GridFormBundle\Entity\Campo $campos)
    {
        $this->campos->removeElement($campos);
    }

    /**
     * Get campos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCampos()
    {
        return $this->campos;
    }

    /**
     * Set origenDatos
     *
     * @param \MINSAL\IndicadoresBundle\Entity\OrigenDatos $origenDatos
     * @return Formulario
     */
    public function setOrigenDatos(\MINSAL\IndicadoresBundle\Entity\OrigenDatos $origenDatos = null)
    {
        $this->origenDatos = $origenDatos;

        return $this;
    }

    /**
     * Get origenDatos
     *
     * @return \MINSAL\IndicadoresBundle\Entity\OrigenDatos
     */
    public function getOrigenDatos()
    {
        return $this->origenDatos;
    }

    /**
     * Set areaCosteo
     *
     * @param string $areaCosteo
     * @return Formulario
     */
    public function setAreaCosteo($areaCosteo)
    {
        $this->areaCosteo = $areaCosteo;

        return $this;
    }

    /**
     * Get areaCosteo
     *
     * @return string
     */
    public function getAreaCosteo()
    {
        return $this->areaCosteo;
    }

    /**
     * Set columnasFijas
     *
     * @param integer $columnasFijas
     * @return Formulario
     */
    public function setColumnasFijas($columnasFijas)
    {
        $this->columnasFijas = $columnasFijas;

        return $this;
    }

    /**
     * Get columnasFijas
     *
     * @return integer
     */
    public function getColumnasFijas()
    {
        return $this->columnasFijas;
    }

    /**
     * Set periodoLecturaDatos
     *
     * @param string $periodoLecturaDatos
     * @return Formulario
     */
    public function setPeriodoLecturaDatos($periodoLecturaDatos)
    {
        $this->periodoLecturaDatos = $periodoLecturaDatos;

        return $this;
    }

    /**
     * Get periodoLecturaDatos
     *
     * @return string
     */
    public function getPeriodoLecturaDatos()
    {
        return $this->periodoLecturaDatos;
    }

    /**
     * Set sqlLecturaDatos
     *
     * @param string $sqlLecturaDatos
     * @return Formulario
     */
    public function setSqlLecturaDatos($sqlLecturaDatos)
    {
        $this->sqlLecturaDatos = $sqlLecturaDatos;

        return $this;
    }

    /**
     * Get sqlLecturaDatos
     *
     * @return string
     */
    public function getSqlLecturaDatos()
    {
        return $this->sqlLecturaDatos;
    }

    /**
     * Set rutaManualUso
     *
     * @param string $rutaManualUso
     * @return Formulario
     */
    public function setRutaManualUso($rutaManualUso)
    {
        $this->rutaManualUso = $rutaManualUso;

        return $this;
    }

    /**
     * Get rutaManualUso
     *
     * @return string
     */
    public function getRutaManualUso()
    {
        return $this->rutaManualUso;
    }

    /**
     * Set ajustarAltoFila
     *
     * @param boolean $ajustarAltoFila
     * @return Formulario
     */
    public function setAjustarAltoFila($ajustarAltoFila)
    {
        $this->ajustarAltoFila = $ajustarAltoFila;

        return $this;
    }

    /**
     * Get ajustarAltoFila
     *
     * @return boolean
     */
    public function getAjustarAltoFila()
    {
        return $this->ajustarAltoFila;
    }

    /**
     * Set tituloColumnas
     *
     * @param string $tituloColumnas
     * @return Formulario
     */
    public function setTituloColumnas($tituloColumnas)
    {
        $this->tituloColumnas = $tituloColumnas;

        return $this;
    }

    /**
     * Get tituloColumnas
     *
     * @return string
     */
    public function getTituloColumnas()
    {
        return $this->tituloColumnas;
    }

    /**
     * Add variables
     *
     * @param \MINSAL\GridFormBundle\Entity\VariableCaptura $variables
     * @return Formulario
     */
    public function addVariable(\MINSAL\GridFormBundle\Entity\VariableCaptura $variables)
    {
        $this->variables[] = $variables;

        return $this;
    }

    /**
     * Remove variables
     *
     * @param \MINSAL\GridFormBundle\Entity\VariableCaptura $variables
     */
    public function removeVariable(\MINSAL\GridFormBundle\Entity\VariableCaptura $variables)
    {
        $this->variables->removeElement($variables);
    }

    /**
     * Get variables
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * Set ocultarNumeroFila
     *
     * @param boolean $ocultarNumeroFila
     * @return Formulario
     */
    public function setOcultarNumeroFila($ocultarNumeroFila)
    {
        $this->ocultarNumeroFila = $ocultarNumeroFila;

        return $this;
    }

    /**
     * Get ocultarNumeroFila
     *
     * @return boolean
     */
    public function getOcultarNumeroFila()
    {
        return $this->ocultarNumeroFila;
    }

    /**
     * Set noOrdenarPorFila
     *
     * @param boolean $noOrdenarPorFila
     * @return Formulario
     */
    public function setNoOrdenarPorFila($noOrdenarPorFila)
    {
        $this->noOrdenarPorFila = $noOrdenarPorFila;

        return $this;
    }

    /**
     * Get noOrdenarPorFila
     *
     * @return boolean
     */
    public function getNoOrdenarPorFila()
    {
        return $this->noOrdenarPorFila;
    }

    /**
     * Set meta
     *
     * @param float $meta
     * @return Formulario
     */
    public function setMeta($meta)
    {
        $this->meta = $meta;

        return $this;
    }

    /**
     * Get meta
     *
     * @return float
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * Add grupoFormularios
     *
     * @param \MINSAL\GridFormBundle\Entity\Formulario $grupoFormularios
     * @return Formulario
     */
    public function addGrupoFormulario(\MINSAL\GridFormBundle\Entity\Formulario $grupoFormularios)
    {
        $this->grupoFormularios->add($grupoFormularios);
        $grupoFormularios->setFormularioSup($this);
        return $this;
    }

    /**
     * Remove grupoFormularios
     *
     * @param \MINSAL\GridFormBundle\Entity\Formulario $grupoFormularios
     */
    public function removeGrupoFormulario(\MINSAL\GridFormBundle\Entity\Formulario $grupoFormularios)
    {
        $this->grupoFormularios->removeElement($grupoFormularios);
        //$grupoFormularios->setFormularioSup(null);
    }

    /**
     * Get grupoFormularios
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGrupoFormularios()
    {
        return $this->grupoFormularios;
    }
    
    public function setGrupoFormularios($formularios)
    {        
        if (count($formularios) > 0) {
            foreach ($formularios as $i) {
                $this->addGrupoFormulario($i);
            }
        }

        return $this;
    }

    /**
     * Set formularioSup
     *
     * @param \MINSAL\GridFormBundle\Entity\Formulario $formularioSup
     * @return Formulario
     */
    public function setFormularioSup(\MINSAL\GridFormBundle\Entity\Formulario $formularioSup = null)
    {
        $this->formularioSup = $formularioSup;

        return $this;
    }

    /**
     * Get formularioSup
     *
     * @return \MINSAL\GridFormBundle\Entity\Formulario 
     */
    public function getFormularioSup()
    {
        return $this->formularioSup;
    }

    /**
     * Set calculoFilas
     *
     * @param string $calculoFilas
     * @return Formulario
     */
    public function setCalculoFilas($calculoFilas)
    {
        $this->calculoFilas = $calculoFilas;

        return $this;
    }

    /**
     * Get calculoFilas
     *
     * @return string 
     */
    public function getCalculoFilas()
    {
        return $this->calculoFilas;
    }

    /**
     * Set formaEvaluacion
     *
     * @param string $formaEvaluacion
     * @return Formulario
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
     * Add indicadores
     *
     * @param \MINSAL\GridFormBundle\Entity\Indicador $indicadores
     * @return Formulario
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
     * Set evaluacionPorExpedientes
     *
     * @param boolean $evaluacionPorExpedientes
     * @return Formulario
     */
    public function setEvaluacionPorExpedientes($evaluacionPorExpedientes)
    {
        $this->evaluacionPorExpedientes = $evaluacionPorExpedientes;

        return $this;
    }

    /**
     * Get evaluacionPorExpedientes
     *
     * @return boolean 
     */
    public function getEvaluacionPorExpedientes()
    {
        return $this->evaluacionPorExpedientes;
    }

    /**
     * Set posicion
     *
     * @param float $posicion
     * @return Formulario
     */
    public function setPosicion($posicion)
    {
        $this->posicion = $posicion;

        return $this;
    }

    /**
     * Get posicion
     *
     * @return float 
     */
    public function getPosicion()
    {
        return $this->posicion;
    }    

    /**
     * Set origenNumerosExpedientes
     *
     * @param string $origenNumerosExpedientes
     *
     * @return Formulario
     */
    public function setOrigenNumerosExpedientes($origenNumerosExpedientes)
    {
        $this->origenNumerosExpedientes = $origenNumerosExpedientes;

        return $this;
    }

    /**
     * Get origenNumerosExpedientes
     *
     * @return string
     */
    public function getOrigenNumerosExpedientes()
    {
        return $this->origenNumerosExpedientes;
    }

    /**
     * Set conexionOrigenExpedientes
     *
     * @param \MINSAL\IndicadoresBundle\Entity\Conexion $conexionOrigenExpedientes
     *
     * @return Formulario
     */
    public function setConexionOrigenExpedientes(\MINSAL\IndicadoresBundle\Entity\Conexion $conexionOrigenExpedientes = null)
    {
        $this->conexionOrigenExpedientes = $conexionOrigenExpedientes;

        return $this;
    }

    /**
     * Get conexionOrigenExpedientes
     *
     * @return \MINSAL\IndicadoresBundle\Entity\Conexion
     */
    public function getConexionOrigenExpedientes()
    {
        return $this->conexionOrigenExpedientes;
    }
}
