<?php
namespace Tests\OrderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiTest extends  WebTestCase
{
    //testeamos los eventos
    public function testEvents()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/order/events');
        //Vemos si carga bien la página
        $this->assertGreaterThan(0,$crawler->filter('html:contains("0 tickets")')->count());
        //Vemos si al menos se ha devuelto un evento
        $this->assertGreaterThan(0, $crawler->filter('button.ajax')->count());

    }

    //testeamos los tickets
    public function testTickets ()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/order/tickets?event_id=1');
        //Vemos si al menos aparece un ticket para el evento 1
        // como mejora se deberia obtener el id del evento en el método  testEvents para que fuese mas dinámico
        $this->assertGreaterThan(0,$crawler->filter('html:contains("Precio")')->count());
    }
}
