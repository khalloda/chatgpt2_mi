<?php
/**
 * File: app/lang/en.php
 * Purpose: English language strings (UI labels, messages, table headers, statuses)
 * Notes:
 *  - Keep keys stable; other locales (e.g., ar.php) must mirror this structure.
 *  - Use t('key.path') in PHP views/controllers.
 *  - Do NOT include secrets here.
 */
return [

    /* =========================
     * App
     * ========================= */
    'app.title'        => 'MI Spare Parts',
    'app.tagline'      => 'Inventory, Sales & Purchasing Management',
    'app.version'      => 'Version',
    'app.loading'      => 'Loading…',
    'app.search'       => 'Search',
    'app.clear'        => 'Clear',
    'app.close'        => 'Close',

    /* =========================
     * Navigation (sidebar / header)
     * ========================= */
    'nav.dashboard'    => 'Dashboard',
    'nav.sales'        => 'Sales',
    'nav.purchasing'   => 'Purchasing',
    'nav.inventory'    => 'Inventory',
    'nav.crm'          => 'CRM',
    'nav.reports'      => 'Reports',
    'nav.settings'     => 'Settings',
    'nav.tools'        => 'Tools',

    // Sales sub
    'nav.quotes'           => 'Quotes',
    'nav.sales_orders'     => 'Sales Orders',
    'nav.invoices'         => 'Invoices',
    'nav.sales_returns'    => 'Sales Returns',
    'nav.payments_in'      => 'Payments In (AR)',

    // Purchasing sub
    'nav.suppliers'        => 'Suppliers',
    'nav.purchase_orders'  => 'Purchase Orders',
    'nav.purchase_invoices'=> 'Purchase Invoices',
    'nav.goods_receipts'   => 'Goods Receipts',
    'nav.purchase_returns' => 'Purchase Returns',

    // Inventory sub
    'nav.products'         => 'Products',
    'nav.categories'       => 'Categories',
    'nav.makes'            => 'Makes',
    'nav.models'           => 'Models',
    'nav.warehouses'       => 'Warehouses',
    'nav.transfers'        => 'Transfers',
    'nav.adjustments'      => 'Adjustments',
    'nav.reservations'     => 'Reservations',
    'nav.low_stock'        => 'Low Stock',

    // CRM sub
    'nav.clients'          => 'Clients',
    'nav.contacts'         => 'Contacts',

    // Reports sub
    'nav.reports_sales'        => 'Sales Reports',
    'nav.reports_purchasing'   => 'Purchasing Reports',
    'nav.reports_inventory'    => 'Inventory Reports',
    'nav.reports_ar'           => 'Accounts Receivable',
    'nav.reports_ap'           => 'Accounts Payable',

    // Settings sub
    'nav.users_roles'      => 'Users & Roles',
    'nav.taxes_currency'   => 'Taxes & Currency',
    'nav.units_sequences'  => 'Units & Sequences',
    'nav.translations'     => 'Translations',
    'nav.notifications'    => 'Notifications',
    'nav.integrations'     => 'Integrations',

    // Tools sub
    'nav.import_export'    => 'Import/Export',
    'nav.backups'          => 'Backups',
    'nav.audit_log'        => 'Audit Log',
    'nav.system_health'    => 'System Health',

    /* =========================
     * Actions / Buttons
     * ========================= */
    'action.create'        => 'Create',
    'action.add'           => 'Add',
    'action.edit'          => 'Edit',
    'action.view'          => 'View',
    'action.delete'        => 'Delete',
    'action.save'          => 'Save',
    'action.cancel'        => 'Cancel',
    'action.update'        => 'Update',
    'action.export'        => 'Export',
    'action.print'         => 'Print',
    'action.download'      => 'Download',
    'action.back'          => 'Back',
    'action.filter'        => 'Filter',
    'action.reset_filters' => 'Reset Filters',
    'action.show_columns'  => 'Show/Hide Columns',
    'action.more'          => 'More',
    'action.apply'         => 'Apply',
    'action.confirm'       => 'Confirm',
    'action.yes'           => 'Yes',
    'action.no'            => 'No',

    /* =========================
     * Dashboard KPIs
     * ========================= */
    'kpi.total_stock_value'   => 'Total Stock Value',
    'kpi.out_of_stock'        => 'Out of Stock',
    'kpi.low_stock'           => 'Low Stock',
    'kpi.quotes_week'         => 'Quotes (This Week)',
    'kpi.orders_week'         => 'Sales Orders (This Week)',
    'kpi.invoices_week'       => 'Invoices (This Week)',
    'kpi.overdue_ar'          => 'Overdue AR',
    'kpi.best_seller_week'    => 'Best Selling (This Week)',

    /* =========================
     * Table - generic strings & controls
     * ========================= */
    'table.search_placeholder' => 'Search…',
    'table.rows_per_page'      => 'Rows per page',
    'table.total'              => 'Total',
    'table.of'                 => 'of',
    'table.showing'            => 'Showing',
    'table.to'                 => 'to',
    'table.entries'            => 'entries',
    'table.no_data'            => 'No data available',
    'table.export_csv'         => 'Export CSV',
    'table.export_xlsx'        => 'Export XLSX',
    'table.export_pdf'         => 'Export PDF',
    'table.refresh'            => 'Refresh',
    'table.actions'            => 'Actions',
    'table.pinned_left'        => 'Pinned Left',
    'table.pinned_right'       => 'Pinned Right',
    'table.pin'                => 'Pin',
    'table.unpin'              => 'Unpin',
    'table.density'            => 'Density',
    'table.density_compact'    => 'Compact',
    'table.density_cozy'       => 'Cozy',
    'table.show_core_on_mobile'=> 'Show core columns on mobile',
    'table.truncated_tooltip'  => 'Hover to see full text',
    'table.numeric_hint'       => 'Right-aligned numeric column',

    /* =========================
     * Products (index & forms)
     * ========================= */
    'products.title'           => 'Products',
    'products.code'            => 'Code/SKU',
    'products.name'            => 'Name',
    'products.make_model'      => 'Make • Model',
    'products.stock_avail'     => 'Avail',
    'products.stock_resvd'     => 'Resvd',
    'products.stock_compact'   => 'Avail / Resvd',
    'products.price_sell'      => 'Price',
    'products.status'          => 'Status',
    'products.category'        => 'Category',
    'products.subcategory'     => 'Subcategory',
    'products.category_compact'=> 'Category → Subcategory',
    'products.supplier'        => 'Supplier',
    'products.cost'            => 'Cost',
    'products.warehouse_breakdown' => 'Warehouses',
    'products.updated_at'      => 'Updated',
    'products.create'          => 'Create Product',
    'products.edit'            => 'Edit Product',
    'products.view'            => 'View Product',
    'products.delete_confirm'  => 'Delete this product?',
    'products.placeholder_code'=> 'Enter SKU…',
    'products.placeholder_name'=> 'Enter product name…',
    'products.placeholder_price'=> 'Enter selling price…',

    /* =========================
     * Customers (index & forms)
     * ========================= */
    'customers.title'          => 'Clients',
    'customers.code'           => 'Customer Code',
    'customers.name'           => 'Name',
    'customers.company'        => 'Company',
    'customers.person'         => 'Person',
    'customers.phone'          => 'Phone',
    'customers.email'          => 'Email',
    'customers.city'           => 'City',
    'customers.vat_id'         => 'VAT/Tax ID',
    'customers.salesperson'    => 'Salesperson',
    'customers.payment_terms'  => 'Payment Terms',
    'customers.outstanding'    => 'Outstanding Balance',
    'customers.last_activity'  => 'Last Activity',
    'customers.status'         => 'Status',
    'customers.create'         => 'Create Client',
    'customers.edit'           => 'Edit Client',
    'customers.view'           => 'View Client',
    'customers.delete_confirm' => 'Delete this client?',

    /* =========================
     * Sales Invoices (index & forms)
     * ========================= */
    'invoices.title'           => 'Invoices',
    'invoices.number'          => 'Invoice #',
    'invoices.date'            => 'Date',
    'invoices.customer'        => 'Customer',
    'invoices.total'           => 'Total',
    'invoices.paid'            => 'Paid',
    'invoices.due'             => 'Due',
    'invoices.paid_due_compact'=> 'Paid / Due',
    'invoices.currency'        => 'Currency',
    'invoices.method'          => 'Method',
    'invoices.salesperson'     => 'Salesperson',
    'invoices.linked_so_quote' => 'Linked SO/Quote',
    'invoices.due_date'        => 'Due Date',
    'invoices.created_by'      => 'Created by',
    'invoices.status'          => 'Status',
    'invoices.create'          => 'Create Invoice',
    'invoices.view'            => 'View Invoice',
    'invoices.edit'            => 'Edit Invoice',
    'invoices.delete_confirm'  => 'Delete this invoice?',

    /* =========================
     * Purchase Orders
     * ========================= */
    'pos.title'                => 'Purchase Orders',
    'pos.number'               => 'PO #',
    'pos.date'                 => 'Date',
    'pos.supplier'             => 'Supplier',
    'pos.total'                => 'Total',
    'pos.received_pct'         => 'Received %',
    'pos.status'               => 'Status',
    'pos.expected_date'        => 'Expected Date',
    'pos.warehouse'            => 'Warehouse',
    'pos.created_by'           => 'Created by',
    'pos.payment_terms'        => 'Payment Terms',
    'pos.create'               => 'Create Purchase Order',
    'pos.view'                 => 'View Purchase Order',
    'pos.edit'                 => 'Edit Purchase Order',
    'pos.delete_confirm'       => 'Delete this purchase order?',

    /* =========================
     * Purchase Invoices
     * ========================= */
    'pis.title'                => 'Purchase Invoices',
    'pis.number'               => 'PI #',
    'pis.date'                 => 'Date',
    'pis.supplier'             => 'Supplier',
    'pis.total'                => 'Total',
    'pis.paid_due'             => 'Paid / Due',
    'pis.status'               => 'Status',
    'pis.linked_po'            => 'Linked PO #',
    'pis.currency'             => 'Currency',
    'pis.grn_ref'              => 'GRN Ref',
    'pis.created_by'           => 'Created by',
    'pis.create'               => 'Create Purchase Invoice',
    'pis.view'                 => 'View Purchase Invoice',
    'pis.edit'                 => 'Edit Purchase Invoice',
    'pis.delete_confirm'       => 'Delete this purchase invoice?',

    /* =========================
     * Statuses / Badges
     * ========================= */
    'status.active'                => 'Active',
    'status.inactive'              => 'Inactive',
    'status.low_stock'             => 'Low',
    'status.ok'                    => 'OK',

    'status.draft'                 => 'Draft',
    'status.issued'                => 'Issued',
    'status.paid'                  => 'Paid',
    'status.part_paid'             => 'Part-paid',
    'status.overdue'               => 'Overdue',

    'status.open'                  => 'Open',
    'status.closed'                => 'Closed',
    'status.cancelled'             => 'Cancelled',
    'status.partially_received'    => 'Partially received',
    'status.posted'                => 'Posted',

    /* =========================
     * Forms / Validation
     * ========================= */
    'form.required'            => 'This field is required',
    'form.invalid_email'       => 'Invalid email address',
    'form.invalid_phone'       => 'Invalid phone number',
    'form.duplicate_sku'       => 'SKU already exists',
    'form.min_length'          => 'Too short',
    'form.max_length'          => 'Too long',
    'form.must_be_number'      => 'Must be a number',
    'form.must_be_integer'     => 'Must be an integer',
    'form.must_be_positive'    => 'Must be positive',
    'form.select_option'       => 'Select an option…',

    /* =========================
     * Flash / Toasts
     * ========================= */
    'flash.saved'              => 'Saved successfully.',
    'flash.updated'            => 'Updated successfully.',
    'flash.deleted'            => 'Deleted successfully.',
    'flash.error'              => 'Something went wrong.',
    'flash.invalid_session'    => 'Invalid session. Please try again.',
    'flash.not_found'          => 'Record not found.',
    'flash.confirm_delete'     => 'Are you sure you want to delete this item?',

    /* =========================
     * Print / Documents
     * ========================= */
    'print.title'              => 'Print',
    'print.company'            => 'Company',
    'print.client'             => 'Client',
    'print.supplier'           => 'Supplier',
    'print.date'               => 'Date',
    'print.subtotal'           => 'Subtotal',
    'print.tax'                => 'Tax',
    'print.discount'           => 'Discount',
    'print.total'              => 'Total',
    'print.page'               => 'Page',
    'print.of'                 => 'of',

    /* =========================
     * Dates / Time
     * ========================= */
    'date.today'               => 'Today',
    'date.this_week'           => 'This week',
    'date.this_month'          => 'This month',

    /* =========================
     * Currency labels (display only)
     * ========================= */
    'currency.egp'             => 'EGP',
    'currency.usd'             => 'USD',

    /* =========================
     * Accessibility / Hints
     * ========================= */
    'a11y.skip_to_content'     => 'Skip to content',
    'a11y.navigation'          => 'Main navigation',
    'hint.truncate'            => 'Text truncated; hover to see more',
    'hint.numeric_right'       => 'Numeric values are right-aligned',

    /* =========================
     * Errors (generic)
     * ========================= */
    'error.title'              => 'Error',
    'error.404'                => 'Page not found',
    'error.500'                => 'Server error',
    'error.permission'         => 'You do not have permission to perform this action',
];
