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
    var tableroCalidadApp = angular.module('tableroCalidadApp', ['servicios', "chart.js"])
        .config(['$interpolateProvider', function ($interpolateProvider) {
                $interpolateProvider.startSymbol('[[');
                $interpolateProvider.endSymbol(']]');
            }])
        .directive('ifLoading', ifLoading)
        .controller('mainCtrl', function AppCtrl ($scope, Periodos, Establecimientos, Evaluaciones, Criterios, HistorialEstablecimiento) {
            $scope.options = {width: 300, height: 250, 'bar': 'aaa'};
            $scope.data = [0];
            $scope.hovered = function(d){
                $scope.barValue = d;
                $scope.$apply();
            };
            $scope.periodoSeleccionado = '';
            $scope.mostrarListadoEstablecimientos = true;
            $scope.fila = 1;
            $scope.establecimientoSeleccionado = '';
            $scope.evaluacionSeleccionada = '';
            $scope.mes_ = '';
            $scope.titulo_grafico1 = 'Establecimientos vs porcentaje cumplimiento'
            
            $scope.titulo = 'Monitoreo y Evaluación de Calidad';
            $scope.establecimientos = [];
            $scope.datosGrafico1 = [];
            $scope.datosGrafico2 =  [];
            $scope.evaluaciones = [];
            $scope.criterios = [];
            
            //Las areas del gráfico
            $scope.capameta = 1;
            $scope.calificaciones = [];
            $scope.metas = [];
            $scope.brechas = [];
            
            $scope.periodos = Periodos.query();
            
            $scope.getValorMes = function(criterio){
                return criterio['mes_check_'+$scope.mes_];
            };
            
            $scope.getAlertas =  function(criterio, cellValue){
                var estilo='';
                if (criterio.alertas != ''){
                    var tipo_control = criterio.codigo_tipo_control;
                    // Si es tiempo pasarlo a minutos
                    if (tipo_control == 'time'){
                        partes = cellValue.split(':');
                        valor = parseInt(partes[0]) * 60 + parseInt(partes[1]);
                    }
                    var rangos = criterio.alertas.split(',');
                    rangos.forEach(function(nodo, index){
                        var limites = nodo.split('-');
                        //Si no existe alguno de los límites del rango ponerle valores 
                        // muy grandes (si falta lim sup) o muy pequeño( si falta el lim inf)
                        limites[0] = (limites[0]=='') ? -1000000 : limites[0]; 
                        limites[1] = (limites[1]=='') ? 1000000 : limites[1];
                        if (!(isNaN(valor))){
                            if (valor >= parseFloat(limites[0]) && parseFloat(valor) <= parseFloat(limites[1])){                                                    
                                estilo = "border-style: solid; border-color:"+limites[2]+ " white ; color: " +limites[2]+" ; font-size:14pt; font-weight: bold; ";
                                criterio.rango = limites[2];
                            }
                        }
                    });
                }
                return estilo;
            };
            
            $scope.getValorExpediente = function(exp){
                var respuesta='';
                if (exp.search(':') !== -1){
                    valor = exp.split(':');
                    valor.shift();
                    respuesta =  valor.join(':');
                } else {
                    respuesta = exp.trim();
                }
                return (respuesta == 'NaN') ? '': respuesta;
            };
            
            $scope.getExpedientes = function(criterio){
                var expedientes = [];
                angular.forEach(criterio, function(value, key) {
                    if (key.search('num_expe_') !== -1 || key.search('cant_mensual') !== -1 || key.search('dias_mes') !== -1 || key.search('mes_check') !== -1 )
                        this.push(key+":"+value);
                }, expedientes);
                
                return expedientes;
            };
            
            $scope.getEstablecimientos = function() {
                $scope.mes_ = ($scope.periodoSeleccionado.mes < 10) ? '0' + $scope.periodoSeleccionado.mes : $scope.periodoSeleccionado.mes ;
                
                Establecimientos.query({ periodo: $scope.periodoSeleccionado.periodo })
                    .$promise.then(
                        function (data) {                            
                            $scope.establecimientos = (data != '') ? data : [];
                            $scope.datosGrafico1 = $scope.establecimientos;
                            $scope.evaluaciones = [];
                            $scope.titulo_grafico1 = 'Establecimientos vs porcentaje cumplimiento';
                            $scope.criterios = [];                            
                        },
                        function (error) {
                            alert(error);
                        }
                    );
            };
           
            
            $scope.mostrarEvaluacionesComplementarias = function(establecimiento) {
                $scope.establecimientoEvalExt = establecimiento;
                $('#modalEvaluacionesComplementarias').modal('show')
            };
            $scope.getEvaluaciones = function(establecimientoSel) {
                $scope.establecimientoSeleccionado = establecimientoSel;
                $scope.mostrarListadoEstablecimientos = false;
                
                Evaluaciones.query({ establecimiento: establecimientoSel.establecimiento, periodo: $scope.periodoSeleccionado.periodo })
                    .$promise.then(
                        function (data) {                            
                            $scope.evaluaciones = (data != '') ? data : [];
                            var aux = [];
                                     
                            $scope.calificaciones = data;
                            var datos = JSON.stringify(data);
                            
                            datos = datos.replace(/"value":/g,'"valueant":');
                            $scope.metas = JSON.parse(datos.replace(/"meta":/g,'"value":'));
                            angular.forEach($scope.metas, function(value, key) {
                                    this.categoria = 'Meta';
                            }, $scope.metas);
                            
                            aux.push($scope.calificaciones);
                            aux.push($scope.metas);

                            $scope.datosGrafico2 = aux;
                            $scope.criterios = [];
                        },
                        function (error) {
                            alert(error);
                        }
                    );
                             
                //Cambiar el gráfico 1
                $scope.titulo_grafico1 = 'Cumplimiento por mes';
                $scope.titulo = 'Evaluación de Calidad :: '+ establecimientoSel.nombre;
                HistorialEstablecimiento.query({ establecimiento: establecimientoSel.establecimiento,
                                                periodo: $scope.periodoSeleccionado.periodo,})
                    .$promise.then(
                        function (data) {                            
                            $scope.datosGrafico1 = (data != '') ? data : [];
                        },
                        function (error) {
                            alert(error);
                        }
                    );                
            };
            
            $scope.getCriterios = function(evaluacionSel) {
                $scope.evaluacionSeleccionada = evaluacionSel;
                Criterios.query(
                        { establecimiento: $scope.establecimientoSeleccionado.establecimiento, 
                            periodo: $scope.periodoSeleccionado.periodo,
                            evaluacion: evaluacionSel.codigo
                        })
                        .$promise.then(
                        function (data) {                            
                            $scope.criterios = data;
                            $scope.labels_rec = [];
                            $scope.data_rec = [];
                            $scope.series_rec = ['Series A'];
                            $scope.datasetOverride_rec = [{ xAxisID: 'x-axis-1' }, { xAxisID: 'x-axis-2' }];
                            $scope.options_rec = {
                              scales: {                                    
                                    xAxes: [
                                        {
                                          id: 'x-axis-1',
                                          type: 'linear',
                                          display: true,
                                          position: 'top',
                                          ticks: {
                                                    min: 0,
                                                    max: 100
                                                }
                                        },
                                        {
                                          id: 'x-axis-2',
                                          type: 'linear',
                                          display: true,
                                          position: 'bottom',
                                          ticks: {
                                                    min: 0,
                                                    max: 100
                                                }
                                        }
                                    ]
                                }
                            };
                            angular.forEach(data[0].resumen_criterios, function(value, key) {
                                    this.push(value.descripcion_variable);
                                    $scope.data_rec.push(value.porc_cumplimiento);
                            }, $scope.labels_rec);
                            
                        },
                        function (error) {
                            alert(error);
                        }
                    );
            };
        });