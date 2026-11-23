CREATE TABLE IF NOT EXISTS brands (
  id INTEGER PRIMARY KEY,
  code TEXT NOT NULL UNIQUE,
  name TEXT NOT NULL UNIQUE COLLATE NOCASE,
  active INTEGER NOT NULL DEFAULT 1 CHECK(active IN (0,1)),
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS categories (
  id INTEGER PRIMARY KEY,
  parent_id INTEGER REFERENCES categories(id),
  code TEXT NOT NULL UNIQUE,
  name TEXT NOT NULL,
  department TEXT NOT NULL DEFAULT '',
  active INTEGER NOT NULL DEFAULT 1 CHECK(active IN (0,1)),
  UNIQUE(parent_id,name)
);

CREATE TABLE IF NOT EXISTS suppliers (
  id INTEGER PRIMARY KEY,
  supplier_code TEXT NOT NULL UNIQUE,
  name TEXT NOT NULL,
  contact_name TEXT NOT NULL DEFAULT '',
  email TEXT NOT NULL DEFAULT '',
  phone TEXT NOT NULL DEFAULT '',
  tax_id TEXT NOT NULL DEFAULT '',
  address TEXT NOT NULL DEFAULT '',
  opening_balance_cents INTEGER NOT NULL DEFAULT 0 CHECK(opening_balance_cents >= 0),
  active INTEGER NOT NULL DEFAULT 1 CHECK(active IN (0,1)),
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS customers (
  id INTEGER PRIMARY KEY,
  customer_code TEXT NOT NULL UNIQUE,
  name TEXT NOT NULL,
  customer_type TEXT NOT NULL DEFAULT 'retail' CHECK(customer_type IN ('retail','wholesale','reseller','corporate')),
  contact_name TEXT NOT NULL DEFAULT '',
  email TEXT NOT NULL DEFAULT '',
  phone TEXT NOT NULL DEFAULT '',
  tax_id TEXT NOT NULL DEFAULT '',
  address TEXT NOT NULL DEFAULT '',
  credit_limit_cents INTEGER NOT NULL DEFAULT 0 CHECK(credit_limit_cents >= 0),
  opening_balance_cents INTEGER NOT NULL DEFAULT 0,
  active INTEGER NOT NULL DEFAULT 1 CHECK(active IN (0,1)),
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS purchase_orders (
  id INTEGER PRIMARY KEY,
  po_number TEXT NOT NULL UNIQUE,
  supplier_id INTEGER NOT NULL REFERENCES suppliers(id),
  location_id INTEGER NOT NULL REFERENCES locations(id),
  order_date TEXT NOT NULL,
  expected_date TEXT,
  status TEXT NOT NULL CHECK(status IN ('draft','ordered','part_received','received','cancelled')),
  subtotal_cents INTEGER NOT NULL DEFAULT 0 CHECK(subtotal_cents >= 0),
  notes TEXT NOT NULL DEFAULT '',
  created_by INTEGER REFERENCES users(id),
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS purchase_order_lines (
  id INTEGER PRIMARY KEY,
  purchase_order_id INTEGER NOT NULL REFERENCES purchase_orders(id) ON DELETE CASCADE,
  sku TEXT NOT NULL REFERENCES products(sku),
  ordered_qty REAL NOT NULL CHECK(ordered_qty > 0),
  received_qty REAL NOT NULL DEFAULT 0 CHECK(received_qty >= 0),
  unit_cost_cents INTEGER NOT NULL CHECK(unit_cost_cents >= 0),
  line_total_cents INTEGER NOT NULL CHECK(line_total_cents >= 0),
  UNIQUE(purchase_order_id,sku)
);
CREATE TABLE IF NOT EXISTS goods_receipts (
  id INTEGER PRIMARY KEY,
  receipt_number TEXT NOT NULL UNIQUE,
  purchase_order_id INTEGER REFERENCES purchase_orders(id),
  supplier_id INTEGER REFERENCES suppliers(id),
  location_id INTEGER NOT NULL REFERENCES locations(id),
  receipt_date TEXT NOT NULL,
  received_by INTEGER REFERENCES users(id),
  notes TEXT NOT NULL DEFAULT '',
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS goods_receipt_lines (
  id INTEGER PRIMARY KEY,
  goods_receipt_id INTEGER NOT NULL REFERENCES goods_receipts(id),
  sku TEXT NOT NULL REFERENCES products(sku),
  qty REAL NOT NULL CHECK(qty > 0),
  unit_cost_cents INTEGER NOT NULL CHECK(unit_cost_cents >= 0)
);

CREATE TABLE IF NOT EXISTS requisitions (
  id INTEGER PRIMARY KEY,
  requisition_number TEXT NOT NULL UNIQUE,
  from_location_id INTEGER NOT NULL REFERENCES locations(id),
  to_location_id INTEGER NOT NULL REFERENCES locations(id),
  status TEXT NOT NULL CHECK(status IN ('draft','submitted','approved','rejected','fulfilled','cancelled')),
  requested_by INTEGER REFERENCES users(id),
  approved_by INTEGER REFERENCES users(id),
  approved_at TEXT,
  notes TEXT NOT NULL DEFAULT '',
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS requisition_lines (
  id INTEGER PRIMARY KEY,
  requisition_id INTEGER NOT NULL REFERENCES requisitions(id) ON DELETE CASCADE,
  sku TEXT NOT NULL REFERENCES products(sku),
  qty REAL NOT NULL CHECK(qty > 0)
);
CREATE TABLE IF NOT EXISTS reservations (
  id INTEGER PRIMARY KEY,
  reservation_number TEXT NOT NULL UNIQUE,
  location_id INTEGER NOT NULL REFERENCES locations(id),
  customer_id INTEGER REFERENCES customers(id),
  status TEXT NOT NULL CHECK(status IN ('active','fulfilled','released','expired')),
  reserved_until TEXT,
  notes TEXT NOT NULL DEFAULT '',
  created_by INTEGER REFERENCES users(id),
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS reservation_lines (
  id INTEGER PRIMARY KEY,
  reservation_id INTEGER NOT NULL REFERENCES reservations(id) ON DELETE CASCADE,
  sku TEXT NOT NULL REFERENCES products(sku),
  qty REAL NOT NULL CHECK(qty > 0)
);

CREATE TABLE IF NOT EXISTS stock_counts (
  id INTEGER PRIMARY KEY,
  count_number TEXT NOT NULL UNIQUE,
  location_id INTEGER NOT NULL REFERENCES locations(id),
  status TEXT NOT NULL CHECK(status IN ('draft','submitted','released','cancelled')),
  counted_by INTEGER REFERENCES users(id),
  released_by INTEGER REFERENCES users(id),
  released_at TEXT,
  notes TEXT NOT NULL DEFAULT '',
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS stock_count_lines (
  id INTEGER PRIMARY KEY,
  stock_count_id INTEGER NOT NULL REFERENCES stock_counts(id) ON DELETE CASCADE,
  sku TEXT NOT NULL REFERENCES products(sku),
  expected_qty REAL NOT NULL,
  counted_qty REAL NOT NULL CHECK(counted_qty >= 0),
  variance_qty REAL NOT NULL,
  UNIQUE(stock_count_id,sku)
);
CREATE TABLE IF NOT EXISTS damage_reports (
  id INTEGER PRIMARY KEY,
  damage_number TEXT NOT NULL UNIQUE,
  location_id INTEGER NOT NULL REFERENCES locations(id),
  reported_by INTEGER REFERENCES users(id),
  reason TEXT NOT NULL,
  status TEXT NOT NULL CHECK(status IN ('posted','reversed')),
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS damage_report_lines (
  id INTEGER PRIMARY KEY,
  damage_report_id INTEGER NOT NULL REFERENCES damage_reports(id),
  sku TEXT NOT NULL REFERENCES products(sku),
  qty REAL NOT NULL CHECK(qty > 0),
  unit_cost_cents INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS sales_returns (
  id INTEGER PRIMARY KEY,
  return_number TEXT NOT NULL UNIQUE,
  sale_id TEXT NOT NULL REFERENCES pos_sales(id),
  location_id INTEGER NOT NULL REFERENCES locations(id),
  customer_id INTEGER REFERENCES customers(id),
  reason TEXT NOT NULL,
  refund_method TEXT NOT NULL CHECK(refund_method IN ('CASH','CARD','MOBILE_MONEY','BANK_TRANSFER','STORE_CREDIT')),
  refund_total_cents INTEGER NOT NULL CHECK(refund_total_cents >= 0),
  received_by INTEGER REFERENCES users(id),
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS sales_return_lines (
  id INTEGER PRIMARY KEY,
  return_id INTEGER NOT NULL REFERENCES sales_returns(id),
  sale_line_id INTEGER NOT NULL REFERENCES pos_sale_lines(id),
  sku TEXT NOT NULL REFERENCES products(sku),
  qty REAL NOT NULL CHECK(qty > 0),
  unit_price_cents INTEGER NOT NULL CHECK(unit_price_cents >= 0),
  line_total_cents INTEGER NOT NULL CHECK(line_total_cents >= 0)
);

CREATE TABLE IF NOT EXISTS wholesale_orders (
  id INTEGER PRIMARY KEY,
  order_number TEXT NOT NULL UNIQUE,
  customer_id INTEGER NOT NULL REFERENCES customers(id),
  location_id INTEGER NOT NULL REFERENCES locations(id),
  status TEXT NOT NULL CHECK(status IN ('draft','confirmed','fulfilled','cancelled')),
  payment_method TEXT NOT NULL CHECK(payment_method IN ('CASH','CARD','MOBILE_MONEY','BANK_TRANSFER','CREDIT')),
  total_cents INTEGER NOT NULL DEFAULT 0 CHECK(total_cents >= 0),
  notes TEXT NOT NULL DEFAULT '',
  created_by INTEGER REFERENCES users(id),
  confirmed_by INTEGER REFERENCES users(id),
  confirmed_at TEXT,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS wholesale_order_lines (
  id INTEGER PRIMARY KEY,
  wholesale_order_id INTEGER NOT NULL REFERENCES wholesale_orders(id) ON DELETE CASCADE,
  sku TEXT NOT NULL REFERENCES products(sku),
  qty REAL NOT NULL CHECK(qty > 0),
  unit_price_cents INTEGER NOT NULL CHECK(unit_price_cents >= 0),
  line_total_cents INTEGER NOT NULL CHECK(line_total_cents >= 0)
);

CREATE TABLE IF NOT EXISTS accounts (
  id INTEGER PRIMARY KEY,
  account_code TEXT NOT NULL UNIQUE,
  account_name TEXT NOT NULL,
  account_type TEXT NOT NULL CHECK(account_type IN ('asset','liability','equity','revenue','expense')),
  normal_balance TEXT NOT NULL CHECK(normal_balance IN ('debit','credit')),
  active INTEGER NOT NULL DEFAULT 1 CHECK(active IN (0,1)),
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS journal_entries (
  id INTEGER PRIMARY KEY,
  journal_number TEXT NOT NULL UNIQUE,
  entry_date TEXT NOT NULL,
  source_type TEXT NOT NULL,
  source_id TEXT NOT NULL,
  description TEXT NOT NULL,
  status TEXT NOT NULL CHECK(status IN ('draft','posted','reversed')),
  debit_total_cents INTEGER NOT NULL DEFAULT 0,
  credit_total_cents INTEGER NOT NULL DEFAULT 0,
  created_by INTEGER REFERENCES users(id),
  posted_by INTEGER REFERENCES users(id),
  posted_at TEXT,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE(source_type,source_id)
);
CREATE TABLE IF NOT EXISTS journal_lines (
  id INTEGER PRIMARY KEY,
  journal_entry_id INTEGER NOT NULL REFERENCES journal_entries(id),
  account_id INTEGER NOT NULL REFERENCES accounts(id),
  description TEXT NOT NULL DEFAULT '',
  debit_cents INTEGER NOT NULL DEFAULT 0 CHECK(debit_cents >= 0),
  credit_cents INTEGER NOT NULL DEFAULT 0 CHECK(credit_cents >= 0),
  CHECK((debit_cents > 0 AND credit_cents = 0) OR (credit_cents > 0 AND debit_cents = 0))
);
CREATE TABLE IF NOT EXISTS expenses (
  id INTEGER PRIMARY KEY,
  expense_number TEXT NOT NULL UNIQUE,
  expense_date TEXT NOT NULL,
  location_id INTEGER REFERENCES locations(id),
  account_id INTEGER NOT NULL REFERENCES accounts(id),
  paid_from_account_id INTEGER NOT NULL REFERENCES accounts(id),
  amount_cents INTEGER NOT NULL CHECK(amount_cents > 0),
  description TEXT NOT NULL,
  reference TEXT NOT NULL DEFAULT '',
  journal_entry_id INTEGER REFERENCES journal_entries(id),
  created_by INTEGER REFERENCES users(id),
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS supplier_bills (
  id INTEGER PRIMARY KEY,
  bill_number TEXT NOT NULL UNIQUE,
  supplier_id INTEGER NOT NULL REFERENCES suppliers(id),
  bill_date TEXT NOT NULL,
  due_date TEXT,
  total_cents INTEGER NOT NULL CHECK(total_cents > 0),
  paid_cents INTEGER NOT NULL DEFAULT 0 CHECK(paid_cents >= 0),
  status TEXT NOT NULL CHECK(status IN ('open','part_paid','paid','void')),
  journal_entry_id INTEGER REFERENCES journal_entries(id),
  created_by INTEGER REFERENCES users(id),
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS customer_invoices (
  id INTEGER PRIMARY KEY,
  invoice_number TEXT NOT NULL UNIQUE,
  customer_id INTEGER NOT NULL REFERENCES customers(id),
  invoice_date TEXT NOT NULL,
  due_date TEXT,
  total_cents INTEGER NOT NULL CHECK(total_cents > 0),
  paid_cents INTEGER NOT NULL DEFAULT 0 CHECK(paid_cents >= 0),
  status TEXT NOT NULL CHECK(status IN ('open','part_paid','paid','void')),
  source_type TEXT NOT NULL DEFAULT 'MANUAL',
  source_id TEXT NOT NULL DEFAULT '',
  journal_entry_id INTEGER REFERENCES journal_entries(id),
  created_by INTEGER REFERENCES users(id),
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS financial_payments (
  id INTEGER PRIMARY KEY,
  payment_number TEXT NOT NULL UNIQUE,
  payment_type TEXT NOT NULL CHECK(payment_type IN ('ap','ar')),
  party_id INTEGER NOT NULL,
  payment_date TEXT NOT NULL,
  amount_cents INTEGER NOT NULL CHECK(amount_cents > 0),
  payment_method TEXT NOT NULL CHECK(payment_method IN ('CASH','CARD','MOBILE_MONEY','BANK_TRANSFER')),
  reference TEXT NOT NULL DEFAULT '',
  supplier_bill_id INTEGER REFERENCES supplier_bills(id),
  customer_invoice_id INTEGER REFERENCES customer_invoices(id),
  journal_entry_id INTEGER REFERENCES journal_entries(id),
  created_by INTEGER REFERENCES users(id),
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS accounting_periods (
  id INTEGER PRIMARY KEY,
  period_code TEXT NOT NULL UNIQUE,
  start_date TEXT NOT NULL,
  end_date TEXT NOT NULL,
  status TEXT NOT NULL CHECK(status IN ('open','closed')),
  closed_by INTEGER REFERENCES users(id),
  closed_at TEXT
);

CREATE TABLE IF NOT EXISTS employees (
  id INTEGER PRIMARY KEY,
  employee_number TEXT NOT NULL UNIQUE,
  full_name TEXT NOT NULL,
  email TEXT NOT NULL DEFAULT '',
  phone TEXT NOT NULL DEFAULT '',
  department TEXT NOT NULL,
  job_title TEXT NOT NULL,
  location_id INTEGER REFERENCES locations(id),
  hire_date TEXT NOT NULL,
  employment_status TEXT NOT NULL CHECK(employment_status IN ('active','leave','terminated')),
  base_salary_cents INTEGER NOT NULL DEFAULT 0 CHECK(base_salary_cents >= 0),
  bank_name TEXT NOT NULL DEFAULT '',
  bank_account_mask TEXT NOT NULL DEFAULT '',
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS employment_contracts (
  id INTEGER PRIMARY KEY,
  employee_id INTEGER NOT NULL REFERENCES employees(id),
  contract_number TEXT NOT NULL UNIQUE,
  contract_type TEXT NOT NULL CHECK(contract_type IN ('permanent','fixed_term','probation','part_time')),
  start_date TEXT NOT NULL,
  end_date TEXT,
  salary_cents INTEGER NOT NULL CHECK(salary_cents >= 0),
  terms TEXT NOT NULL DEFAULT '',
  status TEXT NOT NULL CHECK(status IN ('draft','active','expired','terminated')),
  created_by INTEGER REFERENCES users(id),
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS attendance_records (
  id INTEGER PRIMARY KEY,
  employee_id INTEGER NOT NULL REFERENCES employees(id),
  work_date TEXT NOT NULL,
  status TEXT NOT NULL CHECK(status IN ('present','absent','leave','holiday','half_day')),
  hours REAL NOT NULL DEFAULT 8 CHECK(hours >= 0 AND hours <= 24),
  notes TEXT NOT NULL DEFAULT '',
  recorded_by INTEGER REFERENCES users(id),
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE(employee_id,work_date)
);
CREATE TABLE IF NOT EXISTS payroll_runs (
  id INTEGER PRIMARY KEY,
  payroll_number TEXT NOT NULL UNIQUE,
  period_month TEXT NOT NULL,
  status TEXT NOT NULL CHECK(status IN ('draft','posted')),
  gross_total_cents INTEGER NOT NULL DEFAULT 0,
  deduction_total_cents INTEGER NOT NULL DEFAULT 0,
  net_total_cents INTEGER NOT NULL DEFAULT 0,
  journal_entry_id INTEGER REFERENCES journal_entries(id),
  created_by INTEGER REFERENCES users(id),
  posted_by INTEGER REFERENCES users(id),
  posted_at TEXT,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE(period_month)
);
CREATE TABLE IF NOT EXISTS payroll_lines (
  id INTEGER PRIMARY KEY,
  payroll_run_id INTEGER NOT NULL REFERENCES payroll_runs(id) ON DELETE CASCADE,
  employee_id INTEGER NOT NULL REFERENCES employees(id),
  basic_cents INTEGER NOT NULL CHECK(basic_cents >= 0),
  allowance_cents INTEGER NOT NULL DEFAULT 0 CHECK(allowance_cents >= 0),
  overtime_cents INTEGER NOT NULL DEFAULT 0 CHECK(overtime_cents >= 0),
  tax_cents INTEGER NOT NULL DEFAULT 0 CHECK(tax_cents >= 0),
  pension_cents INTEGER NOT NULL DEFAULT 0 CHECK(pension_cents >= 0),
  other_deduction_cents INTEGER NOT NULL DEFAULT 0 CHECK(other_deduction_cents >= 0),
  gross_cents INTEGER NOT NULL,
  deduction_cents INTEGER NOT NULL,
  net_cents INTEGER NOT NULL CHECK(net_cents >= 0),
  UNIQUE(payroll_run_id,employee_id)
);
CREATE TABLE IF NOT EXISTS daily_closings (
  id INTEGER PRIMARY KEY,
  closing_number TEXT NOT NULL UNIQUE,
  location_id INTEGER NOT NULL REFERENCES locations(id),
  business_date TEXT NOT NULL,
  opening_cash_cents INTEGER NOT NULL DEFAULT 0,
  expected_cash_cents INTEGER NOT NULL DEFAULT 0,
  counted_cash_cents INTEGER NOT NULL DEFAULT 0,
  cash_variance_cents INTEGER NOT NULL DEFAULT 0,
  expected_card_cents INTEGER NOT NULL DEFAULT 0,
  expected_mobile_cents INTEGER NOT NULL DEFAULT 0,
  expected_bank_cents INTEGER NOT NULL DEFAULT 0,
  status TEXT NOT NULL CHECK(status IN ('submitted','closed')),
  submitted_by INTEGER REFERENCES users(id),
  closed_by INTEGER REFERENCES users(id),
  closed_at TEXT,
  notes TEXT NOT NULL DEFAULT '',
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE(location_id,business_date)
);

CREATE TABLE IF NOT EXISTS number_sequences (
  sequence_key TEXT PRIMARY KEY,
  next_value INTEGER NOT NULL DEFAULT 1
);
CREATE INDEX IF NOT EXISTS idx_purchase_order_status ON purchase_orders(status,order_date);
CREATE INDEX IF NOT EXISTS idx_customer_name ON customers(name);
CREATE INDEX IF NOT EXISTS idx_supplier_name ON suppliers(name);
CREATE INDEX IF NOT EXISTS idx_journal_date ON journal_entries(entry_date,status);
CREATE INDEX IF NOT EXISTS idx_employee_status ON employees(employment_status,department);
CREATE INDEX IF NOT EXISTS idx_payroll_period ON payroll_runs(period_month,status);
CREATE INDEX IF NOT EXISTS idx_closings_date ON daily_closings(business_date,location_id);

INSERT OR IGNORE INTO accounts(account_code,account_name,account_type,normal_balance) VALUES
('1000','Cash on Hand','asset','debit'),
('1010','Bank Account','asset','debit'),
('1020','Card Clearing','asset','debit'),
('1030','Mobile Money Clearing','asset','debit'),
('1100','Accounts Receivable','asset','debit'),
('1200','Inventory Asset','asset','debit'),
('2000','Accounts Payable','liability','credit'),
('2010','Goods Received Not Invoiced','liability','credit'),
('2100','Wages Payable','liability','credit'),
('2200','Pension Payable','liability','credit'),
('2300','Income Tax Payable','liability','credit'),
('3000','Owner Equity','equity','credit'),
('4000','Retail and Wholesale Sales','revenue','credit'),
('4010','Sales Returns and Allowances','revenue','debit'),
('4020','Inventory Count Gain','revenue','credit'),
('5000','Cost of Goods Sold','expense','debit'),
('5100','Payroll Expense','expense','debit'),
('5200','Operating Expenses','expense','debit'),
('5300','Inventory Shrinkage and Damage','expense','debit'),
('2400','Customer Store Credit and Other Deductions','liability','credit');

PRAGMA user_version = 2;
