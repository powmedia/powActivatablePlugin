<?php

require_once dirname(__FILE__).'/../bootstrap/bootstrap.php';

class powActivatableTest extends PHPUnit_Framework_TestCase
{
    protected   $customOptions,
                $userTable,
                $videoTable,
                $activeVideoNamesByUserId;
    
    public function setUp()
    {
        $this->userTable = Doctrine::getTable('User');
        $this->videoTable = Doctrine::getTable('Video');
        
        //Simulate plugin options the user might change e.g. in the YAML schema
        $this->customOptions = array(
            'columnName'    => 'is_visible',
            'options'       => array('default' => true),
            'indexName'     => 'my_index',
        );
        
        //This sets the user ID as index, and the expected active videos for each
        $this->activeVideoNamesByUserId = array(
            1 => array('video4', 'video6', 'video7'),
            2 => array(),
            3 => array('video2'),
            5 => array(),
        );
    }
    
    
    // __call()
    
    /**
     * Test that calling something like getActiveVideos() proxies to
     * getActiveRelations('Video')
     */
    public function testCall_getActiveXxx_proxiesToGetActiveRelations()
    {
        $user = $this->userTable->find(1);
        $videos = $user->getActiveVideos();
        
        $this->assertEquals('Doctrine_Collection', get_class($videos));
    }
    
    public function testCall_countActiveXxx_proxiesToCountActiveRelations()
    {
        $user = $this->userTable->find(1);
        
        $this->assertEquals(3, $user->countActiveVideos());
    }
    
    public function testCall_unknownMethod_throwsException()
    {
        $this->setExpectedException('Doctrine_Record_UnknownPropertyException');
        
        $user = $this->userTable->find(1);
        $user->gnsyrbgyrsy();
    }
    
    
    // setTableDefinition()
    
    public function testSetTableDefinition_defaultOptions()
    {   
        $mock = $this->getMock('powActivatable', array('hasColumn', 'index'));
        
        $mock->expects($this->once())
            ->method('hasColumn')
            ->with(
                $this->equalTo('is_active'),
                $this->equalTo('boolean'),
                $this->equalTo(null),
                $this->equalTo(array('default' => false))
            );
        
        $mock->expects($this->once())
            ->method('index')
            ->with(
                $this->equalTo('is_active'),
                $this->equalTo(array('fields' => 'is_active'))
            );
        
        $mock->setTableDefinition();
    }

    public function testSetTableDefinition_customOptions_columnName()
    {
        $options = array(
            'columnName' => 'is_visible',
        );
        
        $mock = $this->getMock('powActivatable', array('hasColumn', 'index'), array($options));

        $mock->expects($this->once())
            ->method('hasColumn')
            ->with(
                $this->equalTo('is_visible'),
                $this->equalTo('boolean'),
                $this->equalTo(null),
                $this->equalTo(array('default' => false))
            );

        $mock->expects($this->once())
            ->method('index')
            ->with(
                $this->equalTo('is_visible'),
                $this->equalTo(array('fields' => 'is_visible'))
            );

        $mock->setTableDefinition();
    }

    public function testSetTableDefinition_customOptions_default()
    {
        $options = array(
            'options'    => array('default' => true),
        );

        $mock = $this->getMock('powActivatable', array('hasColumn', 'index'), array($options));

        $mock->expects($this->once())
            ->method('hasColumn')
            ->with(
                $this->equalTo('is_active'),
                $this->equalTo('boolean'),
                $this->equalTo(null),
                $this->equalTo(array('default' => true))
            );

        $mock->expects($this->once())
            ->method('index')
            ->with(
                $this->equalTo('is_active'),
                $this->equalTo(array('fields' => 'is_active'))
            );

        $mock->setTableDefinition();
    }
    
    public function testSetTableDefinition_customOptions_indexName()
    {
        $options = array(
            'indexName' => 'my_index',
        );

        $mock = $this->getMock('powActivatable', array('hasColumn', 'index'), array($options));

        $mock->expects($this->once())
            ->method('hasColumn')
            ->with(
                $this->equalTo('is_active'),
                $this->equalTo('boolean'),
                $this->equalTo(null),
                $this->equalTo(array('default' => false))
            );

        $mock->expects($this->once())
            ->method('index')
            ->with(
                $this->equalTo('my_index'),
                $this->equalTo(array('fields' => 'is_active'))
            );

        $mock->setTableDefinition();
    }

