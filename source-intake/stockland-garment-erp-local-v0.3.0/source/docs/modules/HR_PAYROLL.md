# HR and payroll

## Purpose

Maintain employees, employment contracts, attendance and monthly payroll calculation/posting records.

## Employee record

Employee number, full name, contact details, department, job title, location, hire date, employment status, base salary and masked bank account details.

## Payroll workflow

`EMPLOYEE MASTER → ATTENDANCE → PAYROLL DRAFT → GROSS/DEDUCTION/NET CALCULATION → JOURNAL POSTING`

Payroll lines include basic pay, allowances, overtime, income tax, pension, other deduction, gross, deductions and net. Posting writes a balanced journal and links the payroll run to the journal entry.

## Tables

`employees`, `employment_contracts`, `attendance_records`, `payroll_runs`, `payroll_lines`.

## API

`GET/POST /api/hr/employees`, `GET/POST /api/hr/contracts`, `GET/POST /api/hr/attendance`, `GET/POST /api/hr/payroll`, `POST /api/hr/payroll/{id}/post`.
