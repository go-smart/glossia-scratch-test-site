<?php

class PowerGeneratorTest extends TestCase {

  public function testPowerGeneratorSuccess()
  {
    $generator = new PowerGenerator();
    $generator->modality_id = 1;
    $generator->name = "Model 1500X RF";
    $generator->manufacturer = "Rita";

    $this->assertTrue($generator->save());
  }
}
