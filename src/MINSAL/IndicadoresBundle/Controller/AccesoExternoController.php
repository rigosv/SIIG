<?php

namespace MINSAL\IndicadoresBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
    

class AccesoExternoController extends Controller {

    /**
     * @Route("/ae/{token}/", name="sala_acceso_externo")
     */
    public function salaAccesoExterno($token) {
        $em = $this->getDoctrine()->getManager();
        $ahora = new \DateTime();

        $ae = $em->getRepository('IndicadoresBundle:AccesoExterno')->findOneBy(array('token' => $token));

        if ($ae == null and $ahora <= $ae->getCaducidad()) {
            throw $this->createNotFoundException($this->get('translator')->trans('_token_no_valido_'));
        }

        $this->accesoExterno('externo', 'externo');
        
        return $this->redirectToRoute('admin_minsal_indicadores_fichatecnica_tablero', array('token' => $token));
        
    }

    /**
     * @Route("/externo/autenticar/{user}/{pw}", name="autenticar")
     */
    public function accesoExterno($user, $pw) {
        $em = $this->getDoctrine()->getManager();
        $usuarioBD = $em->getRepository("IndicadoresBundle:User")->findOneBy(array('username' => $user));

        // Get the encoder for the users password
        $encoder_service = $this->get('security.encoder_factory');
        $encoder = $encoder_service->getEncoder($usuarioBD);

        //Verificar si el password es correcto
        if ($encoder->isPasswordValid($usuarioBD->getPassword(), $pw, $usuarioBD->getSalt())) {
            $token = new UsernamePasswordToken($usuarioBD, $pw, $this->container->getParameter('fos_user.firewall_name'), $usuarioBD->getRoles());

            $this->get('security.token_storage')->setToken($token);

            $event = new InteractiveLoginEvent($this->getRequest(), $token);
            $this->get('event_dispatcher')->dispatch('security.interactive_login', $event);

        } else {
            throw $this->createNotFoundException($this->get('translator')->trans('_clave_no_valida_'));
        }
    }
    
    /**
     * @Route("/externo/autenticar/ppal/{user}/{pw}", name="autenticar")
     */
    public function accesoExternoAPrincipal($user, $pw) {
        $this->accesoExterno($user, $pw);
        
        return $this->redirectToRoute('_inicio');
    }

}
