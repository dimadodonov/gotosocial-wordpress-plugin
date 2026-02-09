<?php
/**
 * Uninstall GoToSocial Widget
 * 
 * Удаление всех данных плагина из базы данных при удалении плагина
 */

// Если файл вызван не через WordPress, прекращаем выполнение
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Список всех опций плагина
$options = array(
    'gotosocial_enabled',
    'gotosocial_button_color',
    'gotosocial_position',
    'gotosocial_bottom_offset',
    'gotosocial_side_offset',
    'gotosocial_hide_mobile',
    'gotosocial_telegram',
    'gotosocial_whatsapp',
    'gotosocial_max',
    'gotosocial_vk',
    'gotosocial_instagram',
    'gotosocial_viber',
    'gotosocial_pinterest',
);

// Удаляем все опции из базы данных
foreach ($options as $option) {
    delete_option($option);
}

// Для multisite - удаляем опции из всех сайтов сети
if (is_multisite()) {
    global $wpdb;
    
    $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
    
    foreach ($blog_ids as $blog_id) {
        switch_to_blog($blog_id);
        
        foreach ($options as $option) {
            delete_option($option);
        }
        
        restore_current_blog();
    }
}

// Очистка кэша (опционально)
wp_cache_flush();
