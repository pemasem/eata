<?php
namespace Tests\OrderBundle\Services;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
class ApiTest extends KernelTestCase
{
    private $api;

    //Configuramos el test para obtener el servicio ApiClient
    protected function setUp()
    {
        self::bootKernel();

        $this->api = static::$kernel
            ->getContainer()
            ->get('ApiClient');

    }

    //Testeamos el metodo generateCode
    public function testGenerateCode()
    {
        //preparamos un pedido de prueba con dos líneas de pedido
        $order = (object) array('id' => '1','lines' => array((object)array("id" => 2,"ticket" => (object)array("id" => 3)),(object)array("id" => 4,"ticket" => (object)array("id" => 5))));

        //lamamos al método
        $result = $this->api->generateCode($order);
        dump($result);
        //Esperamos quese sigan manteniendo las dos líneas del pedido
        $this->assertEquals(2, count($order->lines));
        //Verificamos que se haya añadido un nuevo campo "code"
        $this->assertObjectHasAttribute('code', $order->lines[0]);
        $this->assertObjectHasAttribute('code', $order->lines[1]);
        //verificamos que este campo tenga una longitud mínima
        $this->assertGreaterThanOrEqual(100,strlen($order->lines[0]->code));
        $this->assertGreaterThanOrEqual(100,strlen($order->lines[1]->code));
    }
}
 ?>
