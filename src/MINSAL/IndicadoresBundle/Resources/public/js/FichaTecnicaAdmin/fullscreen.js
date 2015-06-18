document.addEventListener("mozfullscreenchange", function () {
    if (!document.mozFullScreen) {
        restablecer();
    } else {
        aplicarZoom();
    }
}, false);
document.addEventListener("fullscreenchange", function () {
    fullscreenState.innerHTML = (document.fullscreenElement) ? "" : "not ";
    if (!document.fullscreenElement) {
        restablecer();
    }else {
        aplicarZoom();
    }
}, false);
document.addEventListener("msfullscreenchange", function () {
    if (!document.msFullscreenElement) {
        restablecer();
    }else {
        aplicarZoom();
    }
}, false);
document.addEventListener("webkitfullscreenchange", function () {
    if (!document.webkitIsFullScreen) {
        restablecer();
    }else {
        aplicarZoom();
    }
}, false);

function restablecer() {
    $('.zona_maximizada').toggleClass('zona_maximizada');
    $('.zoom').show();
    var zona = $(".zona_actual").attr('id');
    dibujarGraficoPrincipal(zona, $('#' + zona + ' .tipo_grafico_principal').val());
}

function aplicarZoom() {    
    var zona = $(".zona_actual").attr('id');
    dibujarGraficoPrincipal(zona, $('#' + zona + ' .tipo_grafico_principal').val());
}
function goFullscreen(id) {
    var element = document.getElementById(id);

    // These function will not exist in the browsers that don't support fullscreen mode yet, 
    // so we'll have to check to see if they're available before calling them.    
    if (element.requestFullscreen) {
        element.requestFullscreen();
    } else if (element.mozRequestFullScreen) {
        // This is how to go into fullscren mode in Firefox
        // Note the "moz" prefix, which is short for Mozilla.
        element.mozRequestFullScreen();
    } else if (element.webkitRequestFullScreen) {
        // This is how to go into fullscreen mode in Chrome and Safari
        // Both of those browsers are based on the Webkit project, hence the same prefix.
        element.webkitRequestFullScreen();
    } else if (element.msRequestFullscreen) {
        element.msRequestFullscreen();
    }    
}