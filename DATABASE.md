# Документация базы данных GoToSocial Widget

## Опции плагина в wp_options

Плагин сохраняет следующие опции в таблице `wp_options`:

### Настройки внешнего вида

| Option Name                  | Type   | Default   | Description                                    |
| ---------------------------- | ------ | --------- | ---------------------------------------------- |
| `gotosocial_enabled`         | string | '1'       | Включение/выключение виджета ('1' или '0')     |
| `gotosocial_button_color`    | string | '#C69843' | HEX цвет кнопки виджета                        |
| `gotosocial_position`        | string | 'right'   | Позиция виджета: 'left' (слева) или 'right' (справа) |
| `gotosocial_bottom_offset`   | string | '20'      | Отступ снизу в пикселях (высота отображения)   |
| `gotosocial_side_offset`     | string | '20'      | Отступ от левого или правого края в пикселях   |
| `gotosocial_hide_mobile`     | string | '0'       | Скрыть на мобильных устройствах ('1' или '0')  |

### Ссылки на социальные сети

| Option Name            | Type   | Default | Description             |
| ---------------------- | ------ | ------- | ----------------------- |
| `gotosocial_phone`     | string | ''      | URI телефона (tel:+7...)  |
| `gotosocial_email`     | string | ''      | URI email (mailto:...)   |
| `gotosocial_telegram`  | string | ''      | URL профиля Telegram    |
| `gotosocial_whatsapp`  | string | ''      | URL/URI WhatsApp        |
| `gotosocial_vk`        | string | ''      | URL профиля VK          |
| `gotosocial_instagram` | string | ''      | URL профиля Instagram   |
| `gotosocial_viber`     | string | ''      | URI Viber               |
| `gotosocial_pinterest` | string | ''      | URL профиля Pinterest   |
| `gotosocial_max`       | string | ''      | Пользовательская ссылка |

## Удаление данных

При удалении плагина через админ-панель WordPress автоматически запускается файл `uninstall.php`, который:

1. Удаляет все опции из таблицы `wp_options`
2. Для multisite установок - удаляет опции из всех сайтов сети
3. Очищает кэш WordPress

## Деактивация vs Удаление

**Деактивация плагина:**

- Плагин перестаёт работать
- Все настройки сохраняются в базе данных
- При повторной активации все настройки восстанавливаются

**Удаление плагина:**

- Плагин полностью удаляется
- Все настройки удаляются из базы данных
- Восстановление невозможно без резервной копии

## SQL примеры

### Просмотр всех настроек плагина

```sql
SELECT * FROM wp_options
WHERE option_name LIKE 'gotosocial_%';
```

### Ручное удаление всех настроек (если нужно)

```sql
DELETE FROM wp_options
WHERE option_name IN (
    'gotosocial_enabled',
    'gotosocial_button_color',
    'gotosocial_position',
    'gotosocial_bottom_offset',
    'gotosocial_side_offset',
    'gotosocial_hide_mobile',
    'gotosocial_phone',
    'gotosocial_email',
    'gotosocial_telegram',
    'gotosocial_whatsapp',
    'gotosocial_max',
    'gotosocial_vk',
    'gotosocial_instagram',
    'gotosocial_viber',
    'gotosocial_pinterest'
);
```

### Экспорт настроек (резервная копия)

```sql
SELECT option_name, option_value
FROM wp_options
WHERE option_name LIKE 'gotosocial_%'
INTO OUTFILE '/tmp/gotosocial_backup.csv'
FIELDS TERMINATED BY ','
ENCLOSED BY '"'
LINES TERMINATED BY '\n';
```

## Размер данных

Приблизительный размер данных в базе:

- 1 опция ≈ 100-200 байт (включая служебные поля)
- Всего опций: 15
- Общий размер: ≈ 1500-3000 байт (< 3 KB)

Плагин имеет минимальное влияние на размер базы данных.

## Autoload

Все опции имеют `autoload = 'yes'`, что означает:

- Загружаются при каждом запросе WordPress
- Кэшируются в памяти для быстрого доступа
- Из-за малого размера не влияют на производительность

## Безопасность

Все данные проходят через:

- `sanitize_text_field()` при сохранении
- `esc_url()` для URL при выводе
- `esc_attr()` для атрибутов при выводе
- `esc_html()` для текста при выводе

## Multisite

Для WordPress Multisite:

- Опции сохраняются отдельно для каждого сайта сети
- При удалении плагина очищаются все сайты автоматически
- Настройки не синхронизируются между сайтами
