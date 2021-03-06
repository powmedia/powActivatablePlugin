<?php

/**
 * BaseUser
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property varchar $name
 * @property Doctrine_Collection $Videos
 * 
 * @method integer             getId()     Returns the current record's "id" value
 * @method varchar             getName()   Returns the current record's "name" value
 * @method Doctrine_Collection getVideos() Returns the current record's "Videos" collection
 * @method User                setId()     Sets the current record's "id" value
 * @method User                setName()   Sets the current record's "name" value
 * @method User                setVideos() Sets the current record's "Videos" collection
 * 
 * @package    ##PROJECT_NAME##
 * @subpackage model
 * @author     ##AUTHOR_NAME##
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseUser extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('user');
        $this->hasColumn('id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             'autoincrement' => true,
             ));
        $this->hasColumn('name', 'varchar', null, array(
             'type' => 'varchar',
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasMany('Video as Videos', array(
             'local' => 'id',
             'foreign' => 'user_id'));

        $powactivatable0 = new powActivatable();
        $this->actAs($powactivatable0);
    }
}