<?php
namespace Model;

use App;
use CI_Emerald_Model;

class Transaction_info_model extends CI_Emerald_Model
{
    const CLASS_TABLE = 'transaction_info';

    /** @var int */
    protected $id;
    /** @var int */
    protected $transaction_id;
    /** @var int */
    protected $boosterpack_id;
    /** @var float */
    protected $amount;
    /** @var string */
    protected $time_created;

    public function __construct($id = null)
    {
        parent::__construct();
        $this->set_id($id);
    }

    /**
     * @param int $transaction_id
     * @param float $amount
     * @param int $boosterpack_id
     * @return Transaction_info_model
     * @throws \Exception
     */
    public static function insert_boosterpack_buy_info(int $transaction_id, float $amount, int $boosterpack_id): Transaction_info_model
    {
        $data = [
            'transaction_id' => $transaction_id,
            'amount' => $amount,
            'boosterpack_id' => $boosterpack_id
        ];

        App::get_ci()->s->from(self::CLASS_TABLE)->insert($data)->execute();

        return new static(App::get_ci()->s->get_insert_id());
    }

    /**
     * Заполняем таблицу транзакций
     * @throws \Exception
     */
    public static function seedDemoData()
    {
        return false;
        $dt = strtotime("-30 days");
        $dt_current = time();
        while ($dt < $dt_current) {
            $user = new User_model(rand(1, 2));
            $boosterpack = new Boosterpack_model(rand(1, 3));
            Transaction_model::balance_refill_processing($user, $boosterpack->get_price() + rand(0, 10));
            $user->balance_withdraw($boosterpack->get_price());
            $transaction_id = Transaction_model::insert_transaction($user->get_id(), Transaction_type_model::TRANSACTION_TYPE_BALANCE_WITHDRAW, $boosterpack->get_price());
            $likes = $user->openBoosterPack($boosterpack);

            $data = [
                'transaction_id' => $transaction_id,
                'amount' => $likes,
                'boosterpack_id' => $boosterpack->get_id(),
                'time_created' => date("Y-m-d H:i:s", $dt)
            ];

            App::get_ci()->s->from(self::CLASS_TABLE)->insert($data)->execute();
            $dt += 1200;
        }
    }
}