var formatAsPercentage = d3.format("%"),
        formatAsPercentage1Dec = d3.format(".1%"),
        formatPercent = d3.format("%"),
        formatAsInteger = d3.format(","),
        fsec = d3.time.format("%S s"),
        fmin = d3.time.format("%M m"),
        fhou = d3.time.format("%H h"),
        fwee = d3.time.format("%a"),
        fdat = d3.time.format("%d d"),
        fmon = d3.time.format("%b")
        ;
var sSwfPath = '';
var oLanguage = '';
var zona = 1;
var max_zonas = 3;

var color = d3.scale.category20();    //builtin range of colors

function colores_alertas(zona, indice, i) {

    var rangos_alertas = JSON.parse($('#' + zona + ' .titulo_indicador').attr('rangos_alertas'));
    if (rangos_alertas.length === 0)
        return color(i);
    else {
        for (ii = 0; ii < rangos_alertas.length; ii++) {
            if (indice <= rangos_alertas[ii].limite_sup)
                return rangos_alertas[ii].color;
        }
        return 'lightblue';
    }
}

function dibujarGraficoPrincipal(zona, tipo) {
    $('#' + zona + ' .dimension').html($('#opciones_dimension_' + zona + ' .dimensiones option:selected').html());
    cerrarMenus();
    var grafico = crearGraficoObj(zona, tipo);

    $('#' + zona + ' .titulo').show();
    grafico.dibujar();
    aplicarFormato();

    var datasetPrincipal = JSON.parse($('#' + zona).attr('datasetPrincipal'));
    construir_tabla_datos(zona, datasetPrincipal);
    //Corrección de error no despliega los controles select en ventana modal en firefox
    $( "SELECT, INPUT" ).click(function() {
        $( this ).focus();
    });
}
function aplicarFormato() {
    d3.selectAll(".axis path, .axis line")
            .attr('fill', 'none')
            .attr('stroke', 'black');
    d3.selectAll(".line")
            .attr('fill', 'none')
            .attr('stroke-width', '2px');
    d3.selectAll(".slice")
            .attr('font-size', '11pt')
            .attr('font-family', 'sans-serif');
    d3.selectAll(".axis text")
            .attr('font-family', 'sans-serif')
            .attr('font-size', '9pt');
    d3.selectAll(".background").attr('fill', 'none');

    d3.selectAll(".x g text").attr("transform", "rotate(45)").attr('x', 43).attr('y', 8).attr('text-anchor', 'start');
}
function crearGraficoObj(zona, tipo) {
    var grafico;
    var datasetPrincipal = JSON.parse($('#' + zona).attr('datasetPrincipal'));
    if (tipo == 'pastel')
        grafico = new graficoPastel(zona, datasetPrincipal);
    else if (tipo == 'lineas')
        grafico = new graficoLineas(zona, datasetPrincipal);
    else if (tipo == 'mapa')
        grafico = new graficoMapa(zona, datasetPrincipal);
    else if (tipo == 'gauge')
    {
        grafico = new graficoGauge(zona, datasetPrincipal);
    }
    else if (tipo == 'lineargauge' /*termometro*/)
    {
        grafico = new graficoTermometro(zona, datasetPrincipal);
    }
    else
        grafico = new graficoColumnas(zona, datasetPrincipal);
    return grafico;
}
function ascenderNivelDimension(zona, nivel) {
    var ultimo,
            ruta = '',
            $filtro = $('#' + zona + ' .filtros_dimensiones'),
            filtros_obj = jQuery.parseJSON($filtro.attr('data'));

    var nuevo_filtro = [{}];
    $.each(filtros_obj, function(i, obj) {

        if (i <= nivel) {
            nuevo_filtro[i] = obj;
            if (i == nivel)
                ruta += obj.etiqueta + ': ' + obj.valor;
            else{                
                ruta += '<A data="' + i + '">' + obj.etiqueta + ': ' + obj.valor + '</A> / ';
            }
        }
        else {
            // Los que estén a la derecha del seleccionado deben volver al control de dimensiones                        
            $('#' + zona + ' .dimensiones').append("<OPTION VALUE='" + obj.codigo + "'>" + obj.etiqueta + "</OPTION>");
            if (i == parseInt(nivel) + 1)
                primero = obj.codigo;
        }
    });

    //El primer elemento d
    $('#' + zona + ' .dimensiones').children('option[value="' + primero + '"]').attr('selected', 'selected');

    $filtro.html(ruta);
    $filtro.attr('data', JSON.stringify(nuevo_filtro));

    $('#' + zona + ' .filtros_dimensiones A').click(function() {
        ascenderNivelDimension(zona, $(this).attr('data'));
    });

    dibujarGrafico(zona, $('#' + zona + ' .dimensiones').val());
    $('#' + zona).attr('orden', null);
    $('#' + zona + ' .ordenar_dimension').children('option[value="-1"]').attr('selected', 'selected');
    $('#' + zona + ' .ordenar_medida').children('option[value="-1"]').attr('selected', 'selected');
}

