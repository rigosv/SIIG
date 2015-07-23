$(document).ready(function () {
    var id = $('select[id$="_unidad"]').attr('id').split('_')[0];
    padre = $('select[id$="_unidad"]').val();
    
    $('#s2id_'+id+'_unidad').hide();
    $('#s2id_'+id+'_unidad').after("<div id='jqxTree'></div>");
    
    var tree = $('#jqxTree');
    var source = null;
    $.ajax({
        async: false,
        url: Routing.generate('get_estructura_organizativa'),
        success: function (data, status, xhr) {
            source = jQuery.parseJSON(data);
        }
    });
    
    var dataAdapter = new $.jqx.dataAdapter(source);    
    dataAdapter.dataBind();
    
    var records = dataAdapter.getRecordsHierarchy('id', 'parentid', 'items', [{ name: 'text', map: 'label'}]);
    $('#jqxTree').jqxTree({ source: records, width: '300px'});
    $("#jqxTree").jqxTree('selectItem', $("#"+padre)[0]);
    var selectedItem = $('#jqxTree').jqxTree('selectedItem');
    if (selectedItem != null) {
        var superior = null;
        $.each(source, function(i, obj) {
            if (obj.id == padre){
                superior = obj.parentid;
            }
        });
        $('#jqxTree').jqxTree('expandItem', $("#"+superior)[0]);
    }
    
    $('#jqxTree').on('select', function (event) {
        var args = event.args;
        var item = $('#jqxTree').jqxTree('getItem', args.element);
        $('select[id$="_unidad"]').val(item.id);
    });
});