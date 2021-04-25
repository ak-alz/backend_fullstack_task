<?php
namespace Model;

use System\Core\Emerald_enum;

class Assign_type_model extends Emerald_enum
{
    const ASSIGN_TYPE_POST = 1;
    const ASSIGN_TYPE_COMMENT = 2;

    const ASSIGN_ERROR_WRONG_TYPE = 'wrong_type_id';
}