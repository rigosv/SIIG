// Para los controles de cuadro desplegable que tenga el atributo
// multiple, no cerrarlos al seleccionar las opciones
$(function() {
    $('select[multiple="multiple"]').select2({
        closeOnSelect: false,
        width: '100%'
    });
});
    