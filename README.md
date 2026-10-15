# Kopi Kita — Coffee Shop Ordering Web App

## Identity

| Name                          | NRP           |
|-------------------------------|---------------|
| Ahmad Farras Favian Al Efasi  | YOUR_NRP_HERE |

Web Programming — Section 7 Case Study

---

## 1. Case study

A coffee shop owner needs a web application for ordering coffee.
- An **administrator** can add products, set images, and set prices.
- A **customer who registers and logs in** gets a **10% discount** on the order.
- A **visitor who orders without logging in** (guest) pays the **regular price**.

## 2. Features

| Feature                                   | Guest | Customer | Admin |
|-------------------------------------------|:-----:|:--------:|:-----:|
| View menu and add items to cart           | Yes   | Yes      | Yes   |
| Place an order                            | Yes (regular price) | Yes (**10% off**) | Yes (regular price) |
| Register / log in                         | Yes   | -        | -     |
| View own order history                    | No    | Yes      | -     |
| Add / edit / delete products, upload images, set prices | No | No | Yes |
| View all orders                           | No    | No       | Yes   |

## 3. How the discount works

1. The cart is stored in `$_SESSION['cart']` (product id → quantity), so guests can order without an account.
2. When the cart or checkout is shown, `calc_totals()` in `functions.php` checks the session: **only a logged-in user with role `customer`** gets `MEMBER_DISCOUNT_PERCENT` (10%).
3. At checkout, prices are **re-read from the database** (never trusted from the browser), the discount is calculated on the server, and the order is saved with `subtotal`, `discount` and `total`.
4. If a guest logs in or registers while the cart is not empty, the cart is kept and the discount applies immediately.
5. Order items store a **snapshot** of the product name and price, so changing or deleting a product later does not change past orders.

## 4. Database design

```
users (id, name, email UNIQUE, password hash, role: admin|customer)
products (id, name, description, price, image)
orders (id, user_id NULL for guests, customer_name, subtotal, discount, total, created_at)
order_items (id, order_id → orders, product_id → products, product_name, unit_price, qty)
```

- `orders.user_id` is `NULL` for guest orders (`ON DELETE SET NULL`).
- `order_items.order_id` uses `ON DELETE CASCADE`; `order_items.product_id` uses `ON DELETE SET NULL`.

## 5. Security

- **Prepared statements** (`mysqli_prepare`) for every query with user input (SQL injection).
- **`htmlspecialchars()`** (helper `e()`) on all output (XSS).
- **`password_hash()` / `password_verify()`**; `session_regenerate_id(true)` on login.
- **Server-side role check**: every page in `/admin` calls `require_admin()` (403 for non-admins). The role of new users is always `customer`, never read from the form.
- **Safe image upload**: size limit (2 MB), real content check with `getimagesize()`, allowed types only (JPG/PNG/WEBP/GIF), random file name, and `uploads/.htaccess` blocks script execution.
- **State-changing actions use POST** (add to cart, delete product, checkout).
- **Order pages** can only be opened by the person who just ordered, the order owner, or an admin.

## 6. Project structure

```
coffee-shop/
├── config.php          # database connection
├── database.sql        # schema + sample products
├── seed_admin.php      # creates the first admin (run once, then delete)
├── functions.php       # session, helpers, discount logic, cart loader
├── header.php          # shared page top + navbar
├── footer.php
├── index.php           # public menu
├── cart.php            # cart + checkout
├── order_success.php   # order confirmation
├── register.php
├── login.php
├── logout.php
├── my_orders.php       # customer order history
├── uploads/            # product images (.htaccess blocks scripts)
└── admin/
    ├── index.php           # product list
    ├── product_form.php    # add / edit product + image upload
    ├── product_delete.php
    └── orders.php          # all orders
```

## 7. Setup (XAMPP)

1. Copy the `coffee-shop` folder to `C:\xampp\htdocs\coffee-shop`.
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Open phpMyAdmin → **Import** → choose `database.sql` → Go.
4. Check `config.php` (defaults: `localhost`, `root`, empty password, `db_coffee`, port 3306).
5. Open `http://localhost/coffee-shop/seed_admin.php` **once**, then **delete `seed_admin.php`**.
6. Open `http://localhost/coffee-shop/`.

Admin login: `admin@coffee.test` / `admin123` (change for real use).

## 8. Testing checklist

- [ ] As a guest, add items and place an order with a name → regular price, no discount
- [ ] Register a new account → logged in automatically, discount banner shown
- [ ] As that customer, place an order → 10% discount shown on cart, confirmation and My Orders
- [ ] Add items as a guest, then log in → cart is kept and the discount applies
- [ ] Log in as admin → add a product with an image and a price → it appears on the menu
- [ ] Edit a product price → the new price is used in new orders; old orders keep the old price
- [ ] Try uploading a `.php` or text file as the image → rejected
- [ ] As a customer or guest, open `admin/index.php` → redirected to login / 403
- [ ] Register with an existing email → "That email is already registered."
