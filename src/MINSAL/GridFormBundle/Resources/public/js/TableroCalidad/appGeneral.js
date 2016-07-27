function ifLoading($http) {
      return {
        restrict: 'A',
        link: function(scope, elem) {
            scope.isLoading = isLoading;

            scope.$watch(scope.isLoading, toggleElement);

            function toggleElement(loading) {
              (loading) ? elem.show() : elem.hide();           
            }

            function isLoading() {
              return $http.pendingRequests.length > 0;
            }
        }
      };
    }

    ifLoading.$inject = ['$http'];
    
var tableroCalidadApp = angular.module('tableroCalidadApp', ['serviciosGeneral', 'ui.bootstrap', 'chart.js'])
        .config(['$interpolateProvider', function ($interpolateProvider) {
                $interpolateProvider.startSymbol('[[');
                $interpolateProvider.endSymbol(']]');
            }])
        .directive('ifLoading', ifLoading)
        .controller('mainCtrl', function AppCtrl($scope, Indicadores, Periodos, DetalleIndicador) {
            $scope.options = {width: 300, height: 250, 'bar': 'aaa'};
            $scope.data = [0];
            $scope.hovered = function (d) {
                $scope.barValue = d;
                $scope.$apply();
            };
            $scope.filtroIndicador = '';
            $scope.filtroListadoIndicador = '';

            $scope.titulo = 'Monitoreo y Evaluación de la Gestión de la calidad en RIISS';
            $scope.subtitulo = 'Unidad Nacional Gestión de Calidad de la RIISS - VMSS';
            $scope.subtitulo2 = '';
            $scope.periodoSeleccionado = [];
            $scope.indicadorSeleccionado = [];
            $scope.datosGrafico2 = [];
            $scope.mostrarInfoIndicador = false;
            $scope.options_bar_line = {
                scales: {                                     
                    yAxes: [{ ticks: { min: 0, max: 100} }]
                }
            };
            
            

            $scope.periodos = Periodos.query()
                .$promise.then(
                    function (data) {
                        $scope.periodos = (data != '') ? data : [];

                        if (data.length > 0){
                            $scope.periodoSeleccionado = $scope.periodos[0];
                            $scope.subtitulo2 = 'Tablero General :: Periodo ' + $scope.periodoSeleccionado.etiqueta;
                        }
                    }
                );
            $scope.cambiar_periodo = function(){
                $scope.subtitulo2 = 'Tablero General :: Periodo ' + $scope.periodoSeleccionado.etiqueta;
                $scope.procesar();
            };
            
            $scope.procesar = function () {
                Indicadores.query({ periodo: $scope.periodoSeleccionado.periodo, tipo:1 })
                    .$promise.then(
                            function (data) {
                                $scope.indicadores = (data != '') ? data[0].datos : [];
                                $scope.indicadoresTop10 = (data != '') ? data[0].top10 : [];
                                $scope.indicadoresLess10 = (data != '') ? data[0].less10 : [];
                                $scope.filtroListadoIndicador = 'todos';
                                
                                $scope.datosGrafico1 = $scope.indicadores;
                                
                                $scope.labelsGrp1 = [];
                                $scope.valorGrp1 = [];
                                $scope.datosGrafico1.forEach(function(nodo, index){
                                    $scope.labelsGrp1.push(nodo.codigo_indicador);
                                    $scope.valorGrp1.push(nodo.calificacion);
                                });
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
            
            $scope.detalleArea = function(indicador){
                $scope.detalleIndicador = indicador;

                DetalleIndicador.query({ periodo: $scope.periodoSeleccionado.periodo, id: indicador.id })
                    .$promise.then(
                            function (data) {
                                $scope.detalle = (data != '') ? data[0] : [];
                                $('#modalDetalleIndicador').modal('show');
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
                $scope.labelsGrp1 = [];
                $scope.valorGrp1 = [];
                $scope.datosGrafico1.forEach(function(nodo, index){
                    $scope.labelsGrp1.push(nodo.codigo_indicador);
                    $scope.valorGrp1.push(nodo.calificacion);
                });
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
                
                indicadorSel.evaluacion.forEach(function(nodo, index){
                    labels.push(nodo.nombre_corto);
                    valorEstandar.push(indicadorSel.calificacion);
                    valorEstablecimiento.push(nodo.calificacion);
                });
                $scope.datosGrafico2 = [];
                $scope.labels = labels;
                $scope.datosGrafico2.push(valorEstablecimiento);
                $scope.datosGrafico2.push(valorEstandar); 
                
            };
        });
var app = tableroCalidadApp;