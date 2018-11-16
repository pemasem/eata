<?php
namespace Tests\OrderBundle\Services;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
class ApiTest extends KernelTestCase
{
    private $api;

    protected function setUp()
    {
        self::bootKernel();

        $this->api = static::$kernel
            ->getContainer()
            ->get('ApiClient');

    }

    public function testSubtract()
    {
        $order = (object) array('id' => '1','lines' => array((object)array("id" => 2,"ticket" => (object)array("id" => 3)),(object)array("id" => 4,"ticket" => (object)array("id" => 5))));

        $result = $this->api->generateCode($order);
        dump(rawurlencode($order->lines[0]->code));
        $this->assertEquals(2, count($order->lines));
        $this->assertObjectHasAttribute('code', $order->lines[0]);
        $this->assertObjectHasAttribute('code', $order->lines[1]);
        $this->assertGreaterThanOrEqual(100,strlen($order->lines[0]->code));
        $this->assertGreaterThanOrEqual(100,strlen($order->lines[1]->code));
    }
}
 ?>
