<?php
/*
 * (c) Pere Mataix Sempere.
 *
 *
 */

namespace OrderBundle\Services;

use Unirest\Request;
use Firebase\JWT\JWT;
use Symfony\Component\Cache\Adapter\RedisAdapter;

/*
* servicio para el manejo del API tanto de entrada como de salida de datos.
* Se utiliza el framework de Unirest para realizar las peticiones y los post al API
* Utilizamos el componente JWT para gestionar los códigos generados y sus validaciones
*/
class ApiClient{

  /*
  * Clave privada que se utilizará para la autenticación del API
  */
  private $key;

  /*
  * Token temporal para el uso del API
  */
  private $access_token ="";

  private $last_used_token_time = 0;

  /*
  * URL del API
  */
  private $url = "http://devtest.entradasatualcance.com/api/v1/";

  /*
  * Constructor.
  * Requiere de una clave de acceso válida para la api
  */
  function __construct($key){
    $this->key = $key;
  }

  /*
  * Función que procesa la respuesta obtenida por el API y laza una excepción en caso de no ser una respuesta válida.
  */
  private function responseProcess($response){
    if($response->code != "200"){
      // En el caso que la respuesta del API no sea 200 consideramos que se trata de un error.
      throw new ApiError($response->body->error->message);
    }else{
      return $response;
    }
  }

  /*
  * Función que genera un token temporal para el uso del API en caso de que no se tenga uno
  */
  private function getToken(){
    // En caso de que haga un minuto que no se usa el token se renueva
    if($this->last_used_token_time > 0){
      $now = microtime(true);
      $diff = ($now - $this->last_used_token_time) * 1000000; // El tiempo que ha pasado desde la ultima llamada en milisegundos
      if($diff > 1000 * 60 ){
        $headers = array('Accept' => 'application/json');
        $query = array('refresh_token' => $this->access_token);
        $response = Request::post($this->url.'oauth/refresh',$headers,$query);
        $response =  $this->responseProcess($response);
        $this->access_token = $response->body->access_token;
      }
    }
    // En caso de que no se haya obtenido el token anteriormente se pide uno.
    if($this->access_token == ""){
      $headers = array('Accept' => 'application/json');
      $query = array('api_key' => $this->key);
      $response = Request::post($this->url.'oauth/token',$headers,$query);
      $response =  $this->responseProcess($response);
      $this->access_token = $response->body->access_token;
    }
    //Indicamos la fecha de uso del token como la actual
    $this->last_used_token_time = microtime(true);
    return   $this->access_token;

  }

  /*
  * Método que develve los tickets relacionados a un determinado evento
  */
  public function getTickets($event_id){
    $headers = array('Accept' => 'application/json');
    $query = array('access_token' => $this->getToken());
    $response = Request::get($this->url.'events/'.$event_id.'/tickets',$headers,$query);
    $response =  $this->responseProcess($response);
    return $response->body;
  }

  /*
  * Método que nos devuelve todos los eventos existentes
  */
  public function getEvents(){
      $headers = array('Accept' => 'application/json');
      $query = array('access_token' => $this->getToken());
      $response = Request::get($this->url.'events',$headers,$query);
      $response =  $this->responseProcess($response);
      return $response->body;
  }
  /*
  * Método que crea un nuevo pedido a partir de los datos del usuario y de las líneas seleccionadas
  */
  public function newOrder($order,$lines){
    $headers = array('Accept' => 'application/json');
    $order["lines"] = $lines;
    $query = array('access_token' => $this->getToken(),"order" =>  $order);
    $response = Request::get($this->url.'orders',$headers,$query);
    $response =  $this->responseProcess($response);
    $order = $response->body;
    return $this->generateCode($order);
  }

  /*
  * Función que agrega los códigos a cada una de las líneas del pedido
  */
  public function generateCode($order){
    //Para cada una de las líneas delpedido
    foreach($order->lines as $key => $linea){
      // Montamos la información que queremos que inluya el código
      $token_array = array(
        "sub" => $order->id, // almacenamos el identificador del pedido
        "orderLine" => $linea->id, // almacenamos el identificador de la línea de pedidos
        "ticket" => $linea->ticket->id, // almacenamos el identificador del ticket
        "iat" => time(),
        "exp" => time() + (365 * 24 * 60 * 60 ) // se le da una vida de un año para usar el código
      );
      //Generamos el código
      $token = JWT::encode($token_array,$this->key, 'HS256');
      // Se lo asignamos a la linea
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
