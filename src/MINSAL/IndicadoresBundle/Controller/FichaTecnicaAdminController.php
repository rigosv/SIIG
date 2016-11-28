<?php

namespace MINSAL\IndicadoresBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use \MINSAL\IndicadoresBundle\Entity\GrupoIndicadores;
//use Symfony\Component\Console\Input\ArrayInput;

class FichaTecnicaAdminController extends Controller {

    public function editAction($id = null) {
        $repo = $this->getDoctrine()->getManager()->getRepository('IndicadoresBundle:FichaTecnica');
        $this->admin->setRepository($repo);

        return parent::editAction($id);
    }

    public function createAction() {
        $repo = $this->getDoctrine()->getManager()->getRepository('IndicadoresBundle:FichaTecnica');
        $this->admin->setRepository($repo);

        return parent::createAction();
    }

    public function getListadoIndicadores() {
        $em = $this->getDoctrine()->getManager();
        $clasificacionUso = $em->getRepository("IndicadoresBundle:ClasificacionUso")->findBy(array(), array('descripcion' => 'ASC'));

        //Luego agregar un método para obtener la clasificacion de uso por defecto del usuario
        $usuario = $this->getUser();
        if ($usuario->getClasificacionUso()) {
            $clasificacionUsoPorDefecto = $usuario->getClasificacionUso();
        } else {
            $clasificacionUsoPorDefecto = $clasificacionUso[0];
        }
        $categorias = $em->getRepository("IndicadoresBundle:ClasificacionTecnica")->findBy(array('clasificacionUso' => $clasificacionUsoPorDefecto));

        //Indicadores asignados por usuario
        $usuarioIndicadores = ($usuario->hasRole('ROLE_SUPER_ADMIN')) ?
                $em->getRepository("IndicadoresBundle:FichaTecnica")->findBy(array(), array('nombre' => 'ASC')) :
                $usuario->getIndicadores();
        //Indicadores asignadas al grupo al que pertenece el usuario
        $indicadoresPorGrupo = array();
        foreach ($usuario->getGroups() as $grp) {
            foreach ($grp->getIndicadores() as $indicadores_grupo) {
                $indicadoresPorGrupo[] = $indicadores_grupo;
            }
        }

        $indicadores_por_usuario = array();
        $indicadores_clasificados = array();
        foreach ($usuarioIndicadores as $ind) {
            $indicadores_por_usuario[] = $ind->getId();
        }

        foreach ($indicadoresPorGrupo as $ind) {
            $indicadores_por_usuario[] = $ind->getId();
        }

        $categorias_indicador = array();
        foreach ($categorias as $cat) {
            $categorias_indicador[$cat->getId()]['cat'] = $cat;
            $categorias_indicador[$cat->getId()]['indicadores'] = array();
            $indicadores_por_categoria = $cat->getIndicadores();
            foreach ($indicadores_por_categoria as $ind) {
                if (in_array($ind->getId(), $indicadores_por_usuario)) {
                    $categorias_indicador[$cat->getId()]['indicadores'][] = $ind;
                    $indicadores_clasificados[] = $ind->getId();
                }
            }
        }

        $indicadores_no_clasificados = array();
        foreach ($usuarioIndicadores as $ind) {
            if (!in_array($ind->getId(), $indicadores_clasificados)) {
                $indicadores_no_clasificados[] = $ind;
            }
        }
        foreach ($indicadoresPorGrupo as $ind) {
            if (!in_array($ind->getId(), $indicadores_clasificados)) {
                $indicadores_no_clasificados[] = $ind;
            }
        }
        $resp = array('categorias' => $categorias_indicador,
            'clasficacion_uso' => $clasificacionUso,
            'indicadores_no_clasificados' => $indicadores_no_clasificados);

        return $resp;
    }

