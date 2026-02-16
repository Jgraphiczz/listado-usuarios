jQuery(document).ready(function($) {
    
    // Cargar usuarios al iniciar
    cargarUsuarios();
    
    // Envio del formulario de búsqueda
    $('#form-buscar-usuarios').on('submit', function(e) {
        e.preventDefault();
        cargarUsuarios(1);
    });
    
    // Boton limpiar busqueda
    $('#limpiar-busqueda').on('click', function() {
        $('#form-buscar-usuarios')[0].reset();
        cargarUsuarios(1);
    });
    
    // Manejar clicks en botones de paginación
    $(document).on('click', '.pagina-btn', function() {
        var pagina = $(this).data('pagina');
        cargarUsuarios(pagina);
    });
    
    // Cargar usuarios
    function cargarUsuarios(pagina) {
        // Página por defecto
        if (!pagina) {
            pagina = 1;
        }
        
        // Obtener valores del formulario
        var datos = {
            action: 'buscar_usuarios',
            nonce: ajaxData.nonce,
            nombre: $('#buscar-nombre').val(),
            apellidos: $('#buscar-apellidos').val(),
            email: $('#buscar-email').val(),
            pagina: pagina
        };
        
        // Mostrar indicador de carga
        $('#cargando').show();
        $('#contenedor-resultados').html('');
        
        // Petición AJAX
        $.ajax({
            url: ajaxData.url,
            type: 'POST',
            data: datos,
            success: function(respuesta) {
                console.log(respuesta);
                // Ocultar de cargaa
                $('#cargando').hide();
                
                if (respuesta.success) {
                    // Mostrar tabla de usuarios
                    $('#contenedor-resultados').html(respuesta.data.tabla);
                    
                    // Mostrar paginacoin
                    if (respuesta.data.paginacion) {
                        $('#contenedor-resultados').append(respuesta.data.paginacion);
                    }
                    
                    // Mostrar total de usuarios
                    if (respuesta.data.total > 0) {
                        var mensaje = '<p style="text-align: center; margin-top: 10px; color: #666;">Total: ' + 
                                     respuesta.data.total + ' usuarios encontrados</p>';
                        $('#contenedor-resultados').append(mensaje);
                    }
                } else {
                    $('#contenedor-resultados').html('<p class="sin-resultados">Error al cargar usuarios.</p>');
                }
            },
            error: function() {
                $('#cargando').hide();
                $('#contenedor-resultados').html('<p class="sin-resultados">Error en la solicitud AJAX.</p>');
            }
        });
    }
});
