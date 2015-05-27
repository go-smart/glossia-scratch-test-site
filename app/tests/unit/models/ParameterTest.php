<?php

class ParameterTest extends TestCase {

  public function testNormalParameterSuccess()
  {
    $parameter = new Parameter();
    $parameter->name = "NEEDLE_LENGTH";
    $parameter->type = "float";
    $parameter->widget = "textbox";
    $parameter->value = "3.0";
    $parameter->priority = 1;
    $parameter->paramable_id = '-1';
    $parameter->paramable_type = '';

    $this->assertTrue($parameter->save());
  }

  public function testRequirementParameterSuccess()
  {
    $parameter = new Parameter();
    $parameter->name = "NEEDLE_LENGTH";
    $parameter->type = "float";
    $parameter->widget = null;
    $parameter->value = null;
    $parameter->priority = 2;
    $parameter->paramable_id = '-1';
    $parameter->paramable_type = '';

    $this->assertTrue($parameter->save());
  }
}
