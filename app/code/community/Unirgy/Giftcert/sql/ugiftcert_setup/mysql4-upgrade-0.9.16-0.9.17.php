<?php

$this->startSetup();
$table = $this->getTable('sales_flat_quote_address');
$this->_conn->addColumn($table, 'giftcert_code', 'varchar(255)');
$this->_conn->addColumn($table, 'base_giftcert_balances', 'varchar(255)');
$this->_conn->addColumn($table, 'giftcert_balances', 'varchar(255)');
$this->endSetup();