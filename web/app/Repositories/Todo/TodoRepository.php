<?php

namespace App\Repositories\Todo;

use App\Facades\Neo4jDB;
use App\Repositories\Todo\TodoRepositoryInterface;

class TodoRepository implements TodoRepositoryInterface
{
    protected $client;

    public function __construct()
    {
        $this->client = Neo4jDB::call();
    }

    /**
     * 選択されたプロジェクトのTodo一覧を取得
     * 選択されたプロジェクトの親仮説と子仮説と子仮説のゴールからの深さ（距離）を取得
     *
     * @param string $project_uuid
     * @return $todo_list
     */
    public function getTodoList(string $project_uuid)
    {
        $todo_list = $this->client->run(
            <<<'CYPHER'
                MATCH len = (project:Project{uuid: $project_uuid})<- [*] - (parent:Todo)
                OPTIONAL MATCH (parent)<-[]-(child:Todo)
                RETURN project.uuid,parent,collect(child),length(len)
                CYPHER,
            [
                'project_uuid' => $project_uuid,
            ]
        );

        return $todo_list;
    }

    /**
     * TodoをDB上に生成
     *
     * @param array $todo
     */
    public function create(array $todo)
    {
        $this->client->run(
            <<<'CYPHER'
                MATCH   (user:User { uuid : $user_uuid }),
                        (parent:Todo { uuid: $parent_uuid }) - [*] -> (project:Project)
                CREATE (user)-[
                            :CREATED{at:localdatetime({timezone: 'Asia/Tokyo'})}
                        ]->(
                           todo:Todo{
                                name: $name,
                                uuid: $uuid
                        })-[
                            :TO_ACHIEVE{at:localdatetime({timezone: 'Asia/Tokyo'})}
                        ]->(parent)
                WITH project
                MATCH  len = (project:Project) <- [r*] - (parent:Todo)
                OPTIONAL MATCH (parent)<-[]-(child:Todo)
                OPTIONAL MATCH (:User)-[currentGoal:SET_TODAYS_GOAL]->(parent)
                RETURN project,parent,r,collect(child),length(len),currentGoal
                ORDER BY r
                CYPHER,
            [
                'name' => $todo['name'],
                'uuid' => $todo['uuid'],
                'parent_uuid' => $todo['parent_uuid'],
                'user_uuid' => $todo['user_uuid'],
            ]
        );
    }

    /**
     * Todoのテキストを更新
     *
     * @param array $todo
     */
    public function update(array $todo)
    {
        $this->client->run(
            <<<'CYPHER'
                    MATCH (user:User { uuid : $user_uuid }), (todo:Todo { uuid: $uuid })
                    SET todo.name = $name
                    WITH user,todo
                    OPTIONAL MATCH x = (user)-[updated:UPDATED]->(todo)
                    WHERE x IS NOT NULL
                    SET updated.at = localdatetime({timezone: 'Asia/Tokyo'})
                    WITH user,todo,x
                    WHERE x IS NULL
                    CREATE (user)-[:UPDATED{at:localdatetime({timezone: 'Asia/Tokyo'})}]->(todo)
                    RETURN todo
                    CYPHER,
            [
                'name' => $todo['name'],
                'uuid' => $todo['uuid'],
                'user_uuid' => $todo['user_uuid'],
            ]
        );
    }

    /**
     * Todoとユーザーを結ぶリレーションを削除
     *
     * @param array $todo
     * @return array $delete_child
     */
    public function fetchDeleteTodo(array $todo)
    {
        $delete_child = $this->client->run(
            <<<'CYPHER'
                MATCH (user:User { uuid : $user_uuid }) - [r1] -> (todo:Todo{ uuid :$uuid }) ,
                (todo) - [r2] - ()
                OPTIONAL MATCH (child:Todo) - [*] -> (todo) - [r3] -> (parent),
                 (child) - [r4] - ()
                RETURN collect(DISTINCT child) AS childs
                CYPHER,
            [
                'uuid' => $todo['uuid'],
                'user_uuid' => $todo['user_uuid'],
            ]
        );
        return $delete_child->toArray();
    }

    /**
     * Todoとユーザーを結ぶリレーションを削除
     *
     * @param array $todo
     * @return array $delete_child
     */
    public function destroy(array $todo)
    {
        $this->client->run(
            <<<'CYPHER'
                MATCH (user:User { uuid : $user_uuid }) - [r1] -> (todo:Todo{ uuid :$uuid }) ,
                (todo) - [r2] - ()
                OPTIONAL MATCH (child:Todo) - [*] -> (todo) - [r3] -> (parent),
                 (child) - [r4] - ()
                DELETE r1, r2, r3, r4, todo, child
                RETURN child
                CYPHER,
            [
                'uuid' => $todo['uuid'],
                'user_uuid' => $todo['user_uuid'],
            ]
        );
    }

    /**
     * 選択されたTodoの完了を更新する
     *
     * @param array $todo
     */
    public function updateAccomplish(array $todo)
    {
        $this->client->run(
            <<<'CYPHER'
                MATCH (user:User { uuid : $user_uuid }), (todo:Todo { uuid: $uuid })
                CREATE (user) - [
                    accomplished:ACCOMPLISHED{at:localdatetime({timezone: 'Asia/Tokyo'})}
                ] -> (todo)
                RETURN todo
                CYPHER,
            [
                'uuid' => $todo['uuid'],
                'user_uuid' => $todo['user_uuid'],
            ]
        );
    }

    /**
     * 選択されたTodoの完了を取り消し
     *
     * @param array $todo
     */
    public function destroyAccomplish(array $todo)
    {
        $this->client->run(
            <<<'CYPHER'
                MATCH (user:User { uuid : $user_uuid })-[accomplish:ACCOMPLISHED]->(todo:Todo { uuid: $uuid })
                DELETE accomplish
                RETURN todo
                CYPHER,
            [
                'uuid' => $todo['uuid'],
                'user_uuid' => $todo['user_uuid'],
            ]
        );
    }

    /**
     * 習慣にする
     *
     * @param array $todo
     */
    public function updateHabit(array $todo)
    {
        $this->client->run(
            <<<'CYPHER'
                    MATCH (user:User { uuid : $user_uuid }), (todo:Todo { uuid: $uuid })
                    SET todo.interval = $interval,
                    todo.habit_date = $habit_day_no
                    WITH user,todo
                    OPTIONAL MATCH x = (user)-[updated:UPDATED]->(todo)
                    WHERE x IS NOT NULL
                    SET updated.at = localdatetime({timezone: 'Asia/Tokyo'})
                    WITH user,todo,x
                    WHERE x IS NULL
                    CREATE (user)-[:UPDATED{at:localdatetime({timezone: 'Asia/Tokyo'})}]->(todo)
                    RETURN todo
                    CYPHER,
            [
                'uuid' => $todo['uuid'],
                'user_uuid' => $todo['user_uuid'],
                'interval' => $todo['interval'],
                'habit_day_no' => $todo['habit_day_no'],
            ]
        );
    }
}
