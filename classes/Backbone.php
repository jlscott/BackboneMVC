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
     * @params
     *      $model - 
     *      $options - default null - 
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
            $result = self::handleOptionsArray($model, $inFunctionOptionsArray);
        }
        else
        {
            $result = $model->to_array();
        }
        
        return $result;
            
      
    }
    
    
    /**
     * @method - serializeCollection - handles the case when a collection of eloqent models is passed in to serialize. This
     * method loops over the values of the array makes sure they are eloquent models and then serilizes each and pushes it
     * onto the result array.
     * 
     * @param type $collection - an array of eloquent models to be serialized.
     * @param type $options - an array of options for when serializing models. Default is null and is not necessary.
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
    private static function handleOptionsArray($model, $options)
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
     * @method
     * 
     * @params
     * 
     * @return
     * 
     * @author - Jeffrey Scott
     */
    public static function deserialize($model, $data, $options = null)
    {
        return "Deserialize";
    }

}
?>





<!--// look a lot more at eloquent it should be able to help out greatly.
            // probably will give me exactly what I need with out much trouble.
            // next step is to really look at eloquent. it will do the transformations for me quite a bit probably
            // 
            //is_object
            //is_a     and use for checking if it is a eloquent model
            //.property_exists($class, $property)

            //change except to hidden to match eloquents?? or add any exceptions to the hidden attribute.
            // that would hide the implementation a bit more, but not really if they have to use an eloquent model
            // anyways. Ask Jason about it.-->
           
<!--            if(property_exists($model, "theAnswer"))
            {

                //return $model->theAnswer;
                //return "\nTrue from serialize: ";
                $result = $result.strval($model->theAnswer);
            }
            if(is_array($options))
            {
                $result = $result. "</br> Options is an array of size ". strval(count($options));
                $result = $result."</br>".strval($options[0]).", ".strval($options[1]).", ".strval($options[2]);

            }
            return $result;-->