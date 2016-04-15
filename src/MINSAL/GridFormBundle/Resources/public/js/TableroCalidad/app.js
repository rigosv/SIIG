var tableroCalidadApp = angular.module('tableroCalidadApp', ['servicios'])
        .config(['$interpolateProvider', function ($interpolateProvider) {
                $interpolateProvider.startSymbol('[[');
                $interpolateProvider.endSymbol(']]');
            }])

        .controller('mainCtrl', function AppCtrl ($scope, Periodos, Establecimientos, Evaluaciones, Criterios, HistorialEstablecimiento) {
            $scope.options = {width: 300, height: 250, 'bar': 'aaa'};
            $scope.data = [1, 2, 3, 4];
            $scope.hovered = function(d){
                $scope.barValue = d;
                $scope.$apply();
            };
            $scope.periodoSeleccionado = '';
            $scope.fila = 1;
            $scope.establecimientoSeleccionado = '';
            $scope.evaluacionSeleccionada = '';
            $scope.mes_ = '';
            $scope.titulo_grafico1 = 'Establecimientos vs porcentaje cumplimiento'
            
            $scope.titulo = 'Evaluación de Calidad';
            $scope.establecimientos = [];
            $scope.datosGrafico1 = [];
            $scope.datosGrafico2 =  [];
            $scope.evaluaciones = [];
            $scope.criterios = [];
            
            $scope.periodos = Periodos.query();
            
            $scope.getValorMes = function(criterio){
                return criterio['mes_check_'+$scope.mes_];
            };
            
            $scope.getValorExpediente = function(exp){
                var respuesta='';
                if (exp.search(':') !== -1){
                    valor = exp.split(':');
                    respuesta =  valor[1].trim();
                } else {
                    respuesta = exp.trim();
                }
                return respuesta;
            };
            
            $scope.getExpedientes = function(criterio){
                var expedientes = [];
                angular.forEach(criterio, function(value, key) {
                    if (key.search('num_expe_') !== -1)
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
            
            $scope.getEvaluaciones = function(establecimientoSel) {
                $scope.establecimientoSeleccionado = establecimientoSel;
                
                
                Evaluaciones.query({ establecimiento: establecimientoSel.id_establecimiento, periodo: $scope.periodoSeleccionado.periodo })
                    .$promise.then(
                        function (data) {                            
                            $scope.evaluaciones = (data != '') ? data : [];
                            var metas = [];
                            var aux = [];
                                                        
                            var datos = JSON.stringify($scope.evaluaciones);
                            datos = datos.replace(/"value":/g,'"valueant":');
                            metas = JSON.parse(datos.replace(/"meta":/g,'"value":'));                            
                            
                            aux.push($scope.evaluaciones);
                            aux.push(metas);

                            $scope.datosGrafico2 = aux;
                            $scope.criterios = [];
                        },
                        function (error) {
                            alert(error);
                        }
                    );
                
                //Cambiar el gráfico 1
                $scope.titulo_grafico1 = 'Cumplimiento por mes';
                $scope.titulo = 'Evaluación de Calidad :: '+establecimientoSel.nombre;
                HistorialEstablecimiento.query({ establecimiento: establecimientoSel.id_establecimiento})
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
                $scope.criterios = Criterios.query(
                        { establecimiento: $scope.establecimientoSeleccionado.id_establecimiento, 
                            periodo: $scope.periodoSeleccionado.periodo,
                            evaluacion: evaluacionSel.codigo
                        });                
            };            
            
            
        })
        ;