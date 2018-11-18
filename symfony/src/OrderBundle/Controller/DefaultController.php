<?php
/*
 * (c) Pere Mataix Sempere.
 *
 */

namespace OrderBundle\Controller;

use OrderBundle\Services\ApiError;
use OrderBundle\Services\ApiClient;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controlador para el manejo de los tickets y pedidos.
 */
class DefaultController extends Controller
{

    /*
    * Esta acción es la encargada de mostrar les eventos existentes
    */
    public function eventsAction()
    {
      try{
        //Obtenemos una instancia del servicio del API
        $api = $this->get("ApiClient");
        //Obtenemos los eventos disponibles
        $events = $api->getEvents();
      }Catch(ApiError $exA){
        //Registramos el error como no crítico porque puede ser un error temporal del servicio del API
        $this->addFlash("warning", "Try again later. Access Info Error: ".$exA->getMessage());
      }Catch(\Exception $ex){
        //Esto si se trata de un error crítico inesperado
        $this->addFlash("error", "Unexpected Error");
      }
      //Presenamos los datos
      return $this->render('OrderBundle:Default:events.html.twig',array("events" => $events));
    }

    /*
    * Esta acción es la encargada de confirmar los datos del pedido.
    */
    public function orderConfirmAction(Request $request){
      try{
        //recogemos los datos del usuario pasados por POST
        $order=array(
          "name" => $request->request->get('name','No name'),
          "lastname" => $request->request->get('lastname','No lastname'),
          "documentId" => $request->request->get('documentId', 'No document id'),
          "zipcode" => $request->request->get('zipcode', 'No zipcode')
        );
        //para recoger los datos de las lineas seleccionadas parsemos todos los parametros recibidos
        $lines = array( );
        foreach ($request->request->all() as $key => $value) {
          //Separamos el nombre del parametro por "_" porque esperamos un nombre del tipo "ticket_1_2", donde 1 es el id del evento y 2 es el id del ticket
          $expl = explode("_",$key,3);
          // Si el nombre de la variable empieza por "ticket" se trata de una variable deseada, siempre y cuando su valor sea positivo
          if($expl[0] == "ticket" && (int)$value > 0 && count($expl) == 3){
            //Almacenamos el valor desado de la variable en ou array de líneas.
            $lines[] = array("event" => $expl[1],"ticket" => $expl[2],"quantity" => $value);
          }
        }
        //Obtenemos una instancia del servicio del API
        $api = $this->get("ApiClient");
        //Llamamos al método que creará un nuevo pedido
        $order = $api->newOrder($order,$lines);
        //Si todo ha ido bien obtendremos un pedido detallado y enviamos un email al usuario con la información
        try{

          $to = $request->request->get('email','pere.mataix@gmail.com');
          $api->sendEmail($to,"pedido", $this->renderView('OrderBundle:Default:orderEmail.html.twig',array('order' => $order)),"pedido realizado correctamente");

        }Catch(\Exception $ex){
          //En caso de que no se haya podido enviar el mensaje no debe fallar la aplicación porque el pedido ha sido creado, pero se debe avisar
          $this->addFlash("warning", "No le hemos podido enviar un email. Imprima esta página para no perder los datos");
        }
      }Catch(ApiError $exA){
        //Registramos el error como no crítico porque puede ser un error temporal del servicio del API
        $this->addFlash("warning", "Try again later. Access Info Error: ".$exA->getMessage());
        // Redirigimos de nuevo a la pantalla de eventos con el mensaje
        return $this->redirectToRoute('order_events');
      }Catch(\Exception $ex){
        //Esto si se trata de un error crítico inesperado
        $this->addFlash("error", "Unexpected Error");
          // Redirigimos de nuevo a la pantalla de eventos con el mensaje
        return $this->redirectToRoute('order_events');
      }
      //Redirigimos a otra acción para no crear multiples pedidos en caso de recarga de página
      //Guardamos el pedido en la sesión para poderlo obtener mas adelante
      $session = $request->getSession();
      $session->set('order', $order);
      return $this->redirectToRoute('order_confirmed');
   }

   /*
   * Acción para realizar pruebas
   */
   public function testAction(){
       return $this->render('OrderBundle:Default:index.html.twig');
   }


    /*
    * Acción encargada de indicar al usuario que el pedido se ha realizado correctamente
    * Recibe el objeto del pedido para poderlo mostrar.
    */
    public function orderConfirmedAction(Request $request){
      //recogemos el pedido de la sesión
      $session = $request->getSession();
      $order =   $session->get('order');
      dump($order);
      die();
      return $this->render('OrderBundle:Default:orderConfirmed.html.twig',array('order' => $order));
    }

    /*
    * Esta acción es la encargada de presentar los distintos tickets de un evento
    */
    public function ticketsAction(Request $request){
      try{
        //Obtenemos una instancia del servicio del API
        $api = $this->get("ApiClient");
        //Obtenemos el identificador del evento a consultar por POST
        $event_id = $request->request->get('event_id');
        //En caso de no recibir el identificador por POST lo obtenemos por GET. Esto se ha hecho para repurar por web
        if(!isset($event_id)){
          $event_id = $request->query->get('event_id');
        }
        //Obtenemos todos los tickets relacionados al evento
        $tickets = $api->getTickets($event_id);

      }Catch(ApiError $exA){
        //Registramos el error como no crítico porque puede ser un error temporal del servicio del API
        $this->addFlash("warning", "Try again later. Access Info Error: ".$exA->getMessage());
      }Catch(\Exception $ex){
        //Esto si se trata de un error crítico inesperado
        $this->addFlash("error", "Unexpected Error");
      }
      //Presentamos la información al cliente
      //Este template no extiende del layout.html.twig del bundle para que no represente todos los elementos de la web, ya que se trata de un modulo.
      return $this->render('OrderBundle:Default:tickets_ajax.html.twig',array("tickets" => $tickets,"event_id" =>$event_id ));

    }
}
