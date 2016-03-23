<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Thruway\ClientSession;
use Thruway\Connection;

class SessionCounter {
  public $simulationIds;
  public $connection;
  public function __construct($simulationIds, $connection, $infoCallback) {
    $this->connection = $connection;
    $this->simulationIds = $simulationIds;
    $this->infoCallback = $infoCallback;
  }

  public function leave($id) {
    if (($key = array_search($id, $this->simulationIds)) !== false) {
      unset($this->simulationIds[$key]);
      call_user_func($this->infoCallback, 'Counter: removed ' . $id);
    }
    if (empty($this->simulationIds)) {
      call_user_func($this->infoCallback, 'Counter: leaving');
      $this->connection->close();
      return true;
    }
    return false;
  }
}

class CheckForSimulationsCommand extends Command {

  protected $httpTransferrerDownloadBase = 'https://smart-mict.de/api/downloadFile';
  protected $httpTransferrerUploadBase = 'https://smart-mict.de/api/file';
  protected $prefix = 'com.gosmartsimulation';

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
    $this->prefix = $this->prefix . '';
    if ($this->option('server'))
      $this->prefix .= '.' . $this->option('server');

    $connection = new Connection(['realm' => "realm1", 'url' => "ws://127.0.0.1:8081/ws"]);

    $connection->on('open', function (ClientSession $session) use ($connection) {
      $this->process_simulations($session, $connection);
    });
    $connection->open();
	}

  protected function process_simulations($session, $connection) {
    if (!file_exists('/tmp/lastcheck'))
    {
      $this->error('Create /tmp/lastcheck');
      return;
    }

    $lastRun = \Carbon\Carbon::createFromTimestamp(filemtime('/tmp/lastcheck'), 'Europe/London');
    $this->info('Time of previous check:');
    $this->info($lastRun);
    $lastRun->setTimezone('Europe/Berlin');
    //$lastRun->subMinutes(30);
    $lastRun->subSeconds(9);//RMV accounts for time offset at FIT
    $this->info('In Germany: ' . $lastRun);

    touch('/tmp/lastcheck');
    $simulations = Simulation::join('ItemSet as I', 'I.Id', '=', 'Simulation.Id')
      ->where('I.CreationDate', '>', $lastRun)
      /*->where('Simulation.State', '!=', 0)
      ->where('Simulation.State', '!=', 3)*/
      ->select('Simulation.*', 'I.CreationDate')
      ->get();

    $counter = new SessionCounter($simulations->lists('Id'), $connection, function ($i) { return $this->info($i); });

    $simulations->each(function ($s) {
      $this->info($s->CreationDate . ' :: ' . $s->Caption);
    });

    if ($simulations->isEmpty())
    {
      $this->info('No new simulations');
      $connection->close();
      return;
    }

    $simulations->each(function ($s) use ($session, $counter) {
      $this->info('SIMULATION ' . $s->Id . ' :: ' . $s->Caption);
      $runHere = $s->State != 2 || !$s->isDevelopment();

      $dev = false;
      if ($s->isDevelopment() && $s->State != 2) {
        $this->info('--development run -- we are responsible for this--');
        $s->State = 2;
        $s->Progress = 0;
        $s->save();
        $dev = true;
      }

      if ($runHere) {
        $xml = $s->buildXml($this->httpTransferrerDownloadBase);
        $this->info('Built XML');
        $finished = function () use ($s, $counter) { $counter->leave($s->Id); };
        $session->call($this->prefix . '.init', [$s->Id])->then(
          function ($res) use ($session, $s, $xml, $finished) {
            $session->call($this->prefix . '.update_settings_xml', [$s->Id, $xml->saveXML()])->then(
              function ($res) use ($session, $s, $finished) {
                $session->call($this->prefix . '.finalize', [$s->Id, '.'])->then(
                  function ($res) use ($session, $s, $finished) {
                    $session->call($this->prefix . '.start', [$s->Id])->then(
                      function ($res) use ($session, $s, $finished) {
                        $this->info('Started ' . $s->Id);
                        $finished();
                      },
                      function ($err) use ($s, $finished) { $this->error($err); $finished(); }
                    );
                  },
                  function ($err) use ($s, $finished) { $this->error($err); $finished(); }
                );
              },
              function ($err) use ($s, $finished) { $this->error($err); $finished(); }
            );
          },
          function ($err) use ($s, $finished) { $this->error($err); $finished(); }
        );
        $this->info('Sent init call');
      }
      if ($dev) {
        Mail::send('emails.started_sim', ['guid' => $s->Id], function ($message) {
          $message->to('phil.weir@numa.ie', 'Phil Weir')->subject('Development simulation started');
        });
      }
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
      array('server', null, InputOption::VALUE_OPTIONAL, 'simulation server to use.', null),
    );
  }

}
