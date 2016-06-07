<?php

namespace MINSAL\GridFormBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MINSAL\GridFormBundle\Entity\PeriodoIngreso
 *
 * @ORM\Table(name="costos.periodo_ingreso")
 * @ORM\Entity
 */
class PeriodoIngreso
{   
    private $meses = array('01'=>'Ene.',
                '02'=>'Feb.',
                '03'=>'Mar.',
                '04'=>'Abr.',
                '05'=>'May.',
                '06'=>'Jun.',
                '07'=>'Jul.',
                '08'=>'Ago.',
                '09'=>'Sep.',
                '10'=>'Oct.',
                '11'=>'Nov.',
                '12'=>'Dic.'
        );
    /**
     * @var string $anio
     *
     * @ORM\Id
     * @ORM\Column(name="anio", type="integer", nullable=false)
     */
    private $anio;

    /**
     * @var string $mes
     * @ORM\Id
     * @ORM\Column(name="mes", type="string", length=20, nullable=false)
     */
    private $mes;

    /**
     * Set anio
     *
     * @param integer $anio
     * @return FormularioPeriodoIngreso
     */
    public function setAnio($anio)
    {
        $this->anio = $anio;

        return $this;
    }

    /**
     * Get anio
     *
     * @return integer 
     */
    public function getAnio()
    {
        return $this->anio;
    }

    /**
     * Set mes
     *
     * @param integer $mes
     * @return FormularioPeriodoIngreso
     */
    public function setMes($mes)
    {
        $this->mes = $mes;

        return $this;
    }

    /**
     * Get mes
     *
     * @return integer 
     */
    public function getMes()
    {
        return $this->mes;
    }
    
    public function getMesTexto() {        
        return (array_key_exists($this->mes, $this->meses)) ?  $this->meses[$this->mes] : $this->mes;
    }
    
    public function __toString() {
        return $this->mes.'/'.$this->anio;
    }
}
