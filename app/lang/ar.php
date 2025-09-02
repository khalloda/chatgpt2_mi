<?php
/**
 * File: app/lang/ar.php
 * Purpose: Arabic language strings (واجهة عربية) — يجب أن تعكس مفاتيح en.php تمامًا
 * Notes:
 *  - استخدم t('key.path') في القوالب/المتحكمات.
 *  - لا تضع أي أسرار هنا.
 */
return [

    /* =========================
     * App
     * ========================= */
    'app.title'        => 'MI Spare Parts',
    'app.tagline'      => 'إدارة المخزون والمبيعات والمشتريات',
    'app.version'      => 'الإصدار',
    'app.loading'      => 'جاري التحميل…',
    'app.search'       => 'بحث',
    'app.clear'        => 'مسح',
    'app.close'        => 'إغلاق',

    /* =========================
     * Navigation (sidebar / header)
     * ========================= */
    'nav.dashboard'    => 'اللوحة الرئيسية',
    'nav.sales'        => 'المبيعات',
    'nav.purchasing'   => 'المشتريات',
    'nav.inventory'    => 'المخزون',
    'nav.crm'          => 'إدارة العملاء',
    'nav.reports'      => 'التقارير',
    'nav.settings'     => 'الإعدادات',
    'nav.tools'        => 'الأدوات',

    // Sales sub
    'nav.quotes'           => 'عروض الأسعار',
    'nav.sales_orders'     => 'أوامر البيع',
    'nav.invoices'         => 'الفواتير',
    'nav.sales_returns'    => 'مرتجعات المبيعات',
    'nav.payments_in'      => 'المدفوعات الواردة (ذمم مدينة)',

    // Purchasing sub
    'nav.suppliers'        => 'الموردون',
    'nav.purchase_orders'  => 'أوامر الشراء',
    'nav.purchase_invoices'=> 'فواتير الشراء',
    'nav.goods_receipts'   => 'استلام البضائع',
    'nav.purchase_returns' => 'مرتجعات الشراء',

    // Inventory sub
    'nav.products'         => 'المنتجات',
    'nav.categories'       => 'الفئات',
    'nav.makes'            => 'الماركات',
    'nav.models'           => 'الموديلات',
    'nav.warehouses'       => 'المستودعات',
    'nav.transfers'        => 'تحويلات المخزون',
    'nav.adjustments'      => 'تسويات المخزون',
    'nav.reservations'     => 'الحجوزات',
    'nav.low_stock'        => 'انخفاض المخزون',

    // CRM sub
    'nav.clients'          => 'العملاء',
    'nav.contacts'         => 'جهات الاتصال',

    // Reports sub
    'nav.reports_sales'        => 'تقارير المبيعات',
    'nav.reports_purchasing'   => 'تقارير المشتريات',
    'nav.reports_inventory'    => 'تقارير المخزون',
    'nav.reports_ar'           => 'الذمم المدينة',
    'nav.reports_ap'           => 'الذمم الدائنة',

    // Settings sub
    'nav.users_roles'      => 'المستخدمون والصلاحيات',
    'nav.taxes_currency'   => 'الضرائب والعملة',
    'nav.units_sequences'  => 'الوحدات والتسلسل',
    'nav.translations'     => 'الترجمات',
    'nav.notifications'    => 'الإشعارات',
    'nav.integrations'     => 'التكاملات',

    // Tools sub
    'nav.import_export'    => 'الاستيراد والتصدير',
    'nav.backups'          => 'النسخ الاحتياطي',
    'nav.audit_log'        => 'سجل التدقيق',
    'nav.system_health'    => 'فحص النظام',

    /* =========================
     * Actions / Buttons
     * ========================= */
    'action.create'        => 'إنشاء',
    'action.add'           => 'إضافة',
    'action.edit'          => 'تعديل',
    'action.view'          => 'عرض',
    'action.delete'        => 'حذف',
    'action.save'          => 'حفظ',
    'action.cancel'        => 'إلغاء',
    'action.update'        => 'تحديث',
    'action.export'        => 'تصدير',
    'action.print'         => 'طباعة',
    'action.download'      => 'تنزيل',
    'action.back'          => 'رجوع',
    'action.filter'        => 'تصفية',
    'action.reset_filters' => 'إعادة تعيين الفلاتر',
    'action.show_columns'  => 'إظهار/إخفاء الأعمدة',
    'action.more'          => 'المزيد',
    'action.apply'         => 'تطبيق',
    'action.confirm'       => 'تأكيد',
    'action.yes'           => 'نعم',
    'action.no'            => 'لا',

    /* =========================
     * Dashboard KPIs
     * ========================= */
    'kpi.total_stock_value'   => 'قيمة المخزون',
    'kpi.out_of_stock'        => 'نفاد المخزون',
    'kpi.low_stock'           => 'انخفاض المخزون',
    'kpi.quotes_week'         => 'عروض هذا الأسبوع',
    'kpi.orders_week'         => 'أوامر بيع هذا الأسبوع',
    'kpi.invoices_week'       => 'فواتير هذا الأسبوع',
    'kpi.overdue_ar'          => 'ذمم مدينة متأخرة',
    'kpi.best_seller_week'    => 'الأكثر مبيعًا هذا الأسبوع',

    /* =========================
     * Table - generic strings & controls
     * ========================= */
    'table.search_placeholder' => 'ابحث…',
    'table.rows_per_page'      => 'عدد الصفوف لكل صفحة',
    'table.total'              => 'الإجمالي',
    'table.of'                 => 'من',
    'table.showing'            => 'عرض',
    'table.to'                 => 'إلى',
    'table.entries'            => 'سجل',
    'table.no_data'            => 'لا توجد بيانات',
    'table.export_csv'         => 'تصدير CSV',
    'table.export_xlsx'        => 'تصدير XLSX',
    'table.export_pdf'         => 'تصدير PDF',
    'table.refresh'            => 'تحديث',
    'table.actions'            => 'إجراءات',
    'table.pinned_left'        => 'مثبّت يسارًا',
    'table.pinned_right'       => 'مثبّت يمينًا',
    'table.pin'                => 'تثبيت',
    'table.unpin'              => 'إلغاء التثبيت',
    'table.density'            => 'الكثافة',
    'table.density_compact'    => 'مضغوط',
    'table.density_cozy'       => 'مريح',
    'table.show_core_on_mobile'=> 'عرض الأعمدة الأساسية على الجوال',
    'table.truncated_tooltip'  => 'مرّر المؤشر لرؤية النص كاملًا',
    'table.numeric_hint'       => 'القيم الرقمية محاذاة لليمين',

    /* =========================
     * Products (index & forms)
     * ========================= */
    'products.title'           => 'المنتجات',
    'products.code'            => 'الرمز/‏SKU',
    'products.name'            => 'الاسم',
    'products.make_model'      => 'الماركة • الموديل',
    'products.stock_avail'     => 'المتاح',
    'products.stock_resvd'     => 'المحجوز',
    'products.stock_compact'   => 'المتاح / المحجوز',
    'products.price_sell'      => 'السعر',
    'products.status'          => 'الحالة',
    'products.category'        => 'الفئة',
    'products.subcategory'     => 'الفئة الفرعية',
    'products.category_compact'=> 'الفئة → الفرعية',
    'products.supplier'        => 'المورد',
    'products.cost'            => 'التكلفة',
    'products.warehouse_breakdown' => 'المستودعات',
    'products.updated_at'      => 'آخر تحديث',
    'products.create'          => 'إنشاء منتج',
    'products.edit'            => 'تعديل المنتج',
    'products.view'            => 'عرض المنتج',
    'products.delete_confirm'  => 'هل تريد حذف هذا المنتج؟',
    'products.placeholder_code'=> 'أدخل SKU…',
    'products.placeholder_name'=> 'أدخل اسم المنتج…',
    'products.placeholder_price'=> 'أدخل سعر البيع…',

    /* =========================
     * Customers (index & forms)
     * ========================= */
    'customers.title'          => 'العملاء',
    'customers.code'           => 'كود العميل',
    'customers.name'           => 'الاسم',
    'customers.company'        => 'شركة',
    'customers.person'         => 'فرد',
    'customers.phone'          => 'الهاتف',
    'customers.email'          => 'البريد الإلكتروني',
    'customers.city'           => 'المدينة',
    'customers.vat_id'         => 'الرقم الضريبي',
    'customers.salesperson'    => 'مندوب المبيعات',
    'customers.payment_terms'  => 'شروط الدفع',
    'customers.outstanding'    => 'الرصيد المستحق',
    'customers.last_activity'  => 'آخر نشاط',
    'customers.status'         => 'الحالة',
    'customers.create'         => 'إنشاء عميل',
    'customers.edit'           => 'تعديل العميل',
    'customers.view'           => 'عرض العميل',
    'customers.delete_confirm' => 'هل تريد حذف هذا العميل؟',

    /* =========================
     * Sales Invoices (index & forms)
     * ========================= */
    'invoices.title'           => 'الفواتير',
    'invoices.number'          => 'رقم الفاتورة',
    'invoices.date'            => 'التاريخ',
    'invoices.customer'        => 'العميل',
    'invoices.total'           => 'الإجمالي',
    'invoices.paid'            => 'المدفوع',
    'invoices.due'             => 'المتبقي',
    'invoices.paid_due_compact'=> 'المدفوع / المتبقي',
    'invoices.currency'        => 'العملة',
    'invoices.method'          => 'طريقة الدفع',
    'invoices.salesperson'     => 'مندوب المبيعات',
    'invoices.linked_so_quote' => 'أمر/عرض مرتبط',
    'invoices.due_date'        => 'تاريخ الاستحقاق',
    'invoices.created_by'      => 'أنشأها',
    'invoices.status'          => 'الحالة',
    'invoices.create'          => 'إنشاء فاتورة',
    'invoices.view'            => 'عرض الفاتورة',
    'invoices.edit'            => 'تعديل الفاتورة',
    'invoices.delete_confirm'  => 'هل تريد حذف هذه الفاتورة؟',

    /* =========================
     * Purchase Orders
     * ========================= */
    'pos.title'                => 'أوامر الشراء',
    'pos.number'               => 'رقم أمر الشراء',
    'pos.date'                 => 'التاريخ',
    'pos.supplier'             => 'المورد',
    'pos.total'                => 'الإجمالي',
    'pos.received_pct'         => 'نسبة الاستلام',
    'pos.status'               => 'الحالة',
    'pos.expected_date'        => 'تاريخ متوقع',
    'pos.warehouse'            => 'المستودع',
    'pos.created_by'           => 'أنشأها',
    'pos.payment_terms'        => 'شروط الدفع',
    'pos.create'               => 'إنشاء أمر شراء',
    'pos.view'                 => 'عرض أمر الشراء',
    'pos.edit'                 => 'تعديل أمر الشراء',
    'pos.delete_confirm'       => 'هل تريد حذف أمر الشراء؟',

    /* =========================
     * Purchase Invoices
     * ========================= */
    'pis.title'                => 'فواتير الشراء',
    'pis.number'               => 'رقم فاتورة الشراء',
    'pis.date'                 => 'التاريخ',
    'pis.supplier'             => 'المورد',
    'pis.total'                => 'الإجمالي',
    'pis.paid_due'             => 'المدفوع / المتبقي',
    'pis.status'               => 'الحالة',
    'pis.linked_po'            => 'أمر شراء مرتبط',
    'pis.currency'             => 'العملة',
    'pis.grn_ref'              => 'رقم الاستلام (GRN)',
    'pis.created_by'           => 'أنشأها',
    'pis.create'               => 'إنشاء فاتورة شراء',
    'pis.view'                 => 'عرض فاتورة الشراء',
    'pis.edit'                 => 'تعديل فاتورة الشراء',
    'pis.delete_confirm'       => 'هل تريد حذف فاتورة الشراء؟',

    /* =========================
     * Statuses / Badges
     * ========================= */
    'status.active'                => 'نشط',
    'status.inactive'              => 'غير نشط',
    'status.low_stock'             => 'منخفض',
    'status.ok'                    => 'جيد',

    'status.draft'                 => 'مسودة',
    'status.issued'                => 'صادرة',
    'status.paid'                  => 'مدفوعة',
    'status.part_paid'             => 'مدفوعة جزئيًا',
    'status.overdue'               => 'متأخرة',

    'status.open'                  => 'مفتوح',
    'status.closed'                => 'مغلق',
    'status.cancelled'             => 'ملغي',
    'status.partially_received'    => 'تم الاستلام جزئيًا',
    'status.posted'                => 'مُرحَّلة',

    /* =========================
     * Forms / Validation
     * ========================= */
    'form.required'            => 'هذا الحقل مطلوب',
    'form.invalid_email'       => 'بريد إلكتروني غير صالح',
    'form.invalid_phone'       => 'رقم هاتف غير صالح',
    'form.duplicate_sku'       => 'SKU مستخدم من قبل',
    'form.min_length'          => 'قصير جدًا',
    'form.max_length'          => 'طويل جدًا',
    'form.must_be_number'      => 'يجب أن يكون رقمًا',
    'form.must_be_integer'     => 'يجب أن يكون عددًا صحيحًا',
    'form.must_be_positive'    => 'يجب أن يكون موجبًا',
    'form.select_option'       => 'اختر خيارًا…',

    /* =========================
     * Flash / Toasts
     * ========================= */
    'flash.saved'              => 'تم الحفظ بنجاح.',
    'flash.updated'            => 'تم التحديث بنجاح.',
    'flash.deleted'            => 'تم الحذف بنجاح.',
    'flash.error'              => 'حدث خطأ ما.',
    'flash.invalid_session'    => 'جلسة غير صالحة. حاول مرة أخرى.',
    'flash.not_found'          => 'العنصر غير موجود.',
    'flash.confirm_delete'     => 'هل أنت متأكد من حذف هذا العنصر؟',

    /* =========================
     * Print / Documents
     * ========================= */
    'print.title'              => 'طباعة',
    'print.company'            => 'الشركة',
    'print.client'             => 'العميل',
    'print.supplier'           => 'المورد',
    'print.date'               => 'التاريخ',
    'print.subtotal'           => 'الإجمالي الفرعي',
    'print.tax'                => 'الضريبة',
    'print.discount'           => 'الخصم',
    'print.total'              => 'الإجمالي',
    'print.page'               => 'صفحة',
    'print.of'                 => 'من',

    /* =========================
     * Dates / Time
     * ========================= */
    'date.today'               => 'اليوم',
    'date.this_week'           => 'هذا الأسبوع',
    'date.this_month'          => 'هذا الشهر',

    /* =========================
     * Currency labels (display only)
     * ========================= */
    'currency.egp'             => 'جنيه مصري',
    'currency.usd'             => 'دولار أمريكي',

    /* =========================
     * Accessibility / Hints
     * ========================= */
    'a11y.skip_to_content'     => 'تخطي إلى المحتوى',
    'a11y.navigation'          => 'التنقل الرئيسي',
    'hint.truncate'            => 'نص مقطوع؛ مرّر المؤشر لرؤية المزيد',
    'hint.numeric_right'       => 'القيم الرقمية محاذاة لليمين',

    /* =========================
     * Errors (generic)
     * ========================= */
    'error.title'              => 'خطأ',
    'error.404'                => 'الصفحة غير موجودة',
    'error.500'                => 'خطأ في الخادم',
    'error.permission'         => 'ليست لديك صلاحية لتنفيذ هذا الإجراء',
];
