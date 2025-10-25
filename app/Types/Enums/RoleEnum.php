<?php

namespace App\Types\Enums;

enum RoleEnum: string
{
    case ADMIN = 'admin';
    case DOCTOR = 'doctor';
    case PATIENT = 'patient';
}
