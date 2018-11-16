<?php
namespace Tests\OrderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiTest extends  WebTestCase
{
    public function testEvents()
    {
      $client = static::createClient();

        $crawler = $client->request('GET', '/order/events');

        $this->assertGreaterThan(0,$crawler->filter('html:contains("0 tickets")')->count());
        $this->assertGreaterThan(0, $crawler->filter('button.ajax')->count());

    }
    public function testTickets ()
    {
      $client = static::createClient();
        $crawler = $client->request('GET', '/order/tickets?event_id=1');
        $this->assertGreaterThan(0,$crawler->filter('html:contains("Precio")')->count());
    }
}
