<?php
/**
 * Временный файл для отладки проверки обновлений
 * Откройте в браузере: /wp-content/plugins/gotosocial/check-updates.php
 */

// Загружаем WordPress - ищем wp-load.php
$wp_load_paths = [
    __DIR__ . '/../../../../wp-load.php',  // Стандартная структура
    __DIR__ . '/../../../wp-load.php',      // Альтернативная структура
    dirname(dirname(dirname(dirname(__DIR__)))) . '/wp-load.php'
];

$wp_loaded = false;
foreach ($wp_load_paths as $path) {
    if (file_exists($path)) {
        require_once($path);
        $wp_loaded = true;
        break;
    }
}

if (!$wp_loaded) {
    die('WordPress not found. Tried paths: ' . implode(', ', $wp_load_paths));
}

// Проверяем, что пользователь - администратор
if (!current_user_can('manage_options')) {
    die('Access denied');
}

echo '<h1>Проверка системы обновлений GoToSocial</h1>';
echo '<pre>';

// 1. Проверяем текущую версию плагина
$plugin_data = get_plugin_data(__DIR__ . '/gotosocial.php');
echo "\n=== ТЕКУЩАЯ ВЕРСИЯ ===\n";
echo "Установленная версия: " . $plugin_data['Version'] . "\n";

// 2. Проверяем наличие библиотеки
echo "\n=== БИБЛИОТЕКА UPDATE CHECKER ===\n";
$update_checker_file = __DIR__ . '/lib/plugin-update-checker/plugin-update-checker.php';
echo "Путь: $update_checker_file\n";
echo "Существует: " . (file_exists($update_checker_file) ? 'ДА' : 'НЕТ') . "\n";

// 3. Очищаем кэш обновлений
echo "\n=== ОЧИСТКА КЭША ===\n";
delete_site_transient('update_plugins');
echo "Транзиент update_plugins удален\n";

// 4. Принудительно проверяем обновления
echo "\n=== ПРОВЕРКА ОБНОВЛЕНИЙ ===\n";
wp_update_plugins();
echo "Функция wp_update_plugins() выполнена\n";

// 5. Получаем информацию об обновлениях
$updates = get_site_transient('update_plugins');
echo "\n=== ДОСТУПНЫЕ ОБНОВЛЕНИЯ ===\n";
if (isset($updates->response['gotosocial/gotosocial.php'])) {
    $update = $updates->response['gotosocial/gotosocial.php'];
    echo "Найдено обновление!\n";
    echo "Новая версия: " . $update->new_version . "\n";
    echo "URL пакета: " . $update->package . "\n";
    print_r($update);
} else {
    echo "Обновлений не найдено\n";
    echo "\nВсе обновления:\n";
    print_r($updates);
}

// 6. Проверяем настройку Update Checker
echo "\n=== PLUGIN UPDATE CHECKER ===\n";
if (class_exists('YahnisElsts\PluginUpdateChecker\v5\PucFactory')) {
    echo "Класс PucFactory загружен: ДА\n";
    
    // Пробуем получить экземпляр Update Checker
    global $myUpdateChecker;
    if (isset($myUpdateChecker)) {
        echo "Update Checker инициализирован: ДА\n";
        
        // Принудительная проверка через библиотеку
        $update_state = $myUpdateChecker->checkForUpdates();
        if ($update_state !== null) {
            echo "\nИнформация об обновлении:\n";
            echo "Версия: " . $update_state->version . "\n";
            echo "Download URL: " . $update_state->download_url . "\n";
        }
    } else {
        echo "Update Checker инициализирован: НЕТ\n";
    }
} else {
    echo "Класс PucFactory загружен: НЕТ\n";
}

echo "\n=== ИНСТРУКЦИИ ===\n";
echo "1. Убедитесь, что релиз v1.1.1 создан на GitHub\n";
echo "   https://github.com/dimadodonov/gotosocial-wordpress-plugin/releases\n";
echo "2. После этого скрипта перейдите в Плагины\n";
echo "3. Обновите страницу принудительно (Ctrl+F5)\n";

echo '</pre>';

echo '<p><a href="/wp-admin/plugins.php">Перейти к списку плагинов</a></p>';
