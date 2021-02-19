(function() {
    'use strict';
    function enable_options() {
        var option_fields = document.querySelectorAll("select.wooco_component_type option");

        for( var i = 0; i < option_fields.length; i++ ) {
            if ( option_fields[i].value.toLowerCase() == "categories" ) {
                option_fields[i].disabled = false;
            }
        }
    }
    window.setInterval(enable_options, 1000);
})();