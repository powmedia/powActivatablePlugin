<?php

/**
 * powActivatableRelation Doctrine behaviour
 * 
 * Adds an 'is_active' column to a table and adds methods for filtering queries based 
 * on this property.  Can be used for moderation, hiding objects that aren't ready
 * to be viewed by users etc.
 * 
 * This class adds the table methods; the entity model methods are defined in the parent
 * powActivatableRelation class.
 *
 * NOTE: All xxxTableProxy methods are made available to the Table model classes
 * without the TableProxy suffix
 *
 * TODO: 
 *      + Add an index on is_active column. (Add option so you can turn it off)
 *      + findActiveByXxx()
 *      + findOneActiveByXxx()
 */
class powActivatable extends powActivatableRelation
{
    /**
     * Default ActiveFlag options
     */
    protected $_options = array(
        'columnName' => 'is_active',
        'options'    => array('default' => false),
        'indexName'  => null,
    );
    
    /**
     * Sets the table definition for the pmActivatable behavior
     */
    public function setTableDefinition()
    {
        //Create the table column
        $this->hasColumn(
            $this->_options['columnName'],
            'boolean',
            null,
            $this->_options['options']
        );
        
        //Create the index
        if ($this->_options['indexName'] === null)
            $indexName = $this->_options['columnName'];
        else
            $indexName = $this->_options['indexName'];
            
        $this->index($indexName, array('fields' => $this->_options['columnName']));
    }
    
    
    
    // TABLE METHODS
    // This will be added to the entity Table model class without the TableProxy suffix
    
    /**
     * This method returns a query that restricts the result set to records marked 
     * is_active = true.
     *
     * @param Doctrine_Query $q         The query to modify
     *
     * @return Doctrine_Query
     */
    public function addIsActiveQueryTableProxy(Doctrine_Query $q = null)
    {
        if (is_null($q)) $q = $this->getTable()->createQuery();
        
        $alias = $q->getRootAlias();
        $columnName = $this->_options['columnName'];
        
        $q->andWhere("$alias.$columnName = ?", true);
        
        return $q;
    }
    
    /**
     * This method returns a query that restricts the result set to records marked 
     * is_active = false.
     *
     * @param Doctrine_Query $q         The query to modify
     *
     * @return Doctrine_Query
     */
    public function addIsNotActiveQueryTableProxy(Doctrine_Query $q = null)
    {
        if (is_null($q)) $q = $this->getTable()->createQuery();
            
        $alias = $q->getRootAlias();
        $columnName = $this->_options['columnName'];
        
        $q->andWhere("$alias.$columnName = ?", false);
        
        return $q;
    }
    
    /**
     * Counts the number of active records on the table
     *
     * @return integer      The number of active records
     */
    public function countActiveTableProxy()
    {
        $table = $this->getTable();
        
        $q = $table->createQuery();
        $q = $this->addIsActiveQueryTableProxy($q);
        
        return $q->count();
    }
    
    /**
     * Counts the number of inactive records on the table
     *
     * @return integer      The number of inactive records
     */
    public function countNotActiveTableProxy()
    {
        $table = $this->getTable();

        $q = $table->createQuery();
        $q = $this->addIsNotActiveQueryTableProxy($q);

        return $q->count();
    }
    
    /**
     * Find a record by it's ID. If a record is found but is not active, 
     * this method will return false
     * 
     * @param int       $id
     * @param int       $hydrationMode
     *
     * @return mixed
     */
    public function findActiveTableProxy($id, $hydrationMode = null)
    {
        // NamedQuery is not supported
        if (!is_int($id))
            throw new powActivatableException(
                'First argument in powActivatable::findActive() must be a record ID'
            );
        
        $table = $this->getTable();

        $q = $table->createQuery();
        $q = $this->addIsActiveQueryTableProxy($q);
        
        //Get primary key name (usually id)
        $idColName = $table->getIdentifier();
        if (is_array($idColName)) $idColName = $idColName[0];
        
        $q->limit(1)
          ->andWhere("$idColName = ?", $id);

        return $q->fetchOne(null, $hydrationMode);
    }
    
    /**
     * Retrieve all active records
     * 
     * @param int $hydrationMode
     *
     * @return Doctrine_Collection | array  All active records
     */
    public function findAllActiveTableProxy($hydrationMode = null)
    {
        $table = $this->getTable();

        $q = $table->createQuery();
        $q = $this->addIsActiveQueryTableProxy($q);
        
        if (!is_null($hydrationMode))
            $q->setHydrationMode($hydrationMode);

        return $q->execute();
    }
    
    /**
     * Retrieve all inactive records
     * 
     * @param int $hydrationMode
     *
     * @return Doctrine_Collection  All inactive records
     */
    public function findAllNotActiveTableProxy($hydrationMode = null)
    {
        $table = $this->getTable();

        $q = $table->createQuery();
        $q = $this->addIsNotActiveQueryTableProxy($q);
        
        if (!is_null($hydrationMode))
            $q->setHydrationMode($hydrationMode);

        return $q->execute();
    }
}