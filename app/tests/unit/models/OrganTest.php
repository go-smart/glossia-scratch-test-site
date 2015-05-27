<?php

class OrganTest extends TestCase {

  public function testOrganSuccess()
  {
    $organ = new Organ();
    $organ->name = "liver";

    $this->assertTrue($organ->save());
  }
}
