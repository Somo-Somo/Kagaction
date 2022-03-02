<?php

namespace App\Repositories\Hypothesis;

use Illuminate\Support\Facades\Neo4jDB;
use App\Repositories\Hypothesis\HypothesisRepositoryInterface;

class HypothesisRepository implements HypothesisRepositoryInterface
{
    protected $client;

    public function __construct()
    {
        $this->client = Neo4jDB::call();
    }

    /**
     * 選択されたプロジェクトの親仮説と子仮説と子仮説のゴールからの深さ（距離）を取得
     */
    public function getHypothesisList(string $projectUuid)
    {
        $hypothesisList = $this->client->run(
            <<<'CYPHER'
                MATCH len = (project:Project{uuid: $project_uuid})<- [*] - (parent:Hypothesis)
                OPTIONAL MATCH (parent)<-[]-(child:Hypothesis)
                RETURN project.uuid,parent,collect(child),length(len)
                CYPHER,
                [
                    'project_uuid' => $projectUuid,
                ]
            );

        return $hypothesisList;
    }

    public function create($hypothesis)
    {
        $createdHypothesis = $this->client->run(
            <<<'CYPHER'
                MATCH (user:User { email : $user_email }), (parent:Hypothesis { uuid: $parent_uuid })
                CREATE (user)-[
                            :CREATED{at:localdatetime({timezone: 'Asia/Tokyo'})}
                        ]->(
                           hypothesis:Hypothesis {
                                name: $name,
                                uuid: $uuid,
                                status: null,
                                limited: null
                        })-[
                            :TO_ACHIEVE{since:localdatetime({timezone: 'Asia/Tokyo'})}  
                        ]->(parent)
                RETURN hypothesis, parent
                CYPHER,
                [
                    'name' => $hypothesis['name'], 
                    'uuid' => $hypothesis['uuid'], 
                    'parent_uuid' => $hypothesis['parent_uuid'], 
                    'user_email' => $hypothesis['created_by_user_email'], 
                ]
            );

        return $createdHypothesis;
    }
}