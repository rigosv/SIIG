var serviciosGeneral = angular.module('serviciosGeneral', ['ngResource']);

serviciosGeneral.factory('Periodos', ['$resource',
  function($resource){
    return $resource(Routing.generate('get_periodos_evaluacion'), {}, {
      query: {method:'GET', params:{}, isArray:true}
    });
  }]);
    
serviciosGeneral.factory('Indicadores', ['$resource',
  function($resource){
    return $resource(Routing.generate('get_indicadores_calidad_evaluados')+'/:periodo/:tipo', {}, {
      query: {method:'GET', params:{periodo: '@_id', tipo: '@_idt'}, isArray:true}
    });
  }]);
 