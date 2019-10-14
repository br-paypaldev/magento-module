<?php
/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

$options = array(
    'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL,
    'length'     => '12,4',
    'visible'  => true,
    'required' => false,
    'comment' => 'Cost Installments',
    'nullable'  => false,
    'default'   => '0.0000'
);

$connection = $installer->getConnection();

$sales_quote_address = $installer->getTable('sales/quote_address');
if ($connection->tableColumnExists($sales_quote_address,'esmart_paypalbrasil_discount_amount') === false ){
    $connection->addColumn($sales_quote_address, 'esmart_paypalbrasil_discount_amount',$options);
}
if ($connection->tableColumnExists($sales_quote_address,'base_esmart_paypalbrasil_discount_amount') === false ){
    $installer->getConnection()->addColumn($sales_quote_address, 'base_esmart_paypalbrasil_discount_amount',$options);
}

$sales_order = $installer->getTable('sales/order');
if ($connection->tableColumnExists($sales_order,'esmart_paypalbrasil_discount_amount') === false ) {
    $installer->getConnection()->addColumn($sales_order, 'esmart_paypalbrasil_discount_amount', $options);
}
if ($connection->tableColumnExists($sales_order,'base_esmart_paypalbrasil_discount_amount') === false ) {
    $installer->getConnection()->addColumn($sales_order, 'base_esmart_paypalbrasil_discount_amount', $options);
}

$sales_invoice = $installer->getTable('sales/invoice');
if ($connection->tableColumnExists($sales_invoice,'esmart_paypalbrasil_discount_amount') === false ) {
    $installer->getConnection()->addColumn($sales_invoice, 'esmart_paypalbrasil_discount_amount', $options);
}
if ($connection->tableColumnExists($sales_invoice,'base_esmart_paypalbrasil_discount_amount') === false ) {
    $installer->getConnection()->addColumn($sales_invoice, 'base_esmart_paypalbrasil_discount_amount', $options);
}

$sales_creditmemo = $installer->getTable('sales/creditmemo');
if ($connection->tableColumnExists($sales_creditmemo,'esmart_paypalbrasil_discount_amount') === false ) {
    $installer->getConnection()->addColumn($sales_creditmemo, 'esmart_paypalbrasil_discount_amount', $options);
}
if ($connection->tableColumnExists($sales_creditmemo,'base_esmart_paypalbrasil_discount_amount') === false ) {
    $installer->getConnection()->addColumn($sales_creditmemo, 'base_esmart_paypalbrasil_discount_amount', $options);
}

$installer->endSetup();
