<?php
namespace Model;

use App;
use CI_Emerald_Model;
use Library\Comment_library;

/**
 * Created by PhpStorm.
 * User: mr.incognito
 * Date: 27.01.2020
 * Time: 10:10
 */
class Comment_model extends CI_Emerald_Model
{
    const CLASS_TABLE = 'comment';


    /** @var int */
    protected $user_id;
    /** @var int */
    protected $post_id;
    /** @var int */
    protected $parent_id;
    /** @var int */
    protected $replies;

    /** @var string */
    protected $text;

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
     * @return int
     */
    public function get_post_id(): int
    {
        return $this->post_id;
    }

    /**
     * @param int $assing_id
     *
     * @return bool
     */
    public function set_post_id(int $post_id)
    {
        $this->post_id = $post_id;
        return $this->save('post_id', $post_id);
    }

    /**
     * @return int|null
     */
    public function get_parent_id(): ?int
    {
        return $this->parent_id;
    }

    /**
     * @param int $assing_id
     *
     * @return bool
     */
    public function set_parent_id(int $parent_id)
    {
        $this->parent_id = $parent_id;
        return $this->save('parent_id', $parent_id);
    }

    /**
     * @return int
     */
    public function get_replies()
    {
        return $this->replies;
    }

    /**
     * @param int $count
     * @return bool
     */
    public function set_replies(int $count)
    {
        $this->replies = $count;
        return $this->save('replies', $count);
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
     * @return mixed
     */
    public function get_comments()
    {
        return $this->comments;
    }

    /**
     * @return User_model
     */
    public function get_user():User_model
    {
        if (empty($this->user))
        {
            try {
                $this->user = new User_model($this->get_user_id());
            } catch (\Exception $exception)
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

    /**
     * @param array $data
     * @return Comment_model
     * @throws \Exception
     */
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
     * @param int|null $id
     * @return Comment_model
     */
    public static function get_by_id(int $id = null): Comment_model
    {
        $res = !is_null($id) ? App::get_ci()->s->from(self::CLASS_TABLE)->where(['id' => $id])->one() : false;

        return new self($res ? $res['id'] : null);
    }

    /**
     * @param int $assting_id
     * @return array
     * @throws \Exception
     */
    public static function get_all_by_post_id(int $post_id)
    {
        $res = App::get_ci()->s->from(self::CLASS_TABLE)->where(['post_id' => $post_id])->order(['parent_id', 'id'],'ASC')->many();
        $ret = [];
        foreach ($res as $row) {
            $c = (new self())->set($row);
            $ret[(int) $c->parent_id][] = $c;
        }

        return (new Comment_library($ret, 0))->get();
    }

    /**
     * @param self|self[] $data
     * @param string $preparation
     * @return \stdClass|\stdClass[]
     * @throws \Exception
     */
    public static function preparation($data, $preparation = 'default')
    {
        switch ($preparation)
        {
            case 'full_info':
                return self::_preparation_full_info($data);
            default:
                throw new \Exception('undefined preparation type');
        }
    }


    /**
     * @param self[] $data
     * @return \stdClass[]
     */
    private static function _preparation_full_info($data)
    {
        $ret = [];

        foreach ($data as $d){
            $o = new \stdClass();

            $o->id = $d->get_id();
            $o->parent_id = $d->get_parent_id();
            $o->replies = $d->get_replies();

            $o->text = $d->get_text();

            $o->user = User_model::preparation($d->get_user(),'main_page');

            $o->likes = Like_model::get_likes($d->get_id(), Assign_type_model::ASSIGN_TYPE_COMMENT);

            $o->time_created = $d->get_time_created();
            $o->time_updated = $d->get_time_updated();

            $ret[] = $o;
        }


        return $ret;
    }


}
