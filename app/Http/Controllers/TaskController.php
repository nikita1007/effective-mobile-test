<?php

namespace App\Http\Controllers;

use App\Dtos\TaskDto;
use App\Enums\TaskStatus;
use Illuminate\Http\Request;
use App\Services\TaskService;
use Illuminate\Validation\ValidationException;

class TaskController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $user_id = $request->get('user_id');

            $tasks = $this->taskService->getAll(userId: $user_id);
            return response()->json([
                'status' => true,
                'data' => $tasks
            ], 200);
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], (int) $e->getCode() ?: 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string'
            ]);

            $task_data = new TaskDto(
                title: $validated['title'],
                description: $validated['description'] ?? null
            );

            $user_id = $request->get('user_id');

            $task = $this->taskService->create(userId: $user_id, taskData: $task_data);
            return response()->json([
                'status' => true,
                'data' => $task
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 400);
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], (int) $e->getCode() ?: 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, int $taskId)
    {
        try {
            $user_id = $request->get('user_id');

            $task = $this->taskService->getById(userId: $user_id, taskId: $taskId);
            return response()->json([
                'status' => true,
                'data' => $task
            ], 200);
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], (int) $e->getCode() ?: 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $taskId)
    {
        try {
            $validated = $request->validate([
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'status' => 'nullable|in:created,in progress,completed',
            ]);

            $task_data = new TaskDto(
                title: $validated['title'] ?? null,
                description: $validated['description'] ?? null,
                status: empty($validated['status']) ? null : TaskStatus::from($validated['status'])
            );

            $user_id = $request->get('user_id');

            $task = $this->taskService->update(userId: $user_id, taskId: $taskId, taskData: $task_data);
            return response()->json([
                'status' => true,
                'data' => $task
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 400);
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], (int) $e->getCode() ?: 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, int $taskId)
    {
        try {
            $user_id = $request->get('user_id');
            $this->taskService->delete(userId: $user_id, taskId: $taskId);
            return response()->json(['status' => true, 'message' => 'Task deleted successfully'], 200);
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], (int) $e->getCode() ?: 500);
        }
    }
}
