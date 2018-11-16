<?php
namespace OrderBundle\Services;
use Unirest\Request;
use Firebase\JWT\JWT;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class ApiClient{

  private $key;
  private $access_token ="";
  private $url = "http://devtest.entradasatualcance.com/api/v1/";
  function __construct($key){
    $this->key = $key;
  }

  private function responseProcess($response){
    if($response->code != "200"){

      throw new ApiError($response->body->error->message);
    }else{

      return $response;
    }
  }


  private function getToken(){

    if($this->access_token == ""){
      $headers = array('Accept' => 'application/json');
      $query = array('api_key' => $this->key);
      $response = Request::post($this->url.'oauth/token',$headers,$query);
      $response =  $this->responseProcess($response);
      $this->access_token = $response->body->access_token;
    }
    return   $this->access_token;

  }

  public function getTickets($event_id){
    $headers = array('Accept' => 'application/json');
    $query = array('access_token' => $this->getToken());
    $response = Request::get($this->url.'events/'.$event_id.'/tickets',$headers,$query);
    $response =  $this->responseProcess($response);
    return $response->body;
  }

  public function getEvents(){

      $headers = array('Accept' => 'application/json');
      $query = array('access_token' => $this->getToken());
      $response = Request::get($this->url.'events',$headers,$query);
      $response =  $this->responseProcess($response);
      return $response->body;


  }

  public function newOrder($order,$lines){
    $headers = array('Accept' => 'application/json');
    $order["lines"] = $lines;
    $query = array('access_token' => $this->getToken(),"order" => json_encode( $order ) );
   /*
    $response = Request::get($this->url.'orders',$headers,$query);
    $response =  $this->responseProcess($response);
    $order = $response->body;
    return $this->generateCode($order);
    */
    //ESTO DEBE DEVOLVER ALGO:
    return $query;
  }

  public function generateCode($order){
    foreach($order->lines as $key => $linea){
      $token_array = array(
        "sub" => $order->id,
        "orderLine" => $linea->id,
        "ticket" => $linea->ticket->id,
        "iat" => time(),
        "exp" => time() + (365 * 24 * 60 * 60 )
      );
      $token = JWT::encode($token_array,$this->key, 'HS256');
      $linea->code = $token;
    }
    return $order;
  }

  public function codeVerify($code){
    try{
      $order  = JWT::decode($code,$this->key,array('HS256'));
    }catch(\Exception $ex){
      throw new ApiCodeValidationError($code, $ex->getMessage());
    }

    $client = RedisAdapter::createConnection(
        'redis://localhost'
    );
    $usage = $client->get("token_".base64_encode($code));
    if(isset($usage)){
      throw new ApiCodeUsedError($order, $usage, $code);

    }else{
      $client->set("token_".base64_encode($code),date("Y-m-d H:i:s"));
    }
    return $order;
  }


}
class ApiCodeValidationError extends \Exception{

  private $token;
  public function getToken(){
    return $this->token;
  }
  public function __construct($code,$message){
    parent::__construct($message);
    $this->token = $code;
  }
}
class ApiError extends \Exception{}
class ApiCodeUsedError extends \Exception{
  private $order;
  private $usage;
  public function getOrder(){
    return $this->order;
  }
  public function getUsage(){
    return $this->usage;
  }
  public function __construct($order,$usage,$code){
    parent::__construct($code);
    $this->order = $order;
    $this->usage = $usage;
  }
}


 ?>
