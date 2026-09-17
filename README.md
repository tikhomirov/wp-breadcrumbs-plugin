# WP Breadcrumbs

[![Version](https://img.shields.io/badge/version-1.0.2-blue.svg)](https://github.com/rwsite/wp-breadcrumbs-plugin/releases)
[![WordPress](https://img.shields.io/badge/WordPress-4.6%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-8.0%2B-purple.svg)](https://php.net/)

Lightweight WordPress breadcrumbs with **Schema.org JSON-LD** markup. Outputs an accessible HTML trail for posts, pages, taxonomies, archives, search, and 404 — plus structured data for search engines.

## Requirements

| Component | Minimum | Tested |
|-----------|---------|--------|
| **WordPress** | 4.6 | 6.6 – 6.8 |
| **PHP** | 8.0 | 8.2, 8.3 |

## Features

- **HTML breadcrumbs** — `breadcrumbs()` template tag and `[breadcrumbs]` shortcode
- **Schema.org BreadcrumbList** — JSON-LD injected in `<head>` via `SchemaOrgBreadCrumbs`
- **Post types** — posts (with category chain), pages (with ancestors), attachments, custom post types
- **Archives** — categories, tags, authors, date archives, custom taxonomies
- **Filters** — `breadcrumbs_args` (defaults) and `breadcrumbs_filter` (final HTML)
- **i18n** — text domain `breadcrumbs`, bundled `.mo` files in `languages/`

## Installation

### Composer (recommended for rwsite projects)

```bash
composer require rwsite/wp-breadcrumbs-plugin
```

The package installs to `wp-content/plugins/wp-breadcrumbs-plugin/` via `composer/installers`.

### Manual

1. Download the [latest release](https://github.com/rwsite/wp-breadcrumbs-plugin/releases).
2. Upload to `wp-content/plugins/wp-breadcrumbs-plugin/`.
3. Activate **Breadcrumbs** in WordPress admin.

## Usage

### Template tag

```php
if (function_exists('breadcrumbs')) {
    echo breadcrumbs();
}
```

### Shortcode

```
[breadcrumbs]
```

### Customize defaults

```php
add_filter('breadcrumbs_args', function (array $args): array {
    $args['home_title'] = 'Home';
    $args['separator_icon'] = '/';
    $args['breadcrumbs_classes'] = 'breadcrumb my-trail';

    return $args;
});
```

### Modify output HTML

```php
add_filter('breadcrumbs_filter', function (string $html): string {
    return '<nav aria-label="Breadcrumb">' . $html . '</nav>';
});
```

## Arguments

| Key | Default | Description |
|-----|---------|-------------|
| `separator_icon` | `&gt;` | Separator between crumbs |
| `breadcrumbs_id` | `breadcrumbs` | Root element `id` |
| `breadcrumbs_classes` | `breadcrumb-trail breadcrumbs` | Root element `class` |
| `home_title` | `Главная` | Homepage link label |

## Development

```bash
composer install
vendor/bin/phpcs
```

## Architecture

```
wp-breadcrumbs-plugin/
├── wp-breadcrumbs-plugin.php   # Bootstrap, breadcrumbs(), shortcode
├── SchemaOrgBreadCrumbs.php    # JSON-LD BreadcrumbList builder
├── languages/                  # Translations
└── CHANGELOG.md
```

HTML trail logic lives in `breadcrumbs()`. Structured data is built independently in `SchemaOrgBreadCrumbs` on `wp` — both stay in sync for supported views.

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## License

GPL-2.0-or-later

## Author

Aleksey Tikhomirov — [rwsite.ru](https://rwsite.ru)

---

## Русский

**WP Breadcrumbs** — лёгкие хлебные крошки для WordPress с разметкой **Schema.org JSON-LD**.

### Совместимость

- **WordPress:** от 4.6, протестировано на 6.6 – 6.8
- **PHP:** от 8.0, протестировано на 8.2, 8.3

### Что умеет

- HTML-цепочка через `breadcrumbs()` или шорткод `[breadcrumbs]`
- JSON-LD `BreadcrumbList` в `<head>` для SEO
- Записи (с цепочкой рубрик), страницы, вложения, произвольные типы записей
- Архивы: рубрики, метки, авторы, даты, таксономии
- Фильтры `breadcrumbs_args` и `breadcrumbs_filter`

### Установка

Скачайте [релиз](https://github.com/rwsite/wp-breadcrumbs-plugin/releases) или подключите через Composer. Активируйте плагин **Breadcrumbs** в админке WordPress.

### Пример в шаблоне темы

```php
<?php if (function_exists('breadcrumbs')) {
    echo breadcrumbs();
} ?>
```
