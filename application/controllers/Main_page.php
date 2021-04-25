<?php

use Model\Login_model;
use Model\Post_model;
use Model\User_model;
use Model\Like_model;
use Model\Transaction_model;
use Model\Boosterpack_model;
use Model\Transaction_info_model;

/**
 * Created by PhpStorm.
 * User: mr.incognito
 * Date: 10.11.2018
 * Time: 21:36
 */
class Main_page extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();

        if (is_prod())
        {
            die('In production it will be hard to debug! Run as development environment!');
        }
    }

    public function index()
    {
        $user = User_model::get_user();

        App::get_ci()->load->view('main_page', ['user' => User_model::preparation($user, 'default')]);
    }

    public function get_all_posts()
    {
        $posts =  Post_model::preparation(Post_model::get_all(), 'main_page');
        return $this->response_success(['posts' => $posts]);
    }

    public function get_post($post_id){ // or can be $this->input->post('news_id') , but better for GET REQUEST USE THIS

        $post_id = intval($post_id);

        if (empty($post_id)){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        try
        {
            $post = new Post_model($post_id);
        } catch (EmeraldModelNoDataException $ex){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NO_DATA);
        }


        $posts =  Post_model::preparation($post, 'full_info');
        return $this->response_success(['post' => $posts]);
    }

    public function comment()
    {
        $post_id = intval($this->input->get_post('post_id', true));
        $parent_id = intval($this->input->get_post('parent_id', true));
        $message = $this->input->get_post('message', true);

        //Ид пользователя пробрасывается для тестирования
        $user_id = $this->input->get_post('user_id', true);

        if (!$user_id && !User_model::is_logged()){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $post_id = intval($post_id);

        if ($post_id < 1 || empty($message)){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        try {
            $post = new Post_model($post_id);
        } catch (EmeraldModelNoDataException $ex){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NO_DATA);
        }

        // Todo: 2 nd task Comment
        try {
            //Получаем либо авторизованного пользователя, либо пользователя по переданному ТЕСТОВОМУ!!! id
            $user = $user_id ? new User_model($user_id) : User_model::get_user();

            $post->comment($user->get_id(), $post_id, $message, $parent_id);
        } catch (\Exception $e) {
            return $this->response_error($e->getMessage());
        }

        $posts =  Post_model::preparation($post, 'full_info');

        return $this->response_success(['post' => $posts]);
    }

    /**
     * Данные для авторизации:
     *
     * $login = "admin@niceadminmail.pl"
     * $password ="1234"
     * или
     * $login = "simpleuser@niceadminmail.pl"
     * $password = "1234"
     *
     * @return object|string|void
     */
    public function login()
    {
        $user = User_model::get_user();

        if ($user->is_loaded()) return $this->response_error(CI_Core::AUTH_USER_ALREADY_AUTHORIZED);

        $login = App::get_ci()->input->post('login');
        $password = App::get_ci()->input->post('password');

        if (empty($login) || empty($password)){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        // But data from modal window sent by POST request.  App::get_ci()->input...  to get it.


        //Todo: 1 st task - Authorisation.

        if (!Login_model::login($login, $password)) return $this->response_error(CI_Core::AUTH_CREDENTIALS_ERROR);

        return $this->response_success(['user_id' => User_model::get_user()->get_id()]);
    }

    public function logout()
    {
        Login_model::logout();
        redirect(site_url('/'));
    }

    public function add_money(){
        // todo: 4th task  add money to user logic
        //Ид пользователя пробрасывается для тестирования
        $user_id = $this->input->get_post('user_id', true);

        if (!$user_id && !User_model::is_logged()){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $amount = floatval($this->input->get_post('amount', true));
        if ($amount <= 0) {
            return $this->response_error(Transaction_model::TRANSACTION_ERROR_WRONG_AMOUNT);
        }

        try {
            $user = $user_id ? new User_model($user_id) : User_model::get_user();
            Transaction_model::balance_refill_processing($user, $amount);
        } catch (Exception $e) {
            return $this->response_error($e->getMessage());
        }

        return $this->response_success(['amount' => $amount]); // Колво лайков под постом \ комментарием чтобы обновить . Сейчас рандомная заглушка
    }

    public function buy_boosterpack(){
        // todo: 5th task add money to user logic
        //Ид пользователя пробрасывается для тестирования
        $user_id = $this->input->get_post('user_id', true);

        if (!$user_id && !User_model::is_logged()){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $boosterpack_id = intval($this->input->get_post('boosterpack_id', true));

        $boosterpack = new Boosterpack_model($boosterpack_id);

        if (!$boosterpack->is_loaded()) {
            return $this->response_error(Boosterpack_model::BOOSTERPACK_ERROR_WRONG_ID);
        }

        try {
            $user = $user_id ? new User_model($user_id) : User_model::get_user();
            Transaction_model::balance_withdraw_processing($user, $boosterpack);
        } catch (Exception $e) {
            return $this->response_error($e->getMessage());
        }

        return $this->response_success(['amount' => $boosterpack->get_price()]); // Колво лайков под постом \ комментарием чтобы обновить . Сейчас рандомная заглушка
    }


    public function like(){
        // todo: 3rd task add like post\comment logic
        //Ид пользователя пробрасывается для тестирования
        $user_id = $this->input->get_post('user_id', true);

        if (!$user_id && !User_model::is_logged()) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $assign_id = (int) $this->input->get_post('assign_id', true);
        $type_id = (int) $this->input->get_post('type_id', true);

        if ($assign_id < 1 || $type_id < 1) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        $like = new Like_model();

        try {
            //Получаем либо авторизованного пользователя, либо пользователя по переданному ТЕСТОВОМУ!!! id
            $user = $user_id ? new User_model($user_id) : User_model::get_user();
            $like->add_like($user, $assign_id, $type_id);
        } catch (\Exception $e) {
            return $this->response_error($e->getMessage());
        }

        return $this->response_success(['likes' => $like->get_likes($assign_id, $type_id)]); // Колво лайков под постом \ комментарием чтобы обновить . Сейчас рандомная заглушка
    }

    public function seed_demo_data()
    {
        Transaction_info_model::seedDemoData();
    }
}
