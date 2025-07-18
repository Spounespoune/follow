<?php

namespace App\Tests\Units\Trait;

use App\Entity\Address;
use App\Entity\Organization;
use PHPUnit\Framework\TestCase;

class HashableTest extends TestCase
{
    public function testOrganisationHashable()
    {
        $address = Address::create('street', 'street2', 'manual_zip_code', 'manual_city');
        $organisation1 = Organization::create('technical_id', 'name', address: $address);
        $organisation2 = Organization::create('technical_id', 'name', address: $address);

        $this->assertSame($organisation1->hash(), $organisation2->hash());
    }
}