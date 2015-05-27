<?php

class ProtocolTest extends TestCase {

  public function testProtocolSuccess()
  {
    $protocol = new Protocol();
    $protocol->modality_id = 1;
    $protocol->name = "Rita 3cm Protocol";
    $protocol->algorithm = "Some storage pattern";

    $this->assertTrue($protocol->save());
  }
}
