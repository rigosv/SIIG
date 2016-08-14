<?php

namespace MINSAL\IndicadoresBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * MINSAL\IndicadoresBundle\Entity\AccesoExterno
 *
 * @ORM\Table(name="acceso_externo")
 * @UniqueEntity(fields="token", message="token ya existe")
 * @ORM\Entity
 */
class AccesoExterno
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
     * @var string $token
     *
     * @ORM\Column(name="token", type="string", length=255, nullable=false)
     */
    private $token;
    
    /**
     * @var datetime $caducidad
     *
     * @ORM\Column(name="caducidad", type="datetime", nullable=false)
     */
    private $caducidad;
    
    
    /**
     * @ORM\ManyToMany(targetEntity="MINSAL\IndicadoresBundle\Entity\GrupoIndicadores")
     **/
    protected $salas;
    
    /**
     * @ORM\ManyToOne(targetEntity="User")
     * */
    private $usuarioCrea;
    

    

    public function __toString()
    {
        return $this->codigo ? : '';
    }
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->salas = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set token
     *
     * @param string $token
     *
     * @return AccesoExterno
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set caducidad
     *
     * @param \DateTime $caducidad
     *
     * @return AccesoExterno
     */
    public function setCaducidad($caducidad)
    {
        $this->caducidad = $caducidad;

        return $this;
    }

    /**
     * Get caducidad
     *
     * @return \DateTime
     */
    public function getCaducidad()
    {
        return $this->caducidad;
    }

    /**
     * Add sala
     *
     * @param \MINSAL\IndicadoresBundle\Entity\GrupoIndicadores $sala
     *
     * @return AccesoExterno
     */
    public function addSala(\MINSAL\IndicadoresBundle\Entity\GrupoIndicadores $sala)
    {
        $this->salas[] = $sala;

        return $this;
    }

    /**
     * Remove sala
     *
     * @param \MINSAL\IndicadoresBundle\Entity\GrupoIndicadores $sala
     */
    public function removeSala(\MINSAL\IndicadoresBundle\Entity\GrupoIndicadores $sala)
    {
        $this->salas->removeElement($sala);
    }

    /**
     * Get salas
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSalas()
    {
        return $this->salas;
    }

    /**
     * Set usuarioCrea
     *
     * @param \MINSAL\IndicadoresBundle\Entity\User $usuarioCrea
     *
     * @return AccesoExterno
     */
    public function setUsuarioCrea(\MINSAL\IndicadoresBundle\Entity\User $usuarioCrea = null)
    {
        $this->usuarioCrea = $usuarioCrea;

        return $this;
    }

    /**
     * Get usuarioCrea
     *
     * @return \MINSAL\IndicadoresBundle\Entity\User
     */
    public function getUsuarioCrea()
    {
        return $this->usuarioCrea;
    }
}
