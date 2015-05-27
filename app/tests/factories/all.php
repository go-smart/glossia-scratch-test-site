<?php

use League\FactoryMuffin\Facade as FactoryMuffin;

FactoryMuffin::define('Modality', array(
  'name' => 'word'
));

FactoryMuffin::define('Needle', array(
  'modality_id' => 'factory|Modality',
  'name' => 'sentence',
  'manufacturer' => 'company',
  'file' => 'md5'
), function ($object) {
  $parameters = FactoryMuffin::seed(3, 'Parameter');
  foreach ($parameters as $parameter) {
    $parameter->paramable_id = $object->id;
    $parameter->paramable_type = 'needle';
  }
  $object->parameters()->saveMany($parameters);
});

FactoryMuffin::define('PowerGenerator', array(
  'modality_id' => 'factory|Modality',
  'name' => 'sentence',
  'manufacturer' => 'company'
), function ($object) {
  $needles = FactoryMuffin::seed(3, 'Needle');
  $object->needles()->saveMany($needles);
  $parameters = FactoryMuffin::seed(3, 'Parameter');
  foreach ($parameters as $parameter) {
    $parameter->paramable_id = $object->id;
    $parameter->paramable_type = 'power_generator';
  }
  $object->parameters()->saveMany($parameters);
});

FactoryMuffin::define('Protocol', array(
  'modality_id' => 'factory|Modality',
  'name' => 'sentence',
  'algorithm' => 'text'
));

FactoryMuffin::define('Parameter', array(
  'name' => 'word',
  'type' => 'word',
  'widget' => 'word',
  'value' => 'float',
  'priority' => 'integer',
  'paramable_id' => '-1',
  'paramable_type' => ''
));

FactoryMuffin::define('Requirement', array(
  'protocol_id' => 'factory|Protocol',
  'family' => 'word',
  'parameter_id' => 'factory|Parameter'
), function ($object) {
  $parameter = FactoryMuffin::create('Parameter');
  $parameter->paramable_id = $object->id;
  $parameter->paramable_type = 'requirement';
  $object->parameter()->save($parameter);
});

FactoryMuffin::define('Combination', array(
  'needle_id' => 'factory|Needle',
  'power_generator_id' => 'factory|PowerGenerator',
  'protocol_id' => 'factory|Protocol'
));

FactoryMuffin::define('User', array(
    'username' => 'string',
    'password' => 'string',
    'email' => 'string'           
), function ($object) {
    $accounts = FactoryMuffin::seed(5, 'Account');
    $object->accounts()->saveMany($accounts); 
});
