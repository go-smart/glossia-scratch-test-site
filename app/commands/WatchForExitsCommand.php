<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Thruway\ClientSession;
use Thruway\Connection;

class WatchForExitsCommand extends Command {

  protected $httpTransferrerDownloadBase = 'https://smart-mict.de/api/downloadFile';
  protected $httpTransferrerUploadBase = 'https://smart-mict.de/api/file';
  protected $prefix = 'com.gosmartsimulation';

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'gosmart:watch';

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
    $this->prefix = 'com.gosmartsimulation';
    if ($this->option('server'))
      $this->prefix .= '.' . $this->option('server');

    $connection = new Connection(['realm' => "realm1", 'url' => "ws://127.0.0.1:8081/ws"]);

    $connection->on('open', function (ClientSession $session) use ($connection) {
      $onComplete = function ($args) use ($session) {
        $this->completed_simulation($args, $session);
      };
      $session->subscribe('com.gosmartsimulation.complete', $onComplete);

      $onFail = function ($args) use ($session) {
        $this->failed_simulation($args, $session);
      };
      $session->subscribe('com.gosmartsimulation.fail', $onFail);

      $onStatus = function ($args) use ($session) {
        $this->status_simulation($args, $session);
      };
      $session->subscribe('com.gosmartsimulation.status', $onStatus);

      $this->info('Subscribed');
    });
    $connection->open();
	}

  protected function upload_signal($s, $file) {
      $this->info('Simulation is marked as a development run, so updating DB and pushing our result');
      DB::table('ItemSet')->insert(['Id' => $s->Id, 'CreationDate' => date('Y-m-d H:i:s'), 'IsDeleted' => 0, 'Version' => 1]);
      DB::table('ItemSet_File')->insert(['Id' => $s->Id, 'State' => 0, 'FileName' => 'progress', 'Extension' => 'vtp']);
      $targetProgress = $this->httpTransferrerUploadBase . '/' . strtolower($s->Id) . '/name/progress';
      $session->call($this->prefix . '.request_files', [$s->Id, [$file => $targetProgress]])->then(
        function ($res) use ($guid, $s) {
          $this->info('Uploaded trigger for production server ' . $s->Id);
        },
        function ($err) { $this->error($err); }
      );
  }

  protected function status_simulation($args, $session) {
    $s = Simulation::find($args[0]);

    if ($s && $s->State != 0 && $s->isDevelopment())
    {
          $this->info("DEVELOPMENT PROGRESS: " . $args[1][0] . " :: " . $args[1][1]->message);
          $s->Progress = $args[1][0];
          $s->State = 2;
          $s->save();
    }

    //$this->upload_signal($s, 'last_message');
  }

  protected function failed_simulation($args, $session) {
    $s = Simulation::find($args[0]);

    if (!$s || $s->State == 0)
    {
      $this->info("Irrelevant failure : " . $args[0]);
      return;
    }

    $this->info('Simulation ' . $args[0] . ' failed');
    Mail::send('emails.failed_sim', ['guid' => $args[0]], function ($message) {
      $message->to('phil.weir@numa.ie', 'Phil Weir')->subject('Simulation failed');
    });
    //$this->upload_signal($s, 'last_message');
  }

  protected function completed_simulation($args, $session) {
    $s = Simulation::find($args[0]);
    if (!$s || $s->State == 0)
    {
      $this->info("Irrelevant completion : " . $s->Id);
      return;
    }

    $this->info('Simulation ' . $args[0] . ' completed');
    if ($s->isDevelopment()) {
      $this->info('Simulation is marked as a development run, so updating DB and pushing our result');
      $guid = strtoupper($this->gen_uuid());
      $this->info('New file: ' . $guid);
      DB::table('ItemSet')->insert(['Id' => $guid, 'CreationDate' => date('Y-m-d H:i:s'), 'IsDeleted' => 0, 'Version' => 1]);
      DB::table('ItemSet_File')->insert(['Id' => $guid, 'State' => 0, 'FileName' => 'NUMA_External_Simulation', 'Extension' => 'vtp']);
      $target = $this->httpTransferrerUploadBase . '/' . strtolower($guid) . '/name/NUMA_External_Simulation';
      $session->call($this->prefix . '.request_files', [$s->Id, ['output/lesion_surface.vtp' => $target]])->then(
        function ($res) use ($guid, $s) {
          $this->info('Uploaded result ' . $guid . ' for simulation ' . $s->Id);

          DB::table('ItemSet_VtkFile')->insert(['Id' => $guid, 'Simulation_Id' => strtoupper($s->Id)]);
          $s->Progress = 100;
          $s->State = 3;
          $s->save();

          $this->info('Processing complete, reported and uploaded');
          //$this->upload_signal($s, 'last_message');
        },
        function ($err) { $this->error($err); }
      );
    }

    Mail::send('emails.completed_sim', ['guid' => $args[0]], function ($message) {
      $message->to('phil.weir@numa.ie', 'Phil Weir')->subject('Simulation succeeded');
    });

  }

  /* http://www.php.net/manual/en/function.uniqid.php#94959 (TODO: check status) */
  protected function gen_uuid() {
      return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
          // 32 bits for "time_low"
          mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

          // 16 bits for "time_mid"
          mt_rand( 0, 0xffff ),

          // 16 bits for "time_hi_and_version",
          // four most significant bits holds version number 4
          mt_rand( 0, 0x0fff ) | 0x4000,

          // 16 bits, 8 bits for "clk_seq_hi_res",
          // 8 bits for "clk_seq_low",
          // two most significant bits holds zero and one for variant DCE1.1
          mt_rand( 0, 0x3fff ) | 0x8000,

          // 48 bits for "node"
          mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
      );
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
