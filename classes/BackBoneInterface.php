<?php
// crap why did i do this
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BackBoneInterface
 *
 * @author DAD
 */
Interface BackBoneInterface {
    public static function serialize($model, $options = null);
    public static function deserialize($model, $data, $options = null);
}

?>
