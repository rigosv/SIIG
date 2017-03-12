$(document).ready(function () {
    $('.dropdown-submenu>a').addClass('dropdown-toggle');
    $('.dropdown-submenu>a').attr('data-toggle', 'dropdown');
    $('ul.dropdown-menu [data-toggle=dropdown]').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        $(this).parent().siblings().removeClass('open');
        $(this).parent().toggleClass('open');
    });
});