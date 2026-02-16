<?php
/**
 * Plugin Name: Listado de Usuarios
 * Description: Muestra un listado paginado de usuarios en el panel de administración
 * Version: 1.0
 * Author: Jesús David Lario Bermejo
 */

if (!defined('ABSPATH')) {
    exit;
}

class ListadoUsuarios {
    
    public function __construct() {
        // Agregar menú en el administrador
        add_action('admin_menu', array($this, 'agregar_menu'));
        
        // Cargar CSS y JS solo en nuestra página
        add_action('admin_enqueue_scripts', array($this, 'cargar_recursos'));
        
        // Manejar peticiones AJAX
        add_action('wp_ajax_buscar_usuarios', array($this, 'buscar_usuarios'));
    }
    
    // Agregar pestaña en el menú de administración
    public function agregar_menu() {
        add_menu_page(
            'Listado de Usuarios',
            'Listado Usuarios',
            'manage_options',
            'listado-usuarios',
            array($this, 'mostrar_pagina'),
            'dashicons-groups',
            30
        );
    }
    
    // Cargar CSS y JavaScript
    public function cargar_recursos($hook) {
        if ($hook != 'toplevel_page_listado-usuarios') {
            return;
        }
        wp_enqueue_style('listado-usuarios-css', plugin_dir_url(__FILE__) . 'listado-usuarios.css');
        wp_enqueue_script('listado-usuarios-js', plugin_dir_url(__FILE__) . 'listado-usuarios.js', array('jquery'), '1.0', true);
        
        wp_localize_script('listado-usuarios-js', 'ajaxData', array(
            'url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('usuarios_nonce')
        ));
    }
    
    // Mostrar la página en el administrador
    public function mostrar_pagina() {
        ?>
        <div class="wrap">
            <h1>Listado de Usuarios</h1>
            
            <!-- Formulario de búsqueda -->
            <div class="formulario-busqueda">
                <h2>Buscar Usuarios</h2>
                <form id="form-buscar-usuarios">
                    <div class="campos-busqueda">
                        <div class="campo">
                            <label>Nombre:</label>
                            <input type="text" id="buscar-nombre" name="nombre" placeholder="Buscar por nombre">
                        </div>
                        <div class="campo">
                            <label>Apellidos:</label>
                            <input type="text" id="buscar-apellidos" name="apellidos" placeholder="Buscar por apellidos">
                        </div>
                        <div class="campo">
                            <label>Email:</label>
                            <input type="text" id="buscar-email" name="email" placeholder="Buscar por email">
                        </div>
                    </div>
                    <div class="botones">
                        <button type="submit" class="button button-primary">Buscar</button>
                        <button type="button" id="limpiar-busqueda" class="button">Limpiar</button>
                    </div>
                </form>
            </div>
            
            <!-- Loader -->
            <div id="cargando" style="display:none;">Cargando usuarios...</div>
            
            <div id="contenedor-resultados"></div>
        </div>
        <?php
    }
    
    // Manejar búsqueda AJAX
    public function buscar_usuarios() {
        check_ajax_referer('usuarios_nonce', 'nonce');
        
        // Obtener datos del formulario
        $nombre = isset($_POST['nombre']) ? sanitize_text_field($_POST['nombre']) : '';
        $apellidos = isset($_POST['apellidos']) ? sanitize_text_field($_POST['apellidos']) : '';
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $pagina = isset($_POST['pagina']) ? intval($_POST['pagina']) : 1;
        
        $respuesta_api = $this->simular_api($nombre, $apellidos, $email);
        
        //Paginación
        $usuarios_por_pagina = 5;
        $total_usuarios = count($respuesta_api['usuarios']);
        $total_paginas = ceil($total_usuarios / $usuarios_por_pagina);
        $inicio = ($pagina - 1) * $usuarios_por_pagina;
        
        $usuarios_pagina = array_slice($respuesta_api['usuarios'], $inicio, $usuarios_por_pagina);
        
        $html_tabla = $this->generar_tabla($usuarios_pagina);
        
        $html_paginacion = $this->generar_paginacion($pagina, $total_paginas);
        
        // Enviar respuesta
        wp_send_json_success(array(
            'tabla' => $html_tabla,
            'paginacion' => $html_paginacion,
            'total' => $total_usuarios
        ));
    }
    
