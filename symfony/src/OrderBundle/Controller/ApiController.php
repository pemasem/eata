<?php

namespace OrderBundle\Controller;
use OrderBundle\Services\ApiClient;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;


class ApiController extends Controller
{
    public function indexAction()
    {
      return $this->render('OrderBundle:Api:index.html.twig');
    }

    public function codeVerifyAction(Request $request)
    {
      try{
        $api = $this->get("ApiClient");
        $code = $api->codeVerify($request->query->get('code'));
        return new JsonResponse($this->correctRequest(array("order" => $code->sub,"line" => $code->orderLine,"ticket" => $code->ticket)));
      }catch(ApiCodeUsedError $exu){
          return new JsonResponse($this->badRequest("Used Code", array("order" =>  $exu->getOrder(),"usage" => $exu->getUsage() )));

      }catch(ApiCodeValidationError $exv){
        return new JsonResponse( $this->badRequest("Invalid Code", $exv->getToken()));

      }catch(\Exception $ex){

        return new JsonResponse($this->badRequest("Unexpected Error",$ex->getMessage()));
      }

    }
    private function correctRequest($msg){
      return  array("error" => false,"msg" => $msg);
    }
    private function badRequest($msg,$help = NULL){
      return  array("error" => true,"msg" => $msg,"help" => $help);
    }

}
