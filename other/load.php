<?php

declare(strict_types=1);

define('PHP_MAX_INT32', 2147483647);
define('MILLISECONDS', 1000);
pcntl_async_signals(true);

class LoadImitator
{
    /** @var string */
    private $host;

    /** @var string */
    private $db;

    /** @var string */
    private $port;

    /** @var string */
    private $user;

    /** @var string */
    private $pwd;

    /** @var \PDO */
    private $conn;

    /** @var int */
    private $waitTtl;

    /** @var int */
    private $cnt = 0;

    public function __construct($host, $db, $port, $user, $pwd, $waitTtl = 50) {
        $this->host = $host;
        $this->db = $db;
        $this->port = $port;
        $this->user = $user;
        $this->pwd = $pwd;
        $this->waitTtl = $waitTtl;

        $this->connect()->registerSignals();
    }

    public function createTable(): void {
        $result = $this->conn->exec('create table if not exists demo (id int);');
        if (false === $result) {
            $this->echoBr('SCHEMA WASN\'t CREATED!');

            return;
        }

        $this->echoBr('SCHEMA CREATED!');
    }

    public function insert(): void {
        $stmt = $this->conn->prepare('INSERT INTO demo (id) values (:value)');

        while(true) {
            $stmt->execute([':value' => \random_int(0, PHP_MAX_INT32)]);
            $isSuccess = $stmt->execute([':value' => \random_int(0, PHP_MAX_INT32)]);
            if (false === $isSuccess) {
                $this->echoBBr('FAIL!');
                $this->onSignalHandler();
            }

            ++$this->cnt;
            $this->echo('+');

            if (0 === $this->cnt % 100) {
                $this->echoBBr(\sprintf('Inserted: %d', $this->cnt));
            }

            if ($this->waitTtl > 0 ) {
                usleep($this->waitTtl * MILLISECONDS);
            }
        }
    }

    private function connect(): self {
        $this->conn = new \PDO(
            sprintf('mysql:host=%s;dbname=%s;port=%s', $this->host, $this->db, $this->port),
            $this->user,
            $this->pwd
        );

        return $this;
    }

    private function echoBr(string $string): void {
        echo $this->echo($string) . PHP_EOL;
    }

    private function echoBBr(string $string): void {
        echo PHP_EOL . $this->echoBr($string);
    }

    private function echo(string $string): void {
        echo $string;
    }

    private function registerSignals(): self {
        foreach ([SIGTERM, SIGINT, SIGALRM, SIGHUP] as $signal) {
            pcntl_signal($signal, function () {
                $this->onSignalHandler();
            });
        }

        return $this;
    }

    private function onSignalHandler(): void {
        $this->echoBBr(sprintf('TOTAL INSERTED ROWS: %d', $this->cnt));
        exit(1);
    }
}

$options = getopt('', ['port:', 'host:', 'user:', 'pwd:', 'db:', 'waitTtl:', 'action::']);
if (false === $options) {
    echo 'not all required options were provided'.PHP_EOL;
    exit(1);
}

$loadImitator = new LoadImitator(
    $options['host'],
    $options['db'],
    $options['port'],
    $options['user'],
    $options['pwd'],
    $options['waitTtl'],
);

switch ($options['action'] ?? null) {
    case 'createTable':
        $loadImitator->createTable();
        break;

    default:
        $loadImitator->insert();
}
