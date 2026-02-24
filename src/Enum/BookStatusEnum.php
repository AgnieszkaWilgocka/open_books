<?php

namespace App\Enum;


enum BookStatusEnum: string
{
    case Borrowed = 'borrowed';
    case Available = 'available';

}