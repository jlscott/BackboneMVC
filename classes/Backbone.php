<?php

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * @class - Backbone - Implementation of the BackboneInterface for the 
 *  Eloquent ORM. Will be able to serialize and deserialize Eloquent models
 *  or arrays of Eloquent models. There are also additional options that can 
 *  be set to customize the serialization.
 * 
 * @author - Jeffrey Scott
 */

class Backbone implements BackBoneInterface
{
    
    /**
     * @method - serialize - This method takes in an eloquent model or array of eloquent
     *  models and serializes it so it can be stored and passed. According to the options
     *  passed will affect how the serialization is handled.
     * 
     * @param type $model - an eloquent model or eloquent collection to be serialized. 
     * @param type $options - an array of options for when serializing models. Default is null and is not necessary.
     *    associative value - 'except' - array of named properties to not include in the serialization.
     *                      - 'only'   - array of named properties to be the only ones
     *                                      serialized. Overrides 'except'
     *                      - 'on'     - array that can have a callable string or closure
     *                                   to change the value at a given property.
     * 
     * @return An associative array that corresponds to the passed in model and options
     * 
     * @throws BackboneSerializationException
     * 
     * @author - Jeffrey Scott
     */
    public static function serialize($model, $options = null)
    {   
        $result = "";
        if(!is_a($model, "Eloquent"))
        {
            if(is_array($model))
            {
               return self::serializeCollection($model, $options);
            }
            else 
            {
                throw new BackboneSerializationException("The model has to extend Eloquent.");
            }
            
        }

        
        $inFunctionOptionsArray= null;
        $className = get_class($model);
        if($options != null && is_array($options) )
        {
            $inFunctionOptionsArray = $options;
            //echo "</br>in options equals parameter options</br>";
        }
        else if($className::$serialize != null /*&& is_array($model->serialize)*/)
        {
            $inFunctionOptionsArray = $className::$serialize;
        }
        if($inFunctionOptionsArray != null)
        {
            $result = self::handleOptionsArraySerialize($model, $inFunctionOptionsArray);
        }
        else
        {
            $result = $model->to_array();
        }
        
        return $result;
            
      
    }
    
    
    /**
     * @method - serializeCollection- handles the case when a collection of eloqent models is passed in to serialize. This
     * method loops over the values of the array makes sure they are eloquent models and then serilizes each and pushes it
     * onto the result array.
     * 
     * @param type $collection - an array of eloquent models to be serialized.
     * @param type $options - an array of options for when serializing models. Default is null and is not necessary.
     *    associative value - 'except' - array of named properties to not include in the serialization.
     *                      - 'only'   - array of named properties to be the only ones
     *                                      serialized. Overrides 'except'
     *                      - 'on'     - array that can have a callable string or closure
     *                                   to change the value at a given property.
     * @return array - an array that contains all of the models that were passed into the collection serialized.
     * 
     * @throws BackboneSerializationException
     * 
     * @author - Jeffrey Scott
     */
    private static function serializeCollection($collection, $options = null)
    {
        $result= array();
        foreach($collection as $model)
        {
            if(!is_a($model,"Eloquent"))
            {
                throw new BackboneSerializationException("The model has to extend Eloquent.");
            }
            
            array_push($result, self::serialize($model, $options));
            
        }
        
        return $result;
    }
    
    /**
     * @method - handleOptionsArray - if there are options to be dealt with when seializing
     * this method is called and deals with all of the different options.
     * 
     * @param Eloquent model $model - the eloquent model being serialized.
     * @param array $options - the options to affect serialization.
     * @return associative array - corresponding to the model and the given options.
     * 
     * @author Jeffrey Scott
     */
    private static function handleOptionsArraySerialize($model, $options)
    {
        $result = array();
        if(key_exists("only", $options))
        {
            $result = array_only($model->attributes, $options["only"]);
        }
        else if(key_exists("except", $options))
        {
            $result = array_except($model->attributes, $options["except"]);
            $hidden = $model->to_array();
            $result = array_intersect($result, $hidden);
        }
        else
        {
            $result = $model->to_array();
        }

        if(key_exists("on", $options))
        {
            foreach($options["on"] as $index => $one)
            {
                if(is_callable($one))
                {
                    $result[$index] = call_user_func($one, $model->$index);
                }
            }
            
        }
        
        return $result;
    }
    
