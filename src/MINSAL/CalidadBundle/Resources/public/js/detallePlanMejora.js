$(document).ready(function () {
    var rowid;
    // Grid de criterios
    $("#gridCriterios").jqGrid({
        url: Routing.generate('calidad_planmejora_get_criterios', {id: idPlan}),
        datatype: "json",
        editurl: Routing.generate('calidad_planmejora_set_criterio', {id: idPlan}),
        colModel: [
            {label: 'ID', name: 'id', key: true, width: 50, hidden: true},
            {label: 'Descripción', name: 'descripcion', width: 100, editable: true, editoptions: { readonly: "readonly" }},
            {label: 'Brecha', name: 'brecha', align: "right", sorttype: "number", width: 30, editable:true, editoptions: { readonly: "readonly" }},
            {label: 'Causa brecha', name: 'causaBrecha', width: 150, editable: true, edittype: 'textarea', editrules: {required: true}},
            {label: 'Oportunidad mejora', name: 'oportunidadMejora', width: 150, editable: true, edittype: 'textarea', editrules: {required: true}},
            {label: 'Factores de mejoramiento', name: 'factoresMejoramiento', width: 150, editable: true, edittype: 'textarea', editrules: {required: true}},
            {label: 'Tipo intervención', name: 'tipoIntervencion', width: 60, editable: true, edittype: 'select', formatter:'select',
                editoptions:{value:tiposIntervencion}, 
                editrules: {required: true}
            },
            {label: 'Prioridad', name: 'prioridad', width: 50, editable: true, edittype: 'select', formatter:'select',
                editoptions:{value: prioridades},
                editrules: {required: true}
            }
        ],
        autowidth: true,
        height: 150,
        rowNum: 100,
        viewrecords: true,
        loadonce: true,
        caption: 'Criterios',
        onSelectRow: function (row, selected) {
            rowid = row;
            if (rowid != null) {
                var descripcionCriterio = jQuery("#gridCriterios").jqGrid ('getCell', rowid, 'descripcion');
                jQuery("#gridActividades").jqGrid('setGridParam', {url: Routing.generate('calidad_planmejora_get_actividades', {criterio: rowid}), datatype: 'json'}); // the last setting is for demo only
                jQuery("#gridActividades").jqGrid('setCaption', 'Actividades de criterio :: ' + descripcionCriterio);
                jQuery("#gridActividades").trigger("reloadGrid");
            }
        }, // use the onSelectRow that is triggered on row click to show a details grid
        onSortCol: clearSelection,
        onPaging: clearSelection,
        pager: "#pagerGridCriterios"
    });

    // grid de actividades
    $("#gridActividades").jqGrid({
        url: Routing.generate('calidad_planmejora_get_actividades', {criterio: 0}),
        datatype: "json",
        editurl: Routing.generate('calidad_planmejora_set_actividad', {criterio: rowid}),
        colModel: [
            {label: 'ID', name: 'id', key: true, width: 50, hidden: true},
            {label: 'Nombre de actividad', name: 'nombre', width: 100, editable: true, edittype: 'textarea', editrules: {required: true}},
            {label: 'Fecha inicio', name: 'fechaInicio', width: 30, editable: true, editrules: {required: true},
                edittype:"text",
                editoptions: {
                    dataInit: function (element) {
                        $(element).datepicker({
                            id: 'fechaInicio_datePicker',
                            datefmt: 'd/m/yy',
                            showOn: 'focus'
                        });
                    }
                }
            },
            {label: 'Fecha fin', name: 'fechaFinalizacion', width: 30, editable: true, editrules: {required: true},
                edittype:"text",
                editoptions: {
                    dataInit: function (element) {
                        $(element).datepicker({
                            id: 'fechaFin_datePicker',
                            datefmt: 'd/m/yy',
                            showOn: 'focus'
                        });
                    }
                }
            },
            {label: 'Medio de verificación', name: 'medioVerificacion', width: 100, editable: true, edittype: 'textarea', editrules: {required: true}},
            {label: 'Responsable', name: 'responsable', width: 75, editable: true, edittype: 'textarea', editrules: {required: true}},
            {label: '% de avance', name: 'porcentajeAvance', width: 20, editable: true, editrules: {required: true, integer: true, minValue:0, maxValue:100}}
        ],
        autowidth: true,
        loadonce: true,
        height: '100',
        viewrecords: true,
        caption: 'Actividades :: ',
        pager: "#pagerGridActividades"
    });

    $('#gridActividades').navGrid('#pagerGridActividades',
        // the buttons to appear on the toolbar of the grid
        {edit: true, add: true, del: true, search: false, refresh: false, view: false, position: "left", cloneToTop: false},
        
        // options for the Edit Dialog
        {
            editCaption: "Editar actividad",
            recreateForm: true,
            checkOnUpdate: false,
            checkOnSubmit: false,
            closeAfterEdit: true,
            errorTextFormat: function (data) {
                return 'Error: ' + data.responseText
            },
            afterSubmit : function( data, postdata) {
                return actualizar(data, postdata, 'edit', 'gridActividades');
            },
            beforeShowForm: function(form) {
                var idSelector = $.jgrid.jqID(this.p.id); 
                centrarfrm(idSelector);
            }
        },
        // options for the Add Dialog
        {
            closeAfterAdd: true,
            recreateForm: true,
            afterSubmit : function( data, postdata) {
                return actualizar(data, postdata, 'add', 'gridActividades');
            },
            afterShowForm: function () {
                var idSelector = $.jgrid.jqID(this.p.id);
                if(rowid == undefined) {
                    $.jgrid.hideModal("#editmod" + idSelector, {gbox: "#gbox_" + idSelector});
                    $.notify({
                        message: 'Seleccione un criterio' 
                    },{
                        animate: {
                            enter: 'animated jello'
                        },
                        placement: {
                                from: "bottom",
                                align: "center"
                        },
                        type: 'warning'
                    });
                }
            },
            beforeShowForm: function(form) {
                centrarfrm($.jgrid.jqID(this.p.id));
            },
            errorTextFormat: function (data) {
                return 'Error: ' + data.responseText;
            }
        },
        // options for the Delete Dailog
        {
            errorTextFormat: function (data) {
                return 'Error: ' + data.responseText
        }
    });
    
    $('#gridCriterios').navGrid('#pagerGridCriterios',
        // the buttons to appear on the toolbar of the grid
        {edit: true, add: false, del: false, search: false, refresh: false, view: false, position: "left", cloneToTop: false},
        
        // options for the Edit Dialog
        {
            editCaption: "Editar actividad",
            recreateForm: true,
            checkOnUpdate: false,
            checkOnSubmit: false,
            closeAfterEdit: true,
            afterSubmit : function( data, postdata) {
                return actualizar(data, postdata, 'edit', 'gridCriterios');
            },
            errorTextFormat: function (data) {
                return 'Error: ' + data.responseText
            }
        }
    );
    
    
    
    
});


