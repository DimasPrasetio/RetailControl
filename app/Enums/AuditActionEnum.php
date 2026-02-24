<?php

namespace App\Enums;

enum AuditActionEnum: string
{
    case Create       = 'create';
    case Update       = 'update';
    case Delete       = 'delete';
    case StatusChange = 'status_change';
    case Void         = 'void';
    case Login        = 'login';
    case Logout       = 'logout';
}
