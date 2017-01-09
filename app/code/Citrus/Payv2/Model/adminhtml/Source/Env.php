<?php

namespace Citrus\Payv2\Model\Adminhtml\Source;

use Magento\Payment\Model\Method\AbstractMethod;

class Env implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
	{
		return array(
				array('value' => 'sandbox','label' => 'sandbox'),
				array('value' => 'staging','label' => 'staging'),
				array('value' => 'production','label' => 'production')
				);
	}
}