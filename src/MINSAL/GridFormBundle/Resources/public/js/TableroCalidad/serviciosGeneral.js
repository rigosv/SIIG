var serviciosGeneral = angular.module('serviciosGeneral', ['ngResource']);

serviciosGeneral.factory('Periodos', ['$resource',
  function($resource){
    return $resource(Routing.generate('get_periodos_evaluacion'), {}, {
      query: {method:'GET', params:{}, isArray:true}
    });
  }]);
    
serviciosGeneral.factory('Indicadores', ['$resource',
  function($resource){
    return $resource(Routing.generate('get_indicadores_calidad_evaluados')+'/:periodo/:tipo/:nivel/:departamento', {}, {
      query: {method:'GET', params:{periodo: '@_id', tipo: '@_idt', nivel: '@_idn', departamento:'@_idn'}, isArray:true}
    });
  }]);
  
serviciosGeneral.factory('DetalleIndicador', ['$resource',
  function($resource){
    return $resource(Routing.generate('get_detalle_indicador_calidad')+'/:periodo/:id/:nivel/:departamento', {}, {
      query: {method:'GET', params:{periodo: '@_id', id: '@_idi', nivel: '@_idn', departamento:'@_idn'}, isArray:true}
    });
  }]);
  
serviciosGeneral.factory('EvaluacionesComplementarias', ['$resource',
  function($resource){
    return $resource(Routing.generate('get_evaluaciones_complementarias')+'/:nivel/:departamento', {}, {
      query: {method:'GET', params:{nivel: '@_idn', departamento:'@_idn'}, isArray:true}
    });
  }]);
 