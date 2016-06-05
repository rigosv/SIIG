var tableroCalidadApp = angular.module('tableroCalidadApp', ['serviciosGeneral', 'ui.bootstrap', 'n3-line-chart'])
        .config(['$interpolateProvider', function ($interpolateProvider) {
                $interpolateProvider.startSymbol('[[');
                $interpolateProvider.endSymbol(']]');
            }])

        .controller('mainCtrl', function AppCtrl($scope, Indicadores, Periodos) {
            $scope.options = {width: 300, height: 250, 'bar': 'aaa'};
            $scope.data = [0];
            $scope.hovered = function (d) {
                $scope.barValue = d;
                $scope.$apply();
            };
            $scope.filtroIndicador = '';
            $scope.filtroListadoIndicador = '';

            $scope.titulo = 'Monitoreo y Evaluación de la Calidad - Tablero General';
            $scope.periodoSeleccionado = [];
            $scope.indicadorSeleccionado = [];
            $scope.datosGrafico2 = [];
            $scope.mostrarInfoIndicador = false;
            
            $scope.data = {
                dataset0: [
                  {x: 0, val_0: 0, val_1: 0, val_2: 0, val_3: 0},
                  {x: 1, val_0: 0.993, val_1: 3.894, val_2: 8.47, val_3: 14.347},
                  {x: 2, val_0: 1.947, val_1: 7.174, val_2: 13.981, val_3: 19.991},
                  {x: 3, val_0: 2.823, val_1: 9.32, val_2: 14.608, val_3: 13.509},
                  {x: 4, val_0: 3.587, val_1: 9.996, val_2: 10.132, val_3: -1.167},
                  {x: 5, val_0: 4.207, val_1: 9.093, val_2: 2.117, val_3: -15.136},
                  {x: 6, val_0: 4.66, val_1: 6.755, val_2: -6.638, val_3: -19.923},
                  {x: 7, val_0: 4.927, val_1: 3.35, val_2: -13.074, val_3: -12.625}
                ]
            };

          $scope.options = {
            series: [
              {
                axis: "y",
                dataset: "dataset0",
                key: "val_0",
                label: "Calificación establecimiento",
                color: "#1f77b4",
                type: ['column'],
                id: 'mySeries0'
              },
              {
                axis: "y",
                dataset: "dataset0",
                key: "val_1",
                label: "Valor del indicador",
                color: "rgb(126, 181, 63)",
                type: ['line', 'dot'],
                id: 'mySeries1'
              }
            ],
            axes: {x: {key: "x"}}
          };

            $scope.periodos = Periodos.query()
                .$promise.then(
                    function (data) {
                        $scope.periodos = (data != '') ? data : [];

                        if (data.length > 0){
                            $scope.periodoSeleccionado = $scope.periodos[0];
                            $scope.titulo = $scope.titulo + ' :: Periodo ' + $scope.periodoSeleccionado.mes + '/' + $scope.periodoSeleccionado.anio;
                            //$('#selectPeriodo').val(data[0].periodo).trigger("change");;
                        }
                    }
                );

            $scope.procesar = function () {
                //$scope.mes_ = ($scope.periodoSeleccionado.mes < 10) ? '0' + $scope.periodoSeleccionado.mes : $scope.periodoSeleccionado.mes ;
                Indicadores.query({ periodo: $scope.periodoSeleccionado.periodo, tipo:1 })
                    .$promise.then(
                            function (data) {
                                $scope.indicadores = (data != '') ? data[0].datos : [];
                                $scope.indicadoresTop10 = (data != '') ? data[0].top10 : [];
                                $scope.indicadoresLess10 = (data != '') ? data[0].less10 : [];
                                $scope.filtroListadoIndicador = 'todos';
                                
                                $scope.datosGrafico1 = $scope.indicadores;
                            },
                            function (error) {
                                alert(error);
                            }
                    );
            
                 Indicadores.query({ periodo: $scope.periodoSeleccionado.periodo, tipo:2 })
                    .$promise.then(
                            function (data) {
                                $scope.indicadores2 = (data != '') ? data : [];
                            },
                            function (error) {
                                alert(error);
                            }
                    );
            };
            
            $scope.cambiarGrafico1 = function(filtro){
                if (filtro === 'todos'){
                    $scope.datosGrafico1 = $scope.indicadores;
                }else if (filtro === '+10'){
                    $scope.datosGrafico1 = $scope.indicadoresTop10;
                }else if (filtro === '-10'){
                    $scope.datosGrafico1 = $scope.indicadoresLess10;
                }
                $scope.$apply();
            };
            $scope.seleccionarIndicador = function(indicadorSel){
                $scope.indicadorSeleccionado = indicadorSel;
                $scope.mostrarInfoIndicador = true;
                //var datos = [];                
                
            };
        });
var app = tableroCalidadApp;