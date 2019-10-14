<?php
class
Esmart_PayPalBrasil_Block_Adminhtml_Totals_Invoice_Cost extends Mage_Adminhtml_Block_Sales_Order_Totals_Item
{
    public function initTotals()
    {
        $totalsBlock = $this->getParentBlock();
        $invoice = $totalsBlock->getInvoice();

        $totalsBlock->addTotal(new Varien_Object(array(
             'code' => 'rewardpoints',
             'label' => $this->__('Custom Discount'),
             'value' => -$invoice->getCustomDiscount(),
        )), 'subtotal');
    }
}