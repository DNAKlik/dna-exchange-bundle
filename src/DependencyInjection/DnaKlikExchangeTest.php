<?php

namespace DnaKlik\DnaExchangeBundle\DependencyInjection;

use DnaKlik\DnaExchangeBundle\Service\DnaKlikExchange;
use DnaKlik\DnaExchangeBundle\Service\DnaKlikStampProvider;
use PHPUnit\Framework\TestCase;

class DnaKlikExchangeTest extends TestCase
{
    public function testGetStamps()
    {
        $exchange = new DnaKlikExchange([new DnaKlikStampProvider()]);

        $stamps = $exchange->getStamps(1);
        $this->assertIsString($stamps);
        $this->assertCount(1, explode(' ', $stamps));

        $stamps = $exchange->getStamps(10);
        $this->assertCount(10, explode(' ', $stamps));

        $stamps = $exchange->getStamps(10, true);
        $this->assertCount(10, $stamps);
    }
}