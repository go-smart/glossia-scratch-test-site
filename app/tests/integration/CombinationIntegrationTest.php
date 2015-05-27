<?php

use \League\FactoryMuffin\Facade as FactoryMuffin;

class CombinationIntegrationTest extends IntegrationTestCase {

  public function testCombinationSuccess()
  {
    $combination = FactoryMuffin::create('Combination');

    $combination->load('power_generator');
    $this->assertInstanceOf('Needle', $combination->needle);
    $this->assertInstanceOf('PowerGenerator', $combination->power_generator);
    $this->assertInstanceOf('Protocol', $combination->protocol);
  }
}
