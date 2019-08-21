<?php

namespace App\Infraestructure;

use App\Domain\Events\MeteosalleEvents;
use App\Domain\TailMessageRepository;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class TailsRepositoryRabbit implements TailMessageRepository
{

	private $channel;
	private $connection;

	public function __construct($host)
	{
		$this->connection = new AMQPStreamConnection($host, 5672, $_SERVER['RABBIT_USER'],
			$_SERVER['RABBIT_PASS']);
		$this->channel = $this->connection->channel();
	}

	public function publishEvent(MeteosalleEvents $event): void
	{
		$this->channel->exchange_declare((string)$event, 'fanout', false, false, false);
		$data = $event->getDataAsJson();

		$msg = new AMQPMessage($data);

		$this->channel->basic_publish($msg, (string)$event);
		//echo ' [x] Sent ', $data, "\n";
	}

	public function __destruct()
	{
		$this->channel->close();
		$this->connection->close();
	}
}