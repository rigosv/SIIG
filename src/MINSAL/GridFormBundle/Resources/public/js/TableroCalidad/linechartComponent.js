angular.module('tableroCalidadApp')
        .directive('lineChart', function () {            
            var datos = [];            
            return {
                restrict: 'E',
                replace: true,
                template: '<div id="lineChart"></div>',
                scope: {data: '=data'},
                link: function (scope, element, attrs) {
                    //var chartEl = d3.select(element[0]);
                    scope.$watch('data', function (newVal, oldVal) {                       
                        //RadarChart(".radarChart", newVal, radarChartOptions);
                        drawLineChart('lineChart', newVal );
                    });
                }
            };
        });