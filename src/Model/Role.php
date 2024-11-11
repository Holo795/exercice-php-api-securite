<?php

namespace App\Model;

enum Role: string
{
    case ADMIN = 'ROLE_ADMIN';
    case MANAGER = 'ROLE_MANAGER';
    case CONSULTANT = 'ROLE_CONSULTANT';
}
