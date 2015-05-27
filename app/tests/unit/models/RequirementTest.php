<?php

class RequirementTest extends TestCase {

  public function testRequirementSuccess()
  {
    $requirement = new Requirement();
    $requirement->protocol_id = 1;
    $requirement->family = "user";
    $requirement->parameter_id = 1;

    $this->assertTrue($requirement->save());
  }
}
