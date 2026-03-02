# Axiom Meta Fields

Custom fields and meta boxes for WordPress.

## Features

- Custom post types and taxonomies
- Meta boxes with various field types (text, textarea, select, date, file, image, gallery, etc.)
- REST API endpoints
- Gutenberg blocks
- Template tags and shortcodes

## Requirements

- WordPress 5.8 or higher
- PHP 8.0 or higher
- MySQL 5.7 or higher / MariaDB 10.3 or higher

## Installation

1. Copy the `axiom-meta-fields` folder to your WordPress `wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Axiom Meta Fields' in the admin menu to start creating meta boxes

## Usage

### Register a Meta Box

```php
amf_register_meta_box([
    'id' => 'product_details',
    'title' => __('Product Details', 'amf'),
    'post_types' => ['product', 'post'],
    'fields' => [
        [
            'id' => 'price',
            'type' => 'number',
            'name' => __('Price', 'amf'),
        ],
        [
            'id' => 'sku',
            'type' => 'text',
            'name' => __('SKU', 'amf'),
        ],
    ],
]);

// or fluent: amf_meta_box('product_details')->title(...)->forPostTypes(...)->fields(...)->register();
```

### Register a Custom Post Type

```php
amf_register_post_type([
    'key' => 'product',
    'labels' => [
        'name' => __('Products', 'amf'),
        'singular_name' => __('Product', 'amf'),
    ],
    'public' => true,
    'show_in_rest' => true,
    'supports' => ['title', 'editor', 'thumbnail'],
]);
```

### Register a Custom Taxonomy

```php
amf_register_taxonomy([
    'key' => 'product_category',
    'labels' => [
        'name' => __('Product Categories', 'amf'),
        'singular_name' => __('Product Category', 'amf'),
    ],
    'post_types' => ['product'],
    'hierarchical' => true,
]);
```

### Template Tags

```php
// Get meta value
$price = amf_get_meta('price');

// Display meta value
amf_the_meta('price');

// Get field value
$field = amf_get_field_value('price');
```

### Shortcodes

```
[amf_meta key="price" post_id="123"]
[amf_gallery key="gallery_images" columns="3"]
[amf_relationship key="related_posts" display="title"]
[amf_meta_all template="table"]
```

## Project Structure

```
axiom-meta-fields/
├── axiom-meta-fields.php      # Main plugin file
├── uninstall.php               # Cleanup on uninstall
├── readme.txt                  # WordPress.org readme
├── assets/
│   ├── css/
│   │   ├── admin.css          # Admin styles
│   │   └── frontend.css       # Frontend styles
│   └── js/
│       └── admin.js           # Admin JavaScript
├── includes/
│   ├── Core/                  # Bootstrap, Container, Loader, Activation
│   ├── MetaBox/               # MetaBox registration, rendering, saving
│   ├── Fields/                # Field factory and field types
│   ├── PostType/              # Custom post type registration
│   ├── Taxonomy/              # Custom taxonomy registration
│   ├── Admin/                 # Menu, Settings, Notices
│   ├── API/REST/              # REST API controllers
│   ├── Frontend/              # Shortcodes, Template Tags, Blocks
│   ├── Providers/             # Service providers
│   ├── Traits/                # Singleton, Hookable, Cacheable
│   └── Helpers/               # Global helper functions
└── templates/
    └── admin/
        └── dashboard.php      # Admin dashboard
```

## REST API

The plugin provides REST API endpoints for:

- Meta operations: `GET/POST/DELETE /wp-json/amf/v1/meta/{post_type}/{id}`
- Meta boxes: `GET/POST/PUT/DELETE /wp-json/amf/v1/meta-boxes`
- Post types: `GET/POST/PUT/DELETE /wp-json/amf/v1/post-types`
- Taxonomies: `GET/POST/PUT/DELETE /wp-json/amf/v1/taxonomies`
- Fields: `GET /wp-json/amf/v1/field-types`

## License

GPL-2.0+
