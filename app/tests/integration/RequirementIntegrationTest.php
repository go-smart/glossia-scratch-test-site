<?php

use \League\FactoryMuffin\Facade as FactoryMuffin;

class RequirementIntegrationTest extends IntegrationTestCase {

  public function testRequirementSuccess()
  {
    $requirement = FactoryMuffin::create('Requirement');

    $requirement->load('parameter');
    $this->assertInstanceOf('Parameter', $requirement->parameter);
    $this->assertInstanceOf('Protocol', $requirement->protocol);
  }
}