    public function testSetTableDefinition_customOptions_all()
    {
        $options = array(
            'columnName'    => 'is_visible',
            'options'       => array('default' => true),
            'indexName'     => 'my_index',
        );
        
        $mock = $this->getMock('powActivatable', array('hasColumn', 'index'), array($options));

        $mock->expects($this->once())
            ->method('hasColumn')
            ->with(
                $this->equalTo('is_visible'),
                $this->equalTo('boolean'),
                $this->equalTo(null),
                $this->equalTo(array('default' => true))
            );

        $mock->expects($this->once())
            ->method('index')
            ->with(
                $this->equalTo('my_index'),
                $this->equalTo(array('fields' => 'is_visible'))
            );

        $mock->setTableDefinition();
    }


    // addIsActiveQuery()
    
    public function testAddIsActiveQuery_withNewQueryArgument()
    {
        $q = $this->userTable->createQuery();
        $q = $this->userTable->addIsActiveQuery($q);
        $this->assertEquals(5, $q->count());
                
        $q = $this->videoTable->createQuery();
        $q = $this->videoTable->addIsActiveQuery($q);
        $this->assertEquals(4, $q->count());
    }
    
    public function testAddIsActiveQuery_withModifiedQueryArgument()
    {
        $q = $this->userTable->createQuery()
            ->where('id > ?', 3);
        $q = $this->userTable->addIsActiveQuery($q);
        $this->assertEquals(3, $q->count());
                
        $q = $this->videoTable->createQuery()
            ->where('id < ?', 5);
        $q = $this->videoTable->addIsActiveQuery($q);
        $this->assertEquals(2, $q->count());
    }
    
    public function testAddIsActiveQuery_withoutQueryArgument()
    {
        $q = $this->userTable->addIsActiveQuery();
        $this->assertEquals('Doctrine_Query', get_class($q), 'Returns Doctrine_Query');
        $this->assertEquals(5, $q->count(), 'Works');
        
        $q = $this->videoTable->addIsActiveQuery();
        $this->assertEquals('Doctrine_Query', get_class($q), 'Returns Doctrine_Query');
        $this->assertEquals(4, $q->count(), 'Works');
    }

    
    // addIsNotActiveQuery()
    
    public function testAddIsNotActiveQuery_withNewQueryArgument()
    {
        $q = $this->userTable->createQuery();
        $q = $this->userTable->addIsNotActiveQuery($q);
        $this->assertEquals(2, $q->count());
                
        $q = $this->videoTable->createQuery();
        $q = $this->videoTable->addIsNotActiveQuery($q);
        $this->assertEquals(3, $q->count());
    }
    
    public function testAddIsNotActiveQuery_withModifiedQueryArgument()
    {
        $q = $this->userTable->createQuery()
            ->where('id > ?', 3);
        $q = $this->userTable->addIsNotActiveQuery($q);
        $this->assertEquals(1, $q->count());
                
        $q = $this->videoTable->createQuery()
            ->where('id < ?', 5);
        $q = $this->videoTable->addIsNotActiveQuery($q);
        $this->assertEquals(2, $q->count());
    }
    
    public function testAddIsNotActiveQuery_withoutQueryArgument()
    {
        $q = $this->userTable->addIsNotActiveQuery();
        $this->assertEquals('Doctrine_Query', get_class($q), 'Returns Doctrine_Query');
        $this->assertEquals(2, $q->count(), 'Works');
        
        $q = $this->videoTable->addIsNotActiveQuery();
        $this->assertEquals('Doctrine_Query', get_class($q), 'Returns Doctrine_Query');
        $this->assertEquals(3, $q->count(), 'Works');
    }
    
    
    // countActive()
    
    public function testCountActive()
    {
        $this->assertEquals(
            5, $this->userTable->countActive()
        );
        
        $this->assertEquals(
            4, $this->videoTable->countActive()
        );
    }
    
    
    // countNotActive()
    
    public function testCountNotActive()
    {    
        $this->assertEquals(
            2, $this->userTable->countNotActive()
        );
        
        $this->assertEquals(
            3, $this->videoTable->countNotActive()
        );
    }
    
    
    // findActive()
    
    public function testFindActive_foundActiveRecord_returnsModel()
    {
        //Default table
        $result = $this->userTable->find(2);
        $this->assertEquals('User', get_class($result));
        $this->assertEquals('user2', $result->name);
        
        //Custom table
        $result = $this->videoTable->find(4);
        $this->assertEquals('Video', get_class($result));
        $this->assertEquals('video4', $result->name);
    }
    
    public function testFindActive_firstArgumentIsString_throws()
    {
        $this->setExpectedException('powActivatableException');
        
        $this->userTable->findActive('string');
    }
    
