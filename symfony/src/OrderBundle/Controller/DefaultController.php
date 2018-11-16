<?php

namespace OrderBundle\Controller;
use OrderBundle\Services\ApiClient;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function indexAction()
    {
      return $this->render('@OrderBundle\Default\index.html.twig');
    }
    public function eventsAction()
    {
      try{

        $api = $this->get("ApiClient");
        $events = $api->getEvents();
      }catch(\Exception $ex){
        echo  $ex->getMessage();
        die();
      }
      return $this->render('OrderBundle:Default:events.html.twig',array("events" => $events));
    }
    public function orderConfirmAction(Request $request){
      try{

        $api = $this->get("ApiClient");
        $order=array(
          "name" => $request->request->get('name'),
          "lastname" => $request->request->get('lastname'),
          "documentId" => $request->request->get('documentId'),
          "zipcode" => $request->request->get('zipcode')
        );

        $lines = array( );
        foreach ($request->request->all() as $key => $value) {
          $expl = explode("_",$key,3);
          if($expl[0] == "ticket" && (int)$value > 0){
            $lines[] = array("ticket" => $expl[2],"quantity" => $value);

          }
        }

        $order = $api->newOrder($order,$lines);
        $message = (new \Swift_Message('Hello Email'))
               ->setFrom('pere.mataix@gmail.com')
               ->setTo($request->request->get('email'))
               ->setBody(
                   $this->renderView(
                       // app/Resources/views/Emails/registration.html.twig
                       'OrderBundle:Default:orderEmail.html.twig',
                       array('order' => $order)
                   ),
                   'text/html'
       );

      $this->get('mailer')->send($message);


      }catch(\Exception $ex){
        echo  $ex->getMessage();

        die();
      }
      return $this->redirectToRoute('order_confirmed');


   }


    public function orderConfirmedAction(Request $request){
      return $this->render('OrderBundle:Default:orderConfirmed.html.twig');
    }
    public function ticketsAction(Request $request){
      try{
        $api = $this->get("ApiClient");
        $event_id = $request->request->get('event_id');
        if(!isset($event_id)){
          $event_id = $request->query->get('event_id');
        }

        $tickets = $api->getTickets($event_id);

      }catch(\Exception $ex){
        echo  $ex->getMessage();
        die();
      }
    
        return $this->render('OrderBundle:Default:tickets_ajax.html.twig',array("tickets" => $tickets,"event_id" =>$event_id ));


    }
}
