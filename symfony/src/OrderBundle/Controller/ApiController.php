<?php
/*
 * (c) Pere Mataix Sempere.
 *
 *
 */

namespace OrderBundle\Controller;

use OrderBundle\Services\ApiClient;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controlador para el API.
 */
class ApiController extends Controller
{
    /*
    * En esta acción se representa la documentación del API
    */
    public function indexAction()
    {
      return $this->render('OrderBundle:Api:index.html.twig');
    }

    /*
    * Esta acción valida si un código es correcto y lo marca como usado
    */
    public function codeVerifyAction(Request $request)
    {
      try{
        //Obtenemos una instancia del servicio del API
        $api = $this->get("ApiClient");
        //Verificamos si el código es correcto o ha sido sado. En caso de ser correcto se marcará como usado y no podrá volver a ser usado
        $code = $api->codeVerify($request->query->get('code'));
        //Devolvemos la respuesta en formato JSON al tratarse de un API REST
        return new JsonResponse($this->correctRequest(array("order" => $code->sub,"line" => $code->orderLine,"ticket" => $code->ticket)));
      }Catch(ApiCodeUsedError $exu){
        //En caso de que el codigo haya sido usado devolvemos una respuesta de error en formato JSON
          return new JsonResponse($this->badRequest("Used Code", array("order" =>  $exu->getOrder(),"usage" => $exu->getUsage() )));

      }Catch(ApiCodeValidationError $exv){
        //En caso de que el codigo no sea valido devolvemos una respuesta de error en formato JSON
        return new JsonResponse( $this->badRequest("Invalid Code", $exv->getToken()));

      }Catch(\Exception $ex){
        //En el caso de encontrarnos con un error inesperado devolveremos un mensaje de error específico
        return new JsonResponse($this->badRequest("Unexpected Error",$ex->getMessage()));
      }
    }
    /*
    * Funcion que formatea las respuests correctas.
    */
    private function correctRequest($msg){
      return  array("error" => false,"msg" => $msg);
    }

    /*
    * Funcion que formatea las respuestas incorrectas.
    */
    private function badRequest($msg,$help = NULL){
      return  array("error" => true,"msg" => $msg,"help" => $help);
    }

}
