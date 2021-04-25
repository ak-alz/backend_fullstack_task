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
class Post_model extends CI_Emerald_Model {
    const CLASS_TABLE = 'post';


    /** @var int */
    protected $user_id;
    /** @var string */
    protected $text;
    /** @var string */
    protected $img;

    /** @var string */
    protected $time_created;
    /** @var string */
    protected $time_updated;

    // generated
    protected $comments;
    protected $likes;
    protected $user;


    /**
     * @return int
     */
    public function get_user_id(): int
    {
        return $this->user_id;
    }

    /**
     * @param int $user_id
     *
     * @return bool
     */
    public function set_user_id(int $user_id)
    {
        $this->user_id = $user_id;
        return $this->save('user_id', $user_id);
    }

    /**
     * @return string
     */
    public function get_text(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     *
     * @return bool
     */
    public function set_text(string $text)
    {
        $this->text = $text;
        return $this->save('text', $text);
    }

    /**
     * @return string
     */
    public function get_img(): string
    {
        return $this->img;
    }

    /**
     * @param string $img
     *
     * @return bool
     */
    public function set_img(string $img)
    {
        $this->img = $img;
        return $this->save('img', $img);
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
    public function set_time_updated(int $time_updated)
    {
        $this->time_updated = $time_updated;
        return $this->save('time_updated', $time_updated);
    }

    // generated

    /**
     * @return mixed
     */
    public function get_likes()
    {
        return $this->likes;
    }

    /**
     * @return Comment_model[]
     */
    public function get_comments()
    {
        $this->is_loaded(TRUE);

        if (empty($this->comments))
        {
            $this->comments = Comment_model::get_all_by_post_id($this->get_id());
        }
        return $this->comments;

    }

    /**
     * @return User_model
     */
    public function get_user(): User_model
    {
        $this->is_loaded(TRUE);

        if (empty($this->user))
        {
            try
            {
                $this->user = new User_model($this->get_user_id());
            } catch (Exception $exception)
            {
                $this->user = new User_model();
            }
        }
        return $this->user;
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
     * @param int $user_id
     * @param int $post_id
     * @param string $message
     * @param int|null $parent_id
     * @throws Exception
     */
    public function comment(int $user_id, int $post_id, string $message, int $parent_id = null): Comment_model
    {
        $data = [
            'post_id' => $post_id,
            'user_id' => $user_id,
            'text' => $message,
            'parent_id' => $parent_id === 0 ? null : $parent_id
        ];

        $post = $data['post_id'] != $this->id ? new self($data['post_id']) : $this;
        if (!$post->is_loaded()) throw new Exception('Wrong comment parent post id');

        $parent_comment = Comment_model::get_by_id($data['parent_id']);
        if (!is_null($data['parent_id']) && !$parent_comment->is_loaded()) throw new Exception('Wrong comment parent id');

        $new_comment_id = Comment_model::create($data);
        if ($parent_comment->get_id()) $parent_comment->set_replies($parent_comment->get_replies() + 1);

        return $new_comment_id;
    }

    /**
     * @return self[]
     * @throws Exception
     */
    public static function get_all()
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
     * @param Post_model|Post_model[] $data
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
            case 'full_info':
                return self::_preparation_full_info($data);
            default:
                throw new Exception('undefined preparation type');
        }
    }

    /**
     * @param Post_model[] $data
     * @return stdClass[]
     */
    private static function _preparation_main_page($data)
    {
        $ret = [];

        foreach ($data as $d)
        {
            $o = new stdClass();

            $o->id = $d->get_id();
            $o->img = $d->get_img();

            $o->text = $d->get_text();

            $o->user = User_model::preparation($d->get_user(), 'main_page');

            $o->time_created = $d->get_time_created();
            $o->time_updated = $d->get_time_updated();

            $ret[] = $o;
        }


        return $ret;
    }


    /**
     * @param Post_model $data
     * @return stdClass
     */
    private static function _preparation_full_info(Post_model $data)
    {
        $o = new stdClass();


        $o->id = $data->get_id();
        $o->img = $data->get_img();

        $o->user = User_model::preparation($data->get_user(), 'main_page');

        $o->coments = Comment_model::preparation($data->get_comments(), 'full_info');
        $o->likes = Like_model::get_likes($data->get_id(), Assign_type_model::ASSIGN_TYPE_POST);

        $o->time_created = $data->get_time_created();
        $o->time_updated = $data->get_time_updated();

        $ret[] = $o;


        return $o;
    }


}
