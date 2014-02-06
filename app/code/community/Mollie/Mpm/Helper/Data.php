<?php

/**
 * Copyright (c) 2012-2014, Mollie B.V.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 *
 * @category    Mollie
 * @package     Mollie_Mpm
 * @author      Mollie B.V. (info@mollie.nl)
 * @version     v4.0.4
 * @copyright   Copyright (c) 2012-2014 Mollie B.V. (https://www.mollie.nl)
 * @license     http://www.opensource.org/licenses/bsd-license.php  Berkeley Software Distribution License (BSD-License 2)
 *
 **/

class Mollie_Mpm_Helper_Data extends Mage_Core_Helper_Abstract
{

	/**
	 * Get payment bank status by order_id
	 *
	 * @return array
	 */
	public function getStatusById($transaction_id)
	{
		/** @var $connection Varien_Db_Adapter_Interface */
		$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
		$status = $connection->fetchAll(
			sprintf(
				"SELECT `bank_status` FROM `%s` WHERE `transaction_id` = %s",
				Mage::getSingleton('core/resource')->getTableName('mollie_payments'),
				$connection->quote($transaction_id)
			)
		);

		return $status[0];
	}

	/**
	 * Get order_id by transaction_id
	 *
	 * @return int|null
	 */
	public function getOrderIdByTransactionId($transaction_id)
	{
		/** @var $connection Varien_Db_Adapter_Interface */
		$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
		$id = $connection->fetchAll(
			sprintf(
				"SELECT `order_id` FROM `%s` WHERE `transaction_id` = %s",
				Mage::getSingleton('core/resource')->getTableName('mollie_payments'),
				$connection->quote($transaction_id)
			)
		);

		if (sizeof($id) > 0)
		{
			return $id[0]['order_id'];
		}
		return NULL;
	}

	/**
	 * Get transaction_id by order_id
	 *
	 * @return int|null
	 */
	public function getTransactionIdByOrderId($order_id)
	{
		/** @var $connection Varien_Db_Adapter_Interface */
		$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
		$id = $connection->fetchAll(
			sprintf(
				"SELECT `transaction_id` FROM `%s` WHERE `order_id` = %s",
				Mage::getSingleton('core/resource')->getTableName('mollie_payments'),
				$connection->quote($order_id)
			)
		);

		if (sizeof($id) > 0)
		{
			return $id[0]['transaction_id'];
		}
		return NULL;
	}

	public function getStoredMethods()
	{
		$connection = Mage::getSingleton('core/resource')->getConnection('core_read');

		$methods = $connection->fetchAll(
			sprintf(
				"SELECT * FROM `%s`",
				Mage::getSingleton('core/resource')->getTableName('mollie_methods')
			)
		);
		return $methods;
	}

	public function setStoredMethods (array $methods)
	{
		$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
		$table_name = Mage::getSingleton('core/resource')->getTableName('mollie_methods');

		foreach ($methods as $method)
		{
			$connection->query(sprintf(
				"INSERT INTO `%s` (`method_id`, `description`) VALUES (%s, %s) ON DUPLICATE KEY UPDATE `id`=`id`",
				$table_name,
				$connection->quote($method['method_id']),
				$connection->quote($method['description'])
			));
		}

		return $this;
	}

	/**
	 * Gets Api key from `config_core_data`
	 *
	 * @return string
	 */
	public function getApiKey()
	{
		return trim(Mage::getStoreConfig("payment/mollie/apikey"));
	}

