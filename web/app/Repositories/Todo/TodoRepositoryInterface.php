<?php

namespace App\Repositories\Todo;

interface TodoRepositoryInterface
{
    public function getTodoList(string $project_uuid);
    public function create(array $todo);
    public function update(array $todo);
    public function fetchDeleteTodo(array $todo);
    public function destroy(array $todo);
    public function updateAccomplish(array $todo);
    public function destroyAccomplish(array $todo);
    public function updateHabit(array $todo);
}
