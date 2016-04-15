angular.module('tableroCalidadApp')
        .directive('radarChart', function () {
            var margin = {top: 40, right: 10, bottom: 70, left: 10},
                    //width = Math.min(700, window.innerWidth - 10) - margin.left - margin.right,
                    width = 600 - margin.left - margin.right,
                    height = 360 - margin.top - margin.bottom - 20;
                    //height = Math.min(width, window.innerHeight - margin.top - margin.bottom - 20);
            var color = d3.scale.ordinal()
                        .range(["#00A0B0","#CC333F","#EDC951"]);

            var radarChartOptions = {
              w: width,
              h: height,
              margin: margin,
              maxValue: 1,
              levels: 5,
              roundStrokes: true,
              color: color
            };
            var datos = [];
            return {
                restrict: 'E',
                replace: true,
                template: '<div class="radarChart"></div>',
                scope: {data: '=data'},
                link: function (scope, element, attrs) {
                    //var chartEl = d3.select(element[0]);
                    scope.$watch('data', function (newVal, oldVal) {                       
                        RadarChart(".radarChart", newVal, radarChartOptions);
                    });
                }
            };
        });