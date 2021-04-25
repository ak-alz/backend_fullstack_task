<?php
namespace Library;

use Model\Comment_model;

class Comment_library
{
    /**
     * @param Comment_model[][] $data
     */
    private $comments;
    /**
     * @var int
     */
    private $parent;

    private $cache = [];
    private $cache_key = 'comments';

    /**
     * Comment_library constructor.
     * @param Comment_model[][] $data
     * @param int $parent
     */
    public function __construct(array $comments, int $parent)
    {
        $this->comments = $comments;
        $this->parent = $parent;
    }

    /**
     * Возвращает массив комментариев, отсортированный по порядку добавления
     *
     * @return array
     */
    public function get(): array
    {
        if (!array_key_exists($this->cache_key, $this->cache)) {
            $this->cache[$this->cache_key] = [];
            $this->create_flat_tree($this->comments, $this->parent);
        }

        return $this->cache[$this->cache_key];
    }

    /**
     * Генерируем плоский массив для вывода комментариев
     * Каждый комментарий соберржит ссылку на родителя
     *
     * @param Comment_model[][] $data
     * @param int $parent
     */
    private function create_flat_tree(array $data, int $parent)
    {
        $arr = $data[$parent];

        foreach ($arr as $k => $v) {
            $this->cache[$this->cache_key][] = $v;
            if(array_key_exists($v->get_id(), $data)){
                self::create_flat_tree($data, $v->get_id());
            }
        }
    }
}