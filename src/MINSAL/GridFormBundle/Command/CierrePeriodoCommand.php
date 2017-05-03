<?php

namespace MINSAL\GridFormBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CierrePeriodoCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
                ->setName('periodo-ingreso-datos:cierre')
                ->setDescription('Verifica si corresponde el cierre de periodo de ingreso de datos')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        //Recuperar la fecha actual
        $ahora = new \DateTime("now");
        $anio = $ahora->format('Y');
        $mes = $ahora->format('m');
        $dia = $ahora->format('d');
        
        if ($dia > 1){
            if ($mes == 1){
                $anio_ant = $anio - 1;
                $mes_ant = '12';
            } else {
                $anio_ant = $anio;
                $mes_ant = str_pad($mes - 1, 2, "0", STR_PAD_LEFT);
            }
            $em->getRepository('GridFormBundle:PeriodoIngreso')->cerrarPeriodo($anio_ant, $mes_ant, 'calidad');
        }

        
    }

}
