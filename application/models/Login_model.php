<?php

namespace Model;
use App;
use CI_Emerald_Model;

class Login_model extends CI_Emerald_Model {

    /**
     * Авторизуем пользователя
     *
     * @param string $email
     * @param string $password
     * @return bool
     */
    public static function login(string $email, string $password): bool
    {
        $user = User_model::get_user_by_email($email);

        if (!$user->is_loaded() || $user->get_password() !== $password) return false;

        try {
            self::start_session($user);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    public static function logout()
    {
        App::get_ci()->session->unset_userdata('id');
    }

    /**
     * @param User_model $user
     * @throws \Exception
     */
    public static function start_session(User_model $user)
    {
        // если перенедан пользователь
        $user->is_loaded(TRUE);

        App::get_ci()->session->set_userdata('id', $user->get_id());
    }
}
