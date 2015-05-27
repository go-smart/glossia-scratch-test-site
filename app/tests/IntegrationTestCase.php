<?php

class IntegrationTestCase extends TestCase {
  public static function setupBeforeClass()
  {
    \League\FactoryMuffin\Facade::loadFactories(__DIR__ . '/factories');
  }

  public static function tearDownAfterClass()
  {
    \League\FactoryMuffin\Facade::deleteSaved();
  }

}