    /**
     * @Route("/tablero/sala/{sala}", name="tablero_sala", options={"expose"=true})
     */
    public function tableroSalaAction($sala, Request $request) {
        $em = $this->getDoctrine()->getManager();
        
        $tipo_reporte = ($request->get('indicador') != null) ? 'indicador' : 'sala';

        $html = $this->tableroAction($sala);
        $html = $html->getContent();

        $info_indicador = '';
        if ($request->get('indicador') != null) {
            //Se está cargando el reporte de la sala como reporte asociado
            //a un indicadores, recuperar el indicador para mostrar 
            //información adicional

            $id = $request->get('indicador');
            $indicador = $em->find('IndicadoresBundle:FichaTecnica', $id);
            $info_indicador .= '<BR/></BR/><BR/></BR/>'
                    . '<DIV class="col-md-12" >'
                    . '<B>Interpretación:</B><BR/>' . $indicador->getTema()
                    . '</DIV><BR/><BR/>'
                    . '<DIV class="col-md-12" >'
                    . '<B>Concepto:</B></BR>' . $indicador->getConcepto()
                    . '</DIV><BR/><BR/>'
                    . '<DIV class="col-md-12" >'
                    . '<B>Observaciones:</B><BR/>' . $indicador->getObservacion()
                    . '</div>'
            ;
        }

        $html = preg_replace("/HTTP.+/", "", $html);
        $html = preg_replace("/Cache.+/", "", $html);
        $html = preg_replace("/Date.+/", "", $html);

        $http = 'http';
        if (array_key_exists('HTTPS', $_SERVER)) {
            $http = ($_SERVER['HTTPS'] == null or $_SERVER['HTTPS'] == 'off') ? 'http' : 'https';
        }
        $html = str_replace(array('href="/bundles', 'src="/bundles', 'src="/app_dev.php'), 
                array('href="' . $http . '://' . $_SERVER['HTTP_HOST'] . $this->container->getParameter('directorio') . '/bundles',
                        'src="' . $http . '://' . $_SERVER['HTTP_HOST']. $this->container->getParameter('directorio') . '/bundles',
                        'src="' . $http . '://'. $_SERVER['HTTP_HOST'] . $this->container->getParameter('directorio') . '/app_dev.php'), $html);
        $html .= $info_indicador;
        
        try {
            $html = $this->get('knp_snappy.pdf')->getOutputFromHtml($html);
        } catch (\RuntimeException $e) {
            $matches = [];
            if (preg_match('/([^\']+)\'.$/', $e->getMessage(), $matches)) {
                $html = file_get_contents($matches[1]);
                unlink($matches[1]);
            } else  {
                throw $e;
            }
        }

        return new Response(
                $html, 200, array(
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="reporte.pdf"'
                )
        );
    }

    public function tableroAction($sala_default = null) {
        $em = $this->getDoctrine()->getManager();
        $usuario = $this->getUser();
        $usuarioSalas = array();
        
        $req = $this->getRequest();
        
        $sala_default = ($sala_default == null) ? 0 : $sala_default;
        
        if ($req->get('token') != ''){
            $ae = $em->getRepository('IndicadoresBundle:AccesoExterno')->findOneBy(array('token' => $req->get('token')));
            $ahora = new \DateTime();
            if ($ae != null and $ahora <= $ae->getCaducidad()){
                $salas = $ae->getSalas();            
                $usuarioSalas[$salas[0]->getId()] = $salas[0];
            }
        }        
        

        //Salas por usuario
        if (($usuario->hasRole('ROLE_SUPER_ADMIN'))) {
            foreach ($em->getRepository("IndicadoresBundle:GrupoIndicadores")->findBy(array(), array('nombre' => 'ASC')) as $sala) {
                $usuarioSalas[$sala->getId()] = $sala;
            }
        } else {
            foreach ($usuario->getGruposIndicadores() as $sala) {
                $usuarioSalas[$sala->getGrupoIndicadores()->getId()] = $sala->getGrupoIndicadores();
            }
        }
        //Salas asignadas al grupo al que pertenece el usuario
        foreach ($usuario->getGroups() as $grp) {
            foreach ($grp->getSalas() as $sala) {
                $usuarioSalas[$sala->getId()] = $sala;
            }
        }

        $salas = array();
        foreach ($usuarioSalas as $sala) {
            $salas[$sala->getId()]['datos_sala'] = $sala;
            $salas[$sala->getId()]['indicadores_sala'] = $em->getRepository('IndicadoresBundle:GrupoIndicadores')
                    ->getIndicadoresSala($sala);
        }

        // si hay una sala por defecto recuperar toda la información de los
        // indicadores contenidos en esta.
        $indicadoresDimensiones = array();
        if ($sala_default != 0) {
            foreach ($salas[$sala_default]['indicadores_sala'] as $ind) {
                $req_dimensiones = $this->forward('IndicadoresBundle:Indicador:getDimensiones', array('id' => $ind['idIndicador']));
                $req_datos = $this->forward('IndicadoresBundle:IndicadorREST:getIndicador', array('id' => $ind['idIndicador'],
                    'dimension' => $ind['dimension'],
                    'filtro' => $ind['filtro'],
                    'ver_sql' => false)
                );
                $indicadoresDimensiones[$ind['posicion']]['id'] = $ind['posicion'];
                $indicadoresDimensiones[$ind['posicion']]['dimensiones'] = $req_dimensiones->getContent();
                $indicadoresDimensiones[$ind['posicion']]['datos'] = $req_datos->getContent();
            }
        }
        
        $datos = $this->getListadoIndicadores();

        $confTablero = array('graficos_por_fila' => $this->container->getParameter('graficos_por_fila'),
            'ancho_area_grafico' => $this->container->getParameter('ancho_area_grafico'),
            'alto_area_grafico' => $this->container->getParameter('alto_area_grafico'),
            'titulo_sala_tamanio_fuente' => $this->container->getParameter('titulo_sala_tamanio_fuente'),
            'ocultar_menu_principal' => $this->container->getParameter('ocultar_menu_principal'),
            'directorio' => $this->container->getParameter('directorio'),
        );

        return $this->render('IndicadoresBundle:FichaTecnicaAdmin:tablero.html.twig', array(
                    'categorias' => $datos['categorias'],
                    'clasificacionUso' => $datos['clasficacion_uso'],
                    'salas' => $salas,
                    'id_sala' => $sala_default,
                    'confTablero' => $confTablero,
                    'indicadoresDimensiones' => $indicadoresDimensiones,
                    'indicadores_no_clasificados' => $datos['indicadores_no_clasificados']
        ));
    }

