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
     * @OA\Get(
     *     path="/api/v1/tasks",
     *     operationId="index",
     *     tags={"Tasks"},
     *     summary="Get all tasks for the authenticated user",
     *     description="Returns a list of tasks",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="New Task"),
     *                     @OA\Property(property="description", type="string", example="Task description"),
     *                     @OA\Property(property="status", type="string", example="created"),
     *                     @OA\Property(property="user_id", type="integer", example=1),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-08T12:21:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-08T12:21:00Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Server error")
     *         )
     *     )
     * )
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
     * @OA\Post(
     *     path="/api/v1/tasks",
     *     operationId="store",
     *     tags={"Tasks"},
     *     summary="Create a new task",
     *     description="Create a new task for the authenticated user",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title"},
     *             @OA\Property(property="title", type="string", example="New Task", description="Task title"),
     *             @OA\Property(property="description", type="string", example="Task description", description="Task description", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Task created",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="New Task"),
     *                 @OA\Property(property="description", type="string", example="Task description"),
     *                 @OA\Property(property="status", type="string", example="created"),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-08T12:21:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-08T12:21:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object", example={"title": {"The title field is required."}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Server error")
     *         )
     *     )
     * )
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
     * @OA\Get(
     *     path="/api/v1/tasks/{taskId}",
     *     operationId="show",
     *     tags={"Tasks"},
     *     summary="Get a specific task",
     *     description="Retrieve a task by ID for the authenticated user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="taskId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="New Task"),
     *                 @OA\Property(property="description", type="string", example="Task description"),
     *                 @OA\Property(property="status", type="string", example="created"),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-08T12:21:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-08T12:21:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Задача не найдена.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Server error")
     *         )
     *     )
     * )
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
     * @OA\Put(
     *     path="/api/v1/tasks/{taskId}",
     *     operationId="update",
     *     tags={"Tasks"},
     *     summary="Update a task",
     *     description="Update a task for the authenticated user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="taskId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Updated Task", description="Task title", nullable=true),
     *             @OA\Property(property="description", type="string", example="Updated description", description="Task description", nullable=true),
     *             @OA\Property(property="status", type="string", example="completed", description="Task status", nullable=true, enum={"created", "in progress", "completed"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Updated Task"),
     *                 @OA\Property(property="description", type="string", example="Updated description"),
     *                 @OA\Property(property="status", type="string", example="completed"),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-08T12:21:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-08T12:21:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object", example={"title": {"The title format is invalid."}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Задача не найдена.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Server error")
     *         )
     *     )
     * )
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
     * @OA\Delete(
     *     path="/api/v1/tasks/{taskId}",
     *     operationId="destroy",
     *     tags={"Tasks"},
     *     summary="Delete a task",
     *     description="Delete a task for the authenticated user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="taskId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Task deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Задача не найдена.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Server error")
     *         )
     *     )
     * )
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
