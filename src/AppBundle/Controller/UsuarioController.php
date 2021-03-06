<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Mensaje;
use AppBundle\Form\Type\CuentaEmailType;
use AppBundle\Form\Type\CuentaType;
use AppBundle\Form\Type\MensajeType;
use AppBundle\Form\Type\UsuarioModificarType;
use AppBundle\Form\Type\UsuarioType;
use AppBundle\Entity\Usuario;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
class UsuarioController extends Controller
{


    /**
     * @Route("/nuevo_usuario", name="nuevo_usuario")
     */
    public function registroAction(Request $peticion)
    {
        $usuario = new Usuario();

        // crear el formulario
        $formulario = $this->createForm(new UsuarioType(), $usuario);

        // Procesar el formulario si se ha enviado con un POST
        $formulario->handleRequest($peticion);

        // Si se ha enviado y el contenido es válido, guardar los cambios
        if ($formulario->isSubmitted() && $formulario->isValid()){
            $usuario->setesAdmin(false);
            $usuario->setEsCreador(false);
            $usuario->setEsParticipante(false);
            // Guardar el usuario en la base de datos
                $em = $this->getDoctrine()->getManager();
                $helper =  $password = $this->container->get('security.password_encoder');
                $usuario->setPass($helper->encodePassword($usuario, $usuario->getPass()));
                $usuario->setImagen('/uploads/perfiles/perfil_demo.jpg');

                $em->persist($usuario);
                $em->flush();

            $this->addFlash('success', 'Se ha completado el registro de forma correcta. Elija su imagen de perfil para terminar de configurar su perfil');
            return new RedirectResponse(
                $this->generateUrl('imagen_usuario')
            );

        }elseif($formulario->isSubmitted() && !$formulario->isValid()){
            $this->addFlash('danger', 'Ha habido un error al completar el registro, compruebe los datos introducidos');
    }


        return $this->render(':default/usuario:registro.html.twig', array(
            'usuario' => $usuario,
            'formulario' => $formulario->createView()
        ));
    }

    /**
     * @Route("/perfil/{id}", name="perfil")
     */
    public function perfilAction(Request $peticion, Usuario $id)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        //participaciones
        $par = $user->getParticipaciones();
        $usuario_perfil = $em->getRepository('AppBundle:Usuario')
            ->find($id);
        $proyectos_usuario_perfil = $em->getRepository('AppBundle:Proyecto')
            ->findby(array('usuario' => $usuario_perfil->getId()));
        $proyectos = $em->getRepository('AppBundle:Proyecto')
            ->findby(array('usuario' => $user->getId()));

        // MENSAJES //
        $mensaje = new Mensaje();

        // crear el formulario
        $formulario1 = $this->createForm(new MensajeType(), $mensaje);

        // Procesar el formulario si se ha enviado con un POST
        $formulario1->handleRequest($peticion);
        ;
        if ($formulario1->isSubmitted() && $formulario1->isValid()){
            // Guardar el mensaje en la base de datos
            $em = $this->getDoctrine()->getManager();
            $mensaje->setRemitente($user->getId());
            $mensaje->setLeido(false);
            $mensaje->setFecha(new \DateTime());
            $mensaje->setUsuario($usuario_perfil);

            $em->persist($mensaje);
            $em->flush();

            $this->addFlash('success', 'Mensaje enviado de forma correcta');

        }

