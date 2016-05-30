$(document).ready(function() {
    $('i').popover({html: true});
    $('i').popover('show');
    
    // Para arreglar la presentación de los checkbox
    var checkbox_label, checkbox_widget;
    $('DIV.sonata-ba-field:first-child .checkbox').each(function(i, nodo){        
        checkbox_widget = $(nodo).find('div');
        checkbox_label = $(nodo).find('label');
        
        $(checkbox_label).addClass('col-sm-3 control-label');
        
        $(nodo).before(checkbox_widget);
        $(nodo).parent().before(checkbox_label);
        $(nodo).remove();
    });
});