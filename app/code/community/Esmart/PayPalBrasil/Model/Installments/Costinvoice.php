<?php
class Esmart_PayPalBrasil_Model_Installments_Costinvoice extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $order = $invoice->getOrder();

        if ($order->getEsmartPaypalbrasilCostAmount() > 0) {

            $invoice->setEsmartPaypalbrasilCostAmount($order->getEsmartPaypalbrasilCostAmount());
            $invoice->setBaseEsmartPaypalbrasilCostAmount($order->getBaseEsmartPaypalbrasilCostAmount());

            $invoice->setGrandTotal($invoice->getGrandTotal() + $invoice->getEsmartPaypalbrasilCostAmount());
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $invoice->getBaseEsmartPaypalbrasilCostAmount());
        }

        if ($order->getEsmartPaypalbrasilDiscountAmount() != 0) {

            $invoice->setEsmartPaypalbrasilDiscountAmount($order->getEsmartPaypalbrasilDiscountAmount());
            $invoice->setBaseEsmartPaypalbrasilDiscountAmount($order->getBaseEsmartPaypalbrasilDiscountAmount());

            $invoice->setGrandTotal($invoice->getGrandTotal() + $invoice->getEsmartPaypalbrasilDiscountAmount());
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $invoice->getBaseEsmartPaypalbrasilDiscountAmount());
        }

        return $this;
    }
}


