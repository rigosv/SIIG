var tableroCalidadApp = angular.module('tableroCalidadApp', ['servicios'])
        .config(['$interpolateProvider', function ($interpolateProvider) {
                $interpolateProvider.startSymbol('[[');
                $interpolateProvider.endSymbol(']]');
            }])

        .controller('mainCtrl', function AppCtrl ($scope, Periodos, Establecimientos, Evaluaciones, Criterios, HistorialEstablecimiento) {
            $scope.options = {width: 300, height: 250, 'bar': 'aaa'};
            $scope.data = [0];
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
            
            //Las areas del gráfico
            $scope.capameta = 1;
            $scope.calificaciones = [];
            $scope.metas = [];
            $scope.brechas = [];
            
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
                            var aux = [];
                                     
                            $scope.calificaciones = $scope.evaluaciones
                            var datos = JSON.stringify($scope.evaluaciones);
                            
                            datos = datos.replace(/"value":/g,'"valueant":');
                            $scope.metas = JSON.parse(datos.replace(/"meta":/g,'"value":'));
                            angular.forEach($scope.metas, function(value, key) {
                                    this.categoria = 'Meta';
                            }, $scope.metas);
                            $scope.brechas = JSON.parse(datos.replace(/"brecha":/g,'"value":'));
                            angular.forEach($scope.brechas, function(value, key) {
                                    this.categoria = 'Brecha';
                            }, $scope.brechas);
                            
                            aux.push($scope.calificaciones);
                            aux.push($scope.metas);
                            aux.push($scope.brechas);

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
            
            $scope.toggleLabel = function(control, activo){
                var aux = [];                
                //var vacio = [];
                //var aux_vacio = $scope.calificaciones[0];
                var metas = $scope.metas;
                var brechas = $scope.brechas;
                
                /*angular.forEach(aux_vacio, function(value, key) {
                    this.value = 0;
                }, aux_vacio);
                vacio.push(aux_vacio);*/
                
                if (control == 'estandar'){
                    metas = (activo == true ) ? metas : [];
                }else if (control == 'brecha'){
                    brechas = (activo == true ) ? brechas : [];
                }
                
                aux.push($scope.calificaciones);
                aux.push(metas);
                aux.push(brechas);
                
                $scope.datosGrafico2 = aux;
                $scope.$apply();
            }
            
            $('input').on('ifChecked', function(event){
                $scope.toggleLabel($(this).val(), true);
            });
            $('input').on('ifUnchecked', function(event){
                $scope.toggleLabel($(this).val(), false);
            });
        })
        ;