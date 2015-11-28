<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class CheckForSimulationsCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'gosmart:check';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
    if (file_exists('/tmp/lastcheck'))
      $lastRun = \Carbon\Carbon::createFromTimestamp(filemtime('/tmp/lastcheck'));
    //RMV touch('/tmp/lastcheck');
    $this->info('Time of previous check:');
    $this->info($lastRun);

    $simulations = Simulation::join('ItemSet as I', 'I.Id', '=', 'Simulation.Id')->where('I.CreationDate', '>', $lastRun)
      ->select('Simulation.*', 'I.CreationDate')
      ->get();
    $simulations->each(function ($s) {
      $this->info($s->CreationDate . ' :: ' . $s->Caption);
    });
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
		);
	}

}
