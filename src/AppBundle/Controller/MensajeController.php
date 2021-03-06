<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Mensaje;
use AppBundle\Form\Type\MensajeType;
use Proxies\__CG__\AppBundle\Entity\Usuario;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class MensajeController extends Controller
{
    /**
     * @Route("/marcar_leidos", name="marcar_leidos")
     */
    public function marcarLeidosAction()
    {

        $user = $this->getUser();
        if(isset($_POST['marcar-leidos'])){
            $em = $this->getDoctrine()->getManager();
            $mensajes = $em->getRepository('AppBundle:Mensaje')
                ->findBy(array('usuario'=>$user));
            foreach($mensajes as $item){
                $item->setLeido(true);
                $em->persist($item);
                $em->flush();
            }

            $this->addFlash('success', 'Mensajes marcados como leídos correctamente');
            return new RedirectResponse(
                $this->generateUrl('mensajes_usuario')
            );
        }

    }

    /**
     * @Route("/mensajes_usuario", name="mensajes_usuario")
     */
    public function mensajesAction(Request $peticion)
    {

        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        // Mensajes del usuario
        $mensajes = $em->getRepository('AppBundle:Mensaje')
            ->findBy(array('usuario' => $user->getId(), 'leido' => true));
        ;
        // Mensajes no leidos
        $em = $this->getDoctrine()->getManager();
        $noLeidos = $em->getRepository('AppBundle:Mensaje')
            ->findBy(array('leido' => false, 'usuario' => $user->getId()));

        // Mensajes enviados
        $em = $this->getDoctrine()->getManager();
        $enviados = $em->getRepository('AppBundle:Mensaje')
            ->findBy(array('remitente' => $user->getId()));




        // MENSAJES //
        $mensaje = new Mensaje();

        // crear el formulario
        $formulario1 = $this->createForm(new MensajeType(), $mensaje);

        // Procesar el formulario si se ha enviado con un POST
        $formulario1->handleRequest($peticion);

        $em = $this->getDoctrine()->getManager();
        if ($formulario1->isSubmitted() && $formulario1->isValid()){
            // Guardar el mensaje en la base de datos
            $em = $this->getDoctrine()->getManager();
            $mensaje->setRemitente($user->getId());
            $mensaje->setLeido(false);
            $mensaje->setFecha(new \DateTime());
            ;

            $em->persist($mensaje);
            $em->flush();

        }

        // FIN MENSAJES //
        // notis no leídas
        $nnl = $em->getRepository('AppBundle:Notificacion')
            ->findBy(array('usuario' => $user, 'leida' => false));


        return $this->render(':default/usuario:mensajes.html.twig', [
            'usuario' => $user,
            'mensajes' => $mensajes,
            'contadorMensajes' => count($mensajes),
            'noLeidos' => $noLeidos,
            'contadorNoLeidos' => count($noLeidos),
            'enviados' => $enviados,
            'contadorEnviados' => count($enviados),
            'formulario' => $formulario1,
            'mnl' => count($noLeidos),
            'nnl' => count($nnl)
        ]);
    }

    /**
     * @Route("/responder_mensaje/{id}", name="responder_mensaje")
     */
    public function responderMensajeAction(Mensaje $id, Request $peticion)
    {
        $user = $this->getUser();


        // MENSAJES //
        $mensaje = new Mensaje();

        // crear el formulario
        $formulario1 = $this->createForm(new MensajeType(), $mensaje);

        // Procesar el formulario si se ha enviado con un POST
        $formulario1->handleRequest($peticion);

        // Usuario del mensaje
        $em = $this->getDoctrine()->getManager();
        $remitente = $em->getRepository('AppBundle:Usuario')
            ->find($id->getRemitente());
        $em = $this->getDoctrine()->getManager();
        if ($formulario1->isSubmitted() && $formulario1->isValid()){
            // Guardar el mensaje en la base de datos
            $em = $this->getDoctrine()->getManager();
            $mensaje->setRemitente($user->getId());
            $nombreRemi = $em->getRepository('AppBundle:Usuario')
                ->find($user->getId());
            $mensaje->setNombreRemitente($nombreRemi->getNombreUsuario());
            $mensaje->setLeido(false);
            $mensaje->setFecha(new \DateTime());
            $mensaje->setUsuario($remitente);

            $em->persist($mensaje);
            $em->flush();

            $this->addFlash('success', 'Mensaje respondido de forma correcta');

            return new RedirectResponse(
                $this->generateUrl('mensajes_usuario')
            );

        }

        // FIN MENSAJES //


        // Mensajes no leidos
        $em = $this->getDoctrine()->getManager();
        $noLeidos = $em->getRepository('AppBundle:Mensaje')
            ->findBy(array('leido' => false, 'usuario' => $user->getId()));
        // notis no leídas
        $nnl = $em->getRepository('AppBundle:Notificacion')
            ->findBy(array('usuario' => $user, 'leida' => false));

        return $this->render(':default/usuario:ResponderMensajes.html.twig', [
            'usuario' => $user,
            'formulario' => $formulario1->createView(),
            'mensaje' => $id,
            'remitente' => $remitente,
            'mnl' => count($noLeidos),
            'nnl' => count($nnl)
        ]);

    }


    /**
     * @Route("/eliminar_mensaje/{id}", name="eliminar_mensaje")
     */
    public function eliminarComentarioAction(Mensaje $id)
    {
        if(isset($_POST['eliminar_mens'])){
            $em = $this->getDoctrine()->getManager();
            $mensaje = $em->getRepository('AppBundle:Mensaje')
                ->find($id);
            $em->remove($mensaje);
            $em->flush();

            $this->addFlash('success', 'Mensaje eliminado de forma correcta');

            return new RedirectResponse(
                $this->generateUrl('mensajes_usuario')
            );
        }

    }


}