function actualizar(data, postdata, oper, gridID){
        var response = $.parseJSON(data.responseText);
        if (response.hasOwnProperty("error")) {
            if(response.error.length) {
                return [false,response.error ];
            }
        }
        if (oper === 'edit'){
            $('#'+gridID).jqGrid("setRowData", postdata.id, postdata);
        } else if (oper === 'add'){
            var idNewRow = data.id; 
            $('#'+gridID).jqGrid("addRowData",idNewRow , postdata, "first");
        } 
        return [true,"",""];
}

function centrarfrm(idSelector){
    // "editmodlist"
    var dlgDiv = $("#editmod"+idSelector);
    var parentDiv = dlgDiv.parent();
    var dlgWidth = dlgDiv.width();
    var parentWidth = parentDiv.width();
    var dlgHeight = dlgDiv.height();
    var parentHeight = parentDiv.height();
    dlgDiv[0].style.top = Math.round((parentHeight+dlgHeight)/2) + "px";
    dlgDiv[0].style.left = Math.round((parentWidth-dlgWidth)/2) + "px";
}
function clearSelection() {
    jQuery("#gridActividades").jqGrid('setGridParam', {url: Routing.generate('calidad_planmejora_get_actividades', {criterio: 0}), datatype: 'json'}); // the last setting is for demo purpose only
    jQuery("#gridActividades").jqGrid('setCaption', 'Actividades :: ');
    jQuery("#gridActividades").trigger("reloadGrid");

}