    public function testFindActive_foundInactiveRecord_returnsFalse()
    {
        $this->assertFalse($this->userTable->findActive(1));
        
        $this->assertFalse($this->videoTable->findActive(3));
    }
    
    public function testFindActive_recordNotFound_returnsFalse()
    {
        $this->assertFalse($this->userTable->findActive(0));
    }
    
    public function testFindActive_canChangeHydrationMode()
    {
        $result = $this->userTable->findActive(2, Doctrine_Core::HYDRATE_ARRAY);
        
        $this->assertTrue(is_array($result));
    }
    
    
    // findAllActive()
    
    public function testFindAllActive()
    {
        $result = $this->userTable->findAllActive();
        $this->assertEquals('Doctrine_Collection', get_class($result));
        $this->assertEquals(5, count($result));
        
        $result = $this->videoTable->findAllActive();
        $this->assertEquals('Doctrine_Collection', get_class($result));
        $this->assertEquals(4, count($result));
    }
    
    public function testFindAllActive_canChangeHydrationMode()
    {
        $result = $this->userTable->findAllActive(Doctrine_Core::HYDRATE_ARRAY);
        $this->assertTrue(is_array($result[0]));
    }
    
    
    // findAllNotActive()
    
    public function testFindAllNotActive()
    {
        $result = $this->userTable->findAllNotActive();
        $this->assertEquals('Doctrine_Collection', get_class($result));
        $this->assertEquals(2, count($result));
        
        $result = $this->videoTable->findAllNotActive();
        $this->assertEquals('Doctrine_Collection', get_class($result));
        $this->assertEquals(3, count($result));
    }
    
    public function testFindAllNotActive_canChangeHydrationMode()
    {
        $result = $this->userTable->findAllNotActive(Doctrine_Core::HYDRATE_ARRAY);
        $this->assertTrue(is_array($result[0]));
    }
    
    
    // findByXxxActive
    
    public function testFindByXxxActive()
    {
        
    }
    
    
    // findOneByXxxActive
    
    public function testFindOneByXXXNotActive()
    {
        
    }
    
    
    // getRelatedActiveQuery()
    
    public function testGetActiveRelationsQuery()
    {
        // Run the tests for each user
        foreach ($this->activeVideoNamesByUserId as $userId => $expectedVideoNames)
        {
            //Get videos related to user
            $user = $this->userTable->find($userId);
            $q = $user->getActiveRelationsQuery('Videos');
            $params = $q->getParams();

            //Test query looks right
            $this->assertEquals(
                ' FROM Video WHERE Video.user_id = ? AND Video.is_visible = ?',
                $q->getDql()
            );
            $this->assertEquals(array($userId, true), $params['where']);

            //Query returns correctly
            $videos = $q->execute();
            $this->assertEquals(
                $expectedVideoNames, 
                $this->getPropertyFromCollection('name', $videos)
            );
        }
    }
    
    public function testGetActiveRelationsQuery_invalidRelation_throws()
    {
        $this->setExpectedException('powActivatableException');
        
        $user = $this->userTable->find(1);
        $q = $user->getActiveRelationsQuery('Something');
    }

    public function testGetActiveRelationsQuery_oneToOne_throws()
    {
        $this->setExpectedException('powActivatableException');

        $video = $this->videoTable->find(2);
        $q = $video->getActiveRelationsQuery('User');
    }
    
    
    // getActiveRelations()
    
    public function testGetActiveRelations()
    {
        foreach ($this->activeVideoNamesByUserId as $userId => $expectedVideoNames)
        {
            $user = $this->userTable->find($userId);
            $videos = $user->getActiveRelations('Videos');

            $this->assertEquals(
                $expectedVideoNames, 
                $this->getPropertyFromCollection('name', $videos)
            );
        }
    }


    // countActiveRelations()

    public function testCountActiveRelations()
    {
        foreach ($this->activeVideoNamesByUserId as $userId => $expectedVideoNames)
        {
            $user = $this->userTable->find($userId);
            $numVideos = $user->countActiveRelations('Videos');

            $this->assertEquals(
                count($expectedVideoNames), 
                $numVideos
            );
        }
    }
    
    
    
    
    // PROTECTED HELPER METHODS
    
    /**
     * Extracts a property from each object and maps it to an array.
     * Useful for testing that the results come back are as expected, in order etc.
     *
     * @param string                The property name to get
     * @param Doctrine_Collection   The collection of objects
     *
     * @return array                An array of strings (names)
     */
    protected function getPropertyFromCollection($property, Doctrine_Collection $collection)
    {
        $properties = array();
        
        foreach ($collection as $obj)
            $properties[] = $obj->$property;
        
        return $properties;
    }
}
