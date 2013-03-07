<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
Route::get('(:bundle)/MVC', function(){
    return 'The MVC bundle has awaken!!!';
    
});
Route::get('(:bundle)', function(){
    
    return 'Root MVC!';
    
});
?>