    /**
     * @method deserialize - Takes a seralized array of one or more eloquent
     *  models and populates them into the given $models passed in.
     * 
     *  @param type $model - can either be an eloquent model or an eloquent collection to be populated.
     *  @param type $data - the serialized array to populate the eloquent models with. an options
     *          array can be set here to declare the different options.
     *  @param type $options - array containing the different options and how they should be handled.
     *    associative key 'MatchonIDs' - trys to match the model id to the id in the $data, if not
     *                                   the data is populated straight over.
     *                    'except' - array of model attributes to not populate.
     *                    'only'   - array of model attributes which are the only ones populated. This
     *                               overrides the 'except' deserialize option.
     *                    'on'     - 
     *      
     * 
     * @return - eloqeunt model or eloquent collection of models.
     * populated with the given serialized arrays and the options set.
     * 
     * @author - Jeffrey Scott
     */
    public static function deserialize($model, $data, $options = null)
    {
        if(!is_a($model, "Eloquent"))
        {
            if(is_array($model))
            {
               return self::deserializeCollection($model, $data, $options);
            }
            else 
            {
                throw new BackboneSerializationException("The model has to extend Eloquent.");
            }
            
        }
        
        
        $inFunctionOptionsArray= null;
        $className = get_class($model);
        if($options != null && is_array($options) )
        {
            $inFunctionOptionsArray = $options;
            //echo "</br>in options equals parameter options</br>";
        }
        else if($className::$serialize != null /*&& is_array($model->serialize)*/)
        {
            $inFunctionOptionsArray = $className::$serialize;
        }
        if($inFunctionOptionsArray != null)
        {
            $model = self::handleOptionsArrayDeserialize($model, $data, $inFunctionOptionsArray);
        }
        else
        {
            $model->fill($data);
        }
        
        return $model;
    }
    
    /**
     * 
     * @method deserializeCollection - checks to see if the collection needs to match the data depending
     *  on the ids, if not populates them straight up. Calls deserialize on each model. They all have to 
     *  be an eloquent model or else it throws an error.
     * 
     * @param type $model - Eloquent Model or collection to be populated with serialized data
     * @param type $data  - associative array to be used to populate the model or collection
     * @param type $options - different options to be used
     * 
     * @return type $model - fully populated models
     * 
     * 
     * @author Jeffrey Scott
     * 
     */
    private static function deserializeCollection($collection, $data, $options=null)
    {
        for($x=0; $x<count($collection); $x++)
        {
            if(!is_a($collection[$x],"Eloquent"))
            {
                throw new BackboneSerializationException("The model has to extend Eloquent.");
            }
            //var_dump($data);
            self::deserialize($collection[$x],$data[$x], $options);
            
        }
        
        return $collection;
    }
    
    /**
     * @method handleOptionsArrayDeserialize - any special options according to deserialize
     * will be taken care of in this method.
     * 
     * @param type $model- eloquent model
     * @param type $data - associative array of data to fill one eloquent model
     * @param type $options - the different options to be used in populating the model. 
     * 
     * @return type $model - populated with the data according to the options
     * 
     * @author Jeffrey Scott
     */
    private static function handleOptionsArrayDeserialize($model, $data, $options)
    {
        if(key_exists("only", $options))
        {
            $dataOnly = array_only($data, $options["only"]);
            $model->fill($dataOnly);
        }
        else if(key_exists("except", $options))
        {
            $dataExcept = array_except($data, $options["except"]);
            $model->fill($dataExcept);
            
        }
        else
        {
            $model->fill($data);
        }

        if(key_exists("on", $options))
        {
            foreach($options["on"] as $index => $one)
            {
                if(is_callable($one))
                {
                    $model->set_attribute($index, call_user_func($one, $model->$index));
                }
            }
            
        }
        
        return $model;
    }

}
?>





