# Domain modules

`enterprise.mjs` implements supplier/customer masters, procurement, requisitions, reservations, counts, damage, returns, wholesale, finance, HR, payroll and daily closing. It exports the shared balanced journal helper used by the core server. Keep new business routes behind the shared role/location and transaction helpers.