function filtroRuta(filtros_obj) {
    var ruta = '';
    //var filtros_obj = );
    var cant_obj = filtros_obj.length;
    $.each(filtros_obj, function(i, obj) {
        if (i == (cant_obj - 1))
            ruta += obj.etiqueta + ': ' + obj.valor;
        else
            ruta += '<A data="' + i + '">' + obj.etiqueta + ': ' + obj.valor + '</A> / ';
    });
    return ruta;
}
function descenderNivelDimension(zona, category) {
    if ($('#' + zona + ' .dimensiones option').length <= 1) {
        //alert(trans.no_mas_niveles);
        $('#modal_msj_content').html('<div class="alert alert-warning" role="alert">'+trans.no_mas_niveles+'</div>');
        $('#modal_msj').modal('show');
        return;
    }
    var $dimension = $('#' + zona + ' .dimensiones option:selected');
    var $filtro = $('#' + zona + ' .filtros_dimensiones');
    var separador1 = '',
            separador2 = '';

    // Construir la cadena de filtros
    var filtros = $filtro.attr('data');
    var filtro_a_agregar = '{"codigo":"' + $dimension.val() + '",' +
            '"etiqueta":"' + $dimension.html() + '",' +
            '"valor":"' + category + '"' +
            "}";

    if (filtros != '' && filtros != null)
        separador1 += ',';
    else
        filtros = '[';

    filtros = filtros.replace(']', '');
    $filtro.attr('data', filtros + separador1 + filtro_a_agregar + ']');

    var ruta = filtroRuta(jQuery.parseJSON($filtro.attr('data')));
    $filtro.html(ruta);

    //Borrar la opcion del control de dimensiones
    $dimension.remove();
    //Actualizar el control para que ya no muestre el elemento eliminado
    $('#' + zona + ' .dimensiones option:first-child').trigger("chosen:updated");

    $('#' + zona + ' .filtros_dimensiones A').click(function() {
        ascenderNivelDimension(zona, $(this).attr('data'));
    });

    dibujarGrafico(zona, $('#' + zona + ' .dimensiones').val());
    $('#' + zona).attr('orden', null);
    $('#' + zona + ' .ordenar_dimension').children('option[value="-1"]').attr('selected', 'selected');
    $('#' + zona + '.ordenar_medida').children('option[value="-1"]').attr('selected', 'selected');
}

function dibujarGrafico(zona, dimension, desde_sala) {
    if (dimension === null)
        return;
    var filtro = $('#' + zona + ' .filtros_dimensiones').attr('data');
    var id_indicador  = $('#' + zona + ' .titulo_indicador').attr('data-id');
    
    if ($('#sala_default').val()==0){ 
        //Hace uso del servicio web REST en la ruta get_indicador
        $.getJSON(Routing.generate('get_indicador',
                {id: id_indicador, dimension: dimension}),
                {filtro: filtro, ver_sql: false},
        function(resp) {
            procesarDibujarGrafico(resp, zona, desde_sala);
        }).fail(function() {
            $('#div_carga').hide();
            $('#modal_msj_content').html('<div class="alert alert-danger" role="alert">'+trans._error_conexion_2_+'</div>');            
            $('#modal_msj').modal('show');
        });
    } else {
        var posicion = zona.split('_');
        var indice = parseInt(posicion[1]);
        procesarDibujarGrafico(indicadoresDatos[indice], zona, desde_sala);
    }
}

function procesarDibujarGrafico(resp, zona, desde_sala) {
    var datos = JSON.stringify(resp.datos);
    $('#' + zona).attr('datasetPrincipal_bk', datos);
    if ($('#' + zona).attr('orden') !== undefined
            && $('#' + zona).attr('orden') !== null
            && $('#' + zona).attr('orden') !== '')
    {
        if ($('#' + zona).attr('orden-aplicado') !== 'true') {
            var ordenobj = JSON.parse($('#' + zona).attr('orden'));
            datos = JSON.stringify(ordenarArreglo(resp.datos, ordenobj[0].tipo, ordenobj[0].modo));
            $('DIV.zona_actual').attr('orden-aplicado', 'true');
        }
    }

    $('#' + zona).attr('datasetPrincipal', datos);

    controles_filtros(zona);
    if (desde_sala){
       aplicarFiltro(zona);
    }
    dibujarGraficoPrincipal(zona, $('#opciones_' + zona + ' .tipo_grafico_principal').val());
}

function ordenarDatos(zona, ordenar_por, modo_orden) {
    if (modo_orden === '-1')
        return;
    if (ordenar_por === 'dimension')
        $('#opciones_' + zona + ' .ordenar_medida').children('option[value="-1"]').attr('selected', 'selected');
    else
        $('#opciones_' + zona + ' .ordenar_dimension').children('option[value="-1"]').attr('selected', 'selected');

    cerrarMenus();
    $('#' + zona).attr('orden', '[{"tipo":"' + ordenar_por + '", "modo": "' + modo_orden + '"}]');
    var grafico = crearGraficoObj(zona, $('#opciones_' + zona + ' .tipo_grafico_principal').val());
    grafico.ordenar(modo_orden, ordenar_por);
    var datasetPrincipal = JSON.parse($('#' + zona).attr('datasetPrincipal'));
    construir_tabla_datos(zona, datasetPrincipal);
    aplicarFormato();
}

