<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pp
 * Date: 22.10.12
 * Time: 18:33
 * To change this template use File | Settings | File Templates.
 */
class Unirgy_Giftcert_Model_Settings_Url
    extends Mage_Core_Model_Config_Data
{
    /**
     * @var string
     */
    protected $url = 'ugiftcert/customer/balance';


    /**
     * @param string $url
     * @return \Unirgy_Giftcert_Model_Settings_Url
     */
    public function setDefaultUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultUrl()
    {
        return $this->url;
    }

    /**
     * @return void
     */
    protected function _afterSave()
    {
        $target_url = $this->getDefaultUrl();
        $source_url = $this->getData('value');

        $storeIds = $this->getStoreIds();

        try {
            /* @var $rewrite Mage_Core_Model_Url_Rewrite */
            $rewrite = Mage::getModel('core/url_rewrite');

            foreach ($storeIds as $storeId) {
                $id_path     = $this->getCustomUrlId($storeId);
                $cur_rewrite = clone $rewrite;
                $cur_rewrite->loadByIdPath($id_path);
                if (empty($source_url)) {
                    $cur_rewrite->delete();
                } else {
                    $cur_rewrite->setData('store_id', $storeId)
                        ->setData('id_path', $id_path)
                        ->setData('is_system', 0)
                        ->setData('target_path', $target_url)
                        ->setData('request_path', $source_url)
                        ->save();
                }
            }

        } catch (Exception $e) {
            return;
        }
    }

    public function getCustomUrlId($store_id)
    {
        return trim($this->getDefaultUrl(), '/') . '/' . $store_id;
    }

    /**
     * @return array
     */
    public function getStoreIds()
    {
        $storeIds = array();
        $app      = Mage::app();
        switch ($this->getData('scope')) {
            case 'websites':
                $website  = $app->getWebsite($this->getData('website_code'));
                $storeIds = $website->getStoreIds();
                break;
            case 'stores' :
                $storeIds = (array)$app->getStore($this->getStoreCode())->getId();
                break;
            default :
                $storeIds = (array)$app->getStore()->getId();
                break;
        }
        return $storeIds;
    }
}
