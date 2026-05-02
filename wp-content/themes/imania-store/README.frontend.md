# Imania Store Frontend Conventions

## Design System
- Tokens live in `assets/css/imania-theme.css` under `:root` variables (`--im-*`).
- Bootstrap is used only for grid classes (`container`, `row`, `col-*`).
- Visual style is custom CSS (no Bootstrap component classes).

## Components
- Home components are in `template-parts/home/`:
  - `hero.php`
  - `product-section.php`
  - `product-card.php`
- Shared utility classes follow `imania-*` namespace.

## Dynamic Data
- Home helpers live in `inc/home.php`.
- Product sections use WooCommerce queries via `wc_get_products`/`WC_Product_Query`.
- Price output must use `$product->get_price_html()` to respect `imania-pricing-engine` rules.
