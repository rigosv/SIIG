var tableroCalidadApp = angular.module('tableroCalidadApp', ['serviciosGeneral', 'ui.bootstrap', 'chart.js'])
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
                $scope.colors = ['#45b7cd', '#ff6384', '#ff8e72'];
                $scope.datasetOverride = [
                    {
                      label: "Calificación establecimiento",
                      borderWidth: 1,
                      type: 'bar'
                    },
                    {
                      label: "Calificación estándar",
                      borderWidth: 3,
                      hoverBackgroundColor: "rgba(255,99,132,0.4)",
                      hoverBorderColor: "rgba(255,99,132,1)",
                      type: 'line'
                    }
                ];
                
                var labels = [];
                var valorEstandar = [];
                var valorEstablecimiento = [];
                
                /*for(var i = 0; i < indicadorSel.evaluacion.lenght; i++){
                    var f = indicadorSel.evaluacion[i];
                    
                }*/
                indicadorSel.evaluacion.forEach(function(nodo, index){
                    labels.push(nodo.nombre_corto);
                    valorEstandar.push(indicadorSel.calificacion);
                    valorEstablecimiento.push(nodo.calificacion);
                });
                
                $scope.labels = labels;
                $scope.datosGrafico2.push(valorEstandar); 
                $scope.datosGrafico2.push(valorEstablecimiento);
                        
                
            };
        });
var app = tableroCalidadApp;