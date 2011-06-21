<?php

/**
 * BaseVideo
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $user_id
 * @property varchar $name
 * @property User $User
 * 
 * @method integer getUserId()  Returns the current record's "user_id" value
 * @method varchar getName()    Returns the current record's "name" value
 * @method User    getUser()    Returns the current record's "User" value
 * @method Video   setUserId()  Sets the current record's "user_id" value
 * @method Video   setName()    Sets the current record's "name" value
 * @method Video   setUser()    Sets the current record's "User" value
 * 
 * @package    ##PROJECT_NAME##
 * @subpackage model
 * @author     ##AUTHOR_NAME##
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseVideo extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('video');
        $this->hasColumn('user_id', 'integer', null, array(
             'type' => 'integer',
             ));
        $this->hasColumn('name', 'varchar', null, array(
             'type' => 'varchar',
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('User', array(
             'local' => 'user_id',
             'foreign' => 'id'));

        $powactivatable0 = new powActivatable(array(
             'columnName' => 'is_visible',
             'options' => 
             array(
              'default' => true,
             ),
             ));
        $this->actAs($powactivatable0);
    }
}