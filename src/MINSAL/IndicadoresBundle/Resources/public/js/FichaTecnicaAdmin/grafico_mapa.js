graficoMapa = function(ubicacion, datos, colorChosen, categoryChoosen) {
    this.dibujar = function() {
        var dimension = $('#' + ubicacion + ' .dimensiones').val();
        var elemento_id_codigo = new Object();

        var width = 370,
                height = 250,
                currentDatasetChart = datos,
                centered,
                arreglo_datos = new Object();
        var colors = ['#D6C3BC', '#D4B6AC', '#D2AA9C', '#CF9C8C', '#CD8E7C', '#CB806C', '#C8705D', '#C6604F', '#C45042', '#C23E37', '#C0282E', '#BD0926'];
        var categorias = currentDatasetChart.map(function(d) {
            return d.category;
        });
        var medidas = currentDatasetChart.map(function(d) {
            return d.measure;
        });
        $.each(categorias, function(i, nodo) {            
            arreglo_datos[i] = medidas[i];
        });
        
        //Recuperar las coordenadas a utilizar 
        var coordenadas = $('#' + ubicacion + ' .dimensiones option[value="' + dimension + '"]');
        coordenadas = $(coordenadas[0]);
        if (coordenadas.attr('data-escala') === 'null'
                || coordenadas.attr('data-x') === 'null'
                || coordenadas.attr('data-y') === 'null') {
            alert(trans.no_mapa);
            $('#' + ubicacion + ' .tipo_grafico_principal').val('columnas');
            dibujarGrafico(ubicacion, $('#' + ubicacion + ' .dimensiones').val());
            return;
        }
        
        var datasetPrincipal_bk = JSON.parse($('#' + ubicacion).attr('datasetPrincipal_bk'));
        var max_y = d3.max(datasetPrincipal_bk, function(d) { return parseFloat(d.measure);});
        var min_y = d3.min(datasetPrincipal_bk, function(d) { return parseFloat(d.measure);});
        
        var colorScale = d3.scale.quantile()
                .domain([ min_y, max_y])
                .range(colors)
                ;
        var rangos_alertas = JSON.parse($('#' + ubicacion + ' .titulo_indicador').attr('rangos_alertas'));        
    
        
        
        $('#' + ubicacion + ' .grafico').html('');
        var svg = d3.select("#" + ubicacion + ' .grafico')
                .append("svg")
                .attr("viewBox", '-5 0 440 310')
                .attr("preserveAspectRatio", 'none')
                .attr("width", width)
                .attr("height", height)
                .attr("id", "ChartPlot");

        svg.append("rect")
                .attr("class", "background")
                .attr("width", width)
                .attr("height", height)
                .on("contextmenu", right_click)
                .on("click", function(d, i) {
                    descenderNivelDimension(ubicacion, d.properties.NAME);
                });

        var g = svg.append("g");

        var filtro = $('#' + ubicacion + ' .filtros_dimensiones').attr('data');

        d3.json(Routing.generate('indicador_datos_mapa',
                {id: $('#' + ubicacion + ' .titulo_indicador').attr('data-id'), dimension: dimension,
                    filtro: filtro, tipo_peticion: 'mapa'}),
                function(datos) {
                    
                    var objetos = 'datos.objects.elementos';
                    if (topojson.feature(datos, eval(objetos)).features === '') {
                        alert(trans.no_mapa);
                        $('#' + ubicacion + ' .tipo_grafico_principal').val('columnas');
                        dibujarGrafico(ubicacion, $('#' + ubicacion + ' .dimensiones').val());
                        return;
                    }
                    /* Datos para el mapa de El Salvador
                     * .scale(9000)
                     * .parallels([-90.12486,13.152632513245145])
                     */ 
                    
                    var escala = coordenadas.attr('data-escala');
                    var projection = d3.geo.albers()
                            .scale(escala)
                            .parallels(datos.transform.translate)
                            .translate([-escala / 9.2, -escala / 2.8]);

                    var path = d3.geo.path()
                            .projection(projection);

                    var seccion = g.append("g")
                            .attr("id", "states")
                            .selectAll("path")
                            .data(topojson.feature(datos, eval(objetos)).features)
                            .enter().append("path")
                            .attr("d", path)
                            .attr('fill', function(d, i) {
                                return (rangos_alertas.length === 0) ? colorScale(arreglo_datos[d.properties.ID]) : colores_alertas(ubicacion, arreglo_datos[d.properties.ID], i) ;
                            });

                    seccion.append("title").
                            text(function(d) {
                                if (arreglo_datos[d.properties.ID] === undefined)
                                    return d.properties.NAME;
                                else
                                    return d.properties.NAME + ': ' + arreglo_datos[d.properties.ID];
                            });

                    seccion.on("contextmenu", right_click)
                            .on("click", function(d, i) {
                                if (arreglo_datos[d.properties.ID] !== undefined)
                                    descenderNivelDimension(ubicacion, d.properties.NAME);
                            });
                });

        function right_click(d) {
            var x = 0,
                    y = 0,
                    k = 1;

            if (d && centered !== d) {
                var centroid = path.centroid(d);
                x = -centroid[0] + 40;
                y = -centroid[1] + 30;
                k = 4;
                centered = d;
            } else {
                centered = null;
            }

            g.selectAll("path")
                    .classed("active", centered && function(d) {
                        return d === centered;
                    });

            g.transition()
                    .duration(1000)
                    .attr("transform", "scale(" + k + ")translate(" + x + "," + y + ")")
                    .style("stroke-width", 1.5 / k + "px");

            //descenderNivelDimension(depto_id_codigo[d.properties.ID_1]);
            d3.event.preventDefault();
        }
    };
    this.ordenar = function(modo_orden, ordenar_por) {
        //No hacer nada, el mapa no se puede ordenar
        return;
    }

}