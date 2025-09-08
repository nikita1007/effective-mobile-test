<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Created = 'created';
    case InProgress = 'in progress';
    case Completed = 'completed';
}
