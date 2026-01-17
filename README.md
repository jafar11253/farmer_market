# Farmer Market

A PHP-based marketplace that connects local farmers and buyers. Farmers can create listings for fresh produce (fixed price or auctions), and buyers can browse, bid, purchase, and review products. Admins manage users, commission settings, and reports.

## Features

admin password:
user:admin1
pass: 12345678
- User roles: **buyer**, **farmer**, **admin**
- Farmer dashboard to create and manage listings
- Buyer dashboard to browse products, add to cart, checkout, and track orders
- Optional auction-style listings with bids
- Rating & review system between buyers and farmers
- Configurable platform commission
- Admin panel for users, reports, and commission configuration

## Project Structure

- [index.php](index.php) – Landing page and entry point
- [db.sql](db.sql) – Database schema and seed data
- [includes/config.php](includes/config.php) – Database & base URL configuration, session helpers
- [includes/header.php](includes/header.php), [includes/footer.php](includes/footer.php) – Shared layout
- [assets/css/style.css](assets/css/style.css) – Global styles
- [assets/js/script.js](assets/js/script.js) – Client-side scripts
- [assets/img/uploads](assets/img/uploads) – Uploaded product images
- [pages/buyer](pages/buyer) – Buyer-facing pages (browse, cart, checkout, orders, reviews, tracking)
- [pages/farmer](pages/farmer) – Farmer-facing pages (dashboard, create & manage listings, profile, sales, reviews)
- [pages/admin](pages/admin) – Admin dashboard, users, reports, commission config

## Requirements

- PHP 7.4+ (PHP 8.x recommended)
- MySQL 5.7+ / MariaDB
- A web server (e.g., Apache via XAMPP on Windows)

## Local Setup (XAMPP)

1. **Clone / copy the project** into your XAMPP web root, for example:
   - `C:/xampp/htdocs/farmer_market`

2. **Create the database** in phpMyAdmin (or via MySQL CLI):
   - Create a database, e.g. `farm_market`.
   - Import [db.sql](db.sql) into that database.

3. **Configure database connection**:
   - Open [includes/config.php](includes/config.php).
   - For local development, uncomment the *LOCALHOST XAMPP SETTINGS* block and adjust host/user/password/db/port to match your local MySQL.
   - Comment out or adjust the *LIVE HOSTING SETTINGS* if you are not using them.

4. **Set base URL**:
   - In [includes/config.php](includes/config.php), set `BASE_URL`:
     - For XAMPP in a subfolder: `define('BASE_URL', '/farmer_market');`
     - For root hosting: `define('BASE_URL', '');`

5. **Start Apache and MySQL** from XAMPP control panel, then visit:
   - `http://localhost/farmer_market/`

## Default Accounts

The imported [db.sql](db.sql) includes sample users (emails and roles) with hashed passwords. You can either:

- Use the existing accounts if you know the passwords used when generating the hashes; or
- Register new accounts via [register.php](register.php), then manually update their `role` field in the `users` table (e.g. set to `admin`, `farmer`, or `buyer`).

## Main Flows

- **Registration & Login**:
  - [register.php](register.php) – user signup
  - [login.php](login.php) – authentication
  - [logout.php](logout.php) – end session

- **Farmer**:
  - [pages/farmer/dashboard.php](pages/farmer/dashboard.php)
  - [pages/farmer/create_listing.php](pages/farmer/create_listing.php)
  - [pages/farmer/manage_listings.php](pages/farmer/manage_listings.php)
  - [pages/farmer/sales.php](pages/farmer/sales.php)

- **Buyer**:
  - [pages/buyer/browse.php](pages/buyer/browse.php)
  - [pages/buyer/product.php](pages/buyer/product.php)
  - [pages/buyer/cart.php](pages/buyer/cart.php)
  - [pages/buyer/checkout.php](pages/buyer/checkout.php)
  - [pages/buyer/orders.php](pages/buyer/orders.php)
  - [pages/buyer/track.php](pages/buyer/track.php)

- **Admin**:
  - [pages/admin/dashboard.php](pages/admin/dashboard.php)
  - [pages/admin/users.php](pages/admin/users.php)
  - [pages/admin/reports.php](pages/admin/reports.php)
  - [pages/admin/config_commission.php](pages/admin/config_commission.php)

## Commission & Earnings

- Commission rate is stored in the `config` table (key `commission_rate`).
- Each `orders` record contains a `commission_deducted` field; related commission records are stored in `commission_earnings`.

## Security Notes

- Passwords are stored using PHP `password_hash` (bcrypt); ensure `password_verify` is used for login.
- Always keep `includes/config.php` out of public version control or strip real production credentials before sharing.

## Customization Ideas

- Integrate real payment gateways (bKash, Nagad, card) into checkout flow.
- Add search and filtering to the buyer browse page.
- Add pagination for listings and orders.
- Add email / SMS notifications for orders and bids.
