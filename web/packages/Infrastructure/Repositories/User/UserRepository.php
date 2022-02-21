<?php

namespace packages\Infrastructure\Repositories\User;

use Illuminate\Support\Facades\Neo4jDB;
use packages\Domain\User\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    public function register($user)
    {
        $client = Neo4jDB::call();
        $client->run(
            <<<'CYPHER'
                CREATE (
                    :User {
                        user_id: 
                        name: $name, 
                        email: $email,
                        password: $password
                    })
                CYPHER,
                [
                    'user_id' => $user['id'], 
                    'name' => $user['name'], 
                    'email' => $user['email'],
                    'password' => $user['password']
                ]);
    }
}
