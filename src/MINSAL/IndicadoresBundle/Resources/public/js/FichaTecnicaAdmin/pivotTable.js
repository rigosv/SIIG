var idIndicadorActivo;

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
        $('.pvtTable').table2excel({
                exclude: ".excludeThisClass",
                name: $('#marco-sala').attr('data-content'),
                filename: $('#marco-sala').attr('data-content').trim() //do not include extension
            });
    });
    $('#export_grp').click(function() {        
        var html = $('div.c3').html();
        var svg = $('div.c3 svg');
        if (svg.length == 0){
            $('#export_grp').notify(trans._no_grafico_, {className: "info" });
            return;
        }

        $('#sql').html('<canvas id="canvasGrp" width="'+svg.attr('width')+'" height="'+svg.attr('height')+'"></canvas>');

        var canvas = document.getElementById("canvasGrp");

        rasterizeHTML.drawHTML(html, canvas)
            .then(function success(renderResult) {
                var link = document.createElement("a");
                canvas = document.getElementById("canvasGrp");
                link.href = canvas.toDataURL("image/png");

                link.download = 'grafico.png';
                document.body.appendChild(link);
                link.click();
            });
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
        var idFrm = $(this).attr('data-id');
        $.getJSON(Routing.generate('get_datos_evaluacion_calidad', {id: idFrm}), function(mps) {
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
            $.pivotUtilities.c3_renderers);
                
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
