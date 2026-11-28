# Product catalogue and garment variants

## Purpose

Maintain the saleable Stockland SKU master for brands, product categories, departments, age groups and garment size/colour variants.

## Fields

`sku`, `name`, `brand`, `category`, `department`, `age_group`, `size`, `color`, `barcode`, `unit`, `cost_cents`, `retail_cents`, `wholesale_cents`, `active`, timestamps.

## Rules

- SKU is the stable unique product/variant identity.
- Optional barcode is unique.
- Size and colour are stored on each saleable SKU.
- Cost, retail and wholesale amounts are stored as integer cents.
- Product creation does not create stock; receipt or adjustment creates movement records.
- Retail POS obtains retail price from server-side Product Master.

## UI/API

Product Master lives in `public/app.js`. Routes: `GET/POST /api/products`, `PATCH /api/products/{sku}`, `GET/POST /api/brands`, `GET/POST /api/categories`.

## Tables

`products`, `brands`, `categories`.
