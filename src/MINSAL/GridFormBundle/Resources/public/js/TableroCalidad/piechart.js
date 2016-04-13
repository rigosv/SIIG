tableroCalidadApp.directive('pieChart', function () {
    function link(scope, element, attr) {
        formatPieData = function ()
        {
            //var data = contexto.currentDatasetChart.map(function(d,i)

            return scope.data.map(function (d, i)
            {
                return {"label": d.nombre,
                    "value": parseFloat(d.calificacion)
                };
            });
        };
        
        var color = d3.scale.category10();
        var data = scope.data
        var width = 300;
        var height = 300;
        var min = Math.min(width, height);
        var svg = d3.select(element[0]).append('svg');
        //var pie = d3.layout.pie().value(function(d) { return d.calificacion; }).sort(null);
        
        var pie = new d3pie(element[0],
                {
                    "size":
                            {
                                "canvasWidth": width,
                                "canvasHeight": height
                            },
                    "data":
                            {
                                "sortOrder": "value-desc",
                                "content": formatPieData()
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
                        }
                    }
                });
        
    }
    return {
        link: link,
        restrict: 'E',
        scope: { data: '=' }
    }
});