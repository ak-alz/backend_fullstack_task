<?php
namespace Model;
use App;
use CI_Emerald_Model;
use http\Client\Curl\User;
use mysql_xdevapi\Exception;

class Like_model extends CI_Emerald_Model
{
    const CLASS_TABLE = 'likes';

    /** @var int */
    protected $id;
    /** @var int */
    protected $type_id;
    /** @var int */
    protected $assign_id;
    /** @var int */
    protected $user_id;
    /** @var string */
    protected $time_created;
    /** @var string */
    protected $time_updated;

    public function __construct(int $id = null)
    {
        parent::__construct();
        $this->set_id($id);
    }

    /**
     * @param User_model $user
     * @param int $assign_id
     * @param int $type_id
     * @throws \Exception
     */
    public static function add_like(User_model $user, int $assign_id, int $type_id = Assign_type_model::ASSIGN_TYPE_POST)
    {
        $data = [
            'type_id' => $type_id,
            'assign_id' => $assign_id,
            'user_id' => $user->get_id(),
        ];

        App::get_ci()->s->start_trans();

        try {
            if (!Assign_type_model::has_value($data['type_id'])) throw new \Exception(Assign_type_model::ASSIGN_ERROR_WRONG_TYPE);
            if ($user->get_wallet_likes_balance() < 1) throw new \Exception(User_model::USER_ERROR_NOT_ENOUGH_LIKES_BALANCE);

            $user->balance_likes_withdraw(1);
            App::get_ci()->s->from(self::CLASS_TABLE)->insert($data)->execute();

            App::get_ci()->s->commit();
        } catch (\Exception $e) {
            App::get_ci()->s->rollback();
            throw $e;
        }
    }

    /**
     * @param int $assign_id
     * @param int $type_id
     * @return int
     * @throws \Exception
     */
    public static function get_likes(int $assign_id, int $type_id = Assign_type_model::ASSIGN_TYPE_POST): int
    {
        if (!Assign_type_model::has_value($type_id)) throw new \Exception(Assign_type_model::ASSIGN_ERROR_WRONG_TYPE);

        $likes = App::get_ci()->s->from(self::CLASS_TABLE)
            ->where('assign_id', $assign_id)
            ->where('type_id', $type_id)
            ->count();

        return $likes;
    }
}