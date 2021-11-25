define(function(require) {
    var elgg = require('elgg');
    var $ = require('jquery');
    require('jquery-ui');
    
    $( function() {
        $( "#translate_acc" ).accordion({
            collapsible: true
        });
    } ); 
});
