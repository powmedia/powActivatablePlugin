<?php

/**
 * powActivatableRelation Doctrine behaviour
 * 
 * Adds methods to the entity model class that allow you to select active records from a related
 * table.  The related table must use the powActivatable behaviour.
 */
class powActivatableRelation extends Doctrine_Template
{
    /**
     * Default options
     */
    protected $_options = array(
        'columnName' => 'is_active',
    );
    
    /**
     * An array that caches results of queries previously executed.  Prevents multiple 
     * queries being executed for methods such as getActiveVideos(), countActiveVideos() etc.
     * 
     * The cache is only maintained for the duration of the request, like the normal 
     * Doctrine caching for queries such as $user->getVideos()
     */
    protected static $cache = array();
    
    /**
     * Magic call method used to proxy virtual methods to concrete methods defined below
     */
    public function __call($name, $args)
    {
        // getActiveXxx()
        if (preg_match('/getActive([\w]+)/', $name, $matches))
        {
            return $this->getActiveRelations($matches[1]);
        }
        
        // countActiveXxx()
        if (preg_match('/countActive([\w]+)/', $name, $matches))
        {
            return $this->countActiveRelations($matches[1]);
        }
        
        // If no methods match, throw exception
        $modelName = get_class($this->getInvoker());
        throw new Doctrine_Record_UnknownPropertyException(
            "Unknown method $modelName::$name"
        );
    }
    
    
    
    
    // MODEL METHODS
    // This will be added to the entity model class
    
    /**
     * Get a query that will search for a model's related objects, active only
     * 
     * @param  string $relationName     The related model name
     */
    public function getActiveRelationsQuery($relationAlias)
    {
        // Get the model for the class we're in currently
        $model = $this->getInvoker();
        $modelName = get_class($model);
        
        // Get relation
        if (!$model->hasRelation($relationAlias))
            throw new powActivatableException("$modelName has no relation to $relationAlias");
        else
            $relation = $model->getTable()->getRelation($relationAlias);
        
        // Make sure relationship is one to many (e.g. one user has multiple videos)
        if (get_class($relation) !== 'Doctrine_Relation_ForeignKey')
            throw new powActivatableException("getActiveXxx(), getActiveRelationsQuery() and getActiveRelations() methods are unsupported on one-to-one relationships.");
        
        // Get relationship data
        $foreignClassName = $relation->getClass();
        $foreignColName = $relation->getForeignColumnName();
        
        //Create query
        $q = Doctrine_Query::create()
            ->from($foreignClassName)
            ->where("$foreignClassName.$foreignColName = ?", $model->id);        
        $q = Doctrine::getTable($foreignClassName)->addIsActiveQuery($q);
    
        return $q;
    }
    
    /**
     * Get a model's related objects, active only
     *
     * @param string $relationName      The related model name
     *
     * @return Doctrine_Collection
     */
    public function getActiveRelations($relationName)
    {        
        //Create key to store cached results
        $objectId = $this->getInvoker()->id;
        $className = get_class($this->getInvoker());
        $cacheKey = "$className-$objectId-getActive-$relationName";
        
        //Return cached version if possible
        if (isset(self::$cache[$cacheKey]))
            return self::$cache[$cacheKey];
        
        //Otherwise query the DB
        $q = $this->getActiveRelationsQuery($relationName);
        $collection = $q->execute();
        
        //Cache the result
        self::$cache[$cacheKey] = $collection;
        
        return $collection;
    }
    
    public function countActiveRelations($relationName)
    {
        //Create key to store cached results
        $objectId = $this->getInvoker()->id;
        $className = get_class($this->getInvoker());
        $cacheKey = "$className-$objectId-countActive-$relationName";
        
        //Return cached version if possible
        if (isset(self::$cache[$cacheKey]))
        {
            return self::$cache[$cacheKey];
        }
        else //Not cached
        {
            //If getActiveXxx has been called we can just count the result
            if (isset(self::$cache["$className-$objectId-getActive-$relationName"]))
                return count(self::$cache["$className-$objectId-getActive-$relationName"]);
        }
        
        //Otherwise query the DB
        $q = $this->getActiveRelationsQuery($relationName);
        $count = $q->count();
        
        //Cache the result
        self::$cache[$cacheKey] = $count;
        
        return $count;
    }
}
