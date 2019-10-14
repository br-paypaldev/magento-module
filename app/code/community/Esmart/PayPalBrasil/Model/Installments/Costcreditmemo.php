<?php
class Esmart_PayPalBrasil_Model_Installments_Costcreditmemo extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();
        if ($order->getEsmartPaypalbrasilCostAmount() > 0) {

            $creditmemo->setEsmartPaypalbrasilCostAmount($order->getEsmartPaypalbrasilCostAmount());
            $creditmemo->setBaseEsmartPaypalbrasilCostAmount($order->getBaseEsmartPaypalbrasilCostAmount());

            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $creditmemo->getEsmartPaypalbrasilCostAmount());
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $creditmemo->getBaseEsmartPaypalbrasilCostAmount());
        }

        if ($order->getEsmartPaypalbrasilDiscountAmount() != 0) {

            $creditmemo->setEsmartPaypalbrasilDiscountAmount($order->getEsmartPaypalbrasilDiscountAmount());
            $creditmemo->setBaseEsmartPaypalbrasilDiscountAmount($order->getBaseEsmartPaypalbrasilDiscountAmount());

            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $creditmemo->getEsmartPaypalbrasilDiscountAmount());
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() +  $creditmemo->getBaseEsmartPaypalbrasilDiscountAmount());
        }

        return $this;
    }

}

