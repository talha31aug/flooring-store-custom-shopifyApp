# Shopify Dynamic Pricing Calculator App (Session-Based Product Creation)

A custom Shopify app that dynamically calculates pricing based on user input (area, packs, wastage, etc.) and creates **temporary session-based products** using the Shopify API. This ensures customers are charged the **exact calculated amount** instead of static product pricing.

---

## 🚀 Features

* Dynamic price calculation based on:

  * Area (m²)
  * Pack size
  * Wastage toggle
  * Pack quantity
* Session-based product creation via Shopify API
* Automatic cleanup of temporary products
* Cart monitoring and synchronization
* Prevent duplicate cart items
* Sample order limitation logic
* Stock-aware pack calculation
* Custom line item properties (Packs Required)
* LocalStorage + SessionStorage tracking
* Real-time UI updates

---

## 🧠 Problem This Solves

Shopify products normally have **fixed pricing**, but flooring and similar industries require:

* Pricing per m²
* Pack-based selling
* Waste calculations
* Custom area input
* Dynamic totals

This app bridges that gap by:

1. Calculating the final price in frontend
2. Creating a temporary product via Shopify API
3. Adding that product to cart
4. Deleting unused products automatically

---

## 🧩 Technologies Used

### Backend

* **Laravel** — Core backend framework for API endpoints
* **Osiset Shopify App Package** — Shopify OAuth authentication & app structure
* Shopify Admin API — Dynamic product creation & deletion
* REST API endpoints — Session-based product lifecycle management

### Frontend

* JavaScript (ES6)
* jQuery
* Shopify Liquid
* AJAX / Fetch API

### Data Handling

* LocalStorage — Session ID tracking
* SessionStorage — Temporary product tracking
* CSRF Protection (Laravel)

### Shopify Integrations

* Shopify Storefront Cart API
* Shopify Admin Product API
* Line Item Properties
* Product Metafields
* Variant manipulation

---

## ⚙️ How It Works

### Step 1 — Customer Inputs Area

User enters required area (m²) and optionally adds wastage.

### Step 2 — Price Calculation

Script calculates:

* Packs required
* Total coverage
* Wastage
* Final price

### Step 3 — Temporary Product Creation

A new product is created via Shopify API:

* Custom price
* Session ID
* Image
* Title
* Variant

### Step 4 — Add to Cart

The newly created product is added to cart with:

* Correct quantity
* Line item properties
* Session tracking

### Step 5 — Cleanup

Products are automatically deleted when:

* Cart becomes empty
* Session expires
* User removes item

---

## 🔐 Session Handling

Each visitor gets a unique session:

```
user_timestamp_randomString
```

Stored in:

* localStorage (24 hours)
* sessionStorage (product tracking)

This ensures:

* Only user's products are managed
* No cross-user conflicts
* Clean product lifecycle

---

## 🧮 Pricing Logic

The calculator supports:

* Price per m²
* Pack size
* Wastage %
* Minimum packs
* Stock validation
* Dynamic display pricing

Example logic:

```
Total Area = Area + Wastage
Packs = ceil(Total Area / Pack Size)
Total Price = Packs × Pack Price
```

---

## 🛒 Cart Monitoring

The script listens to:

* cart/add.js
* cart/change.js
* cart/update.js
* cart/clear.js

When user removes items:

* Session products are automatically deleted
* No orphan products remain in Shopify

---

## 🎯 Sample Limitation Feature

Limits sample variant additions:

* Max 3 samples per cart
* Prevents checkout abuse
* Displays user-friendly message

---

## 🧩 Technologies Used

* Shopify Storefront API
* Shopify Admin API
* JavaScript (ES6)
* jQuery
* Liquid
* AJAX
* LocalStorage
* SessionStorage

---

## 📂 Use Cases

Perfect for:

* Flooring stores
* Tile shops
* Fabric stores
* Wallpaper stores
* Custom measurement products
* Print-on-demand area pricing

---

## 🧹 Automatic Product Cleanup

Products are removed when:

* Cart empty
* Item removed
* Session expired
* Page unload
* Periodic background check

This keeps Shopify catalog clean.

---

## 🔧 Setup Requirements

* Shopify Store
* Custom App
* Admin API access
* Product metafields:

  * price_in_meters
  * pack_size
  * pack_price
  * compare_price

---

## 📸 Example UI

* Area input
* Waste toggle
* Packs calculator
* Dynamic pricing
* Add to cart
* Sample limitation

---

## 🏁 Result

✔ Accurate pricing
✔ No manual calculations
✔ Clean Shopify catalog
✔ Better UX
✔ Higher conversion rate

---

## 👨‍💻 Author

Built by a Shopify & eCommerce developer focused on advanced pricing logic and custom store functionality.

---

## 📜 License

MIT — Free to use and modify.
