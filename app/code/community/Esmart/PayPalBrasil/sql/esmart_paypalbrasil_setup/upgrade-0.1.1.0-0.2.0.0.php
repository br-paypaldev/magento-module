<?php
$installer = $this;
$installer->startSetup();

$model = Mage::getResourceModel('customer/setup', 'customer_setup');

$model->addAttribute('customer', 'ppal_remembered_cards', array(
    'label' => 'PayPal Remembered Cards',
    'type' => 'varchar',
    'input' => 'hidden',
    'visible'=> false,
    'required' => false
));

$installer->endSetup();