        // FIN MENSAJES //
        // mensajes no leidos
        $mnl = $em->getRepository('AppBundle:Mensaje')
            ->findBy(array('usuario' => $user, 'leido' => false));
        // notis no leídas
        $nnl = $em->getRepository('AppBundle:Notificacion')
            ->findBy(array('usuario' => $user, 'leida' => false));
        return $this->render(':default/usuario:perfil.html.twig',[
            'usuario' => $user,
            'proyectos' => $proyectos,
            'usuario_perfil' => $usuario_perfil,
            'formularioMensaje' => $formulario1->createView(),
            'proyectos_usuario_perfil' => $proyectos_usuario_perfil,
            'mnl' => count($mnl),
            'nnl' => count($nnl),
            'part' => count($par)
        ]);
    }

    /**
     * @Route("/editarPerfil/{id}", name="editarPerfil")
     *
     */
    public function editarPerfilAction(Request $peticion, Usuario $id)
    {
        $user = $this->getUser();
        //redirección si no es el usuario
        if($id->getId() != $user->getId()){
            if($user->getesAdmin() == false) {
                $this->addFlash('danger', 'Acceso denegado');
                return new RedirectResponse(
                    $this->generateUrl('inicio')

                );
            }
        }
        $em = $this->getDoctrine()->getManager();
        $usuario = $em->getRepository('AppBundle:Usuario')
            ->find($id);
        // crear el formulario
        $formulario = $this->createForm(new UsuarioModificarType(), $usuario);
        // Procesar el formulario si se ha enviado con un POST
        $formulario->handleRequest($peticion);
        // Si se ha enviado y el contenido es válido, guardar los cambios
        if ($formulario->isSubmitted() && $formulario->isValid()){
            // Guardar el mensaje en la base de datos
            $em = $this->getDoctrine()->getManager();

            $em->persist($usuario);
            $em->flush();
            $ok = 'Usuario modificado de forma correcta';
        }else{
            $ok = '';
        }
        // mensajes no leidos
        $mnl = $em->getRepository('AppBundle:Mensaje')
            ->findBy(array('usuario' => $user, 'leido' => false));
        // notis no leídas
        $nnl = $em->getRepository('AppBundle:Notificacion')
            ->findBy(array('usuario' => $user, 'leida' => false));
        return $this->render(':default/usuario:editarPerfil.html.twig',[
            'usuario' => $user,
            'formulario' => $formulario->createView(),
            'mnl' => count($mnl),
            'nnl' => count($nnl),
            'ok' => $ok
        ]);
    }

    /**
     * @Route("/administracion", name="administracion")
     * @Security(expression="has_role('ROLE_ADMIN')")
     */
    public function administracionAction(Request $peticion)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $usuarios = $em->getRepository('AppBundle:Usuario')
            ->findAll();
        $proyectos = $em->getRepository('AppBundle:Proyecto')
            ->findBy(array('esValido' => false));

        $comentarios = $em->getRepository('AppBundle:Comentario')
            ->findBy(array('denunciado' => true));

        // mensajes no leidos
        $mnl = $em->getRepository('AppBundle:Mensaje')
            ->findBy(array('usuario' => $user, 'leido' => false));
        // notis no leídas
        $nnl = $em->getRepository('AppBundle:Notificacion')
            ->findBy(array('usuario' => $user, 'leida' => false));
        return $this->render(':default/usuario:administracion.html.twig',[
            'usuario' => $user,
            'usuarios' => $usuarios,
            'proyectos' => $proyectos,
            'comentarios' => $comentarios,
            'contador_proyectos' => count($proyectos),
            'mnl' => count($mnl),
            'nnl' => count($nnl)
        ]);
    }

    /**
     * @Route("/cuenta/{id}", name="cuenta")
     */
    public function cuentaAction(Request $peticion, Usuario $id)
    {
        $user = $this->getUser();
        //redirección si no es el usuario
        if($id->getId() != $user->getId()){
            if($user->getesAdmin() == false) {
                $this->addFlash('danger', 'Acceso denegado');
                return new RedirectResponse(
                    $this->generateUrl('inicio')

                );
            }
        }
        $em = $this->getDoctrine()->getManager();
        $usuario = $em->getRepository('AppBundle:Usuario')
            ->find($id);
        // crear el formulario
        $formulario = $this->createForm(new CuentaType(), $usuario);
        // Procesar el formulario si se ha enviado con un POST
        $formulario->handleRequest($peticion);
        // Si se ha enviado y el contenido es válido, guardar los cambios
        // crear el formulario
        $formulario1 = $this->createForm(new CuentaEmailType(), $usuario);
        // Procesar el formulario si se ha enviado con un POST
        $formulario1->handleRequest($peticion);
        // Si se ha enviado y el contenido es válido, guardar los cambios

        if ($formulario->isSubmitted() && $formulario->isValid()){


                    if($_POST['cuenta']['nuevapass'] != $_POST['cuenta']['pass']) {
                        $this->addFlash('danger', 'Las nuevas contraseñas no coinciden');
                    }else{
                        $em = $this->getDoctrine()->getManager();
                        $helper =  $password = $this->container->get('security.password_encoder');
                        $usuario->setPass($helper->encodePassword($usuario, $_POST['cuenta']['nuevapass']));
                        $em->persist($usuario);
                        $em->flush();
                        $this->addFlash('success', 'Contraseña modificada correctamente, para que no haya fallos en su sesión le recomendamos volver a autentificarse');
                    }
                }
        if ($formulario1->isSubmitted() && $formulario1->isValid()){

                $em = $this->getDoctrine()->getManager();
                $usuario->setEmail($_POST['cuentaEmail']['email']);
                $em->persist($usuario);
                $em->flush();
                $this->addFlash('success', 'El email se ha modificado de forma correcta');

        }elseif($formulario1->isSubmitted() && !$formulario1->isValid()){
            $this->addFlash('danger', 'Lo sentimos, ha habido un fallo cambiando el email, inténtelo de nuevo');
        }



            $em = $this->getDoctrine()->getManager();
            $helper =  $password = $this->container->get('security.password_encoder');
            $usuario->setPass($helper->encodePassword($usuario, $usuario->getPass()));
            //$em->persist($usuario);
            //$em->flush();


        //Eliminar cuenta
        if(isset($_POST['eliminar_cuenta'])){
            $em = $this->getDoctrine()->getManager();
            $em->remove($id);
            $em->flush();
            $helper = $this->get('security.authentication_utils');
            return $this->render(':default/usuario:entrada.html.twig',
                [
                    'last_username' => $helper->getLastUsername(),
                    'error'         => $helper->getLastAuthenticationError(),
                    'ok' => 'Su cuenta ha sido eliminada de forma correcta'
                ]);
        }

        // mensajes no leidos
        $mnl = $em->getRepository('AppBundle:Mensaje')
            ->findBy(array('usuario' => $user, 'leido' => false));
        // notis no leídas
        $nnl = $em->getRepository('AppBundle:Notificacion')
            ->findBy(array('usuario' => $user, 'leida' => false));
        return $this->render(':default/usuario:cuenta.html.twig',[
            'usuario' => $user,
            'formulario' => $formulario->createView(),
            'formulario1' => $formulario1->createView(),
            'mnl' => count($mnl),
            'nnl' => count($nnl)
        ]);
    }


    /**
     * @Route("/gestion_usuarios/{id}", name="gestion_usuarios")
     */
    public function gestionUsuariosAction(Request $peticion, Usuario $id)
    {
        //Eliminar usuario

        if(isset($_POST['eliminar_user'])){
            $em = $this->getDoctrine()->getManager();
            $em->remove($id);
            $em->flush();
            $this->addFlash('success', 'Usuario eliminado de forma correcta');

            return new RedirectResponse(
                $this->generateUrl('administracion')
            );
        }

    }

    /**
     * @Route("/recuperar_cuenta", name="recuperar_cuenta")
     */
    public function recuperarCuentaAction()
    {


        if(isset($_POST['recuperar'])){
            $em = $this->getDoctrine()->getManager();
            // generador de contraseñas
            $str = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
            $cad = "";
            for($i=0;$i<12;$i++) {
                $cad .= substr($str,rand(0,62),1);
            }

            $usuario = $em->getRepository('AppBundle:Usuario')
                ->findOneByNombreUsuario(array('nombreUsuario' => $_POST['usuario']));

            if(!$usuario){
                return $this->render(':default/usuario:recuperarCuenta.html.twig', [
                    'ok' => 'El usuario que ha introducido no existe.'
                ]);
            }
            if($usuario->getEmail() == $_POST['email']){
                $helper =  $password = $this->container->get('security.password_encoder');
                $usuario->setPass($helper->encodePassword($usuario, $cad));
                $em->persist($usuario);
                $em->flush();

                //email con su nueva contraseña
                // correo electrónico al participante
                $message = \Swift_Message::newInstance()
                    ->setSubject('Recuperación de contraseña en Kindie')
                    ->setFrom('kindieOficial@gmail.com')
                    ->setTo($usuario->getEmail())
                    ->setBody(
                        $this->renderView(
                        // app/Resources/views/Emails/registration.html.twig
                            ':default/emails:recuperarEmail.html.twig',
                            array('pass' => $cad, 'usuario' => $usuario)
                        ),
                        'text/html'
                    );
                $this->get('mailer')->send($message);
                return $this->render(':default/usuario:recuperarCuenta.html.twig', [
                    'ok' => 'Se ha generado una contraseña de forma correcta, compruebe su correo.'
                ]);
            }else{
                return $this->render(':default/usuario:recuperarCuenta.html.twig', [
                    'ok' => 'La relación entre ese email y nombre de usuario no existen'
                ]);
            }

        }

        return $this->render(':default/usuario:recuperarCuenta.html.twig', [
            'ok' => ''
        ]);

    }



}



