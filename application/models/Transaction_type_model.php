<?php
namespace Model;
use System\Core\Emerald_enum;

class Transaction_type_model extends Emerald_enum
{
    const TRANSACTION_TYPE_BALANCE_REFILL = '1';
    const TRANSACTION_TYPE_BALANCE_WITHDRAW = '2';

    const TRANSACTION_TYPE_ERROR = 'wrong_type';
}