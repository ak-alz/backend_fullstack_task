<?php
namespace Model;

use App;
use CI_Emerald_Model;

class Transaction_model extends CI_Emerald_Model
{
    const CLASS_TABLE = 'transaction';
    const TRANSACTION_ERROR_WRONG_AMOUNT = 'wrong_amount';

    /** @var int */
    protected $id;
    /** @var int */
    protected $user_id;
    /** @var int */
    protected $type_id;
    /** @var float */
    protected $amount;
    /** @var string */
    protected $time_created;
    /** @var string */
    protected $time_updated;

    public function __construct($id = null)
    {
        parent::__construct();
        $this->set_id($id);
    }

    /**
     * @param User_model $user
     * @param float $amount
     * @return Transaction_model
     * @throws \Exception
     */
    public static function balance_refill_processing(User_model $user, float $amount): Transaction_model
    {
        if ($amount <= 0) throw new \Exception(self::TRANSACTION_ERROR_WRONG_AMOUNT);

        //Открыли транзакцию
        App::get_ci()->s->start_trans();
        try {
            //Изменили балан сопльзователя
            $user->balance_refill($amount);
            //Создали запись в таблице транзакций
            $transaction_id = self::insert_transaction($user->get_id(), Transaction_type_model::TRANSACTION_TYPE_BALANCE_REFILL, $amount);
        } catch (\Exception $e) {
            //Откатили изменения
            App::get_ci()->s->rollback();
            throw $e;
        }
        //Сохранили именения
        App::get_ci()->s->commit();
        return new self($transaction_id);
    }

    /**
     * @param User_model $user
     * @param float $amount
     * @param int $boostpack_id
     * @return Transaction_model
     * @throws \Exception
     */
    public static function balance_withdraw_processing(User_model $user, Boosterpack_model $boosterpack): Transaction_model
    {
        //Открыли транзакцию
        App::get_ci()->s->start_trans();
        try {
            //Списали пользовательский баланс
            $user->balance_withdraw($boosterpack->get_price());
            //Содали запись в таблице транзакций
            $transaction_id = self::insert_transaction($user->get_id(), Transaction_type_model::TRANSACTION_TYPE_BALANCE_WITHDRAW, $boosterpack->get_price());
            //Открыли бустерпак
            $likes = $user->openBoosterPack($boosterpack);
            //Сохранили детальную информацию об открытом бустерпаке
            Transaction_info_model::insert_boosterpack_buy_info($transaction_id, floatval($likes), $boosterpack->get_id());
        } catch (\Exception $e) {
            //Сохранили изменения
            App::get_ci()->s->rollback();
            throw $e;
        }
        //Завершили транзакцию
        App::get_ci()->s->commit();
        return new self($transaction_id);
    }

    /**
     * @param int $user_id
     * @param int $type
     * @param float $amount
     * @return int
     * @throws \Exception
     */
    public static function insert_transaction(int $user_id, int $type_id, float $amount): int
    {
        $data = [
            'user_id' => $user_id,
            'type_id' => $type_id,
            'amount' => $amount
        ];

        if (!Transaction_type_model::has_value($data['type_id'])) throw new \Exception(Transaction_type_model::TRANSACTION_TYPE_ERROR);

        App::get_ci()->s->from(self::CLASS_TABLE)->insert($data)->execute();

        return App::get_ci()->s->get_insert_id();
    }
}