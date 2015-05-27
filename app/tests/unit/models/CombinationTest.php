<?php

class CombinationTest extends TestCase {

  public function testCombinationSuccess()
  {
    $combination = new Combination();
    $combination->protocol_id = 1;
    $combination->power_generator_id = 1;
    $combination->needle_id = 1;

    $this->assertTrue($combination->save());
  }
}
