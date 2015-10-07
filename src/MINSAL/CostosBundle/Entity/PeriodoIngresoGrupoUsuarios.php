<?php

namespace MINSAL\CostosBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MINSAL\CostosBundle\Entity\PeriodoIngresoGrupoUsuarios
 *
 * @ORM\Table(name="costos.periodo_ingreso_grupo_usuarios")
 * @ORM\Entity
 */
class PeriodoIngresoGrupoUsuarios
{   
     /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="costos.ingreso_datos_grupo_usuarios")
     */
    private $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Sonata\UserBundle\Entity\Group")
     * */
    private $grupoUsuario;
    
    /**
     * @ORM\ManyToOne(targetEntity="Formulario")
     * */
    private $formulario;
        
    /**
     * @ORM\ManyToOne(targetEntity="PeriodoIngreso")
     * @ORM\JoinColumns({
     *                   @ORM\JoinColumn(name="anio_periodo", referencedColumnName="anio"), 
     *                   @ORM\JoinColumn(name="mes_periodo", referencedColumnName="mes")
     *                  })     
     **/
    private $periodo;
    
    

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
     * Set grupoUsuario
     *
     * @param \Application\Sonata\UserBundle\Entity\Group $grupoUsuario
     * @return PeriodoIngresoGrupoUsuarios
     */
    public function setGrupoUsuario(\Application\Sonata\UserBundle\Entity\Group $grupoUsuario = null)
    {
        $this->grupoUsuario = $grupoUsuario;

        return $this;
    }

    /**
     * Get grupoUsuario
     *
     * @return \Application\Sonata\UserBundle\Entity\Group 
     */
    public function getGrupoUsuario()
    {
        return $this->grupoUsuario;
    }

    /**
     * Set formulario
     *
     * @param \MINSAL\CostosBundle\Entity\Formulario $formulario
     * @return PeriodoIngresoGrupoUsuarios
     */
    public function setFormulario(\MINSAL\CostosBundle\Entity\Formulario $formulario = null)
    {
        $this->formulario = $formulario;

        return $this;
    }

    /**
     * Get formulario
     *
     * @return \MINSAL\CostosBundle\Entity\Formulario 
     */
    public function getFormulario()
    {
        return $this->formulario;
    }

    /**
     * Set periodo
     *
     * @param \MINSAL\CostosBundle\Entity\PeriodoIngreso $periodo
     * @return PeriodoIngresoGrupoUsuarios
     */
    public function setPeriodo(\MINSAL\CostosBundle\Entity\PeriodoIngreso $periodo = null)
    {
        $this->periodo = $periodo;

        return $this;
    }

    /**
     * Get periodo
     *
     * @return \MINSAL\CostosBundle\Entity\PeriodoIngreso 
     */
    public function getPeriodo()
    {
        return $this->periodo;
    }
}
