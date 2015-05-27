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

    DB::table('simulations')->delete();
    DB::table('parameter_attributions')->delete();
    DB::table('combination_needle')->delete();
    DB::table('combinations')->delete();
    DB::table('numerical_model_region')->delete();
    DB::table('numerical_models')->delete();
    DB::table('algorithms')->delete();
    DB::table('protocols')->delete();
    DB::table('contexts')->delete();
    DB::table('needle_power_generator')->delete();
    DB::table('needles')->delete();
    DB::table('power_generators')->delete();
    DB::table('modalities')->delete();
    DB::table('parameters')->delete();
    DB::table('arguments')->delete();

		$this->call('RegionSeeder');
		$this->call('\ContextSeeders\ContextSeeder');
		$this->call('\CombinationSeeders\CombinationSeeder');
		$this->call('SimulationSeeder');
		$this->call('ValueSeeder');
		$this->call('AlgorithmSeeder');
	}

}
