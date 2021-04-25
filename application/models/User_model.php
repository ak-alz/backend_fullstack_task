<?php

namespace Model;

use App;
use CI_Emerald_Model;
use Exception;
use stdClass;

/**
 * Created by PhpStorm.
 * User: mr.incognito
 * Date: 27.01.2020
 * Time: 10:10
 */
class User_model extends CI_Emerald_Model {
    const CLASS_TABLE = 'user';

    const USER_ERROR_NOT_ENOUGH_LIKES_BALANCE = 'not_enough_likes_balance';
    const USER_ERROR_NOT_ENOUGH_BALANCE = 'not_enough_balance';

    /** @var string */
    protected $email;
    /** @var string */
    protected $password;
    /** @var string */
    protected $personaname;
    /** @var string */
    protected $profileurl;
    /** @var string */
    protected $avatarfull;
    /** @var int */
    protected $rights;
    /** @var float */
    protected $wallet_balance;
    /** @var float */
    protected $wallet_total_refilled;
    /** @var float */
    protected $wallet_total_withdrawn;
    /** @var int */
    protected $wallet_likes_balance;
    /** @var string */
    protected $time_created;
    /** @var string */
    protected $time_updated;


    private static $_current_user;

    /**
     * @return string
     */
    public function get_email(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return bool
     */
    public function set_email(string $email)
    {
        $this->email = $email;
        return $this->save('email', $email);
    }

    /**
     * @return string|null
     */
    public function get_password(): ?string
    {
        return $this->password;
    }

    /**
     * @param string $password
     *
     * @return bool
     */
    public function set_password(string $password)
    {
        $this->password = $password;
        return $this->save('password', $password);
    }

    /**
     * @return string
     */
    public function get_personaname(): string
    {
        return $this->personaname;
    }

    /**
     * @param string $personaname
     *
     * @return bool
     */
    public function set_personaname(string $personaname)
    {
        $this->personaname = $personaname;
        return $this->save('personaname', $personaname);
    }

    /**
     * @return string
     */
    public function get_avatarfull(): string
    {
        return $this->avatarfull;
    }

    /**
     * @param string $avatarfull
     *
     * @return bool
     */
    public function set_avatarfull(string $avatarfull)
    {
        $this->avatarfull = $avatarfull;
        return $this->save('avatarfull', $avatarfull);
    }

    /**
     * @return int
     */
    public function get_rights(): int
    {
        return $this->rights;
    }

    /**
     * @param int $rights
     *
     * @return bool
     */
    public function set_rights(int $rights)
    {
        $this->rights = $rights;
        return $this->save('rights', $rights);
    }

    /**
     * @return float
     */
    public function get_wallet_balance(): float
    {
        return $this->wallet_balance;
    }

    /**
     * @param float $wallet_balance
     *
     * @return bool
     */
    public function set_wallet_balance(float $wallet_balance)
    {
        $this->wallet_balance = $wallet_balance;
        return $this->save('wallet_balance', $wallet_balance);
    }

    /**
     * @return float
     */
    public function get_wallet_total_refilled(): float
    {
        return $this->wallet_total_refilled;
    }

    /**
     * @param float $wallet_total_refilled
     *
     * @return bool
     */
    public function set_wallet_total_refilled(float $wallet_total_refilled)
    {
        $this->wallet_total_refilled = $wallet_total_refilled;
        return $this->save('wallet_total_refilled', $wallet_total_refilled);
    }

    /**
     * @return float
     */
    public function get_wallet_total_withdrawn(): float
    {
        return $this->wallet_total_withdrawn;
    }

    /**
     * @param float $wallet_total_withdrawn
     *
     * @return bool
     */
    public function set_wallet_total_withdrawn(float $wallet_total_withdrawn)
    {
        $this->wallet_total_withdrawn = $wallet_total_withdrawn;
        return $this->save('wallet_total_withdrawn', $wallet_total_withdrawn);
    }

    /** @return int */
    public function get_wallet_likes_balance(): int
    {
        return $this->wallet_likes_balance;
    }
    /**
     * @param int $likes_count
     * @return bool
     */
    public function set_wallet_likes_balance(int $likes_count): bool
    {
        $this->wallet_likes_balance = $likes_count;
        return $this->save('wallet_likes_balance', $likes_count);
    }

    /**
     * @param int $amount
     * @return $this
     * @throws \Exception
     */
    private function change_likes_balance(int $amount)
    {
        $this->reload(true);
        $this->set_wallet_likes_balance($this->get_wallet_likes_balance() + $amount);

        return $this;
    }

    /**
     * @param $amount
     * @return User_model
     * @throws \Exception
     */
    public function balance_likes_refill($amount)
    {
        if ($amount <= 0) throw new \Exception(self::USER_ERROR_NOT_ENOUGH_LIKES_BALANCE);

        return $this->change_likes_balance($amount);
    }

    /**
     * @param int $amount
     * @return User_model
     * @throws \Exception
     */
    public function balance_likes_withdraw(int $amount)
    {
        if ($this->get_wallet_likes_balance() < $amount || $amount <= 0) throw new \Exception(self::USER_ERROR_NOT_ENOUGH_LIKES_BALANCE);

        return $this->change_likes_balance($amount * -1);
    }

    /**
     * @param float $amount
     * @return $this
     * @throws \Exception
     */
    public function balance_refill(float $amount)
    {
        //Обновляем пользователя и лочим для обновления полей
        $this->reload(true);
        //Изменяем баланс
        $this->set_wallet_balance($this->get_wallet_balance() + $amount);
        $this->set_wallet_total_refilled($this->get_wallet_total_refilled() + $amount);

        return $this;
    }

    /**
     * @param float $amount
     * @return $this
     * @throws \Exception
     */
    public function balance_withdraw(float $amount)
    {
        if ($this->get_wallet_balance() < $amount) {
            throw new \Exception(self::USER_ERROR_NOT_ENOUGH_BALANCE);
        }
        //Обновляем пользователя и лочим для обновления полей
        $this->reload(true);

        //Изменяем баланс
        $this->set_wallet_balance($this->get_wallet_balance() - $amount);
        $this->set_wallet_total_withdrawn($this->get_wallet_total_withdrawn() + $amount);

        return $this;
    }

    /**
     * @param Boosterpack_model $boosterpack
     * @return int
     * @throws \Exception
     */
    public function openBoosterPack(Boosterpack_model $boosterpack): int
    {
        $likes = $boosterpack->open();
        $this->balance_likes_refill($likes);

        return $likes;
    }

    /**
     * @return string
     */
    public function get_time_created(): string
    {
        return $this->time_created;
    }

    /**
     * @param string $time_created
     *
     * @return bool
     */
    public function set_time_created(string $time_created)
    {
        $this->time_created = $time_created;
        return $this->save('time_created', $time_created);
    }

    /**
     * @return string
     */
    public function get_time_updated(): string
    {
        return $this->time_updated;
    }

    /**
     * @param string $time_updated
     *
     * @return bool
     */
    public function set_time_updated(string $time_updated)
    {
        $this->time_updated = $time_updated;
        return $this->save('time_updated', $time_updated);
    }


    function __construct($id = NULL)
    {
        parent::__construct();
        $this->set_id($id);
    }

    public function reload(bool $for_update = FALSE)
    {
        parent::reload($for_update);

        return $this;
    }

    public static function create(array $data)
    {
        App::get_ci()->s->from(self::CLASS_TABLE)->insert($data)->execute();
        return new static(App::get_ci()->s->get_insert_id());
    }

    public function delete()
    {
        $this->is_loaded(TRUE);
        App::get_ci()->s->from(self::CLASS_TABLE)->where(['id' => $this->get_id()])->delete()->execute();
        return (App::get_ci()->s->get_affected_rows() > 0);
    }

    /**
     * @return self[]
     * @throws Exception
     */
    public static function get_all():array
    {

        $data = App::get_ci()->s->from(self::CLASS_TABLE)->many();
        $ret = [];
        foreach ($data as $i)
        {
            $ret[] = (new self())->set($i);
        }
        return $ret;
    }


    /**
     * Getting id from session
     * @return integer|null
     */
    public static function get_session_id(): ?int
    {
        return App::get_ci()->session->userdata('id');
    }

    /**
     * @return bool
     */
    public static function is_logged():bool
    {
        $steam_id = intval(self::get_session_id());
        return $steam_id > 0;
    }



    /**
     * Returns current user or empty model
     * @return User_model
     */
    public static function get_user()
    {
        if (! is_null(self::$_current_user)) {
            return self::$_current_user;
        }
        if ( ! is_null(self::get_session_id()))
        {
            self::$_current_user = new self(self::get_session_id());
            return self::$_current_user;
        } else
        {
            return new self();
        }
    }

    /**
     * Возвращает пользователя по email, либо пустой объект
     *
     * @param string $email
     * @return User_model
     */
    public static function get_user_by_email(string $email): User_model
    {
        $data = App::get_ci()->s->from(self::CLASS_TABLE)->where('email', $email)->one();
        $user = new self();
        return $data ? $user->set($data) : $user;
    }


    /**
     * @param User_model|User_model[] $data
     * @param string $preparation
     * @return stdClass|stdClass[]
     * @throws Exception
     */
    public static function preparation($data, $preparation = 'default')
    {
        switch ($preparation)
        {
            case 'main_page':
                return self::_preparation_main_page($data);
            case 'default':
                return self::_preparation_default($data);
            default:
                throw new Exception('undefined preparation type');
        }
    }

    /**
     * @param User_model $data
     * @return stdClass
     */
    private static function _preparation_main_page($data)
    {
        $o = new stdClass();

        $o->id = $data->get_id();

        $o->personaname = $data->get_personaname();
        $o->avatarfull = $data->get_avatarfull();

        $o->time_created = $data->get_time_created();
        $o->time_updated = $data->get_time_updated();


        return $o;
    }


    /**
     * @param User_model $data
     * @return stdClass
     */
    private static function _preparation_default($data)
    {
        $o = new stdClass();

        if (!$data->is_loaded())
        {
            $o->id = NULL;
        } else {
            $o->id = $data->get_id();

            $o->personaname = $data->get_personaname();
            $o->avatarfull = $data->get_avatarfull();

            $o->time_created = $data->get_time_created();
            $o->time_updated = $data->get_time_updated();
        }

        return $o;
    }

}
