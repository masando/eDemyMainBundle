<?php

namespace eDemy\MainBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MainControllerTest extends WebTestCase
{
    public function testMain()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode()
        );

        $crawler = $client->request('GET', '/anahataespacio');
        $this->assertTrue($client->getResponse()->isSuccessful());

        $crawler = $client->request('GET', '/anahataespacios');
        $this->assertTrue(
            $client->getResponse()->isRedirect('/anahataespacio')
        );

        // Assert that there is at least one h2 tag
        // with the class "subtitle"
        //$this->assertGreaterThan(
        //    0,
        //    $crawler->filter('h2.subtitle')->count()
        //);

        // Assert that there are exactly 4 h2 tags on the page
        //$this->assertCount(4, $crawler->filter('h2'));

        // Assert that the "Content-Type" header is "application/json"
        //$this->assertTrue(
        //    $client->getResponse()->headers->contains(
        //        'Content-Type',
        //        'application/json'
        //    )
        //);

        // Assert that the response content matches a regexp.
        //$this->assertRegExp('/foo/', $client->getResponse()->getContent());

        // Assert that the response status code is 2xx
        //$this->assertTrue($client->getResponse()->isSuccessful());

        // Assert that the response status code is 404
        //$this->assertTrue($client->getResponse()->isNotFound());
        // Assert a specific 200 status code
        //$this->assertEquals(
        //    Response::HTTP_OK,
        //    $client->getResponse()->getStatusCode()
        //);

        // Assert that the response is a redirect to /demo/contact
        //$this->assertTrue(
        //    $client->getResponse()->isRedirect('/demo/contact')
        //);
        // or simply check that the response is a redirect to any URL
        //$this->assertTrue($client->getResponse()->isRedirect());
    }
}
