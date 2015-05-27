<?php

class NeedleTest extends TestCase {

  public function testNeedleSuccess()
  {
    $needle = new Needle();
    $needle->modality_id = 1;
    $needle->name = "Starburst 3cm";
    $needle->manufacturer = "Rita";
    $needle->file = "Some storage pattern";

    $this->assertTrue($needle->save());
  }
}
