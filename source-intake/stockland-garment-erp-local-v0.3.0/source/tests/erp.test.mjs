import test from 'node:test';
import assert from 'node:assert/strict';
import { createApplication } from '../src/server.mjs';

let app, base, ownerCookie, ownerCsrf, managerCookie, managerCsrf, productSku='STK-001', retailSaleId;

async function request(path, { method='GET', body, cookie=ownerCookie, csrf=ownerCsrf, headers={} }={}) {
  const h={...headers};
  if(body!==undefined) h['Content-Type']='application/json';
  if(cookie) h.Cookie=cookie;
  if(csrf && !['GET','HEAD'].includes(method)) h['X-CSRF-Token']=csrf;
  const res=await fetch(base+path,{method,headers:h,body:body===undefined?undefined:JSON.stringify(body)});
  const data=res.status===204?null:await res.json();
  return {res,data};
}

async function login(email,password){
  const r=await request('/api/auth/login',{method:'POST',body:{email,password},cookie:null,csrf:null});
  assert.equal(r.res.status,200,JSON.stringify(r.data));
  return {cookie:r.res.headers.get('set-cookie').split(';')[0],csrf:r.data.csrfToken,user:r.data.user};
}

test('Stockland full-stack local ERP acceptance flow', async t => {
  app=createApplication({dbPath:':memory:'});
  await new Promise(resolve=>app.server.listen(0,'127.0.0.1',resolve));
  base=`http://127.0.0.1:${app.server.address().port}`;
  t.after(async()=>{await new Promise(resolve=>app.server.close(resolve));app.db.close();});

  await t.test('health, setup wizard and secure initial owner',async()=>{
    const health=await request('/api/health',{cookie:null,csrf:null});
    assert.equal(health.res.status,200);assert.equal(health.data.setupRequired,true);
    const setup=await request('/api/setup',{method:'POST',body:{fullName:'Stockland Owner',email:'owner@stockland.local',password:'OwnerPassword123!Strong'},cookie:null,csrf:null});
    assert.equal(setup.res.status,201,JSON.stringify(setup.data));
    ownerCookie=setup.res.headers.get('set-cookie').split(';')[0];ownerCsrf=setup.data.csrfToken;
    assert.equal(setup.data.user.role,'owner');
    const locations=await request('/api/locations');
    assert.equal(locations.res.status,200);assert.equal(locations.data.length,5);
    assert.ok(locations.data.some(x=>x.code==='CENTRAL_WAREHOUSE'));
    const secondSetup=await request('/api/setup',{method:'POST',body:{fullName:'Intruder',email:'x@y.test',password:'SomePassword123!'},cookie:null,csrf:null});
    assert.equal(secondSetup.res.status,409);
  });

  await t.test('CSRF protection and role-based user provisioning',async()=>{
    const noCsrf=await fetch(base+'/api/products',{method:'POST',headers:{Cookie:ownerCookie,'Content-Type':'application/json'},body:JSON.stringify({sku:'STK-X',name:'Bad'})});
    assert.equal(noCsrf.status,403);
    const created=await request('/api/users',{method:'POST',body:{fullName:'Goro Branch Manager',email:'manager@stockland.local',password:'ManagerPassword123!Strong',role:'branch_manager',locationCode:'GORO_BRANCH'}});
    assert.equal(created.res.status,201,JSON.stringify(created.data));
    const profileUpdate=await request(`/api/users/${created.data.id}`,{method:'PATCH',body:{fullName:'Goro Branch Manager Updated'}});
    assert.equal(profileUpdate.res.status,200,JSON.stringify(profileUpdate.data));assert.equal(profileUpdate.data.fullName,'Goro Branch Manager Updated');
    const users=await request('/api/users');assert.equal(users.res.status,200);assert.equal(users.data.length,2);
    const invalidBranch=await request('/api/users',{method:'POST',body:{fullName:'Invalid Manager',email:'invalid-manager@stockland.local',password:'InvalidManagerPassword!123',role:'branch_manager',locationCode:'CENTRAL_WAREHOUSE'}});
    assert.equal(invalidBranch.res.status,400,'branch manager cannot be assigned to Central Warehouse');
    const unassignedWarehouse=await request('/api/users',{method:'POST',body:{fullName:'Unassigned Warehouse Manager',email:'unassigned-warehouse@stockland.local',password:'UnassignedWarehousePassword!123',role:'warehouse_manager'}});
    assert.equal(unassignedWarehouse.res.status,400,'warehouse managers must be assigned to a location; unassigned role must not become global access');
    const centralWarehouseManager=await request('/api/users',{method:'POST',body:{fullName:'Central Warehouse Manager',email:'central-warehouse@stockland.local',password:'CentralWarehousePassword!123',role:'warehouse_manager',locationCode:'CENTRAL_WAREHOUSE'}});
    assert.equal(centralWarehouseManager.res.status,201,'warehouse manager can be assigned to Central Warehouse');
    const wm=await login('central-warehouse@stockland.local','CentralWarehousePassword!123');
    const centralStock=await request('/api/inventory?location=CENTRAL_WAREHOUSE',{cookie:wm.cookie,csrf:wm.csrf});
    assert.equal(centralStock.res.status,200,'warehouse manager can access assigned warehouse');
    const otherBranchStock=await request('/api/inventory?location=GORO_BRANCH',{cookie:wm.cookie,csrf:wm.csrf});
    assert.equal(otherBranchStock.res.status,403,'warehouse manager cannot access a different location');
  });

  await t.test('product variant, goods receipt and ledger-derived stock',async()=>{
    const product=await request('/api/products',{method:'POST',body:{sku:productSku,name:'Cotton Polo Shirt',brand:'Sample Brand',category:'Polo',department:'Men',ageGroup:'Adult',size:'M',color:'Navy',barcode:'STK001NAVM',unit:'piece',cost:200,retailPrice:450,wholesalePrice:350}});
    assert.equal(product.res.status,201,JSON.stringify(product.data));
    const productUpdate=await request(`/api/products/${productSku}`,{method:'PATCH',body:{name:'Cotton Polo Shirt Updated',brand:'Sample Brand',category:'Polo',department:'Men',ageGroup:'Adult',size:'M',color:'Navy',barcode:'STK001NAVM',unit:'piece',cost:200,retailPrice:450,wholesalePrice:350}});
    assert.equal(productUpdate.res.status,200,JSON.stringify(productUpdate.data));
    const receipt=await request('/api/inventory/receipts',{method:'POST',body:{locationCode:'GORO_BRANCH',sku:productSku,qty:15,unitCost:200,note:'Local acceptance fixture'}});
    assert.equal(receipt.res.status,201,JSON.stringify(receipt.data));
    const inventory=await request('/api/inventory?location=GORO_BRANCH');
    assert.equal(inventory.res.status,200);
    const row=inventory.data.find(x=>x.sku===productSku);assert.equal(row.quantity,15);
  });

  await t.test('POS posts once and blocks aggregate same-SKU oversell',async()=>{
    const first=await request('/api/pos/sales',{method:'POST',headers:{'Idempotency-Key':'GRO-SALE-001'},body:{locationCode:'GORO_BRANCH',paymentMethod:'CASH',lines:[{sku:productSku,qty:1,unitPrice:0.01,unitPriceCents:1}]}});
    assert.equal(first.res.status,201,JSON.stringify(first.data));assert.equal(first.data.total,450,'server product pricing is authoritative');retailSaleId=first.data.id;
    const retry=await request('/api/pos/sales',{method:'POST',headers:{'Idempotency-Key':'GRO-SALE-001'},body:{locationCode:'GORO_BRANCH',paymentMethod:'CASH',lines:[{sku:productSku,qty:1}]}});
    assert.equal(retry.res.status,200);assert.equal(retry.data.duplicate,true);assert.equal(retry.data.id,first.data.id);
    const oversell=await request('/api/pos/sales',{method:'POST',headers:{'Idempotency-Key':'GRO-SALE-OVER'},body:{locationCode:'GORO_BRANCH',paymentMethod:'CASH',lines:[{sku:productSku,qty:8},{sku:productSku,qty:7}]}});
    assert.equal(oversell.res.status,409);
    const inventory=await request('/api/inventory?location=GORO_BRANCH');
    assert.equal(inventory.data.find(x=>x.sku===productSku).quantity,14);
    const saleLines=await request(`/api/pos/sales/${encodeURIComponent(retailSaleId||'not-created')}/lines`);
    assert.equal(saleLines.res.status,200,JSON.stringify(saleLines.data));assert.equal(saleLines.data.length,1);assert.equal(saleLines.data[0].sku,productSku);
  });

  await t.test('POS import stages without posting and control-total variance blocks release',async()=>{
    const csv='branch_code,external_sale_id,sku,qty,unit_price,payment_method,sale_time\nGORO_BRANCH,IMP-VARIANCE-001,STK-001,2,5.00,CASH,2026-11-22T12:00:00Z';
    const missingTotal=await request('/api/pos/imports',{method:'POST',body:{fileName:'pos.csv',csv}});
    assert.equal(missingTotal.res.status,400,'a POS control total is mandatory');
    const stagedWrong=await request('/api/pos/imports',{method:'POST',body:{fileName:'pos.csv',declaredTotal:9,csv}});
    assert.equal(stagedWrong.res.status,201);assert.equal(stagedWrong.data.status,'blocked');assert.equal(stagedWrong.data.variance,1);
    const before=await request('/api/inventory?location=GORO_BRANCH');
    assert.equal(before.data.find(x=>x.sku===productSku).quantity,14);
    const csv2=csv.replace('IMP-VARIANCE-001','IMP-POST-001');
    const staged=await request('/api/pos/imports',{method:'POST',body:{fileName:'pos.csv',declaredTotal:10,csv:csv2}});
    assert.equal(staged.res.status,201);assert.equal(staged.data.status,'staged');
    const duplicateUpload=await request('/api/pos/imports',{method:'POST',body:{fileName:'pos.csv',declaredTotal:10,csv:csv2}});
    assert.equal(duplicateUpload.res.status,200);assert.equal(duplicateUpload.data.duplicate,true);assert.equal(duplicateUpload.data.batchId,staged.data.batchId);
    const batch=await request(`/api/pos/imports/${staged.data.batchId}`);assert.equal(batch.res.status,200);
    assert.equal(batch.data.status,'staged');assert.equal(batch.data.rows.length,1);
    const beforeRelease=await request('/api/inventory?location=GORO_BRANCH');
    assert.equal(beforeRelease.data.find(x=>x.sku===productSku).quantity,14,'upload must not change stock');
    const ownerRelease=await request(`/api/pos/imports/${staged.data.batchId}/release`,{method:'POST',body:{}});
    assert.equal(ownerRelease.res.status,403,'owner is not a POS import release approver');
  });

  await t.test('Branch Manager release is final approval and automatically posts',async()=>{
    const manager=await login('manager@stockland.local','ManagerPassword123!Strong');managerCookie=manager.cookie;managerCsrf=manager.csrf;
    const batchList=await request('/api/pos/imports',{cookie:managerCookie,csrf:managerCsrf});
    assert.equal(batchList.res.status,200);const target=batchList.data.find(x=>x.status==='staged');assert.ok(target);
    const released=await request(`/api/pos/imports/${target.id}/release`,{method:'POST',body:{},cookie:managerCookie,csrf:managerCsrf});
    assert.equal(released.res.status,200,JSON.stringify(released.data));assert.equal(released.data.status,'posted');assert.equal(released.data.salesPosted,1);
    const duplicateRelease=await request(`/api/pos/imports/${target.id}/release`,{method:'POST',body:{},cookie:managerCookie,csrf:managerCsrf});
    assert.equal(duplicateRelease.res.status,200);assert.equal(duplicateRelease.data.duplicate,true);
    const inventory=await request('/api/inventory?location=GORO_BRANCH',{cookie:managerCookie,csrf:managerCsrf});
    assert.equal(inventory.res.status,200);assert.equal(inventory.data.find(x=>x.sku===productSku).quantity,12);
    const stockReport=await request('/api/reports/stock',{cookie:managerCookie,csrf:managerCsrf});
    assert.equal(stockReport.res.status,200);assert.ok(stockReport.data.length>0);assert.ok(stockReport.data.every(r=>r.location_code==='GORO_BRANCH'));
    const salesReport=await request('/api/reports/sales',{cookie:managerCookie,csrf:managerCsrf});
    assert.equal(salesReport.res.status,200);assert.ok(salesReport.data.every(r=>r.location_code==='GORO_BRANCH'));
    const forbiddenLocation=await request('/api/inventory?location=GERJI_BRANCH',{cookie:managerCookie,csrf:managerCsrf});
    assert.equal(forbiddenLocation.res.status,403);
  });

  await t.test('POS import release transaction rolls back as one unit and remains retryable',async()=>{
    const csv='branch_code,external_sale_id,sku,qty,unit_price,payment_method,sale_time\nGORO_BRANCH,IMP-ROLLBACK-001,STK-001,1,4.50,CASH,2026-11-22T13:00:00Z';
    const staged=await request('/api/pos/imports',{method:'POST',body:{fileName:'rollback-test.csv',declaredTotal:4.5,csv}});
    assert.equal(staged.res.status,201,JSON.stringify(staged.data));assert.equal(staged.data.status,'staged');
    const before=app.db.prepare(`SELECT COALESCE(SUM(qty_delta),0) qty FROM inventory_movements WHERE sku=? AND location_id=(SELECT id FROM locations WHERE code='GORO_BRANCH')`).get(productSku).qty;
    app.db.exec(`CREATE TRIGGER test_force_pos_journal_failure BEFORE INSERT ON journal_entries WHEN NEW.source_type='POS_SALE' BEGIN SELECT RAISE(ABORT,'release rollback test'); END;`);
    const originalConsoleError=console.error;
    let failed;
    try {
      console.error=()=>{};
      failed=await request(`/api/pos/imports/${staged.data.batchId}/release`,{method:'POST',body:{},cookie:managerCookie,csrf:managerCsrf});
    } finally { console.error=originalConsoleError; }
    assert.equal(failed.res.status,500,'injected journal failure must return a server error');
    const state=app.db.prepare('SELECT status,last_error FROM import_batches WHERE id=?').get(staged.data.batchId);
    assert.equal(state.status,'staged','batch state remains staged after transactional rollback');
    assert.match(state.last_error,/release rollback test/);
    assert.equal(app.db.prepare('SELECT COUNT(*) n FROM pos_sales WHERE external_sale_id=?').get('IMP-ROLLBACK-001').n,0,'failed release leaves no sale header');
    const after=app.db.prepare(`SELECT COALESCE(SUM(qty_delta),0) qty FROM inventory_movements WHERE sku=? AND location_id=(SELECT id FROM locations WHERE code='GORO_BRANCH')`).get(productSku).qty;
    assert.equal(after,before,'failed release leaves inventory unchanged');
    app.db.exec('DROP TRIGGER test_force_pos_journal_failure');
    const retry=await request(`/api/pos/imports/${staged.data.batchId}/release`,{method:'POST',body:{},cookie:managerCookie,csrf:managerCsrf});
    assert.equal(retry.res.status,200,JSON.stringify(retry.data));assert.equal(retry.data.status,'posted');
    assert.equal(app.db.prepare('SELECT COUNT(*) n FROM pos_sales WHERE external_sale_id=?').get('IMP-ROLLBACK-001').n,1,'retry posts the sale once');
  });

  await t.test('branch transfer posts balanced ledger legs atomically',async()=>{
    const overTransfer=await request('/api/inventory/transfers',{method:'POST',body:{fromLocationCode:'GORO_BRANCH',toLocationCode:'GERJI_BRANCH',sku:productSku,qty:99}});
    assert.equal(overTransfer.res.status,409,'source stock shortage blocks the whole transfer');
    const transfer=await request('/api/inventory/transfers',{method:'POST',body:{fromLocationCode:'GORO_BRANCH',toLocationCode:'GERJI_BRANCH',sku:productSku,qty:3}});
    assert.equal(transfer.res.status,201,JSON.stringify(transfer.data));
    const goro=await request('/api/inventory?location=GORO_BRANCH');
    const gerji=await request('/api/inventory?location=GERJI_BRANCH');
    assert.equal(goro.data.find(x=>x.sku===productSku).quantity,8);
    assert.equal(gerji.data.find(x=>x.sku===productSku).quantity,3);
    const linked=app.db.prepare("SELECT COUNT(*) AS n FROM inventory_movements WHERE source_type='TRANSFER' AND source_id=?").get(transfer.data.transferId);
    assert.equal(linked.n,2);
  });

  await t.test('enterprise masters, procurement and supplier receiving post inventory and AP',async()=>{
    const supplier=await request('/api/suppliers',{method:'POST',body:{code:'SUP-001',name:'Stockland Test Supplier',contactName:'Receiving Desk',phone:'+251900000001',email:'supplier@stockland.local',address:'Addis Ababa'}});
    assert.equal(supplier.res.status,201,JSON.stringify(supplier.data));
    const purchase=await request('/api/procurement/orders',{method:'POST',body:{supplierId:supplier.data.id,locationCode:'GORO_BRANCH',orderDate:'2026-11-22',expectedDate:'2026-11-28',notes:'Garment replenishment',lines:[{sku:productSku,qty:5,unitCost:200}]}});
    assert.equal(purchase.res.status,201,JSON.stringify(purchase.data));
    const receive=await request(`/api/procurement/orders/${purchase.data.id}/receive`,{method:'POST',body:{notes:'Receiving acceptance batch'}});
    assert.equal(receive.res.status,201,JSON.stringify(receive.data));
    assert.equal(receive.data.total,1000);
    const stock=await request('/api/inventory?location=GORO_BRANCH');
    assert.equal(stock.data.find(x=>x.sku===productSku).quantity,13);
    const bills=await request('/api/finance/ap-bills');
    assert.equal(bills.res.status,200);assert.equal(bills.data.length,1);assert.equal(bills.data[0].total,1000);
  });

  await t.test('customer master, wholesale order, confirmation and stock posting',async()=>{
    const customer=await request('/api/customers',{method:'POST',body:{code:'CUS-001',name:'Stockland Wholesale Partner',customerType:'wholesale',contactName:'Buying Office',phone:'+251911000001',creditLimit:50000,address:'Addis Ababa'}});
    assert.equal(customer.res.status,201,JSON.stringify(customer.data));
    const order=await request('/api/wholesale/orders',{method:'POST',body:{customerId:customer.data.id,locationCode:'GORO_BRANCH',paymentMethod:'BANK_TRANSFER',notes:'Wholesale reseller order',lines:[{sku:productSku,qty:2}]}});
    assert.equal(order.res.status,201,JSON.stringify(order.data));assert.equal(order.data.total,700);
    const confirmed=await request(`/api/wholesale/orders/${order.data.id}/confirm`,{method:'POST',body:{}});
    assert.equal(confirmed.res.status,200,JSON.stringify(confirmed.data));assert.equal(confirmed.data.status,'confirmed');
    const stock=await request('/api/inventory?location=GORO_BRANCH');
    assert.equal(stock.data.find(x=>x.sku===productSku).quantity,11);
  });

  await t.test('reservations protect availability and physical counts reconcile to counted quantity',async()=>{
    const reservation=await request('/api/inventory/reservations',{method:'POST',body:{locationCode:'GORO_BRANCH',notes:'Customer pickup hold',lines:[{sku:productSku,qty:2}]}});
    assert.equal(reservation.res.status,201,JSON.stringify(reservation.data));
    const blocked=await request('/api/pos/sales',{method:'POST',headers:{'Idempotency-Key':'GRO-RESERVED-OVERSALE'},body:{locationCode:'GORO_BRANCH',paymentMethod:'CASH',lines:[{sku:productSku,qty:11}]}});
    assert.equal(blocked.res.status,409,'reserved units are excluded from sellable stock');
    const release=await request(`/api/inventory/reservations/${reservation.data.id}/release`,{method:'POST',body:{}});
    assert.equal(release.res.status,200);assert.equal(release.data.status,'released');
    const count=await request('/api/inventory/counts',{method:'POST',body:{locationCode:'GORO_BRANCH',notes:'Acceptance physical count',lines:[{sku:productSku,countedQty:10}]}});
    assert.equal(count.res.status,201,JSON.stringify(count.data));
    const countRelease=await request(`/api/inventory/counts/${count.data.id}/release`,{method:'POST',body:{}});
    assert.equal(countRelease.res.status,200,JSON.stringify(countRelease.data));assert.ok(countRelease.data.journalId,'physical count variance must create accounting evidence');
    const stock=await request('/api/inventory?location=GORO_BRANCH');
    assert.equal(stock.data.find(x=>x.sku===productSku).quantity,10);
  });

  await t.test('damage posting, linked sales returns and location requisitions',async()=>{
    const damage=await request('/api/inventory/damages',{method:'POST',body:{locationCode:'GORO_BRANCH',reason:'Packaging and fabric damage identified during inspection',lines:[{sku:productSku,qty:1}]}});
    assert.equal(damage.res.status,201,JSON.stringify(damage.data));
    const returned=await request('/api/pos/returns',{method:'POST',body:{saleId:retailSaleId,reason:'Customer size exchange return',refundMethod:'STORE_CREDIT',lines:[{saleLineId:app.db.prepare('SELECT id FROM pos_sale_lines WHERE sale_id=?').get(retailSaleId).id,qty:1}]}});
    assert.equal(returned.res.status,201,JSON.stringify(returned.data));assert.equal(returned.data.refundTotal,450);
    const centralReceipt=await request('/api/inventory/receipts',{method:'POST',body:{locationCode:'CENTRAL_WAREHOUSE',sku:productSku,qty:20,unitCost:200,note:'Replenish central warehouse for requisition'}});
    assert.equal(centralReceipt.res.status,201);
    const damageRows=await request('/api/inventory/damages');assert.equal(damageRows.res.status,200);assert.equal(damageRows.data.length,1);
    const plusAdjustment=await request('/api/inventory/adjustments',{method:'POST',body:{locationCode:'CENTRAL_WAREHOUSE',sku:productSku,qtyDelta:1,reason:'Verified opening balance correction'}});
    assert.equal(plusAdjustment.res.status,201,JSON.stringify(plusAdjustment.data));
    const minusAdjustment=await request('/api/inventory/adjustments',{method:'POST',body:{locationCode:'CENTRAL_WAREHOUSE',sku:productSku,qtyDelta:-1,reason:'Reverse verified test correction'}});
    assert.equal(minusAdjustment.res.status,201,JSON.stringify(minusAdjustment.data));
    const adjustmentJournals=app.db.prepare("SELECT COUNT(*) n FROM journal_entries WHERE source_type='STOCK_ADJUSTMENT'").get().n;
    assert.equal(adjustmentJournals,2,'positive and negative stock adjustments each create accounting journals');
    const reqn=await request('/api/inventory/requisitions',{method:'POST',body:{fromLocationCode:'CENTRAL_WAREHOUSE',toLocationCode:'MEXICO_BRANCH',notes:'Branch replenishment request',lines:[{sku:productSku,qty:5}]}});
    assert.equal(reqn.res.status,201,JSON.stringify(reqn.data));
    const approve=await request(`/api/inventory/requisitions/${reqn.data.id}/approve`,{method:'POST',body:{}});
    assert.equal(approve.res.status,200,JSON.stringify(approve.data));
    const fulfill=await request(`/api/inventory/requisitions/${reqn.data.id}/fulfill`,{method:'POST',body:{}});
    assert.equal(fulfill.res.status,200,JSON.stringify(fulfill.data));assert.equal(fulfill.data.status,'fulfilled');
    const mexico=await request('/api/inventory?location=MEXICO_BRANCH');
    assert.equal(mexico.data.find(x=>x.sku===productSku).quantity,5);
  });

  await t.test('double-entry finance, AP/AR payments, HR, payroll and daily closing',async()=>{
    const expense=await request('/api/finance/expenses',{method:'POST',body:{expenseDate:'2026-11-22',locationCode:'GORO_BRANCH',expenseAccountCode:'5200',paidFromAccountCode:'1000',amount:25,description:'Branch operating expense',reference:'EXP-TEST-01'}});
    assert.equal(expense.res.status,201,JSON.stringify(expense.data));
    const bills=await request('/api/finance/ap-bills');assert.equal(bills.res.status,200);assert.ok(bills.data[0].balance>0);
    const apPayment=await request(`/api/finance/ap-bills/${bills.data[0].id}/payments`,{method:'POST',body:{amount:300,paymentMethod:'BANK_TRANSFER',reference:'AP-PAY-01'}});
    assert.equal(apPayment.res.status,201,JSON.stringify(apPayment.data));
    const invoice=await request('/api/finance/ar-invoices',{method:'POST',body:{customerId:1,total:1200,invoiceDate:'2026-11-22',dueDate:'2026-12-22'}});
    assert.equal(invoice.res.status,201,JSON.stringify(invoice.data));
    const arPayment=await request(`/api/finance/ar-invoices/${invoice.data.id}/payments`,{method:'POST',body:{amount:200,paymentMethod:'CASH',reference:'AR-REC-01'}});
    assert.equal(arPayment.res.status,201,JSON.stringify(arPayment.data));
    const employee=await request('/api/hr/employees',{method:'POST',body:{employeeNumber:'STK-EMP-001',fullName:'Test Operations Employee',email:'employee@stockland.local',phone:'+251922000001',department:'Retail Operations',jobTitle:'Sales Associate',locationCode:'GORO_BRANCH',hireDate:'2026-01-01',baseSalary:12000,bankName:'Commercial Bank',bankAccountMask:'****4321'}});
    assert.equal(employee.res.status,201,JSON.stringify(employee.data));
    const contract=await request('/api/hr/contracts',{method:'POST',body:{employeeId:employee.data.id,contractNumber:'CON-TEST-001',contractType:'permanent',startDate:'2026-01-01',salary:12000,terms:'Standard employment terms'}});
    assert.equal(contract.res.status,201,JSON.stringify(contract.data));
    const attendance=await request('/api/hr/attendance',{method:'POST',body:{employeeId:employee.data.id,workDate:'2026-11-22',status:'present',hours:8,notes:'Acceptance test'}});
    assert.equal(attendance.res.status,200,JSON.stringify(attendance.data));
    const payroll=await request('/api/hr/payroll',{method:'POST',body:{periodMonth:'2026-11',lines:[{employeeId:employee.data.id,basicSalary:12000,allowance:1000,overtime:250,incomeTax:1000,pension:500,otherDeduction:100}]}});
    assert.equal(payroll.res.status,201,JSON.stringify(payroll.data));assert.equal(payroll.data.net,11650);
    const payrollPost=await request(`/api/hr/payroll/${payroll.data.id}/post`,{method:'POST',body:{}});
    assert.equal(payrollPost.res.status,200,JSON.stringify(payrollPost.data));
    const closing=await request('/api/operations/daily-closings',{method:'POST',body:{locationCode:'GORO_BRANCH',businessDate:'2026-11-22',openingCash:0,countedCash:460,notes:'End of day acceptance closing'},cookie:managerCookie,csrf:managerCsrf});
    assert.equal(closing.res.status,201,JSON.stringify(closing.data));
    const close=await request(`/api/operations/daily-closings/${closing.data.id}/close`,{method:'POST',body:{},cookie:managerCookie,csrf:managerCsrf});
    assert.equal(close.res.status,200,JSON.stringify(close.data));
    const balance=await request('/api/finance/trial-balance');
    assert.equal(balance.res.status,200);
    const debit=balance.data.reduce((s,r)=>s+r.debit,0),credit=balance.data.reduce((s,r)=>s+r.credit,0);
    assert.equal(debit,credit,'posted journal debits and credits must balance');
  });

  await t.test('ledger and audit history reject update/delete; static app serves',async()=>{
    assert.throws(()=>app.db.prepare('UPDATE inventory_movements SET qty_delta=0 WHERE id=1').run(),/immutable/);
    assert.throws(()=>app.db.prepare('DELETE FROM inventory_movements WHERE id=1').run(),/immutable/);
    assert.throws(()=>app.db.prepare("UPDATE audit_log SET action='rewrite' WHERE id=1").run(),/immutable/);
    const audit=await request('/api/audit');assert.equal(audit.res.status,200);assert.ok(audit.data.length>=5);
    const home=await fetch(base+'/');assert.equal(home.status,200);assert.match(await home.text(),/Stockland ERP/);
    const js=await fetch(base+'/app.js');assert.equal(js.status,200);assert.match(await js.text(),/start\(\)/);
    const pwaManifest=await fetch(base+'/manifest.webmanifest');assert.equal(pwaManifest.status,200);assert.match((await pwaManifest.text()),/Stockland/);
    const serviceWorker=await fetch(base+'/sw.js');assert.equal(serviceWorker.status,200);assert.match(await serviceWorker.text(),/stockland-shell-v0\.3\.0/);
    const icon=await fetch(base+'/icons/stockland.svg');assert.equal(icon.status,200);assert.match(await icon.text(),/<svg/);
    const countTemplate=await fetch(base+'/physical-count-template.csv');assert.equal(countTemplate.status,200);assert.match(await countTemplate.text(),/sku,countedQty/);
  });
});