	/**
	 * Get store config
	 *
	 * @param string $paymentmethod
	 * @param string $key
	 *
	 * @return string
	 */
	public function getConfig($paymentmethod = NULL, $key = NULL)
	{
		$arr = array('active', 'apikey', 'description', 'skip_invoice', 'show_images', 'show_bank_list', 'webhook_tested');
		$paymentmethods = array('mollie');

		if(in_array($key, $arr) && in_array($paymentmethod, $paymentmethods))
			return Mage::getStoreConfig("payment/{$paymentmethod}/{$key}");

		return NULL;
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function getModuleStatus($method_count, $method_limit)
	{
		$core = Mage::helper('core');
		// check missing files
		$needFiles = array();
		$modFiles  = array(
			Mage::getRoot() .'/code/community/Mollie/Mpm/Block/Adminhtml/System/Config/Status.php',
			Mage::getRoot() .'/code/community/Mollie/Mpm/Block/Payment/Api/Form.php',
			Mage::getRoot() .'/code/community/Mollie/Mpm/Block/Payment/Api/Info.php',
			Mage::getRoot() .'/code/community/Mollie/Mpm/controllers/ApiController.php',
			Mage::getRoot() .'/code/community/Mollie/Mpm/etc/adminhtml.xml',
			Mage::getRoot() .'/code/community/Mollie/Mpm/etc/config.xml',
			Mage::getRoot() .'/code/community/Mollie/Mpm/etc/system.xml',
			Mage::getRoot() .'/code/community/Mollie/Mpm/Helper/Data.php',
			Mage::getRoot() .'/code/community/Mollie/Mpm/Helper/Api.php',
			Mage::getRoot() .'/code/community/Mollie/Mpm/Model/Api.php',
			Mage::getRoot() .'/code/community/Mollie/Mpm/Model/Void00.php',

			Mage::getRoot() .'/design/adminhtml/default/default/template/mollie/system/config/status.phtml',
			Mage::getRoot() .'/design/frontend/base/default/layout/mpm.xml',
			Mage::getRoot() .'/design/frontend/base/default/template/mollie/page/exception.phtml',
			Mage::getRoot() .'/design/frontend/base/default/template/mollie/page/fail.phtml',
			Mage::getRoot() .'/design/frontend/base/default/template/mollie/form/details.phtml',

			Mage::getBaseDir('lib') . "/Mollie/src/Mollie/API/Client.php",
			Mage::getBaseDir('lib') . "/Mollie/src/Mollie/API/Autoloader.php",
			Mage::getBaseDir('lib') . "/Mollie/src/Mollie/API/Exception.php",
			Mage::getBaseDir('lib') . "/Mollie/src/Mollie/API/Object/Issuer.php",
			Mage::getBaseDir('lib') . "/Mollie/src/Mollie/API/Object/List.php",
			Mage::getBaseDir('lib') . "/Mollie/src/Mollie/API/Object/Method.php",
			Mage::getBaseDir('lib') . "/Mollie/src/Mollie/API/Object/Payment.php",
			Mage::getBaseDir('lib') . "/Mollie/src/Mollie/API/Resource/Base.php",
			Mage::getBaseDir('lib') . "/Mollie/src/Mollie/API/Resource/Issuers.php",
			Mage::getBaseDir('lib') . "/Mollie/src/Mollie/API/Resource/Methods.php",
			Mage::getBaseDir('lib') . "/Mollie/src/Mollie/API/Resource/Payments.php",
		);

		foreach ($modFiles as $file)
		{
			if(!file_exists($file)) {
				$needFiles[] = '<span style="color:red">'.$file.'</span>';
			}
		}

		if (count($needFiles) > 0)
		{
			return '<b>'.$core->__('Missing file(s) detected!').'</b><br />' . implode('<br />', $needFiles);
		}


		// check version
		if ( version_compare(Mage::getVersion(), '1.4.1.0', '<'))
		{
			return '<b>'.$core->__('Version incompatible!').'</b><br />
				<span style="color:red">'.$core->__('Your Magento version is incompatible with this module!').'<br>
				- '.$core->__('Minimal version requirement: ').'1.4.1.x<br>
				- '.$core->__('Current version: ').Mage::getVersion() .'
				</span>
			';
		}


		// check method count
		if ($method_count > $method_limit)
		{
			return '<b>'.$core->__('Module outdated!').'</b><br />
				<span style="color:#EB5E00">'.sprintf($core->__('Mollie currently provides %d payment methods, while this module only supports %d method slots.'), $method_count, $method_limit).'</span><br />
				'.$core->__('To enable all supported payment methods, get the latest Magento plugin from the <a href="https://www.mollie.nl/betaaldiensten/ideal/modules/" title="Mollie Modules">Mollie Modules list</a>.').'
				<br />
				If no newer version is available, please <a href="https://www.mollie.nl/bedrijf/contact" title="Mollie Support">contact Mollie BV</a>.
			';
		}


		// Check if webhook is set
		if (!Mage::Helper('mpm/data')->getConfig('mollie', 'webhook_tested'))
		{
			return '<b>'.$core->__('Webhook not set!').'</b><br /><span style="color:red;">'.$core->__('Warning: It seems you have not set a webhook in your Mollie profile.').'</span><br />';
		}


		// check deprecated files
		$deprFiles = array();
		$oldFiles = array(
			Mage::getRoot() .'/code/community/Mollie/Mpm/Block/Payment/Idl/Fail.php',
			Mage::getRoot() .'/code/community/Mollie/Mpm/Block/Payment/Idl/Form.php',
			Mage::getRoot() .'/code/community/Mollie/Mpm/Block/Payment/Idl/Info.php',
			Mage::getRoot() .'/code/community/Mollie/Mpm/controllers/IdlController.php',
			Mage::getRoot() .'/code/community/Mollie/Mpm/Helper/Idl.php',
			Mage::getRoot() .'/code/community/Mollie/Mpm/Model/Idl.php',
			Mage::getRoot() .'/design/frontend/base/default/template/mollie/form/idl.phtml',
			Mage::getRoot() .'/design/frontend/base/default/template/mollie/form/api.phtml',
		);

		foreach ($oldFiles as $file)
		{
			if(file_exists($file)) {
				$deprFiles[] = '<span style="color:#EB5E00">'.$file.'</span>';
			}
		}

		if (count($deprFiles) > 0)
		{
			return '<b>'.$core->__('Outdated file(s) found!').'</b><br />' . implode('<br />', $deprFiles) . '<br />'.$core->__('These aren&lsquo;t needed any longer; you might as well delete them.');
		}

		return '<b>'.$core->__('Status').'</b><br /><span style="color:green">'.$core->__('Module status: OK!').'</span>';
	}

	public function getModuleVersion()
	{
		return Mage::getConfig()->getNode('modules')->children()->Mollie_Mpm->version;
	}

}
