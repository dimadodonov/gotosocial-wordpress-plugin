<?php
/**
 * Plugin Name: GoToSocial Widget
 * Plugin URI: https://mitroliti.ru
 * Description: Плавающий виджет с кнопками социальных сетей и мессенджеров
 * Version: 1.1.0
 * Author: Mitroliti
 * Author URI: http://mitroliti.ru
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: gotosocial
 * 
 * ВНИМАНИЕ: При удалении плагина все настройки будут удалены из базы данных.
 * Деактивация плагина сохраняет все настройки для последующего использования.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Определяем константу для basename плагина
define('GOTOSOCIAL_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Подключение библиотеки автоматических обновлений (если доступна)
$update_checker_file = plugin_dir_path(__FILE__) . 'lib/plugin-update-checker/plugin-update-checker.php';
if (file_exists($update_checker_file)) {
    require $update_checker_file;
    
    $myUpdateChecker = YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
        'https://github.com/dimadodonov/gotosocial-wordpress-plugin/',
        __FILE__,
        'gotosocial'
    );

    // Установка ветки для проверки обновлений (GitHub по умолчанию использует 'main')
    $myUpdateChecker->setBranch('main');

    // Если репозиторий приватный, раскомментируйте и добавьте токен:
    // $myUpdateChecker->setAuthentication('your-github-token-here');
}

// Добавляем ссылку на настройки на странице плагинов
add_filter('plugin_action_links_' . GOTOSOCIAL_PLUGIN_BASENAME, 'gotosocial_add_settings_link');
function gotosocial_add_settings_link($links) {
    $settings_link = '<a href="options-general.php?page=gotosocial">Настройки</a>';
    array_unshift($links, $settings_link);
    return $links;
}

// // Добавляем дополнительные ссылки в описании плагина
// add_filter('plugin_row_meta', 'gotosocial_add_plugin_row_meta', 10, 2);
// function gotosocial_add_plugin_row_meta($links, $file) {
//     if (GOTOSOCIAL_PLUGIN_BASENAME === $file) {
//         $row_meta = array(
//             'docs' => '<a href="https://mitroliti.ru/docs/gotosocial" target="_blank">Документация</a>',
//             'support' => '<a href="https://mitroliti.ru/support" target="_blank">Поддержка</a>',
//         );
//         return array_merge($links, $row_meta);
//     }
//     return $links;
// }

class GoToSocial_Widget {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_footer', array($this, 'render_widget'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Подключение стилей и скриптов
     */
    public function enqueue_assets() {
        wp_enqueue_style(
            'gotosocial-styles',
            plugins_url('assets/css/gotosocial.css', __FILE__),
            array(),
            '1.0.5'
        );
        
        // Добавляем кастомные CSS переменные
        $button_color = get_option('gotosocial_button_color', '#C69843');
        $position = get_option('gotosocial_position', 'right');
        $bottom_offset = get_option('gotosocial_bottom_offset', '20');
        $side_offset = get_option('gotosocial_side_offset', '20');
        $hide_mobile = get_option('gotosocial_hide_mobile', '0');
        
        $custom_css = "
            :root {
                --gotosocial-color: {$button_color};
            }
            @keyframes pulse-gold {
                0% { box-shadow: 0 0 0 0 " . $this->hex_to_rgba($button_color, 0.7) . "; }
                70% { box-shadow: 0 0 0 30px " . $this->hex_to_rgba($button_color, 0) . "; }
                100% { box-shadow: 0 0 0 0 " . $this->hex_to_rgba($button_color, 0) . "; }
            }
            #gotosocial .gotosocial__btn {
                background: {$button_color} !important;
            }
            #gotosocial {
                bottom: {$bottom_offset}px !important;
                {$position}: {$side_offset}px !important;
                " . ($position === 'left' ? 'right: auto !important;' : 'left: auto !important;') . "
            }
            " . ($hide_mobile === '1' ? '@media (max-width: 768px) { #gotosocial { display: none !important; } }' : '') . "
        ";
        wp_add_inline_style('gotosocial-styles', $custom_css);
        
        wp_enqueue_script(
            'gotosocial-script',
            plugins_url('assets/js/gotosocial.js', __FILE__),
            array(),
            '1.0.5',
            true
        );
    }
    
    /**
     * Конвертация HEX в RGBA
     */
    private function hex_to_rgba($hex, $alpha = 1) {
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        return "rgba({$r}, {$g}, {$b}, {$alpha})";
    }
    
    /**
     * Вывод виджета в футере
     */
    public function render_widget() {
        $telegram = get_option('gotosocial_telegram', '');
        $whatsapp = get_option('gotosocial_whatsapp', '');
        $max = get_option('gotosocial_max', '');
        $vk = get_option('gotosocial_vk', '');
        $instagram = get_option('gotosocial_instagram', '');
        $viber = get_option('gotosocial_viber', '');
        $pinterest = get_option('gotosocial_pinterest', '');
        $enabled = get_option('gotosocial_enabled', '1');
        
        if ($enabled !== '1') {
            return;
        }
        
        // Проверяем, есть ли хотя бы одна активная ссылка
        $has_links = !empty($telegram) || !empty($whatsapp) || !empty($max) || !empty($vk) || !empty($instagram) || !empty($viber) || !empty($pinterest);
        
        if (!$has_links) {
            return;
        }
        
        include plugin_dir_path(__FILE__) . 'templates/widget.php';
    }
    
    /**
     * Добавление меню в админке
     */
    public function add_admin_menu() {
        add_options_page(
            'GoToSocial Settings',
            'GoToSocial',
            'manage_options',
            'gotosocial',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Регистрация настроек
     */
    public function register_settings() {
        register_setting('gotosocial_settings', 'gotosocial_enabled');
        register_setting('gotosocial_settings', 'gotosocial_button_color');
        register_setting('gotosocial_settings', 'gotosocial_position');
        register_setting('gotosocial_settings', 'gotosocial_bottom_offset');
        register_setting('gotosocial_settings', 'gotosocial_side_offset');
        register_setting('gotosocial_settings', 'gotosocial_hide_mobile');
        register_setting('gotosocial_settings', 'gotosocial_telegram');
        register_setting('gotosocial_settings', 'gotosocial_whatsapp');
        register_setting('gotosocial_settings', 'gotosocial_max');
        register_setting('gotosocial_settings', 'gotosocial_vk');
        register_setting('gotosocial_settings', 'gotosocial_instagram');
        register_setting('gotosocial_settings', 'gotosocial_viber');
        register_setting('gotosocial_settings', 'gotosocial_pinterest');
        
        // Секция внешнего вида
        add_settings_section(
            'gotosocial_appearance_section',
            'Настройки внешнего вида',
            array($this, 'appearance_section_callback'),
            'gotosocial'
        );
        
        add_settings_field(
            'gotosocial_enabled',
            'Включить виджет',
            array($this, 'checkbox_field_callback'),
            'gotosocial',
            'gotosocial_appearance_section',
            array('field' => 'gotosocial_enabled')
        );
        
        add_settings_field(
            'gotosocial_button_color',
            'Цвет кнопки',
            array($this, 'color_field_callback'),
            'gotosocial',
            'gotosocial_appearance_section',
            array('field' => 'gotosocial_button_color')
        );
        
        add_settings_field(
            'gotosocial_position',
            'Позиция виджета',
            array($this, 'select_field_callback'),
            'gotosocial',
            'gotosocial_appearance_section',
            array(
                'field' => 'gotosocial_position',
                'options' => array(
                    'right' => 'Справа',
                    'left' => 'Слева'
                )
            )
        );
        
        add_settings_field(
            'gotosocial_bottom_offset',
            'Отступ снизу (px)',
            array($this, 'number_field_callback'),
            'gotosocial',
            'gotosocial_appearance_section',
            array('field' => 'gotosocial_bottom_offset', 'placeholder' => '20', 'min' => '0', 'max' => '500')
        );
        
        add_settings_field(
            'gotosocial_side_offset',
            'Отступ от края (px)',
            array($this, 'number_field_callback'),
            'gotosocial',
            'gotosocial_appearance_section',
            array('field' => 'gotosocial_side_offset', 'placeholder' => '20', 'min' => '0', 'max' => '500')
        );
        
        add_settings_field(
            'gotosocial_hide_mobile',
            'Скрыть на мобильных',
            array($this, 'checkbox_field_callback'),
            'gotosocial',
            'gotosocial_appearance_section',
            array('field' => 'gotosocial_hide_mobile', 'description' => 'Скрыть виджет на экранах шириной менее 768px')
        );
        
        // Секция социальных сетей
        add_settings_section(
            'gotosocial_main_section',
            'Настройки социальных сетей',
            array($this, 'settings_section_callback'),
            'gotosocial'
        );
        
        add_settings_field(
            'gotosocial_telegram',
            'Telegram',
            array($this, 'text_field_callback'),
            'gotosocial',
            'gotosocial_main_section',
            array('field' => 'gotosocial_telegram', 'placeholder' => 'https://t.me/username')
        );
        
        add_settings_field(
            'gotosocial_whatsapp',
            'WhatsApp',
            array($this, 'text_field_callback'),
            'gotosocial',
            'gotosocial_main_section',
            array('field' => 'gotosocial_whatsapp', 'placeholder' => 'https://wa.me/1234567890')
        );
        
        add_settings_field(
            'gotosocial_max',
            'Max',
            array($this, 'text_field_callback'),
            'gotosocial',
            'gotosocial_main_section',
            array('field' => 'gotosocial_max', 'placeholder' => 'https://example.com')
        );
        
        add_settings_field(
            'gotosocial_vk',
            'VK',
            array($this, 'text_field_callback'),
            'gotosocial',
            'gotosocial_main_section',
            array('field' => 'gotosocial_vk', 'placeholder' => 'https://vk.com/username')
        );
        
        add_settings_field(
            'gotosocial_instagram',
            'Instagram',
            array($this, 'text_field_callback'),
            'gotosocial',
            'gotosocial_main_section',
            array('field' => 'gotosocial_instagram', 'placeholder' => 'https://instagram.com/username')
        );
        
        add_settings_field(
            'gotosocial_viber',
            'Viber',
            array($this, 'text_field_callback'),
            'gotosocial',
            'gotosocial_main_section',
            array('field' => 'gotosocial_viber', 'placeholder' => 'viber://chat?number=1234567890')
        );
        
        add_settings_field(
            'gotosocial_pinterest',
            'Pinterest',
            array($this, 'text_field_callback'),
            'gotosocial',
            'gotosocial_main_section',
            array('field' => 'gotosocial_pinterest', 'placeholder' => 'https://pinterest.com/username')
        );
    }
    
    /**
     * Вывод описания секции внешнего вида
     */
    public function appearance_section_callback() {
        echo '<p>Настройте внешний вид виджета.</p>';
    }
    
    /**
     * Вывод описания секции настроек
     */
    public function settings_section_callback() {
        echo '<p>Укажите ссылки на ваши социальные сети. Оставьте поле пустым, чтобы скрыть кнопку.</p>';
    }
    
    /**
     * Вывод поля выбора цвета
     */
    public function color_field_callback($args) {
        $field = $args['field'];
        $value = get_option($field, '#C69843');
        
        printf(
            '<input type="color" name="%s" value="%s" class="gotosocial-color-picker" />',
            esc_attr($field),
            esc_attr($value)
        );
        echo '<p class="description">Выберите цвет для кнопки виджета и анимации</p>';
    }
    
    /**
     * Вывод текстового поля
     */
    public function text_field_callback($args) {
        $field = $args['field'];
        $value = get_option($field, '');
        $placeholder = isset($args['placeholder']) ? $args['placeholder'] : '';
        
        printf(
            '<input type="text" name="%s" value="%s" class="regular-text" placeholder="%s" />',
            esc_attr($field),
            esc_attr($value),
            esc_attr($placeholder)
        );
    }
    
    /**
     * Вывод чекбокса
     */
    public function checkbox_field_callback($args) {
        $field = $args['field'];
        $value = get_option($field, '1');
        
        printf(
            '<input type="checkbox" name="%s" value="1" %s />',
            esc_attr($field),
            checked($value, '1', false)
        );
        
        if (isset($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }
    
    /**
     * Вывод выпадающего списка
     */
    public function select_field_callback($args) {
        $field = $args['field'];
        $value = get_option($field, 'right');
        $options = isset($args['options']) ? $args['options'] : array();
        
        echo '<select name="' . esc_attr($field) . '">';
        foreach ($options as $option_value => $option_label) {
            printf(
                '<option value="%s" %s>%s</option>',
                esc_attr($option_value),
                selected($value, $option_value, false),
                esc_html($option_label)
            );
        }
        echo '</select>';
        
        if (isset($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }
    
    /**
     * Вывод числового поля
     */
    public function number_field_callback($args) {
        $field = $args['field'];
        $value = get_option($field, '20');
        $placeholder = isset($args['placeholder']) ? $args['placeholder'] : '';
        $min = isset($args['min']) ? $args['min'] : '0';
        $max = isset($args['max']) ? $args['max'] : '1000';
        
        printf(
            '<input type="number" name="%s" value="%s" placeholder="%s" min="%s" max="%s" />',
            esc_attr($field),
            esc_attr($value),
            esc_attr($placeholder),
            esc_attr($min),
            esc_attr($max)
        );
        
        if (isset($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }
    
    /**
     * Страница настроек
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        if (isset($_GET['settings-updated'])) {
            add_settings_error(
                'gotosocial_messages',
                'gotosocial_message',
                'Настройки сохранены',
                'updated'
            );
        }
        
        settings_errors('gotosocial_messages');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?> <span style="font-size: 14px; color: #666;">v1.1.0</span></h1>
            
            <form action="options.php" method="post">
                <?php
                settings_fields('gotosocial_settings');
                do_settings_sections('gotosocial');
                submit_button('Сохранить настройки');
                ?>
            </form>
            
            <div style="margin-top: 30px; padding: 15px; background: #f9f9f9; border-left: 4px solid #C69843;">
                <h3 style="margin-top: 0;">💡 Полезные советы:</h3>
                <ul style="margin-bottom: 0;">
                    <li>Используйте акцентный цвет вашего сайта для кнопки виджета</li>
                    <li>Оставьте поле пустым, если не хотите отображать конкретную социальную сеть</li>
                    <li>Для WhatsApp используйте формат: <code>https://wa.me/1234567890</code></li>
                    <li>Для Viber используйте формат: <code>viber://chat?number=1234567890</code></li>
                </ul>
            </div>
            
            <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107;">
                <h3 style="margin-top: 0;">ℹ️ Важная информация:</h3>
                <p style="margin-bottom: 0;">
                    При <strong>деактивации</strong> плагина все настройки сохраняются. 
                    При <strong>удалении</strong> плагина все настройки будут полностью удалены из базы данных.
                </p>
            </div>
        </div>
        <?php
    }
}

// Инициализация плагина
GoToSocial_Widget::get_instance();
