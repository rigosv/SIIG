$(document).ready(function () {
    var idCriterio;
    var agrupar = (formaEvaluacion === 'rango_colores');
    

    // Grid de criterios
    $("#gridCriterios").jqGrid({
        url: Routing.generate('calidad_planmejora_get_criterios', {id: idPlan}),
        datatype: "json",
        editurl: Routing.generate('calidad_planmejora_set_criterio'),
        colModel: [
            {label: 'ID', name: 'id', key: true, width: 50, hidden: true},
            {label: 'Indicador', name: 'indicador', width: 50, hidden: true},
            {label: 'Descripción', name: 'descripcion', width: 200, editable: true, editoptions: { readonly: "readonly" }},
            {label: 'Brecha', name: 'brecha', align: "right", sorttype: "number", width: 30, editable:true, editoptions: { readonly: "readonly" }},
            {label: 'Causa brecha', name: 'causaBrecha', width: 150, editable: true, edittype: 'textarea', editrules: {required: true}},
            {label: 'Oportunidad mejora', name: 'oportunidadMejora', width: 150, editable: true, edittype: 'textarea', editrules: {required: true}},
            {label: 'Factores de mejoramiento', name: 'factoresMejoramiento', width: 150, editable: true, edittype: 'textarea', editrules: {required: true}},
            {label: 'Periodo de intervención', name: 'tipoIntervencion', width: 60, editable: true, edittype: 'select', formatter:'select',
                editoptions:{value:tiposIntervencion}, 
                editrules: {required: true}
            },
            {label: 'Prioridad', name: 'prioridad', width: 50, editable: true, edittype: 'select', formatter:'select',
                cellattr: function(rowId, tv, rawObject, cm, rdata) {
                    return 'class="prioridad_'+rawObject.prioridad+' "';
                },
                editoptions:{value: prioridades, disabled: "disabled"},
                editrules: {required: true}
            }
        ],
        grouping: agrupar, 
        groupingView : { 
           groupField : ['indicador'],
           groupDataSorted : true,
           groupColumnShow: [false]
        }, 
        autowidth: true,
        height: 150,
        rowNum: 100,
        viewrecords: true,
        loadonce: true,
        sortname: 'descripcion',
        caption: 'Criterios',
        onSelectRow: function (row, selected) {
            idCriterio = row;
            if (idCriterio != null) {
                var descripcionCriterio = jQuery("#gridCriterios").jqGrid ('getCell', idCriterio, 'descripcion');
                jQuery("#gridActividades").jqGrid('setGridParam', {url: Routing.generate('calidad_planmejora_get_actividades', {criterio: idCriterio}), datatype: 'json'});
                jQuery("#gridActividades").jqGrid('setGridParam', {editurl: Routing.generate('calidad_planmejora_set_actividad', {criterio: idCriterio}), datatype: 'json'});
                jQuery("#gridActividades").jqGrid('setCaption', 'Actividades de criterio :: ' + descripcionCriterio);
                jQuery("#gridActividades").trigger("reloadGrid");
            }
        },
        ondblClickRow: function(rowid) {
            jQuery(this).jqGrid('editGridRow', rowid,
                            {editCaption: "Editar criterio", recreateForm: true, 
                                checkOnUpdate: false, checkOnSubmit: false, closeAfterEdit: true,
                                beforeShowForm: function(form) {
                                    addPrioridadClass($("#prioridad", form));
                                },
                                afterclickPgButtons : function (whichbutton, form, rowid) {
                                    addPrioridadClass($("#prioridad", form));
                                },
                                afterSubmit : function( data, postdata) {
                                    return actualizar(data, postdata, 'edit', 'gridCriterios');
                                }
                            });
        },
        onSortCol: clearSelection,
        onPaging: clearSelection,
        pager: "#pagerGridCriterios"
    });

    // grid de actividades
    $("#gridActividades").jqGrid({
        url: Routing.generate('calidad_planmejora_get_actividades', {criterio: 0}),
        datatype: "json",
        editurl: Routing.generate('calidad_planmejora_set_actividad', {criterio: idCriterio}),
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
        ondblClickRow: function(rowid) {
            jQuery(this).jqGrid('editGridRow', rowid,
                            {editCaption: "Editar actividad", recreateForm: true, 
                                checkOnUpdate: false, checkOnSubmit: false, closeAfterEdit: true,
                                afterSubmit : function( data, postdata) {
                                    return actualizar(data, postdata, 'edit', 'gridActividades');
                                }
                            });
        },
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
                var mensaje = null;
                if(idCriterio === undefined) {
                    mensaje = 'Para agregar una actividad, debe seleccionar un criterio' ;
                }
                if ($('#gridActividades').getGridParam("reccount") == 5){
                    mensaje = 'No puede agregar más de 5 actividades' ;
                }
                if (mensaje !== null){
                    $.jgrid.hideModal("#editmod" + idSelector, {gbox: "#gbox_" + idSelector});
                    $.notify({
                        message: mensaje
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
            editCaption: "Editar criterio",
            recreateForm: true,
            checkOnUpdate: false,
            checkOnSubmit: false,
            closeAfterEdit: true,
            afterSubmit : function( data, postdata) {
                return actualizar(data, postdata, 'edit', 'gridCriterios');
            },
            beforeShowForm: function(form) {
                addPrioridadClass($("#prioridad", form));
            },
            afterclickPgButtons : function (whichbutton, form, rowid) {
                addPrioridadClass($("#prioridad", form));
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
            var idNewRow = response.id; 
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

function addPrioridadClass(obj){
    $(obj).removeClass('prioridad_1');
    $(obj).removeClass('prioridad_2');
    $(obj).removeClass('prioridad_3');
    $(obj).addClass('prioridad_'+$(obj).val());
}
