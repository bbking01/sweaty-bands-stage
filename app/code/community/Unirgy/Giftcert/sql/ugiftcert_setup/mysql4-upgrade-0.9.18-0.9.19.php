<?php

$conn = $this->_conn;

$table = $this->getTable('ugiftcert_history');
$conn->dropForeignKey($table, 'FK_ugiftcert_history');
$conn->addConstraint('FK_ugiftcert_history', $table, 'cert_id', $this->getTable('ugiftcert_cert'), 'cert_id', 'CASCADE', 'CASCADE');
