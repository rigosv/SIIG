var servicios = angular.module('servicios', ['ngResource']);

servicios.factory('Periodos', ['$resource',
  function($resource){
    return $resource(Routing.generate('get_periodos_evaluacion'), {}, {
      query: {method:'GET', params:{}, isArray:true}
    });
  }]);
  
servicios.factory('Establecimientos', ['$resource',
  function($resource){
    return $resource(Routing.generate('get_establecimientos_evaluados')+'/:periodo/:nivel', {}, {
      query: {method:'GET', params:{periodo: '@_id', nivel:'@_idn'}, isArray:true}
    });
  }]);
  
servicios.factory('Evaluaciones', ['$resource',
  function($resource){
    return $resource(Routing.generate('get_evaluaciones')+'/:establecimiento/:periodo', {}, {
      query: {method:'GET', params:{establecimiento: '@_ide', periodo: '@_idp'}, isArray:true}
    });
  }]);
  
servicios.factory('Criterios', ['$resource',
  function($resource){
    return $resource(Routing.generate('get_criterios')+'/:establecimiento/:periodo/:evaluacion', {}, {
      query: {method:'GET', params:{establecimiento: '@_ide', periodo: '@_idp', evaluacion : '@_ip'}, isArray:true}
    });
  }]);
  
  servicios.factory('EncabezadoFrm', ['$resource',
  function($resource){
    return $resource(Routing.generate('get_encabezado')+'/:establecimiento/:periodo/:evaluacion', {}, {
      query: {method:'GET', params:{establecimiento: '@_ide', periodo: '@_idp', evaluacion : '@_ip'}, isArray:true}
    });
  }]);
  
servicios.factory('HistorialEstablecimiento', ['$resource',
  function($resource){
    return $resource(Routing.generate('get_historial_establecimiento')+'/:establecimiento/:periodo', {}, {
      query: {method:'GET', params:{establecimiento: '@_ide',  periodo: '@_idp'}, isArray:true}
    });
  }]);