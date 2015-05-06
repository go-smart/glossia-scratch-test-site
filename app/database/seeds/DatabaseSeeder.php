<?php


class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Eloquent::unguard();

    //DB::table('Simulation_Needle_Parameter')->delete();
    //DB::table('Simulation_Needle')->delete();
    //DB::table('PointSet')->delete();
    //DB::table('Simulation')->delete();
    $sS = App::make("SimulationSeeder")->clean();
    $sC = App::make("\CombinationSeeders\CombinationSeeder")->clean($this->command);

    DB::table('Parameter_Attribution')->delete();
    DB::table('Numerical_Model_Region')->delete();
    //DB::table('Region')->delete();
    //DB::table('Numerical_Model')->delete();
    DB::table('Numerical_Model_Argument')->delete();
    DB::table('Algorithm_Argument')->delete();
    DB::table('Algorithm')->delete();

    $sP = App::make("ParameterSeeder")->clean();

    DB::table('Combination_Needle')->delete();
    //DB::table('Combination')->delete();
    //DB::table('Protocol')->delete();
    //DB::table('Context')->delete();
    DB::table('Needle_Power_Generator')->delete();
    //DB::table('Needle')->delete();
    //DB::table('Power_Generator')->delete();
    //DB::table('Modality')->delete();
    DB::table('Argument')->delete();
    //DB::table('Parameter')->delete();

    $this->call('ParameterSeeder');


		$this->call('RegionSeeder');
    if (!Config::get('gosmart.context_as_enum'))
      $this->call('\ContextSeeders\ContextSeeder');
    $this->call('\CombinationSeeders\CombinationSeeder');

    //if (Simulation::count() == 0)
      $this->call('SimulationSeeder');

		$this->call('ValueSeeder');
		$this->call('AlgorithmSeeder');
	}

}
