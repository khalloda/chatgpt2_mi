# project status – spare parts management

_As of: 2025-08-31 (Africa/Cairo)_

## 1) executive summary
Core sales and purchasing flows are live and tested. Printing, notes, stock changes, and both AR (customer) & AP (supplier) payments are in place. System is stable on GoDaddy (Plesk Windows).

## 2) what’s done (✅)
- MVC bootstrap, auth, csrf, flash.
- Master data: clients, suppliers, products, categories, warehouses, makes, models.
- Inventory engine: product_stocks (on-hand & reserved).
- **Sales (AR)**:
  - Quotes → Orders → Invoices
  - Insufficient stock guard on convert
  - Customer payments with cap + lock on paid
  - Prints (quote/order/invoice) with optional logo & public notes
- **Notes**: public/private; inline edit; delete confirm; pagination; safe formatting; prints opt-in
- **Purchasing (AP)**:
  - PO lifecycle (draft/ordered/received/closed)
  - PI from PO; receive (auto-cap), stock increment; receipts history & delete; GRN printing
  - Supplier payments with cap + lock on paid
  - Optional supplier balance on suppliers index
- Print polish: hide navbar on print; A4 layout; logo hook
- Health checks: `/health` (OK), db ping helper

## 3) environment links
- **app**: `https://sp.elmadeenaelmunawarah.com/`
- **health**: `/health` → “OK”
- **prints**: add `?include_notes=1` where supported
- **logo path**: `/public/img/logo.png`

## 4) database state (delta highlights)
- Added `purchase_invoices.paid_amount (decimal)`, `purchase_invoices.status (enum)`
- Created `supplier_payments` (ap)
- Existing: `receipts`, `purchase_order_items`, etc.

## 5) open issues / risks (🟨)
- No roles/permissions yet (all authenticated users are equivalent)
- No returns/credit notes
- No stock transfers/adjustments UI
- No formal migrations framework (manual SQL used)

## 6) next up (🛠️)
**High-value next tasks**
1. **Returns & adjustments**
   - Sales returns / credit notes (restore stock)
   - Purchase returns / debit notes (reduce stock)
   - Simple stock adjustments (count variances)
2. **Reports**
   - Low stock, aging (AR/AP), movement per product
3. **Permissions**
   - Basic roles (admin vs clerk)
4. **Exports**
   - CSV export of invoices, payments, stock
5. **Quality**
   - Guard rails & more tests (edge cases; pagination everywhere)

## 7) test checkpoints (recent)
- PI receive: over-receipt auto-capped; stock increments; GRN prints ✔
- PO auto→received when fully received; manual close ✔
- Supplier payments: cap + lock on paid; central list ✔
- Sales invoices: customer payments work; UI hides on paid ✔
- Quote convert guard: prevents insufficient stock; no bad writes ✔

## 8) change log (recent highlights)
- feat(ap): supplier payments + supplier balance (optional)
- feat(purchasing): receipts history, “receive all”, grn print, close po
- feat(print): logo + opt-in public notes, navbar hidden on print
- feat(notes): public/private, inline edit, pagination, safe rendering
- fix(stock): composite key updates for product_stocks
