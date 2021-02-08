<?php

namespace App\Tests;

//use Symfony\Component\Panther\PantherTestCase;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductCreateTest extends WebTestCase
{
    public function testList(): void
    {
        $client = static::createClient();
        $client->request('GET', '/product/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

    }

    public function testCreate(): void
    {
        $client = static::createClient();
        $client->request('POST', '/product/new',
            [
                'name' => 'Fabien',
                'code' => '1234'
            ]);

        $this->assertResponseIsSuccessful();

    }
}
