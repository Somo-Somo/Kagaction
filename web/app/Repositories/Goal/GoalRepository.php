<?php

namespace App\Repositories\Goal;

use Illuminate\Support\Facades\Neo4jDB;
use App\Repositories\Goal\GoalRepositoryInterface;

class GoalRepository implements GoalRepositoryInterface
{
    protected $client;

    public function __construct()
    {
        $this->client = Neo4jDB::call();
    }

    public function create($goal)
    {
        $createdGoal = $this->client->run(
            <<<'CYPHER'
                MATCH (user:User { email : $user_email }), (project:Project { uuid: $parent_uuid })
                CREATE (user)-[
                            :CREATED{at:localdatetime({timezone: 'Asia/Tokyo'})}
                        ]->(
                           hypothesis:Hypothesis {
                                name: $name,
                                uuid: $uuid,
                                status: null,
                                limited: null
                        })-[
                            :IS_THE_GOAL_OF{since:localdatetime({timezone: 'Asia/Tokyo'})}  
                        ]->(project)
                RETURN hypothesis, project
                CYPHER,
                [
                    'name' => $goal['name'], 
                    'uuid' => $goal['uuid'], 
                    'parent_uuid' => $goal['parent_uuid'], 
                    'user_email' => $goal['created_by_user_email'], 
                ]
            );

        return $createdGoal;
    }
}