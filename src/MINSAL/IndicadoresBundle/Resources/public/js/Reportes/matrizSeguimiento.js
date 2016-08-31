$(document).ready(function() {
    //$('#form_desde').MonthPicker({ShowIcon: false });
    //$('#form_hasta').MonthPicker({ShowIcon: false, MaxMonth: 0 });
    var i18nes = {
            year: 'Año',
            prevYear: 'Año previo',
            nextYear: 'Año siguiente',
            next12Years: 'Saltar 12 años adelante',
            prev12Years: 'Regresar 12 años',
            nextLabel: 'Siguiente',
            prevLabel: 'Anterior',
            buttonText: 'Open Month Chooser',
            jumpYears: 'Cambiar año',
            backTo: 'Regresar a',
            months: ['Ene.', 'Feb.', 'Mar.', 'Apr.', 'May', 'Jun', 'Jul', 'Ago.', 'Sep.', 'Oct.', 'Nov.', 'Dec.']
        }
    
    $('#inicio-div').MonthPicker({
        AltField: '#form_desde',
        SelectedMonth: 0,
        i18n: i18nes,
        MaxMonth: '0'
    });
    
    $('#fin-div').MonthPicker({
        AltField: '#form_hasta',
        SelectedMonth: 0,
        i18n: i18nes,
        MaxMonth: '0'
    });
    
});
