<?php

namespace App\Dtos;

use App\Enums\TaskStatus;

class TaskDto
{
    public function __construct(
        public ?string     $title,
        public ?string     $description = null,
        public ?TaskStatus $status = TaskStatus::Created
    )
    {
    }
}