function aplicarFiltro(zona) {
    var elementos = '';
    var datasetPrincipal = JSON.parse($('#' + zona).attr('datasetPrincipal'));

    $('#opciones_dimension_' + zona + ' .capa_dimension_valores input:checked').each(function() {
        elementos += $(this).val() + '&';
    });
    
    var datos = datasetPrincipal,
            desde = $('#opciones_dimension_' + zona + ' .filtro_desde').val(), 
            hasta = $('#opciones_dimension_' + zona + ' .filtro_hasta').val(),
            datos_aux = [];
    
    if (elementos != '') {
        elementos = (elementos).trim('&');
        var datos_a_mostrar = elementos.split('&');
        $.each(datos, function(nodo, fila) {
            if (datos_a_mostrar.indexOf(fila.category) >= 0) {
                datos_aux.push(fila);
            }
        });
    } else {
        var max = datos.length;
        hasta = (hasta == '' || hasta > max) ? max : hasta;
        desde = (desde == '' || desde <= 0) ? 0 : desde - 1;

        var cantidad = hasta - desde;
        datos_aux = datos.slice(desde, cantidad);
    }
    $('#' + zona).attr('datasetPrincipal', JSON.stringify(datos_aux));    
}

function controles_filtros(zona) {
    var datasetPrincipal = JSON.parse($('#' + zona).attr('datasetPrincipal'));

    var lista_datos_dimension = '<DIV class="capa_dimension_valores col-md-12" >' + trans.filtrar_por_elemento + '<BR>';
    $.each(datasetPrincipal, function(i, dato) {
        lista_datos_dimension += '<label class="forcheckbox" for="categorias_a_mostrar' + zona + i + '" ><input type="checkbox" id="categorias_a_mostrar' + zona + i + '" ' +
                'name="categorias_a_mostrar[]" value="' + dato.category + '" /> ' + dato.category + '</label>';
    });
    lista_datos_dimension += '</DIV>';

    $('#opciones_dimension_' + zona + ' .lista_datos_dimension').html(lista_datos_dimension);

    // Corrige un error de bootstrap para permitir usar controles dentro de un dropdown
    $('.dropdown-menu SELECT, .dropdown-menu LABEL, .dropdown-menu INPUT').click(function(event) {
        $(this).focus();
        event.stopPropagation();
    });
    //Corrige un error de bootstrap para que funcione un menu dropdown en tabletas
    $('body').on('touchstart.dropdown', '.dropdown-menu', function(e) {
        e.stopPropagation();
    });

    $('#opciones_dimension_' + zona + ' .aplicar_filtro').click(function() {
        aplicarFiltro(zona);
        dibujarGraficoPrincipal(zona, $('#opciones_' + zona + ' .tipo_grafico_principal').val());
    });
    $('#opciones_dimension_' + zona + ' .quitar_filtro').click(function() {
        $('#opciones_dimension_' + zona + ' .filtro_desde').val('');
        $('#opciones_dimension_' + zona + ' .filtro_hasta').val('');
        $('#opciones_dimension_' + zona + ' .capa_dimension_valores input:checked').each(function() {
            $(this).attr('checked', false);
        });
        //datasetPrincipal = datasetPrincipal_bk;
        $('#' + zona).attr('datasetPrincipal', $('#' + zona).attr('datasetPrincipal_bk'))
        dibujarGraficoPrincipal(zona, $('#opciones_' + zona + ' .tipo_grafico_principal').val());
    });
    if ($('#' + zona + ' .titulo_indicador').attr('filtro-elementos') !== undefined
            && $('#' + zona + ' .titulo_indicador').attr('filtro-elementos') !== '') {
        var filtroElementos = $('#' + zona + ' .titulo_indicador').attr('filtro-elementos').split(',');
        for (var j = 0; j < filtroElementos.length; j++) {
            $('#opciones_dimension_' + zona + ' .capa_dimension_valores input[value="' + filtroElementos[j] + '"]').attr('checked', true);
        }
        aplicarFiltro(zona);
    }
}

function cerrarMenus() {
    $('.open').each(function(i, nodo) {
        $(nodo).removeClass('open');
    })
}

function construir_tabla_datos(zona, datos) {
    var tabla_datos = '<TABLE class="table table-striped table-condensed" >';
    $.each(datos, function(i, fila) {
        if (i === 0) {
            // Los nombres de los campos
            tabla_datos += '<THEAD><TR>';
            for (var campo in fila) {

                if (campo === 'category')
                    campo = $('#' + zona + ' .dimension').html();
                else if (campo === 'measure')
                    campo = trans.indicador + ' (' + $('#' + zona + ' .titulo_indicador').attr('formula') + ')';
                tabla_datos += '<TH>' + campo.toUpperCase() + '</TH>';
            }
            tabla_datos += '</TR></THEAD><TBODY>';
        }

        //los datos
        tabla_datos += '<TR>';
        for (var i in fila)
            tabla_datos += '<TD>' + fila[i] + '</TD>';
        tabla_datos += '</TR>';

    });
    tabla_datos += '</TBODY></TABLE>';

    $('#' + zona + ' .info').html(tabla_datos);
}