    public function PivotTableAction() {
        $datos = $this->getListadoIndicadores();
        $usuario = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $formularios = array();
        
        if ($usuario->hasRole('ROLE_SUPER_ADMIN') or $usuario->hasRole('ROLE_USER_CAPTURA_DATOS')) {
            //Recuperar los formularios 
            $formularios = $em->getRepository('GridFormBundle:Formulario')->findBy(array('areaCosteo'=>'almacen_datos'));            
        }

        return $this->render('IndicadoresBundle:FichaTecnicaAdmin:pivotTable.html.twig', array(
                    'categorias' => $datos['categorias'],
                    'clasificacionUso' => $datos['clasficacion_uso'],
                    'indicadores_no_clasificados' => $datos['indicadores_no_clasificados'],
                    'formularios' => $formularios
        ));
    }
    
    public function MatrizSeguimientoAction() {
        $url = $this->container->get( 'router' )->generate( 'matriz-seguimiento' );
        return new RedirectResponse( $url );
    }
    
    /**
     * @Route("/sala/{id}/fichas", name="fichas_sala", options={"expose"=true})
     */
    public function fichasSalaAction(GrupoIndicadores $sala) {
        $em = $this->getDoctrine()->getManager();

        $fichas_ = $sala->getIndicadores();
        $fichas = array();
        foreach ($fichas_ as $ficha){
            $fichas[$ficha->getIndicador()->getId()] = $ficha->getIndicador();
        }
        
        return $this->getFichas($fichas);
    }
    
    protected function getFichas($fichas){
        $salida = '';
        foreach ($fichas as $ficha) {

            $admin = $this->get('sonata.admin.ficha');
            $admin->setSubject($ficha);

            $html = $this->render($admin->getTemplate('show'), array(
                'action' => 'show',
                'object' => $ficha,
                'elements' => $admin->getShow(),
                'admin' => $admin,
                'base_template' => 'IndicadoresBundle::ajax_layout.html.twig'
            ));

            $salida .= $html->getContent() . '<BR /><BR />';
        }
        //Quitar los comentarios del código html, enlaces y aplicar estilos
        $salida = preg_replace('/<!--(.|\s)*?-->/', '', $salida);
        $salida = preg_replace('/<a(.|\s)*?>/', '', $salida);
        $salida = str_ireplace('</a>', '', $salida);
        $salida = str_ireplace('TD', "TD STYLE='border: 2px double black'", $salida);
        $salida = str_ireplace('TH', "TH STYLE='border: 2px double black'", $salida);
        $salida = str_ireplace('<TABLE', "<TABLE width=95% ", $salida);

        return new Response('<HTML>' . $salida . '</HTML>', 200, array(
                'Content-Type' => 'application/msword; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="fichas_tecnicas.doc"',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            )
        );
    }
    
    public function batchActionVerFicha($idx = null) {
        $parameterBag = $this->get('request')->request;
        $em = $this->getDoctrine()->getManager();

        $selecciones = $parameterBag->get('idx');
        $fichas = array();
        foreach ($selecciones as $ficha) {
            $fichas[] = $em->find('IndicadoresBundle:FichaTecnica', $ficha);
        }
        return $this->getFichas($fichas);
    }    
    
    /**
     * @Route("/sala/tablas-datos", name="tablas_datos_sala", options={"expose"=true})
     */
    public function tablasDatosSalaAction() {
        $em = $this->getDoctrine()->getManager();

        $req = $this->getRequest();

        $titulos = json_decode($req->get('titulos'), true);
        $tablas = json_decode($req->get('tablas'), true);
        
        $html = '<HTML><HEAD><meta http-equiv="content-type" content="text/html; charset=UTF-8" /><STYLE>table{border-collapse: collapse } td, th {border: 1px solid black} </STYLE></HEAD><BODY>';

        foreach ($titulos as $k=> $t){
            $html .= '<h3>'.$t.'</h3><BR/>'.$tablas[$k].'<BR/><BR/>';
        }
        $html .= '</body></html>';
        
        return new Response($html, 200, array(
                'Content-Type' => 'data:application/vnd.ms-excel;base64',
                'Content-Disposition' => 'attachment; filename="tablas_datos.xls"',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            )
        );
    }
}
