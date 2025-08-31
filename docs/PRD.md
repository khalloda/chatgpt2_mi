# spare parts management – product requirements document (prd)

_Last updated: 2025-08-31 (Africa/Cairo)_

## 1) overview & goals
A lightweight, PHP/MySQL spare-parts management app for small teams, deployed on GoDaddy (Plesk Windows). The app covers sales (quotes → orders → invoices → customer payments) and purchasing (pos → purchase invoices → receipts/grn → supplier payments), with simple stock control, notes, and printable documents.

**Primary goals**
- Fast day-to-day operations for parts sales & purchasing
- Clear stock visibility per warehouse (on-hand vs reserved)
- Clean prints (A4) with optional public notes and logo
- Minimal clicks, simple UI, predictable workflows
- Bilingual (English - Arabic)

**Non-goals (for later)**
- Complex accounting, multi-currency, deep reporting, advanced permissions, EDI integrations

---

## 2) scope (current)

### 2.1 authentication & users
- Login/logout with session + CSRF protection
- Flash messages for user feedback
- (Optional) activity logging hooks

### 2.2 master data
- **clients/customers** (with optional notes)
- **suppliers** (with optional notes; computed **balance** optional)
- **products** (code, name, price, category, make, model, default warehouse; with notes)
- **categories** (with notes)
- **warehouses** (with notes)
- **makes/models** for product structuring

### 2.3 inventory model
- `product_stocks (product_id, warehouse_id, qty_on_hand, qty_reserved)`
- **reserved** increases from sales quotes; released upon edit/cancel/convert; validated against on-hand on conversion
- **on-hand** increases via purchasing receipts; decreases on sales fulfillment (via orders/invoices as implemented)
- Insufficient stock checks with clear error and no unintended data writes

### 2.4 sales flow (ar)
- **quotes**
  - add products, qty, auto unit price, line totals
  - prevent exceeding available stock on convert
  - public/private notes; public optional on print
  - print page without navbar; optional logo; “include public notes” checkbox
- **sales orders**
  - created from quote (“convert to order”)
  - view/print; totals
  - create **sales invoice** button
- **sales invoices**
  - items from order; view/print; totals/subtotal/tax
  - **customer payments (ar)**:
    - add/delete payments; cap to remaining; lock ui when fully paid
    - top-nav “payments” list (optional)
  - notes (public/private) and print flag for public notes

### 2.5 purchasing flow (ap)
- **suppliers** (with optional balance column)
- **purchase orders (po)**
  - draft → ordered → received → closed
  - view/print; notes; “create purchase invoice”
- **purchase invoices (pi)**
  - created from po; shows ordered/received/remaining
  - **receipts (grn)**
    - post multi-line receipts; auto-cap to remaining
    - increments `qty_on_hand` per (product, warehouse)
    - history table; delete line decrements stock
    - **print grn**
  - when fully received, po auto → `received`; separate action to `close`
  - **supplier payments (ap)**
    - payments table + add form; cap to remaining; lock when paid
    - central `/supplierpayments` list
- **supplier balances (optional)**
  - suppliers index shows live balance = sum(pi.total) − sum(ap.amount)

### 2.6 notes system (shared)
- Entities: clients, categories, warehouses, products, quotes, sales orders, sales invoices, purchase orders, purchase invoices
- Multiple notes per entity; each stamped with creator + datetime
- **public vs private**; public can be included on printouts via checkbox
- UX: inline edit (textarea toggle), confirm before delete, pagination for long lists
- Safe formatting: newlines + links only

### 2.7 printing
- Clean A4 template; navbar hidden on print
- Optional logo at `/public/img/logo.png`
- “Include public notes” checkbox on print pages

---

## 3) non-functional requirements
- **stack**: php (mvc, custom), mysql, html/css/js
- **hosting**: godaddy plesk windows
- **coding**: all filenames/paths **lowercase**; controller/model/view separation
- **security**: csrf tokens, auth checks, basic input sanitation; avoid exposing secrets
- **performance**: simple sql indexes on foreign keys; pagination on heavy lists
- **ops**: health check `/health` (ok), db ping utility (guarded by key), opcaching reset helper (temporary use only)

---

## 4) database (current high-level)
- `users`
- `clients`, `suppliers`
- `categories`, `makes`, `models`
- `warehouses`
- `products` (with make/model/category refs)
- `product_stocks` (composite key product_id+warehouse_id; `qty_on_hand`, `qty_reserved`)
- **sales**: `quotes`, `quote_items`, `sales_orders`, `sales_order_items`,
  `sales_invoices`, `sales_invoice_items`, `payments` (customer payments)
- **purchasing**: `purchase_orders`, `purchase_order_items`,
  `purchase_invoices` (with `paid_amount`, `status`), `receipts`,
  `supplier_payments` (ap)
- `notes` (entity_type, entity_id, is_public, body, created_by, created_at)
- (optional) `activity_log`

> Exact create/alter statements live in previous migrations you ran; new columns: `purchase_invoices.paid_amount`, `purchase_invoices.status`; new table: `supplier_payments`.

---

## 5) routes (main)
- **auth**: `/login`, `/logout`
- **dashboard/home**: `/`
- **health**: `/health`, `/db_ping.php?key=…`
- **sales**: `/quotes`, `/orders`, `/invoices`, `/payments`
- **purchasing**: `/purchaseorders`, `/purchaseinvoices`, `/receipts`
- **prints**: `/quotes/print`, `/orders/print`, `/invoices/print`,
  `/purchaseinvoices/print`, `/receipts/print`
- **admin/master**: `/clients`, `/suppliers`, `/products`, `/categories`, `/warehouses`, `/makes`, `/models`

---

## 6) out of scope / backlog
- Returns & credit/debit notes (sales/purchase)
- Stock adjustments & transfers
- Multi-currency, taxes per line, discounts
- Role-based permissions
- Imports/exports, CSV, bulk updates
- Reports (aging, turnover, low-stock alerts)
- Email/pdf sending, templates
- Attachments on entities
- Barcode/QR support, scanners

---

## 7) acceptance criteria (slice highlights)
- Can create a Quote, convert to Order, create Invoice, register Customer Payments until status=paid; prints cleanly with optional public notes and logo.
- Can create PO, PI from PO, receive items (no over-receipt), stocks increase, GRN prints, PO auto→received when complete, can Close PO; can add Supplier Payments until status=paid; optional supplier balance visible.
- Notes add/edit/delete; public notes appear on prints only when opt-in.
