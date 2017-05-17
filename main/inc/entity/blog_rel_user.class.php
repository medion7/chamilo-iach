<?php

namespace Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @license see /license.txt
 * @author autogenerated
 */
class BlogRelUser extends \CourseEntity
{
    /**
     * @return \Entity\Repository\BlogRelUserRepository
     */
     public static function repository(){
        return \Entity\Repository\BlogRelUserRepository::instance();
    }

    /**
     * @return \Entity\BlogRelUser
     */
     public static function create(){
        return new self();
    }

    /**
     * @var integer $c_id
     */
    protected $c_id;

    /**
     * @var integer $blog_id
     */
    protected $blog_id;

    /**
     * @var integer $user_id
     */
    protected $user_id;


    /**
     * Set c_id
     *
     * @param integer $value
     * @return BlogRelUser
     */
    public function set_c_id($value)
    {
        $this->c_id = $value;
        return $this;
    }

    /**
     * Get c_id
     *
     * @return integer 
     */
    public function get_c_id()
    {
        return $this->c_id;
    }

    /**
     * Set blog_id
     *
     * @param integer $value
     * @return BlogRelUser
     */
    public function set_blog_id($value)
    {
        $this->blog_id = $value;
        return $this;
    }

    /**
     * Get blog_id
     *
     * @return integer 
     */
    public function get_blog_id()
    {
        return $this->blog_id;
    }

    /**
     * Set user_id
     *
     * @param integer $value
     * @return BlogRelUser
     */
    public function set_user_id($value)
    {
        $this->user_id = $value;
        return $this;
    }

    /**
     * Get user_id
     *
     * @return integer 
     */
    public function get_user_id()
    {
        return $this->user_id;
    }
}