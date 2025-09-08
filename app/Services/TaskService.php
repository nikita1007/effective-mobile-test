<?php

namespace App\Services;

use App\Dtos\TaskDto;
use App\Models\{User, Task};
use Illuminate\Database\Eloquent\Collection;

class TaskService {
    /**
     * Выбор всех задач
     *
     * @return Collection
     */
    public function getAll(int $userId): Collection
    {
        $user = User::find($userId);

        return $user->tasks;
    }

    /**
     * Выбор задачи по заданному ID
     *
     * @param int $userId
     * @param int $taskId
     * @throws \Exception
     * @return Task
     */
    public function getById(int $userId, int $taskId): Task
    {
        $task = Task::with('user')->whereHas('user', function ($query) use ($userId) {
            $query->where('id', $userId);
        })->find($taskId);

        if (!$task) {
            throw new \Exception('Задача не найдена.', 404);
        }

        return $task;
    }

    /**
     * Создание задачи
     *
     * @param int $userId
     * @param TaskDto $taskData
     * @return Task
     */
    public function create(int $userId, TaskDto $taskData): Task
    {
        return Task::create([
            'user_id' => $userId,
            'title' => $taskData->title,
            'description' => $taskData->description,
            'status' => $taskData->status->value,
        ]);
    }

    /**
     * Обновление задачи
     *
     * @param int $userId
     * @param int $taskId
     * @param TaskDto $taskData
     * @return Task
     * @throws \Exception
     */
    public function update(int $userId, int $taskId, TaskDto $taskData): Task
    {
        $task = $this->getById(userId: $userId, taskId: $taskId);

        $task->update([
            'title' => $taskData->title ?? $task->title,
            'description' => $taskData->description ?? $task->description,
            'status' => $taskData->status->value ?? $task->status,
        ]);

        return $task;
    }

    /**
     * Удаление задачи
     *
     * @param int $userId
     * @param int $taskId
     * @return void
     * @throws \Exception
     */
    public function delete(int $userId, int $taskId): void
    {
        $task = $this->getById(userId: $userId, taskId: $taskId);

        $task->delete();
    }
}
