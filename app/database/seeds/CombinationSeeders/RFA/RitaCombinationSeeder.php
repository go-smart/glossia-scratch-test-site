<?php namespace CombinationSeeders\RFA;
/**
 * This file is part of the Go-Smart Simulation Architecture (GSSA).
 * Go-Smart is an EU-FP7 project, funded by the European Commission.
 *
 * Copyright (C) 2013-  NUMA Engineering Ltd. (see AUTHORS file)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */


use \Seeder;

use \Algorithm;
use \Argument;
use \Combination;
use \Modality;
use \Needle;
use \NumericalModel;
use \Parameter;
use \PowerGenerator;
use \Protocol;
use \Context;

class RitaCombinationSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
    $modality['rfa'] = Modality::whereName('RFA')->first();
    $model['rfa basic sif'] = NumericalModel::whereName('NUMA RFA Basic SIF')->first();

    /* Add generators */
    $generator['rita 1500X rf'] = new PowerGenerator;
    $generator['rita 1500X rf']->fill(array(
      'Name' => '1500X RF',
      'Manufacturer' => 'RITA'
    ));
    $generator['rf3000'] = new PowerGenerator;
    $generator['rf3000']->fill(array(
      'Name' => 'RF3000',
      'Manufacturer' => 'Boston Scientific'
    ));
    $modality['rfa']->powerGenerators()->save($generator['rita 1500X rf']);
    $generator['rita 1500X rf']->attribute(['Name' => 'GENERATOR_WATTAGE', 'Type' => 'float', 'Value' => 250.0, 'Widget' => 'textbox']);
    $generator['rita 1500X rf']->attribute(['Name' => 'INITIAL_POWER', 'Type' => 'float', 'Value' => 250.0, 'Widget' => 'textbox']);
    $generator['rita 1500X rf']->attribute(['Name' => 'NEEDLE_MAX_AMOUNT', 'Type' => 'int', 'Value' => "1", 'Widget' => 'textbox']);
    $generator['rita 1500X rf']->attribute(['Name' => 'NEEDLE_MIN_AMOUNT', 'Type' => 'int', 'Value' => "1", 'Widget' => 'textbox']);

    /* Add needles */
    $needle['rita starburst xl'] = new Needle;
    $needle['rita starburst xl']->fill(array(
      'Name' => 'Starburst XL',
      'Manufacturer' => 'RITA',
      'Geometry' => 'library:straight tines',
      'Class' => 'point-sources'
    ));
    $modality['rfa']->needles()->save($needle['rita starburst xl']);
    $needle['rita starburst xl']->powerGenerators()->attach($generator['rita 1500X rf']);
    $needle['rita starburst xl']->attribute(['Name' => 'NEEDLE_MAX_EXTENSION', 'Type' => 'float', 'Value' => 5.0, 'Widget' => 'textbox']);
    $needle['rita starburst xl']->attribute(['Name' => 'NEEDLE_MIN_EXTENSION', 'Type' => 'float', 'Value' => 2.0, 'Widget' => 'textbox']);
    $needle['rita starburst xl']->attribute(['Name' => 'NEEDLE_PRONG_AMOUNT', 'Type' => 'int', 'Value' => 9, 'Widget' => 'textbox']);

    $needle['rita starburst semi-flex'] = new Needle;
    $needle['rita starburst semi-flex']->fill(array(
      'Name' => 'Starburst Semi-Flex',
      'Manufacturer' => 'RITA',
      'Geometry' => 'library:straight tines',
      'Class' => 'point-sources'
    ));
    $modality['rfa']->needles()->save($needle['rita starburst semi-flex']);
    $needle['rita starburst semi-flex']->powerGenerators()->attach($generator['rita 1500X rf']);
    $needle['rita starburst semi-flex']->attribute(['Name' => 'NEEDLE_MAX_EXTENSION', 'Type' => 'float', 'Value' => 5.0, 'Widget' => 'textbox']);
    $needle['rita starburst semi-flex']->attribute(['Name' => 'NEEDLE_MIN_EXTENSION', 'Type' => 'float', 'Value' => 2.0, 'Widget' => 'textbox']);
    $needle['rita starburst semi-flex']->attribute(['Name' => 'NEEDLE_PRONG_AMOUNT', 'Type' => 'int', 'Value' => 9, 'Widget' => 'textbox']);

    $needle['rita starburst mri'] = new Needle;
    $needle['rita starburst mri']->fill(array(
      'Name' => 'Starburst MRI',
      'Manufacturer' => 'RITA',
      'Geometry' => 'library:straight tines',
      'Class' => 'point-sources'
    ));
    $modality['rfa']->needles()->save($needle['rita starburst mri']);
    $needle['rita starburst mri']->powerGenerators()->attach($generator['rita 1500X rf']);
    $needle['rita starburst mri']->attribute(['Name' => 'NEEDLE_MAX_EXTENSION', 'Type' => 'float', 'Value' => 5.0, 'Widget' => 'textbox']);
    $needle['rita starburst mri']->attribute(['Name' => 'NEEDLE_MIN_EXTENSION', 'Type' => 'float', 'Value' => 2.0, 'Widget' => 'textbox']);
    $needle['rita starburst mri']->attribute(['Name' => 'NEEDLE_PRONG_AMOUNT', 'Type' => 'int', 'Value' => 9, 'Widget' => 'textbox']);

    $needle['rita starburst sde'] = new Needle;
    $needle['rita starburst sde']->fill(array(
      'Name' => 'Starburst SDE',
      'Manufacturer' => 'RITA',
      'Geometry' => 'library:straight tines',
      'Class' => 'point-sources'
    ));
    $modality['rfa']->needles()->save($needle['rita starburst sde']);
    $needle['rita starburst sde']->powerGenerators()->attach($generator['rita 1500X rf']);
    $needle['rita starburst sde']->attribute(['Name' => 'NEEDLE_EXTENSION', 'Type' => 'float', 'Value' => 2.0, 'Widget' => 'textbox']);
    $needle['rita starburst sde']->attribute(['Name' => 'NEEDLE_PRONG_AMOUNT', 'Type' => 'int', 'Value' => 3, 'Widget' => 'textbox']);

    /* Add protocols */
    $protocol['rita starburst 2cm'] = new Protocol;
    $protocol['rita starburst 2cm']->fill(array(
      'Name' => 'RITA Starburst 2cm Protocol',
    ));
    $modality['rfa']->protocols()->save($protocol['rita starburst 2cm']);

    $protocol['rita starburst 3cm'] = new Protocol;
    $protocol['rita starburst 3cm']->fill(array(
      'Name' => 'RITA Starburst 3cm Protocol',
    ));
    $modality['rfa']->protocols()->save($protocol['rita starburst 3cm']);

    $protocol['rita starburst 4cm'] = new Protocol;
    $protocol['rita starburst 4cm']->fill(array(
      'Name' => 'RITA Starburst 4cm Protocol',
    ));
    $modality['rfa']->protocols()->save($protocol['rita starburst 4cm']);

    $protocol['rita starburst 5cm'] = new Protocol;
    $protocol['rita starburst 5cm']->fill(array(
      'Name' => 'RITA Starburst 5cm Protocol',
    ));
    $modality['rfa']->protocols()->save($protocol['rita starburst 5cm']);

    /* Add parameters for results */
    $needle_length_protocols = array("rita starburst 3cm", "rita starburst 4cm", "rita starburst 5cm");
      /*
    foreach ($needle_length_protocols as $pn) {
      $algorithm["$pn extension"] = new Algorithm;
      $algorithm["$pn extension"]->content = <<<ENDLIPSUM1
Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin cursus 
fringilla dolor, at aliquam metus pellentesque a. Proin in scelerisque justo. 
Proin at enim orci. Integer efficitur vel quam et pharetra. Cras porta 
ultricies orci, sed dictum quam porttitor eu. Etiam suscipit dolor in ligula 
elementum imperdiet. Maecenas quis nunc eu lectus interdum venenatis nec id 
mauris. Nam at nisi quis est auctor fermentum auctor vel leo. Nullam id magna 
interdum, euismod mauris ac, vestibulum metus. Etiam ex magna, faucibus vitae 
diam a, maximus rutrum enim. Quisque in lectus a sapien consequat bibendum. 
Proin pellentesque accumsan mauris, in iaculis mauris facilisis id.

Nulla dapibus, metus ut ullamcorper convallis, erat ante condimentum diam, ac 
viverra felis odio non enim. Mauris malesuada, ex vel congue feugiat, lectus 
sapien lobortis ex, non finibus velit neque vel turpis. Curabitur consequat 
tortor ac enim aliquam scelerisque. Sed nec libero consequat, bibendum tortor 
nec, feugiat augue. Donec tristique ex elit, nec eleifend dui tempor in. 
Praesent sed feugiat nibh, in ultrices enim. Fusce diam metus, posuere quis 
sem sed, dignissim congue lectus. Aenean in mollis elit, a interdum quam. 
Integer sem mi, vulputate imperdiet ligula et, lobortis ultrices odio. 
ENDLIPSUM1;
      $algorithm["$pn extension"]->protocol()->associate($protocol[$pn]);

      $result = new Parameter;
      $result->fill(['Name' => 'ALGORITHM_NEEDLE_EXTENSION', 'Type' => 'float']);
      $result->save();
      $algorithm["$pn extension"]->result()->associate($result);
      $algorithm["$pn extension"]->save();

      $argument = new Argument;
      $argument->name = "Time";
      $argument->argumentable()->associate($algorithm["$pn extension"]);
      $argument->save();
    }
       */

    /*
    foreach ($protocol as $pn => $p) {
      $algorithm["$pn power"] = new Algorithm;
      $algorithm["$pn power"]->content = <<<ENDLIPSUM2
Fusce non ex tellus. Vestibulum aliquet lacinia augue, et euismod purus 
congue eget. Nullam sit amet aliquet risus. Mauris suscipit lorem erat, 
a lacinia eros commodo ac. Praesent eu varius magna. Ut aliquet mauris 
nulla, at commodo mi blandit in. Aliquam vel quam rhoncus, mattis lorem 
vitae, tempus tortor. Class aptent taciti sociosqu ad litora torquent 
per conubia nostra, per inceptos himenaeos. Nullam id facilisis felis. 
Nulla facilisi. In et sapien leo. Pellentesque in ornare elit. In sed 
finibus lectus, eget sodales risus.

Vestibulum vitae nibh eget arcu ullamcorper fringilla. Phasellus rhoncus 
libero nulla, vel sollicitudin odio laoreet eu. Lorem ipsum dolor sit 
amet, consectetur adipiscing elit. Sed quis facilisis libero, at ornare 
augue. Integer dignissim, mauris in malesuada tincidunt, turpis nunc 
tempor diam, at pretium mi quam id lectus. In nunc metus, ultrices id 
consectetur sit amet, luctus vitae nunc. Duis in tristique erat, sit 
amet ultrices lectus. Nunc mollis tellus non urna mollis pretium. In 
efficitur ante vel ante pharetra, eget elementum ipsum fermentum. 
ENDLIPSUM2;
      $algorithm["$pn power"]->protocol()->associate($protocol[$pn]);

      $result = new Parameter;
      $result->fill(['Name' => 'INPUT_POWER', 'Type' => 'float']);
      $result->save();
      $algorithm["$pn power"]->result()->associate($result);
      $algorithm["$pn power"]->save();

      $algorithm["$pn power"]->attribute(['Name' => 'NEEDLE_MAX_EXTENSION', 'Type' => 'float']);
      $algorithm["$pn power"]->attribute(['Name' => 'INITIAL_POWER', 'Type' => 'float']);

      $argument = new Argument;
      $argument->name = "Temperature";
      $argument->argumentable()->associate($algorithm["$pn power"]);
      $argument->save();

      $argument = new Argument;
      $argument->name = "Time";
      $argument->argumentable()->associate($algorithm["$pn power"]);
      $argument->save();
    }
     */

    foreach (array('kidney', 'liver', 'lung') as $organ) {
      $o = Context::byNameFamily($organ, 'organ');

      /* Add combinations */
        foreach (array('3cm', '4cm', '5cm') as $p) {
          $c = new Combination;
          $c->isPublic = true;
          $c->protocol()->associate($protocol['rita starburst ' . $p]);
          $c->powerGenerator()->associate($generator['rita 1500X rf']);
          $c->numericalModel()->associate($model['rfa basic sif']);
          $c->context()->associate($o);
          $c->save();
          foreach (array('xl', 'semi-flex', 'mri') as $n)
            $c->needles()->attach($needle['rita starburst ' . $n]);
          $combination['rita-' . $p] = $c;
        }

      $c = new Combination;
      $c->isPublic = true;
      $c->protocol()->associate($protocol['rita starburst 2cm']);
      $c->powerGenerator()->associate($generator['rita 1500X rf']);
      $c->numericalModel()->associate($model['rfa basic sif']);
      $c->context()->associate($o);
      $c->save();
      $c->needles()->attach($needle['rita starburst sde']);
    }
	}

}
