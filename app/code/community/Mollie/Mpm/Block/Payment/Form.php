<?php
/**
 * Copyright (c) 2012-2018, Mollie B.V.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *   this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
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
 * @copyright   Copyright (c) 2012-2018 Mollie B.V. (https://www.mollie.nl)
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD-License 2
 */

class Mollie_Mpm_Block_Payment_Form extends Mage_Payment_Block_Form
{

    /**
     * @var Mollie_Mpm_Helper_Api
     */
    public $mollieHelper;

    /**
     * @inheritdoc
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('mollie/mpm/payment/form.phtml');
        $this->mollieHelper = Mage::helper('mpm/api');
    }

    /**
     * @return string
     */
    public function getMethodLabelAfterHtml()
    {
        $code = $this->getMethod()->getCode();
        $method = $this->getMethodByCode($code);

        if (!$this->hasData('_method_label_html')) {
            $code = $this->getMethod()->getCode();
            $title = $this->mollieHelper->getMethodTitle($code) ?: $method['description'];

            if (!$this->mollieHelper->showImages()) {
                return '';
            }

            $labelBlock = Mage::app()->getLayout()->createBlock('core/template', null, array(
                'template'             => 'mollie/mpm/payment/label.phtml',
                'payment_method_icon'  => $method['image']->normal,
                'payment_method_label' => $title,
                'payment_method_class' => $code
            ));
            $this->setData('_method_label_html', $labelBlock->toHtml());
        }
        return $this->getData('_method_label_html');
    }

    /**
     * @param $code
     *
     * @return mixed
     */
    public function getMethodByCode($code)
    {
        return $this->mollieHelper->getMethodByCode($code);
    }
}
