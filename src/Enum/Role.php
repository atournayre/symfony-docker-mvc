<?php

namespace App\Enum;

enum Role
{
    case ROLE_USER;
    case ROLE_ADMIN;
    case ROLE_SUPER_ADMIN;
}