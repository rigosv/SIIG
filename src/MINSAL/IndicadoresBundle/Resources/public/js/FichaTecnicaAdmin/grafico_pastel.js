graficoPastel = function (ubicacion, datos, colorChosen, categoryChoosen)
{
    this.tipo = 'pastel';
    this.currentDatasetChart = datos;
    this.zona = ubicacion;
    this.color = colorChosen;
    this.category = categoryChoosen;

    var contexto = this;
    this.dibujar = function ()
    {
        var margin = {top: 0, right: 0, bottom: 0, left: 0},
            width = parseInt($(' .area_grafico').first().width(), 10);   
	width  = width - margin.left - margin.right-50;
        var height = parseInt($(' .area_grafico').first().height(), 10)-180;

        $('#' + ubicacion + ' .grafico').html('');
        $("#" + contexto.zona + ' .grafico').attr("id", contexto.zona + '_grafico')
        var pie = new d3pie(contexto.zona + '_grafico',
                {
                    "size":
                            {
                                "canvasWidth": width + margin.left + margin.right,
                                "canvasHeight": height
                            },
                    "data":
                            {
                                "sortOrder": "value-desc",
                                "content":
                                        this.formatPieData()
                            },
                    "labels":
                            {
                                "inner":
                                        {
                                            "hideWhenLessThanPercentage": 3
                                        },
                                "mainLabel":
                                        {
                                            "fontSize": 12
                                        },
                                "percentage": {
                                    "color": "#ffffff",
                                    "decimalPlaces": 0
                                },
                                "value": {
                                    "color": "#adadad",
                                    "fontSize": 12
                                },
                                "lines": {
                                    "enabled": true
                                }
                            },
                    "effects":
                            {
                                "pullOutSegmentOnClick": {
                                    "effect": "linear",
                                    "speed": 400,
                                    "size": 8
                                }
                            },
                    "misc":
                            {
                                "gradient": {
                                    "enabled": true,
                                    "percentage": 100
                                }
                            },
                    "callbacks": {
                        onClickSegment: function (info)
                        {
                            console.log(info);
                            descenderNivelDimension(contexto.zona, info.data.label);
                        }
                    }
                });

    };

    this.formatPieData = function ()
    {
        //var data = contexto.currentDatasetChart.map(function(d,i)

        return contexto.currentDatasetChart.map(function (d, i)
        {
            return {"label": d.category,
                "value": parseFloat(d.measure),
                "color": colores_alertas(contexto.zona, d.measure, i)
            };
        });
    };
    
    this.ordenar = function (modo_orden, ordenar_por)
    { /*No hacer nada, el gráfico circular no se puede ordenar*/
        this.currentDatasetChart = ordenarArreglo(this.currentDatasetChart, ordenar_por, modo_orden);
        this.dibujar();
        $('#' + this.zona).attr('datasetPrincipal', JSON.stringify(this.currentDatasetChart));
    };
}