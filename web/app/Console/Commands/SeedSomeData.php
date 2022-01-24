<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use \Laudis\Neo4j\ClientBuilder;
use \Laudis\Neo4j\Authentication\Authenticate;

class SeedSomeData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seedsomedata';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return int
     */
    public function handle()
    {
        $auth = Authenticate::basic('neo4j', '025Niigata');
        $client = ClientBuilder::create()
            ->withDriver('bolt', 'bolt://localhost:7687')
            ->withDriver('neo4j', 'neo4j://localhost:7687', null, $auth)
            ->withDriver('http', 'http://localhost:7474/', Authenticate::kerberos('token'))
            ->withDefaultDriver('http')
            ->build();

        $cypher = "CREATE (Sato:User {name:'sato'})
        CREATE (Sato)-[:Make]->(baseball:Project {name: 'baseball'})";

        $client->run($cypher);

        $result = $client->run('return Sato');
        echo sprintf($result);
    }
}
