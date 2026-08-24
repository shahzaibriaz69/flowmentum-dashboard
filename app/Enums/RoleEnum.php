<?php

namespace App\Enums;

enum RoleEnum: string
{
    case  AGENCY = "agency";
    case  LOCATION = "location";
    case  ADMIN = "admin";
    case  USER = "user";
}
