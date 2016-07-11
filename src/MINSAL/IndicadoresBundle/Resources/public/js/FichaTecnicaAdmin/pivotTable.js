var idIndicadorActivo;
google.load("visualization", "1", {packages: ["corechart", "charteditor"]});

$(document).ready(function() {
    var datos_ = '';
    var configuracion = '';
    var configuracion_guardada = '';
    var tipoElemento = '';
    var identificadorElemento = '';
    $('#myTab a').click(function(e) {
        e.preventDefault();
        $(this).tab('show');
    });
    function ajax_states() {
        $(document).bind("ajaxStart.mine", function() {
            $('#div_carga').show();
        });
        $(document).bind("ajaxStop.mine", function() {
            $('#div_carga').hide();
        });
    }
    ajax_states();
    $('#export').click(function(){
       var t = $('.pvtTable');
        tableToExcel(t[0],'indicador', $('#marco-sala').attr('data-content').trim()+'.xls');
    });
    $('#export_grp').click(function() {        
        $('#myModalLabel2').html(trans.guardar_imagen);
        $('#myModal2').modal('show');
    });
    
    $('#guardarConf').click(function (){
        configuracion_guardada = configuracion;
        var conf = JSON.stringify(configuracion_guardada, undefined, 2);
        $.post(Routing.generate('pivotable_guardar_estado', 
                {tipoElemento: tipoElemento, id:identificadorElemento, configuracion: conf}), 
            function(datos) {
            
        }, 'json');
    });
    
    $('#cargarConf').click(function (){
        
        var renderers = $.extend($.pivotUtilities.renderers,
            $.pivotUtilities.gchart_renderers);
        if (configuracion_guardada == ''){
           $.post(Routing.generate('pivotable_obtener_estado', 
                {tipoElemento: tipoElemento, id:identificadorElemento}), 
            function(conf) {
                if (conf === ''){
                    alert('No existe una configuración guardada');
                }else {
                    configuracion_guardada = conf;
                    configuracion_guardada["renderers"] = renderers;
                    configuracion_guardada["onRefresh"] = onChangeTable;
                    $("#output").pivotUI(datos_, configuracion_guardada , true, 'es');
                }
        }, 'json');
        } else{
            configuracion_guardada["renderers"] = renderers;
            configuracion_guardada["onRefresh"] = onChangeTable;
            $("#output").pivotUI(datos_, configuracion_guardada , true, 'es');
        }
        
    });
    
    $('#ver_ficha').click(function() {
        if (idIndicadorActivo != null){ 
            $.get(Routing.generate('get_indicador_ficha',
                    {id: idIndicadorActivo}),
                    function(resp) {
                        resp.replace('span12', 'span10');
                        $('#fichaTecnicaContent').html(resp);
                        $('#fichaTecnicaContent').html('<table>' + $('#fichaTecnicaContent table').html() + '</table>');
                        $('#fichaTecnica').modal('show');
                    });
        }
    });
    
    $("#FiltroNoClasificados").searchFilter({targetSelector: ".indicador", charCount: 2});
    
    $('A.indicador').click(function() {
        var id_indicador = $(this).attr('data-id');
        var nombre_indicador = $(this).html();
        
        $.getJSON(Routing.generate('get_datos_indicador', {id: id_indicador}), function(mps) {
            datos_ = mps;
            tipoElemento = 'indicador';
            identificadorElemento = id_indicador;
            cargarTablaDinamica(mps);
            $('#marco-sala').attr('data-content', nombre_indicador);
            $('#myTab a:first').tab('show');
            idIndicadorActivo = id_indicador;
        });
    });
    
    $('A.elemento_costeo').click(function() {
        var codigo = $(this).attr('data-id');
        var nombre_elemento = $(this).html();
        
        $.getJSON(Routing.generate('get_datos_costeo', {codigo: codigo}), function(mps) {
            datos_ = mps;
            tipoElemento = 'costeo';
            identificadorElemento = codigo;
            cargarTablaDinamica(mps);
            $('#marco-sala').attr('data-content', nombre_elemento);
            $('#myTab a:first').tab('show');
        });
    });
    
    $('A.formulario_captura_datos').click(function() {
        var codigo = $(this).attr('data-id');
        var nombre_elemento = $(this).html();

        $.getJSON(Routing.generate('get_datos_formulario_captura', {codigo: codigo}), function(mps) {
            if (mps.estado == 'error'){
                $('#output').html('<div class="alert alert-warning" role="alert">'+mps.msj+'</div>');
            }
            else {
                datos_ = mps;
                tipoElemento = 'formulario';
                identificadorElemento = codigo;
                cargarTablaDinamica(mps);
            }
            $('#marco-sala').attr('data-content', nombre_elemento);   
            $('#myTab a:first').tab('show');
        });
    });
    
    $('A.calidad_datos_item').click(function() {        
        var nombre_elemento = $(this).html();
        
        $.getJSON(Routing.generate('get_datos_evaluacion_calidad'), function(mps) {
                datos_ = mps;
                tipoElemento = 'calidad';
                identificadorElemento = 'calidad';
                cargarTablaDinamica(mps);
            
            $('#marco-sala').attr('data-content', nombre_elemento);   
            $('#myTab a:first').tab('show');
        });
    });
    
    function cargarTablaDinamica(datos){
        var renderers = $.extend($.pivotUtilities.renderers,
            $.pivotUtilities.gchart_renderers);
                
        $("#output").pivotUI(datos, {
            renderers: renderers,
            menuLimit: 500,
            unusedAttrsVertical: false,
            onRefresh: onChangeTable
        }, false, 'es');
    }
    
    var onChangeTable = (function(config) {
                var config_copy = JSON.parse(JSON.stringify(config));
                //delete some values which are functions
                delete config_copy["aggregators"];
                delete config_copy["renderers"];
                //delete some bulky default values
                delete config_copy["rendererOptions"];
                delete config_copy["localeStrings"];
                configuracion = config_copy;
            });   
});

var tableToExcel = (function() {
  var uri = 'data:application/vnd.ms-excel;base64,'
    , template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>'
    , base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) }
    , format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) }
  return function(table, name, filename) {
    if (table !== undefined){        
        if (!table.nodeType) table = document.getElementById(table);
        var ctx = {worksheet: name || 'Worksheet', table: table.innerHTML};
        document.getElementById("dlink").href = uri + base64(format(template, ctx));
            document.getElementById("dlink").download = filename;
            document.getElementById("dlink").click();
    }
}
})();