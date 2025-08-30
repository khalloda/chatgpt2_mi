# Spare Parts Management — PRD vs. Current Status

_Last updated: 2025-08-30_

> Snapshot of what the original PRD set out to build vs. what’s live now, what changed, and what’s next.  
> Tech: PHP + CSS + JS (GoDaddy Plesk Windows), MySQL. All repo paths/filenames are lowercase.

---

## At-a-glance

| Area | PRD Target | Current Status | Notes |
|---|---|---|---|
| App bootstrap & routing | Simple MVC, router, views, layout, health | **Done** | `/health` OK; `db_ping.php` diagnostics; layout & helpers wired. |
| Auth & sessions | Login/logout, session flash | **Done** | Flash messages now pop once (no sticky banners). |
| Entities | Categories, Makes, Models, Warehouses, Products, Customers (CRUD) | **Done** | Forms + lists; warehouses/products carry stock data. |
| Stock tracking | Per product/warehouse: on-hand & reserved | **Done (v1)** | Reservation on quote; deduction on order; validations prevent over-reserve. |
| Quotes | Create, edit items, tax, totals, status; print | **Done (v1.2)** | Auto unit price; line totals; live summary; statuses `sent/ordered`; **Print** page with optional public notes. |
| Sales Orders | Convert from quote, deduct stock, list/view/print | **Done (v1)** | Transactional conversion releases reserved & deducts on-hand; **Print** with notes toggle. |
| Global Notes | Public/Private notes with creator & timestamp | **Done (v1.1)** | On Quotes & Orders (show), Customers/Categories/Warehouses/Products (edit). Inline edit, confirm delete, paging, safe formatting (links + newlines). |
| Printables | Clean A4-friendly HTML | **Done** | Rendered via `view_raw()` (no app navigation). |
| Sales Invoices | SO → Invoice | **Not started** | Planned next milestone. |
| Purchasing | Suppliers, POs, PIs; stock increase on receipts | **Not started** | Design pending. |
| Permissions/roles | Admin vs. standard | **Not started** | Especially for notes edit/delete & admin tools. |
| Stock ledger/audit | Movement log | **Planned** | Trace for reserve/consume/adjustments. |

---

## What’s shipped

- **Bootstrap MVC & Health**
  - Router, controllers, views, layout, helpers.
  - `/health` and `db_ping.php` diagnostics.

- **Authentication**
  - Login/logout; session flash messages (one-time).

- **Core entities**
  - **Categories, Makes, Models, Warehouses, Products, Customers** (CRUD).
  - Product stock per warehouse (`qty_on_hand`, `qty_reserved`).

- **Quotes**
  - Multi-row items; auto-fill **unit price** from product; **line totals** + live page totals.
  - Status flow: `sent → ordered`.
  - **Reservation** increases `qty_reserved` on save with **pre-validation**: cannot exceed available (on-hand − reserved).
  - **Print**: `/quotes/print?id=…` with “Include public notes”.

- **Sales Orders**
  - **Convert** Quote → SO: transactionally **release reserved** and **deduct on-hand**.
  - List & detail with lines and totals.
  - **Print**: `/orders/print?id=…` with notes toggle.

- **Notes system (Public/Private)**
  - Table `notes(entity_type, entity_id, is_public, body, created_by, created_at)`.
  - Inline **edit-in-place**, **confirm delete**, **pagination** after 10.
  - Safe formatting: newlines + clickable links; no HTML injection.
  - Integrated on: **Quotes, Sales Orders** (show), **Customers, Categories, Warehouses, Products** (edit).

- **Print UX**
  - Uses `Controller::view_raw()` to bypass layout/navigation for clean output.

---

## Differences vs. PRD & clarifications

- **Reservations & Integrity**
  - PRD assumed quotes reserve stock. Implemented with **pre-validation** to prevent saving impossible reservations (added after testing).
  - Recovery SQL to recompute `qty_reserved` from all `sent` quotes (for data repair).

- **Notes**
  - PRD required Public/Private + stamping—**delivered**, plus UX polish (inline edit, paging).
  - Public notes are **optional** on printouts via checkbox; ready for Invoices.

- **Routes naming**
  - Avoided collision with base `Controller::view()` by standardizing **`show`** for read actions.

- **Print pages**
  - Rendered raw (no layout) for print-ready documents.

---

## Database changes (since baseline)

- **New**
  - `sales_orders`, `sales_order_items`
  - `notes`
- **Existing utilized**
  - `quotes`, `quote_items`, `product_stocks` (`qty_on_hand`, `qty_reserved`)
- **Helpers**
  - Product stock helpers to reserve/consume within transactions.

---

## Validation & error handling

- Quote create aggregates duplicate product/warehouse rows and validates requested totals vs. **available** before inserts.
- Flash messages are one-time (pop on read).
- Hardened `show()` handlers for missing entities (no redirect loops).

---

## Open items (next milestones)

1. **Sales Invoices (SO → Invoice)**
   - Tables: `invoices`, `invoice_items`, status & payment tracking.
   - Print page + public notes.

2. **Purchasing flow**
   - Suppliers, **Purchase Orders** → **Purchase Invoices**, receiving increases stock.
   - Link receipts to warehouses; support partial receipts.

3. **Permissions**
   - Only creator/admin can edit/delete notes; role-based gates for stock & accounting.

4. **Stock ledger**
   - `stock_moves` to log: quote reserve/release, order consume, manual adjustments, receipts.

5. **Settings**
   - Company profile (logo/address), default tax rate, numbering formats.

6. **Polish**
   - Cancel Order (optional restock), Edit Quote with revalidation, printable headers/footers with page numbers.

---

## Representative PRs / branches

- `feature/bootstrap-mvc` — Bootstrap, router, health, layout  
- `feature/auth-logging` — Auth & session flashes  
- `feature/users-and-categories` — Entities baseline  
- `feature/makes-models` — Additional entities  
- `feature/quotes` — Quotes CRUD, pricing, totals, reservation  
- `feature/orders` — Convert to Sales Order, stock deduction  
- `feature/notes` — Global notes system, inline edit, print integration  
- `feature/print-clean` — `view_raw()` & clean print pages with notes toggle

*(Names condensed for readability; see repo history for exact PRs and commits.)*

---

## Progress gauge

- Core platform & entities: **100%**  
- Quotes: **100%** (v1.2)  
- Orders: **100%** (v1)  
- Global notes & print integration: **100%**  
- Invoices (Sales): **0%**  
- Purchasing (PO/PI): **0%**  
- Permissions & ledger: **0–10%**  

**Overall:** ~**55–60%** of PRD scope complete; sales front-half is production-ready.

---

### How to regenerate this file
When major milestones ship, update the “Last updated” date and the At-a-glance table. Keep this file in **`docs/status.md`** (lowercase) so links remain stable.
