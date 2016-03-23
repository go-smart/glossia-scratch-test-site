<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Thruway\ClientSession;
use Thruway\Connection;

class TestEmailCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'gosmart:test_email';

  protected $responses = [];
  protected $server_names = [];

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

  public function tock($loop, $session)
  {
    if (in_array(false, $this->responses))
    {
      $server_statuses = array_map(function ($id, $resp, $name) {
        return [$id, $resp, $name];
      }, array_keys($this->responses), $this->responses, $this->server_names);

      Mail::send('emails.server_status_error', ['servers' => $server_statuses], function ($message) {
        $message->to('phil.weir@numa.ie', 'Phil Weir')->subject('!!!!SERVER DISAPPEARED!!!!');
      });
    }

    $loop->addTimer(300, function () use ($loop, $session) {
      $this->tick($loop, $session);
    });
  }

  public function tick($loop, $session)
  {
    array_walk($this->responses, function (&$item, $key) { $item = false; });
    $loop->addTimer(10, function () use ($loop, $session) {
      $this->tock($loop, $session);
    });
    $session->publish('com.gosmartsimulation.request_identify', []);
  }

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
    //$simulations = Simulation::join('ItemSet as I', 'I.Id', '=', 'Simulation.Id')
      /*->where('Simulation.State', '!=', 0)
      ->where('Simulation.State', '!=', 3)*/
      //->select('I.*')
      //->first();
    //var_dump($simulations);
    //$s = Simulation::find('C4E3A3FB-9CE8-4FC6-81B9-5994B08EF1B2');
    //$this->info($s->State);
    $connection = new Connection(['realm' => "realm1", 'url' => "ws://127.0.0.1:8081/ws"]);

    $connection->on('open', function (ClientSession $session) use ($connection) {
      $session->subscribe('com.gosmartsimulation.identify', function ($args) {
        $server_id = $args[0];
        $server_name = $args[1];
        $this->responses[$server_id] = true;
        $this->server_names[$server_id] = $server_name;
      });
      $this->tick($connection->getClient()->getLoop(), $session);
    });
    $connection->open();
    //Mail::send('emails.failed_sim', ['guid' => '1'], function ($message) {
    //  $message->to('phil.weir@numa.ie', 'Phil Weir')->subject('Simulation failed');
    //});
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
      array('server', null, InputOption::VALUE_OPTIONAL, 'simulation server to use.', null),
    );
  }

}
