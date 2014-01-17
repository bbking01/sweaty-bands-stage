<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pp
 * Date: 11-6-30
 * Time: 13:37
 */

class Unirgy_Giftcert_Model_Source_Pdf
    extends Varien_Object
{
    public function toOptionHash($selector = false)
    {
        $hlp     = Mage::helper('ugiftcert');
        $conPath = 'ugiftcert/pdf/';
        switch ($this->getPath()) {
            case $conPath . 'units':
            case 'units':
                $options = $this->unitsHash();
                break;
            case 'fields' :
                $options = $this->getFieldsHash();
                break;
            case $conPath . 'cert_number_font_weight':
            case $conPath . 'pin_font_weight':
            case $conPath . 'field_1_font_weight':
            case $conPath . 'field_2_font_weight':
            case $conPath . 'field_3_font_weight':
            case $conPath . 'field_4_font_weight':
            case $conPath . 'field_5_font_weight':
            case 'font_variants':
                $options = $this->weightsHash();
                break;
            case 'use_font':
                $selector = true;
                $options  = array(
                    'B'         => $hlp->__('* Magento Bundled Fonts'),
                    'TIMES'     => $hlp->__('Times New Roman'),
                    'HELVETICA' => $hlp->__('Helvetica'),
                    'COURIER'   => $hlp->__('Courier'),
                );
                break;
            case 'ugiftcert/email/pdf_template':
            default :
                $options = $this->getPdfTemplatesHash();
                break;
        }


        if ($selector) {
            $options = array('' => $hlp->__('* Please select')) + $options;
        }

        return $options;
    }

    public function getAllOptions()
    {
        return $this->toOptionArray();
    }

    public function toOptionArray($selector = false)
    {
        $arr = array();
        foreach ($this->toOptionHash($selector) as $v=> $l) {
            $arr[] = array('label'=> $l, 'value'=> $v);
        }
        return $arr;
    }

    public function unitsArray($selector = false)
    {
        $src = $this->unitsHash($selector);
        $arr = array();
        foreach ($src as $value => $label) {
            $arr[] = array('value' => $value, 'label' => $label);
        }

        return $arr;
    }

    public function unitsHash($selector = false)
    {
        $hlp = Mage::helper('ugiftcert');
        $arr = array();
        if ($selector) {
            $arr[''] = $hlp->__('* Please select');
        }
        $arr['mm']  = $hlp->__('Millimeters');
        $arr['in']  = $hlp->__('Inches');
        $arr['pts'] = $hlp->__('Points');
        return $arr;
    }

    public function weightsHash($selector = false)
    {
        $hlp = Mage::helper('ugiftcert');
        $arr = array();
        if ($selector) {
            $arr[''] = $hlp->__('* Please select');
        }
        $arr['r'] = $hlp->__('Regular');
        $arr['b'] = $hlp->__('Bold');
        $arr['i'] = $hlp->__('Italic');

        return $arr;
    }

    public function weightsArray($selector = false)
    {
        $src = $this->weightsHash($selector);
        $arr = array();
        foreach ($src as $value => $label) {
            $arr[] = array('value' => $value, 'label' => $label);
        }

        return $arr;
    }

    protected function getFieldsHash()
    {
        /* @var $hlp Unirgy_Giftcert_Helper_Data */
        $hlp    = Mage::helper('ugiftcert');
        $fields = $hlp->getGiftcertOptionVars();
        if (array_key_exists('toself_printed', $fields)) {
            unset($fields['toself_printed']);
        }
        $fields['cert_number']   = $hlp->__("Certificate Code");
        $fields['pin']           = $hlp->__("PIN number");
        $fields['balance']       = $hlp->__("Certificate balance");
        $fields['currency_code'] = $hlp->__("Currency code");
        $fields['status']        = $hlp->__("Status");
        $fields['expire_at']     = $hlp->__("Expires at");
        $fields                  = array('other' => $hlp->__("Free text field")) + $fields;
        return $fields;
    }

    /**
     * Retrieve hash of template titles and hashes.
     * @return array
     */
    public function getPdfTemplatesHash($selector = false)
    {
        /* @var $resource  Mage_Core_Model_Resource */
        $resource = Mage::getModel('core/resource');
        /* @var $conn Varien_Db_Adapter_Interface */
        $conn   = $resource->getConnection('read');
        $table  = $resource->getTableName('ugiftcert/pdf');
        $select = $conn->select()->from($table, array('title', 'template_id'));
        $rows   = $conn->fetchAll($select);
        $result = array();

        if ($selector) {
            $results[0] = Mage::helper('ugiftcert')->__("-- Please Select Template --");
        }
        foreach ($rows as $row) {
            $result[$row['template_id']] = $row['title'];
        }
        return $result;
    }

    public function getPdfTemplatesArray($selector = false)
    {
        $templates = $this->getPdfTemplatesHash($selector);
        $results   = array();

        foreach ($templates as $id => $setting) {
            $results[] = array(
                'value' => $id,
                'label' => $setting
            );
        }
        return $results;
    }
}