    // Simular respuesta de API
    private function simular_api($nombre = '', $apellidos = '', $email = '') {
        $todos_usuarios = array(
            array('id' => 1, 'name' => 'Juan', 'surname1' => 'García', 'surname2' => 'López', 'email' => 'juan.garcia@test.com'),
            array('id' => 2, 'name' => 'María', 'surname1' => 'Martínez', 'surname2' => 'Sánchez', 'email' => 'maria.martinez@test.com'),
            array('id' => 3, 'name' => 'Pedro', 'surname1' => 'Rodríguez', 'surname2' => 'Fernández', 'email' => 'pedro.rodriguez@test.com'),
            array('id' => 4, 'name' => 'Ana', 'surname1' => 'López', 'surname2' => 'González', 'email' => 'ana.lopez@test.com'),
            array('id' => 5, 'name' => 'Carlos', 'surname1' => 'Sánchez', 'surname2' => 'Pérez', 'email' => 'carlos.sanchez@test.com'),
            array('id' => 6, 'name' => 'Laura', 'surname1' => 'Fernández', 'surname2' => 'Martín', 'email' => 'laura.fernandez@test.com'),
            array('id' => 7, 'name' => 'David', 'surname1' => 'González', 'surname2' => 'Ruiz', 'email' => 'david.gonzalez@test.com'),
            array('id' => 8, 'name' => 'Sofía', 'surname1' => 'Pérez', 'surname2' => 'Díaz', 'email' => 'sofia.perez@test.com'),
            array('id' => 9, 'name' => 'Miguel', 'surname1' => 'Martín', 'surname2' => 'Moreno', 'email' => 'miguel.martin@test.com'),
            array('id' => 10, 'name' => 'Elena', 'surname1' => 'Ruiz', 'surname2' => 'Jiménez', 'email' => 'elena.ruiz@test.com'),
            array('id' => 11, 'name' => 'Javier', 'surname1' => 'Díaz', 'surname2' => 'Navarro', 'email' => 'javier.diaz@test.com'),
            array('id' => 12, 'name' => 'Carmen', 'surname1' => 'Moreno', 'surname2' => 'Torres', 'email' => 'carmen.moreno@test.com'),
            array('id' => 13, 'name' => 'Antonio', 'surname1' => 'Jiménez', 'surname2' => 'Álvarez', 'email' => 'antonio.jimenez@test.com'),
            array('id' => 14, 'name' => 'Isabel', 'surname1' => 'Navarro', 'surname2' => 'Romero', 'email' => 'isabel.navarro@test.com'),
            array('id' => 15, 'name' => 'Francisco', 'surname1' => 'Torres', 'surname2' => 'Ramos', 'email' => 'francisco.torres@test.com'),
            array('id' => 16, 'name' => 'Rosa', 'surname1' => 'Álvarez', 'surname2' => 'Vázquez', 'email' => 'rosa.alvarez@test.com'),
            array('id' => 17, 'name' => 'Manuel', 'surname1' => 'Romero', 'surname2' => 'Castro', 'email' => 'manuel.romero@test.com'),
            array('id' => 18, 'name' => 'Pilar', 'surname1' => 'Ramos', 'surname2' => 'Suárez', 'email' => 'pilar.ramos@test.com')
        );
        
        // Filtrar usuarios según búsqueda
        $usuarios_filtrados = array_filter($todos_usuarios, function($usuario) use ($nombre, $apellidos, $email) {
            $cumple = true;
            
            //Filtro por nombre
            if (!empty($nombre)) {
                $cumple = $cumple && (stripos($usuario['name'], $nombre) !== false);
            }
            
            // Filtro por apellidos
            if (!empty($apellidos)) {
                $apellidos_completos = $usuario['surname1'] . ' ' . $usuario['surname2'];
                $cumple = $cumple && (stripos($apellidos_completos, $apellidos) !== false);
            }
            
            // Filtro por email
            if (!empty($email)) {
                $cumple = $cumple && (stripos($usuario['email'], $email) !== false);
            }
            
            return $cumple;
        });
        
        return array('usuarios' => array_values($usuarios_filtrados));
    }
    
    // Generar HTML de la tabla
    private function generar_tabla($usuarios) {
        if (empty($usuarios)) {
            return '<p class="sin-resultados">No se encontraron usuarios.</p>';
        }
        
        $html = '<table class="wp-list-table widefat fixed striped">';
        
        // Encabezado
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>Nombre Usuario</th>';
        $html .= '<th>Nombre</th>';
        $html .= '<th>Apellido 1</th>';
        $html .= '<th>Apellido 2</th>';
        $html .= '<th>Correo Electrónico</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        
        // Cuerpo
        $html .= '<tbody>';
        foreach ($usuarios as $usuario) {
            $nombre_usuario = strtolower($usuario['name']);
            
            $html .= '<tr>';
            $html .= '<td>' . esc_html($nombre_usuario) . '</td>';
            $html .= '<td>' . esc_html($usuario['name']) . '</td>';
            $html .= '<td>' . esc_html($usuario['surname1']) . '</td>';
            $html .= '<td>' . esc_html($usuario['surname2']) . '</td>';
            $html .= '<td>' . esc_html($usuario['email']) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody>';
        
        $html .= '</table>';
        
        return $html;
    }
    
    // Generar HTML de paginación
    private function generar_paginacion($pagina_actual, $total_paginas) {
        if ($total_paginas <= 1) {
            return '';
        }
        
        $html = '<div class="paginacion">';
        
        // Botón anterior
        if ($pagina_actual > 1) {
            $html .= '<button class="button pagina-btn" data-pagina="' . ($pagina_actual - 1) . '">« Anterior</button>';
        }
        
        // Números de página
        for ($i = 1; $i <= $total_paginas; $i++) {
            $clase = ($i == $pagina_actual) ? 'button-primary' : 'button';
            $html .= '<button class="button ' . $clase . ' pagina-btn" data-pagina="' . $i . '">' . $i . '</button>';
        }
        
        // Botón siguiente
        if ($pagina_actual < $total_paginas) {
            $html .= '<button class="button pagina-btn" data-pagina="' . ($pagina_actual + 1) . '">Siguiente »</button>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
}

// Inicializar el plugin
new ListadoUsuarios();