function dibujarControles(zona, datos) {
    $('#' + zona + ' .titulo_indicador').html(datos.nombre_indicador)
            .attr('data-unidad-medida', datos.unidad_medida)
            .attr('formula', datos.formula)
            .attr('data-id', datos.id_indicador)            
            .attr('filtro-elementos', '')
            .attr('rangos_alertas', JSON.stringify(datos.rangos));
    
    var meta = (datos.meta==null) ? 0 : datos.meta;
    $('#' + zona ).attr('meta', meta);
    
    var msj_favoritos = '',
        icon_favoritos = '';
    var combo_dimensiones = trans.cambiar_dimension + ":<SELECT class='dimensiones' id= '"+zona+"_dimensiones' name='dimensiones'>";
    
    $.each(datos.dimensiones, function(codigo, datosDimension) {
        combo_dimensiones += "<option value='" + codigo + "' data-escala='" + datosDimension.escala +
                "' data-x='" + datosDimension.origenX +
                "' data-y='" + datosDimension.origenY +
                "' data-graficos='" + JSON.stringify(datosDimension.graficos) + "'>" + datosDimension.descripcion + "</option>";
    });
    combo_dimensiones += "</SELECT>";
    
    msj_favoritos =  ($('#fav-' + datos.id_indicador).length === 0) ? trans.agregar_favorito : trans.quitar_favoritos;
    icon_favoritos =  ($('#fav-' + datos.id_indicador).length === 0) ? '' : '-empty';

    var combo_tipo_grafico = trans.tipo_grafico + ": <SELECT class='form-control tipo_grafico_principal'  ></SELECT>";
    var botones = 
            '<div class="btn-group sobre_div">' +
                '<button class="btn btn-info" data-toggle="modal" data-target="#opciones_'+zona+'" title="' + trans.opciones_grafico + '">' +            
                    '<span class="glyphicon glyphicon-stats"></span>' +
                '</button>'+
                '<button class="zoom btn btn-info" data-toggle="modal" title="Zoom">' +            
                    '<span class="glyphicon glyphicon-zoom-in"></span>' +
                '</button>'+
                '<button class="btn btn-info dropdown-toggle" data-toggle="dropdown" title="' + trans.opciones + '">' +
                    '<span class="glyphicon glyphicon-cog"></span>' +
                '</button>' +
                '<ul class="dropdown-menu" role="menu" >' +
                    '<li><A class="ver_ficha_tecnica">' +
                        '<span class="glyphicon glyphicon-briefcase"></span> ' + trans.ver_ficha_tecnica + '</A>\n\
                    </li>' +
                    '<li><A class="cambiar_vista" ><span class="glyphicon glyphicon-refresh" ></span> ' + trans._cambiar_vista_ + ' </A></li>' +
                    '<li><A class="ver_tabla_datos" ><span class="glyphicon glyphicon-list-alt" ></span> ' + trans.tabla_datos + ' </A></li>' +
                    '<li><A class="ver_sql" ><span class="glyphicon glyphicon-eye-open" ></span> ' + trans.ver_sql + ' </A></li>' +
                    '<li><A class="ver_imagen" ><span class="glyphicon glyphicon-picture"></span> ' + trans.descargar_grafico + '</A></li>' +
                    '<li><A class="quitar_indicador" ><span class="glyphicon glyphicon-remove-sign"></span> ' + trans.quitar_indicador + '</A></li>' +
                    '<li><A class="agregar_como_favorito" data-indicador="' + datos.id_indicador + '" >'+
                        '<span class="glyphicon glyphicon-star'+icon_favoritos+'"></span> ' + msj_favoritos + '</A>'+
                    '</li>'+
                '</ul>'+                
                '<button class="btn btn-info" data-toggle="modal" data-target="#opciones_dimension_'+zona+'" title="' + trans.dimension_opciones + '">' +            
                    '<span class="glyphicon glyphicon-check"></span>' +
                '</button>'+
                '<button class="btn btn-info refrescar"  title="' + trans._recargar_ + '" data-id="'+datos.id_indicador+'">' +
                    '<i class="glyphicon glyphicon-refresh"></i>' +
                '</button>';
            '</DIV>';    
                
    var combo_ordenar_por_dimension = trans.ordenar_x + ": <SELECT class='form-control  ordenar_dimension'>" +
            "<OPTION VALUE='-1'></OPTION>" +
            "<OPTION VALUE='desc'>" + trans.descendente + "</OPTION>" +
            "<OPTION VALUE='asc'>" + trans.ascendente + "</OPTION>" +
            "</SELECT>";
    var combo_ordenar_por_medida = trans.ordenar_y + ": <SELECT class='form-control ordenar_medida'>" +
            "<OPTION VALUE='-1'></OPTION>" +
            "<OPTION VALUE='desc'>" + trans.descendente + "</OPTION>" +
            "<OPTION VALUE='asc'>" + trans.ascendente + "</OPTION>" +
            "</SELECT>";
    var filtro_posicion = trans.filtro_posicion + "<BR/> " + trans.desde +
            "<INPUT class='valores_filtro filtro_desde' type='text' length='5' value=''> " + trans.hasta +
            "<INPUT class='valores_filtro filtro_hasta' type='text' length='5' value=''> ";
    

    var opciones_indicador_modal= 
            '<div class="modal fade" id="opciones_'+zona+'"  role="dialog" aria-labelledby="myModalLabel'+zona+'">'+
                '<div class="modal-dialog" role="document">'+
                  '<div class="modal-content" style="overflow: visible;">'+
                    '<div class="modal-header">'+
                      '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
                      '<h4 class="modal-title" id="myModalLabel'+zona+'">' + trans.opciones_grafico + '</h4>'+
                    '</div>'+
                    '<div class="modal-body" style="overflow: visible;">'+
                        '<div class="container-fluid">'+                            
                            '<div class="col-sm-6">'+
                                '<div class="form-group">' + combo_ordenar_por_medida + '</div>' +
                            '</div>'+
                            '<div class="col-sm-6">'+
                                '<div class="form-group">' + combo_ordenar_por_dimension + '</div>' +
                            '</div>'+
                            '<div class="col-sm-4">'+
                                '<div class="form-group">' + combo_tipo_grafico + '</div>'+
                            '</div>'+
                            '<div class="col-sm-6">'+
                                '<div class="form-group max-eje-y" ></div>'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                    '<div class="modal-footer">'+
                      '<button type="button" class="btn btn-warning" data-dismiss="modal">' + trans._cerrar_ + '</button>'+
                    '</div>'+
                  '</div>'+
                '</div>'+
            '</div>';
    var opciones_dimension_modal =
            '<div class="modal fade" id="opciones_dimension_'+zona+'"  role="dialog" aria-labelledby="myModalLabel2'+zona+'">'+
                '<div class="modal-dialog" role="document">'+
                  '<div class="modal-content" style="overflow: visible;">'+
                    '<div class="modal-header">'+
                      '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
                      '<h4 class="modal-title" id="myModalLabel2'+zona+'">' + trans.dimension_opciones + '</h4>'+
                    '</div>'+
                    '<div class="modal-body" style="overflow: visible;">'+
                        '<div class="container-fluid">'+                            
                            '<div class="col-sm-6">'+
                                '<div class="form-group">' + combo_dimensiones + '</div>' +
                            '</div>'+
                            '<div class="col-sm-6">'+
                                '<div class="form-group">' + filtro_posicion + '</div>' +
                            '</div>'+                            
                            '<div class="col-sm-12">'+
                                '<div class="form-group lista_datos_dimension"></div>'+
                            '</div>'+
                            '<DIV class="filtro_elementos col-sm-6">'+
                                '<input type="button" class="btn btn-info aplicar_filtro" value="' + trans.filtrar + '"/>' +
                                '<input type="button" class="btn btn-danger quitar_filtro" value="' + trans.quitar_filtro + '"/>'+
                            '</DIV>'+
                        '</div>'+
                    '</div>'+
                    '<div class="modal-footer">'+
                      '<button type="button" class="btn btn-warning" data-dismiss="modal">' + trans._cerrar_ + '</button>'+
                    '</div>'+
                  '</div>'+
                '</div>'+
            '</div>';
               
    var rangos_alertas = datos.rangos;

    var alertas = '';
    alertas += '<TABLE class="table"><CAPTION>' + trans.alertas_indicador + '</CAPTION>' +
            '<THEAD>' +
            '<TR>' +
            '<TH>' + trans.color + '</TH>' +
            '<TH>' + trans.limite_inf + '</TH>' +
            '<TH>' + trans.limite_sup + '</TH>' +
            '<TH>' + trans.comentario + '</TH>' +
            '</TR>' +
            '</THEAD>' +
            '<TBODY>';

    var max_rango = 0;
    $.each(rangos_alertas, function(i, rango) {
        var comentario_rango = '';
        if (rango.comentario === null)
            comentario_rango = '';
        else
            comentario_rango = rango.comentario
        alertas += '<TR>' +
                '<TD bgcolor="' + rango.color + '"></TD>' +
                '<TD>' + rango.limite_inf + '</TD>' +
                '<TD>' + rango.limite_sup + '</TD>' +
                '<TD>' + comentario_rango + '</TD>' +
                '</TR>';
        max_rango = rango.limite_sup;
    });
    $('#' + zona + ' .titulo_indicador').attr('data-max_rango', max_rango);
    alertas += '<TR><TD bgcolor="lightblue"></TD>' +
            '<TD colspan="3">' + trans.rango_no_especificado + '</TD>'
    '</TR></TBODY><TABLE>';
    $('#' + zona + ' .alertas').html('');
    $('#' + zona + ' .grafico').html('');
    
    var opciones_max_eje_y= '';
    opciones_max_eje_y += 
        trans.max_escala_y +
        "<div class='input-group'>"+
            "<span class='input-group-addon'>"+  
              "<input name= 'max_y' type='radio' class= 'max_y1 ejey max_y' value= 'indicador' aria-label='...' checked>"+
            "</span>"+
            "<input type='text' class='form-control' aria-label='...' value= '"+ trans.max_indicador +"' readonly>"+
        "</div>";
    if (rangos_alertas.length > 0) {
        opciones_max_eje_y += "<div class='input-group'>"+
            "<span class='input-group-addon'>"+  
              "<input name= 'max_y' type='radio' class= 'max_y2 ejey max_y' value= 'rango_alertas' aria-label='...'>"+
            "</span>"+ 
            "<input type='text' class='form-control' aria-label='...' value= '"+ trans.max_rango_alertas +"' readonly>"+
        "</div>";
    }
    opciones_max_eje_y +=
        "<div class='input-group'>"+
            "<span class='input-group-addon'>"+  
              "<input name= 'max_y' type='radio' class= 'max_y3 ejey max_y' value= 'fijo' aria-label='...'>"+
            "</span>"+ 
            "<input type='text' class='form-control ejey max_y_fijo' aria-label='...' value='100' >"+
        "</div>"
        ;  
    if (rangos_alertas.length > 0) {
        $('#' + zona + ' .controles').append('<div class="btn-group sobre_div">' +
                '<button class="btn btn-warning dropdown-toggle" data-toggle="dropdown" title="' + trans.alertas_indicador + '">' +
                    '<span class="glyphicon glyphicon-exclamation-sign"></span>' +
                '</button>' +                
                '<ul class="dropdown-menu">' +
                alertas +
                '</ul>' +
                '</div>');
    }
    
    // Los botones botones
    $('#' + zona + ' .controles').append(botones);        
    $('#' + zona + ' .controles').append('<a id="' + zona + '_ultima_lectura" data-placement="bottom" data-toggle="popover" class="btn-small btn-warning pull-right" href="#" >' + datos.ultima_lectura + '</a>');
    $('#' + zona + '_ultima_lectura').popover({title: trans.ultima_lectura, content: trans.ultima_lectura_exp});    
    
    
    $('#'+zona).append(opciones_indicador_modal);
    $('#'+zona).append(opciones_dimension_modal);
    
    $('#' + zona + ' .max-eje-y').append(opciones_max_eje_y);    
    
    $('#' + zona + ' .max_y_fijo').click(function() {
        $('#' + zona + ' .max_y3').prop( "checked", true );
    });
    $('#' + zona + ' .ejey').change(function() {
        if($('#' + zona + ' .max_y:checked').val() == 'fijo'){
            if (isNaN($('#' + zona + ' .max_y_fijo').val())){
                $('#' + zona + ' .max_y_fijo').val(100);
            }            
        }
        dibujarGraficoPrincipal(zona, $('#' + zona + ' .tipo_grafico_principal').val());
    });
    
    if ($('#' + zona).attr('meta') > 0){
        $('#' + zona + ' .pie_grafico').html(trans._meta_+': '+meta);
    }
    
    setTiposGraficos(zona);
    $('#opciones_' + zona + ' .ordenar_medida').change(function() {
        ordenarDatos(zona, 'medida', $(this).val());
    });

    $('#opciones_' + zona + ' .ordenar_dimension').change(function() {
        ordenarDatos(zona, 'dimension', $(this).val());
    });

    $('#opciones_dimension_' + zona + ' .dimensiones').change(function() {
        setTiposGraficos(zona);
        if ($('#opciones_' + zona + ' .tipo_grafico_principal').val() != null) {
            $('#opciones_' + zona + ' .ordenar_dimension').children('option[value="-1"]').attr('selected', 'selected');
            $('#opciones_' + zona + ' .ordenar_medida').children('option[value="-1"]').attr('selected', 'selected');
            dibujarGrafico(zona, $(this).val());
            $('#' + zona).attr('orden', null);
        }
    });

    $('#opciones_' + zona + ' .tipo_grafico_principal').change(function() {
        dibujarGraficoPrincipal(zona, $(this).val());
    });
    $('#' + zona + ' .agregar_como_favorito').click(function() {
        alternar_favorito(zona, $(this).attr('data-indicador'));
        cerrarMenus();
    });
    $('#' + zona + ' .zoom').click(function() {
        $('#' + zona).toggleClass('zona_maximizada');        
        $(this).hide();
        goFullscreen('z'+zona);
        
    });
    $('#' + zona + ' .quitar_indicador').click(function() {
        //limpiarZona2(zona);
        $('#'+zona).remove();
    });
    $('#' + zona + ' .info').hide();
    $('#' + zona + ' .cambiar_vista').click(function() {       
        //ocultar la zona del gráfico y mostrar la tabla (o viceversa)
        $('#'+zona+' .row_grafico').toggle();
        $('#'+zona+' .info').toggle();
        
        if ($('#'+zona+' .row_grafico').css('display') == 'block'){
            $('#'+zona+' .titulo_indicador').attr('vista','grafico');
        } else {
            $('#'+zona+' .titulo_indicador').attr('vista','tabla');
        }
    });
    $('#' + zona + ' .ver_tabla_datos').click(function() {
        $('#myModalLabel2').html();
        $('#sql').html($('#' + zona + ' .info').html());
        $('#sql table').dataTable({
            "bJQueryUI": true,
            "sDom": '<"H"Tfr>t<"F"ip>',
            "oTableTools": {
                "sSwfPath": sSwfPath,
                "aButtons": [
                    {
                        "sExtends": "collection",
                        "sButtonText": trans.exportar,
                        "aButtons": [{
                                "sExtends": "csv",
                                "sTitle": $('#' + zona + ' .titulo_indicador').html()
                            }, {
                                "sExtends": "xls",
                                "sTitle": $('#' + zona + ' .titulo_indicador').html()
                            }, {
                                "sExtends": "pdf",
                                "sTitle": $('#' + zona + ' .titulo_indicador').html()
                            }]
                    }
                ]
            },
            "oLanguage": oLanguage
        });
        $('#myModal2').modal('show');
        //cerrarMenus();
    });

    $('#' + zona + ' .ver_sql').click(function() {
        var filtro = $('#opciones_dimension_' + zona + ' .filtros_dimensiones').attr('data');
        var dimension = $('#opciones_dimension_' + zona + ' .dimensiones').val();

        $.getJSON(Routing.generate('get_indicador',
                {id: $('#' + zona + ' .titulo_indicador').attr('data-id'), dimension: dimension}),
                {filtro: filtro, ver_sql: true},
        function(resp) {
            $('#myModalLabel2').html($('#' + zona + ' .titulo_indicador').html());
            $('#sql').html(resp.datos);
            $('#myModal2').modal('show');
        }, 'json').fail(function() {
            $('#div_carga').hide();
            $('#modal_msj_content').html('<div class="alert alert-danger" role="alert">'+trans._error_conexion_2_+'</div>');            
            $('#modal_msj').modal('show');            
    });
    });

    $('#' + zona + ' .ver_imagen').click(function() {
        var html = '<H5 style="text-align:center;">' + $('#' + zona + ' .titulo_indicador').html() +
                ' (por ' + $('#' + zona + ' .dimension').html() + ')</H5>' +
                '<H6 >' + $('#' + zona + ' .filtros_dimensiones').html() + '</H6>' +
                '<svg id="ChartPlot" width="95%" viewBox="-20 -20 440 310" preserveAspectRatio="none">' + d3.select('#' + zona + ' svg').html() + '"</svg>' +
                $('#sql').html('<canvas id="canvasGrp" width="400" height="350"></canvas>');

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

    $('#' + zona + ' .ver_ficha_tecnica').click(function() {
        $.get(Routing.generate('get_indicador_ficha',
                {id: $('#' + zona + ' .titulo_indicador').attr('data-id')}),
                function(resp) {
                    $('#myModalLabel2').html($('#' + zona + ' .titulo_indicador').html());
                    $('#sql').html(resp);
                    //Dejar solo el código html de la tabla, quitar todo lo demás

                    $('#sql').html('<table>' + $('#sql table').html() + '</table>');
                    $('#sql .sonata-ba-view-title').remove();
                    $('#sql table').append('<thead><TR><TH>Campo</TH><TH>Descripcion</TH></TR></thead>');
                    $('#sql table').dataTable({
                        "bFilter": false,
                        "bSort": false,
                        "sDom": '<"H"T>',
                        "bInfo": false,
                        "iDisplayLength": 30,
                        "oTableTools": {
                            "sSwfPath": sSwfPath,
                            "aButtons": [
                                {
                                    "sExtends": "collection",
                                    "sButtonText": trans.exportar,
                                    "aButtons": [{
                                            "sExtends": "xls",
                                            "sTitle": $('#' + zona + ' .titulo_indicador').html()
                                        }, {
                                            "sExtends": "pdf",
                                            "sTitle": $('#' + zona + ' .titulo_indicador').html()
                                        }]
                                }
                            ]
                        },
                    });
                    $('#sql .DTTT_container').css('float', 'left');
                    $('#myModal2').modal('show');
                }, 'html').fail(function() {
                        $('#div_carga').hide();
                        $('#modal_msj_content').html('<div class="alert alert-danger" role="alert">'+trans._error_conexion_2_+'</div>');                        
                        $('#modal_msj').modal('show');                        
                });
    });
    
    $('#' + zona + ' .refrescar').click(function() {
        $(".zona_actual").removeClass('zona_actual');
        $("#"+(zona)).addClass('zona_actual');
        recuperarDimensiones($(this).attr('data-id'), null);
    });
}

function setTiposGraficos(zona) {
    var tipos_graficos = '';
    var graficos = jQuery.parseJSON($('#opciones_dimension_' + zona + ' .dimensiones option:selected').attr('data-graficos'));
    $.each(graficos, function(i, grafico) {
        tipos_graficos += "<OPTION VALUE='" + grafico.codigo + "'>" + grafico.descripcion + "</OPTION>";
    });
    $('#opciones_' + zona + ' .tipo_grafico_principal').html(tipos_graficos);
}

function alternar_favorito(zona, id_indicador) {
    //Revisar si ya es favorito
    var es_favorito;
    ($('#fav-' + id_indicador).length === 0) ? es_favorito = false : es_favorito = true;
    var cant_favoritos = parseInt($('#cantidad_favoritos').html());
    cant_favoritos = (es_favorito) ? cant_favoritos - 1 : cant_favoritos + 1;
    $('#cantidad_favoritos').html(cant_favoritos);

    if (es_favorito) {
        $('#' + zona + ' .agregar_como_favorito').html('<i class="icon-star"></i>' + trans.agregar_favorito);
        $('#li_fav-' + id_indicador).remove();
    } else {
        $('#' + zona + ' .agregar_como_favorito').html('<i class=" icon-star-empty"></i>' + trans.quitar_favoritos);
        $('#listado-favoritos').append("<A data-id='" + id_indicador + "' " +
                "id='fav-" + id_indicador + "' " +
                "data-unidad-medida='" + $('#' + zona + ' .titulo_indicador').attr('data-unidad-medida') + "'>" +
                $('#' + zona + ' .titulo_indicador').html() +
                "</A>");

        $('#fav-' + id_indicador).click(function() {
            $('#' + zona + ' .controles').html('');
            $('#' + zona + ' .filtros_dimensiones').html('').attr('data', '');

            recuperarDimensiones(id_indicador);
        });
    }
    $.get(Routing.generate('indicador_favorito'),
            {id: $('#' + zona + ' .titulo_indicador').attr('data-id'), es_favorito: es_favorito}
    );
}

function limpiarZona(zona) {
    $('#' + zona + ' .controles').html('');
    $('#' + zona + ' .filtros_dimensiones').attr('data', '');
    $('#' + zona + ' .filtros_dimensiones').html('');
    $('#opciones_dimension_'+zona).remove();
    $('#opciones_'+zona).remove();
    $('#' + zona).attr('orden', null);
}

function recuperarDimensiones(id_indicador, datos, desde_sala) {
    var zona_g = $('DIV.zona_actual').attr('id');
    limpiarZona(zona_g);
    
    if ($('#sala_default').val()==0){
        $.getJSON(
            Routing.generate('indicador_dimensiones', {id: id_indicador}),
            function(resp) {
                procesarDimensiones(resp, datos, zona_g, desde_sala);
            }).fail(function() {
                $('#div_carga').hide();
                $('#modal_msj_content').html('<div class="alert alert-danger" role="alert">'+trans._error_conexion_2_+'</div>');                
                $('#modal_msj').modal('show');                
            });
    } else {
        var posicion = zona_g.split('_');
        var indice = parseInt(posicion[1]);
        procesarDimensiones(indicadoresDimensiones[indice] , datos, zona_g, desde_sala);
    }
}

function procesarDimensiones(resp, datos, zona_g, desde_sala) {
    //Construir el campo con las dimensiones disponibles

    if (resp.resultado === 'ok') {
        if (resp.dimensiones == '') {
            $('#modal_msj_content').html('<div class="alert alert-danger" role="alert">'+trans.no_graficos_asignados+'</div>');
            $('#modal_msj').modal('show');
        } else {
            dibujarControles(zona_g, resp);
            if (datos !== null) {
                if (datos.filtro != null && datos.filtro != '') {
                    var $filtro = $('#' + zona_g + ' .filtros_dimensiones');
                    $filtro.attr('data', datos.filtro);
                    filtro_obj = jQuery.parseJSON($filtro.attr('data'));
                    var ruta = filtroRuta(filtro_obj);
                    $filtro.html(ruta);

                    for (i = 0; i < filtro_obj.length; i++) {
                        $('#' + zona_g + ' .dimensiones')
                                .children('option[value=' + filtro_obj[i].codigo + ']')
                                .remove();
                    }

                    $('#' + zona_g + ' .filtros_dimensiones A').click(function() {
                        ascenderNivelDimension(zona_g, $(this).attr('data'));
                    });
                    
                }
                $('#' + zona_g + ' .titulo_indicador').attr('data-id', datos.idIndicador);                
                $('#' + zona_g + ' .titulo_indicador').attr('vista', datos.vista);
                $('#' + zona_g).attr('orden', datos.orden);                
                $('#' + zona_g).attr('orden-aplicado', 'false');
                $('#opciones_dimension_' + zona_g + ' .dimensiones').val(datos.dimension);
                $('#opciones_dimension_' + zona_g + ' .filtro_desde').val(datos.filtroPosicionDesde);
                $('#opciones_dimension_' + zona_g + ' .filtro_hasta').val(datos.filtroPosicionHasta);
                $('#' + zona_g + ' .titulo_indicador').attr('filtro-elementos', datos.filtroElementos);
                $('#' + zona_g + ' .tipo_grafico_principal').val(datos.tipoGrafico);

            }
            dibujarGrafico(zona_g, $('#opciones_dimension_' + zona_g + ' .dimensiones').val(), desde_sala);            
            if ($('#' + zona_g + ' .titulo_indicador').attr('vista') == 'tabla'){
                $('#'+zona_g+' .row_grafico').toggle();
                $('#'+zona_g+' .info').toggle();
            }
        }
    }
}

function ordenarArreglo(datos, ordenar_por, modo_orden) {
    if (ordenar_por === 'dimension')
        var datos_ordenados = datos.sort(
                (modo_orden === 'asc') ?
                function(a, b) {
                    return d3.ascending((isNaN(a.category)) ? a.category : parseFloat(a.category), (isNaN(b.category)) ? b.category : parseFloat(b.category));
                } :
                function(a, b) {
                    return d3.descending((isNaN(a.category)) ? a.category : parseFloat(a.category), (isNaN(b.category)) ? b.category : parseFloat(b.category));
                }
        );
    else
        var datos_ordenados = datos.sort(
                (modo_orden === 'asc') ? function(a, b) {
            return d3.ascending(parseFloat(a.measure), parseFloat(b.measure));
        } :
                function(a, b) {
                    return d3.descending(parseFloat(a.measure), parseFloat(b.measure));
                }
        );
    return datos_ordenados;
}