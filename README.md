# GiantWP Discount Rules

Dynamic Pricing & BOGO Deals for WooCommerce — built with Vue 3 + Vite.

## Changelog

### 1.2.14

**Added**
- Discount Analytics Dashboard — see which rules are driving revenue, how often they apply, and total discount given. All in one place, with a bar chart and per-rule breakdown table.
- Product Badge now shows on single product pages too

**Fixed**
- Badges weren't showing for BOGO, Bulk, and Buy X Get Y — product condition data was being read incorrectly

**Improved**
- Settings page has a cleaner layout with upcoming features shown inline

---

### 1.2.13 - 2026-04-26

**Added**
- Product Badge feature: show a discount badge on product images in the shop and on single product pages
- Badge background color and text color customization from Settings page (with live preview)
- WooCommerce default sale badge is hidden when our badge is enabled to avoid duplicates
- Badge supports all rule types: BOGO (BUY 1 GET 1), Flat/Percentage (X% OFF / SALE), Bulk Discount (BULK DEAL), Buy X Get Y (SPECIAL OFFER)
- Badge shows on all products for global rules (no specific product selected); specific rules always take priority
- Smart Order Bump added to the sidebar recommended plugins list
- `.vscode` and `.idea` folders excluded from WordPress SVN deployment via `.distignore`

**Fixed**
- BOGO badge not showing: `buyProduct` field is an array of condition objects `{ field, operator, value }`, not a flat ID array — parsing corrected
- Bulk Discount badge not showing: same fix applied to `buyProducts` field
- Buy X Get Y badge now correctly reads both `buyProduct` and `getProduct` condition objects
- Category-targeted rules now correctly expand category IDs to product IDs for badge display

**Improved**
- Settings page redesigned to a compact row-based layout (label + description on left, control on right)
- Inter font enforced consistently across all admin UI including Element Plus components
- Sidebar plugins list updated: removed "GiantWP Solutions" self-entry, added Smart Order Bump
- GitHub → SVN deploy confirmed as manual-only (triggered by GitHub Release publish, not on push)
- First Purchase Discount quick-start template now pre-fills condition: customer order count < 1
- Free Shipping and Bulk Buy quick-start templates pre-fill correct field values
- Conditions.vue prop-sync watchers fixed to prevent infinite re-render loop

---

### 1.2.12

- Update plugin assets
- Minor frontend changes

### 1.2.9 - 2024-12-27

- Fixed: Minor bug fixes and stability improvements
- Added: BOGO badge now displays in cart to highlight free/discounted items

### 1.2.7 - 2024-11-08

- Fixed: Minor bugs and compatibility issues
- Added: Better integration with popular themes and plugins

### 1.2.6 - 2024-10-28

- Added: Complete translation support (.pot file) for PHP and Vue.js components
- Fixed: Added translator comments to comply with WordPress standards
- Improved: Overall translation readiness for multilingual stores

### 1.2.3 - 2024-10-28

- Updated: Plugin naming for better clarity

### 1.2.0 - 2024-10-27

- Added: Upsell notification feature for product pages
- Updated: Complete UI refresh with improved user experience
- Improved: WooCommerce compatibility and performance

### 1.1.5 - 2024-10-17

- Improved: Minor updates and data syncing enhancements

### 1.1.4 - 2024-08-15

- Fixed: Various bugs identified by users
- Improved: Compatibility with latest WooCommerce version

### 1.1.0 - 2024-04-11

- Fixed: Various bugs and improvements
- Improved: Overall plugin stability

### 1.0.4

- Initial stable release
- Added: Flat/Percentage discount support
- Added: Advanced condition builder
- Added: Scheduling and usage limit features
- Added: Modern Vue.js admin interface
