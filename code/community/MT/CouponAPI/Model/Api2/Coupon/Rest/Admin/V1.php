<?php
class MT_CouponAPI_Model_Api2_Coupon_Rest_Admin_V1 extends MT_CouponAPI_Model_Api2_Coupon {
	protected function _retrieve() {
		$ruleId = $this->getRequest ()->getParam ( 'rule_id' );
		if (! $ruleId) {
			$this->_critical ( Mage::helper ( 'salesrule' )->__ ( 'Rule ID not specified.' ), Mage_Api2_Model_Server::HTTP_BAD_REQUEST );
		}
		try {
			$rule = Mage::getModel ( 'salesrule/rule' )->load ( $ruleId );
			$generator = Mage::getModel ( 'salesrule/coupon_massgenerator' );
			$generator->setFormat ( Mage_SalesRule_Helper_Coupon::COUPON_FORMAT_ALPHANUMERIC );
			$generator->setDash ( 4 );
			$generator->setLength ( 16 );
			$generator->setPrefix ( "" );
			$generator->setSuffix ( "" );
			$rule->setCouponCodeGenerator ( $generator );
			$rule->setCouponType ( Mage_SalesRule_Model_Rule::COUPON_TYPE_AUTO );
			$coupon = $rule->acquireCoupon ();
			$coupon->setType ( Mage_SalesRule_Helper_Coupon::COUPON_TYPE_SPECIFIC_AUTOGENERATED )->save ();
			$code = $coupon->getId ();
		} catch ( Exception $e ) {
			$this->_critical ( Mage::helper ( 'salesrule' )->__ ( 'Rule ID invalid.' ), Mage_Api2_Model_Server::HTTP_BAD_REQUEST );
		}
		$response = array (
				"Coupon" => $code 
		);
		$result = array ();
		$result [] = $response;
		return $result;
	}

	protected function _loadSalesRule($ruleId) {
		if (! $ruleId) {
			$this->_critical ( Mage::helper ( 'salesrule' )->__ ( 'Rule ID not specified.' ), Mage_Api2_Model_Server::HTTP_BAD_REQUEST );
		}
		$rule = Mage::getModel ( 'salesrule/rule' )->load ( $ruleId );
		if (! $rule->getId ()) {
			$this->_critical ( Mage::helper ( 'salesrule' )->__ ( 'Rule was not found.' ), Mage_Api2_Model_Server::HTTP_NOT_FOUND );
		}
		return $rule;
	}
}