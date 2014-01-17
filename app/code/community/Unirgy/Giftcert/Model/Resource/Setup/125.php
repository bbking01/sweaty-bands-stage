<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pp
 */
class Unirgy_Giftcert_Model_Resource_Setup_125
    implements Unirgy_Giftcert_Model_Resource_Setup_Interface
{
    /**
     * @var Unirgy_Giftcert_Model_Resource_Setup
     */
    protected $setup;

    protected $mainPdfHashes;
    protected $gcPdfHashes;
    protected $prodPdfHashes;
    public $user = 'N/A';
    public $time = '0000000000';

    public function __construct(Unirgy_Giftcert_Model_Resource_Setup $setup)
    {
        $this->setup = $setup;
    }

    /**
     * Update module to 1.2.5 version
     * main task:
     *  - move current pdf settings into dedicated table
     *      - move store and global level to table.
     *      - move per certificate to table
     *      - move per product to table
     *  - use json instead of php serialize
     *  - make sure that same settings are reused
     *  - assign corresponding hashes to stores, certificates and products
     *
     * @return Unirgy_Giftcert_Model_Resource_Setup_125
     * @throws Exception
     */
    public function update()
    {
        $conn = $this->setup->getConnection();
        if (!$conn) {
            Mage::throwException(Mage::helper('ugiftcert')->__("Could not get connection, " . __FILE__));
        }
        $templates = array();
        try {
            $this->time = date('Y-m-d H:i:s');
            $this->createPdfSettingsTable($conn);

            $templates = $this->prepareMainPdfSettings($conn, $templates);

            $templates = $this->prepareGcPdfSettings($conn, $templates);

            $templates = $this->prepareProductPdfSettings($conn, $templates);

            foreach ($templates as $tpl) {
                $conn->insertOnDuplicate($this->getGpTableName(), $tpl, array('settings'));
            }


            $this->updateMainSettings($conn);
            $this->updateGcSettings($conn);
            $this->updateProductSettings($conn);

            $this->addPdfTemplates();
            $this->addTransactionalEmailTemplates();
        } catch (Exception $e) {
            $this->setup->log($e);
        }
        return $this;
    }

    public function prepareMainPdfSettings($conn, $templates)
    {
        try {
            $mainPdfSettings     = $this->getDefaultPdfSettings($conn);
            $this->mainPdfHashes = array(); // store corresponding store settings

            foreach ($mainPdfSettings as $scope => $setting) {
                $title = $setting['title'];
                unset($setting['title']);
                $content                     = Zend_Json::encode($setting);
                $hash                        = sha1($content);
                $this->mainPdfHashes[$scope] = $hash;
                if (!isset($templates[$hash])) {
                    $templates[$hash] = $this->prepareTemplateArray($hash, $title, $content);
                }
            }
        } catch (Exception $e) {
            $this->setup->log($e);
        }
        return $templates;
    }

    public function prepareGcPdfSettings($conn, $templates)
    {
        $this->gcPdfHashes = array();
        try {
            $gcPdfSettings = $this->getGcPdfSettings($conn);
            foreach ($gcPdfSettings as $code => $setting) {
                $setting = unserialize($setting);
                if (!$setting) {
                    continue;
                }
                $content                  = Zend_Json::encode($setting);
                $hash                     = sha1($content);
                $this->gcPdfHashes[$code] = $hash;
                if (!isset($templates[$hash])) {
                    $title            = sprintf("PDF Template for certificate %s", $code);
                    $templates[$hash] = $this->prepareTemplateArray($hash, $title, $content);
                } else {
                    $title = sprintf(" and for certificate %s", $code);
                    $templates[$hash]['title'] .= $title; // if the settings are already fetched, add this code to them
                }
            } // end foreach gc
        } catch (Exception $e) {
            $this->setup->log($e);
        }
        return $templates;
    }

    protected function prepareTemplateArray($hash, $title, $content)
    {
        $template = array(
            'hash'        => $hash,
            'title'       => $title,
            'settings'    => $content,
            'added_at'    => $this->time,
            'added_by'    => $this->user,
            'modified_by' => $this->user,
        );
        return $template;
    }


    public function rollBack()
    {
        try {
            $conn = $this->getConnection();
//        $this->restoreGcTable($conn);
            $this->restoreGcPdfSettings($conn);
//        $this->restoreProductPdfSettings($conn);
            $this->restoreMainPdfSettings($conn);
            $conn->dropTable($this->getGpTableName());
        } catch (Exception $e) {
            $this->setup->log($e, 'Error when rolling back from 1.2.5');
        }
    }

    public function getTable($table)
    {
        if(is_array($table)){
            $version = Mage::getVersionInfo();
            if($version['minor'] < '6'){ // in magento 1.5 and less, table name as array is not supported.
                $table = $this->setup->getTable(array_shift($table));
                $table .= '_' . implode('_', $table);
            }
        }
        return $this->setup->getTable($table);
    }

    public function getConnection()
    {
        return $this->setup->getConnection();
    }

    /**
     * @param Varien_Db_Adapter_Pdo_Mysql $conn
     */
    public function createPdfSettingsTable($conn)
    {
        $pdfTableName = $this->getGpTableName();
        if (!$conn->showTableStatus($pdfTableName)) {

            $pdfTable = $conn->newTable($pdfTableName);
            $pdfTable->addColumn('template_id', 'integer', 12, array('primary' => true, 'auto_increment' => true), 'Template ID')
                ->addColumn('hash', 'char', 40, array('nullable' => false), 'Template settings hashed')
                ->addColumn('title', 'varchar', 255, array('nullable' => false))
                ->addColumn('modified_at', 'timestamp', null, array('nullable' => true, 'default' => new Zend_Db_Expr('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')))
                ->addColumn('added_at', 'timestamp', null, array('nullable' => false))
                ->addColumn('added_by', 'varchar', 40, array('nullable' => false))
                ->addColumn('modified_by', 'varchar', 40, array('nullable' => false));
            $pdfTable->addIndex(strtoupper(implode('_', array('unq', $pdfTableName, 'hash'))),
                'hash', array('type' => 'unique', 'unique' => true)); // Mag. 1.4.x fix
            $conn->createTable($pdfTable);
            $conn->addColumn($pdfTableName, 'settings', 'text NOT NULL');
            $conn->modifyColumn($pdfTableName, 'template_id', 'int(11) NOT NULL AUTO_INCREMENT COMMENT \'Template ID\''); // Mag. 1.4.x fix
        }
    }

    /**
     * getters for various GC table names
     * @return string
     */
    public function getGcTableName()
    {
        return $this->getTable('ugiftcert/cert');
    }

    public function getGhTableName()
    {
        return $this->getTable('ugiftcert/history');
    }

    public function getGpTableName()
    {
        return $this->getTable('ugiftcert/pdf');
    }

    /**
     * @param Varien_Db_Adapter_Interface $conn
     * @return array
     */
    public function getDefaultPdfSettings($conn)
    {
        $select   = $conn->select()
            ->from($this->getTable('core/config_data'))
            ->where('path LIKE \'ugiftcert/pdf/%\'')
            ->order('scope ASC');
        $result   = $conn->fetchAll($select);
        $settings = array();
        if (!empty($result)) {
            foreach ($result as $row) {
                $scope       = $row['scope'];
                $scId        = $row['scope_id'];
                $settingsKey = $scope . ':' . $scId;
                $tmp         = explode('/', $row['path']);
                $key         = array_pop($tmp);
                if (in_array($key, array('enabled', 'form_key'))) {
                    continue;
                }

                if (empty($settings[$settingsKey])) { // setup composed title
                    $title = 'PDF Template for: ';

                    switch ($scope) {
                        case 'stores':
                            $title .= "store {$scId}";
                            break;
                        case 'websites':
                            $title .= "website {$scId}";
                            break;
                        default:
                            $title .= "default";
                    }
                    $settings[$settingsKey]['title'] = $title;
                }
                if (in_array($key, array('text_settings', 'image_settings'))) {
                    $settings[$settingsKey][$key] = unserialize($row['value']);
                } else {
                    $settings[$settingsKey][$key] = $row['value'];
                }
            }
        }
        return $settings;
    }

    /**
     * @param Varien_Db_Adapter_Interface $conn
     * @return array
     */
    public function getGcPdfSettings($conn)
    {
        $settings = array();
        try {
            $gcSelect = $conn->select()
                ->from($this->getGcTableName(), array('cert_number', 'pdf_settings'))
                ->where('pdf_settings IS NOT NULL')
                ->orWhere('pdf_settings <> \'\'');

            $settings = $conn->fetchPairs($gcSelect);
        } catch (Exception $e) {
            $this->setup->log($e);
        }
        return $settings;
    }

    /**
     * @param Varien_Db_Adapter_Interface $conn
     * @return array - format prod_id:store_id => settings
     */
    public function getProductPdfSettings($conn)
    {
        $settings = array();
        try {
            $prodSelect = $conn->select()
                ->from(array('cp' => $this->getTable(array('catalog/product', 'text'))), array('entity_id' => 'CONCAT(entity_id, \':\', store_id)', 'value'))
                ->join(array('ea' => $this->getTable('eav/attribute')), 'ea.attribute_id = cp.attribute_id', null)
                ->where('ea.attribute_code = ?', 'ugiftcert_pdf_settings');

            $settings = $conn->fetchPairs($prodSelect);
        } catch (Exception $e) {
            $this->setup->log($e);
        }
        return $settings;
    }

    public function prepareProductPdfSettings($conn, $templates)
    {
        $this->prodPdfHashes = array();
        $prodPdfSettings     = $this->getProductPdfSettings($conn);
        foreach ($prodPdfSettings as $prodStoreId => $setting) {
            $setting = unserialize($setting);
            if (!$setting) {
                continue;
            }
            $content                           = Zend_Json::encode($setting);
            $hash                              = sha1($content);
            $this->prodPdfHashes[$prodStoreId] = $hash;
            list($prodId, $stId) = explode(':', $prodStoreId);
            if (!isset($templates[$hash])) {
                $title            = sprintf("PDF Template for product %s, store %s", $prodId, $stId);
                $templates[$hash] = $this->prepareTemplateArray($hash, $title, $content);
            } else {
                $title = sprintf(" and for product %s, store %s", $prodId, $stId);
                $templates[$hash]['title'] .= $title; // if the settings are already fetched, add this code to them
            }
        } // end foreach products
        return $templates;
    }

    /**
     * @param Varien_Db_Adapter_Interface $conn
     * @return Unirgy_Giftcert_Model_Resource_Setup_125
     */
    public function updateMainSettings($conn)
    {
        $updated = $this->getPdfSettings($conn, $this->mainPdfHashes);
        $path    = 'ugiftcert/email/pdf_template';
        $config  = array();

        foreach ($updated as $id => $content) {
            $hash      = sha1($content);
            $scopeCode = array_search($hash, $this->mainPdfHashes);
            if (!$scopeCode) {
                continue;
            }
            list($scope, $scope_id) = explode(':', $scopeCode);
            $config[] = array(
                'scope'    => $scope,
                'scope_id' => $scope_id,
                'path'     => $path,
                'value'    => $id
            );
        }
        if (!empty($config)) {
            $table = $this->getTable('core/config_data');
            foreach ($config as $row) {
                $conn->insertOnDuplicate($table, $row, array('value'));
            }
        }
        return $this;
    }

    /**
     * @param Varien_Db_Adapter_Interface $conn
     * @return Unirgy_Giftcert_Model_Resource_Setup_125
     */
    public function updateGcSettings($conn)
    {
        try {
            $conn->changeColumn($this->getGcTableName(), 'pdf_settings', 'pdf_template_id', 'INTEGER(12) NULL');
            $updated = $this->getPdfSettings($conn, $this->gcPdfHashes);
            foreach ($updated as $code => $id) {
                $conn->update($this->getGcTableName(), array('pdf_template_id' => $id), array('cert_number=?'=> $code));
            }
        } catch (Exception $e) {
            $this->setup->log($e);
        }
        return $this;
    }

    /**
     * @param Varien_Db_Adapter_Interface $conn
     * @return Unirgy_Giftcert_Model_Resource_Setup_125
     */
    public function updateProductSettings($conn)
    {
        $pdfTplAttribute = 'ugiftcert_pdf_tpl_id';
        $eav             = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('catalog_setup');
        $entityTypeId    = $eav->getEntityTypeId('catalog_product');
        $eav->addAttribute($entityTypeId, $pdfTplAttribute, array(
            'type'         => 'int',
            'input'        => 'select',
            'label'        => 'GC PDF Template',
            'source'       => 'ugiftcert/source_pdf',
            'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'user_defined' => 1,
            'apply_to'     => 'ugiftcert',
            'required'     => 0,
            'group'        => 'GC Settings',
        ));

        $eav->removeAttribute($entityTypeId, 'ugiftcert_email_template_self');

        $updated = $this->prodPdfHashes;
        if (empty($updated)) {
            return $this;
        }

        $attrId = $eav->getAttributeId($entityTypeId, $pdfTplAttribute);
        $table  = $eav->getAttributeTable($entityTypeId, $attrId);

        if (!$attrId) {
            Mage::throwException("Could not get 'ugiftcert_pdf_settings' attribute id.");
        }
        $pdfSettings = $this->getPdfSettings($conn, $updated);
        foreach ($pdfSettings as $id => $content) {
            $hash      = sha1($content);
            $scopeCode = array_search($hash, $updated);
            if (!$scopeCode) {
                continue;
            }
            list($entity_id, $store_id) = explode(':', $scopeCode);
            $conn->insertOnDuplicate(
                $table,
                array('value'        => $id,
                      'entity_id'    => $entity_id,
                      'store_id'     => $store_id,
                      'attribute_id' => $attrId,
                ),
                array('value')
            );
        }
        return $this;
    }

    /**
     * @return Unirgy_Giftcert_Model_Resource_Setup
     */
    public function getSetupModel()
    {
        return $this->setup;
    }

    /**
     * @param Varien_Db_Adapter_Interface $conn
     * @return \Unirgy_Giftcert_Model_Resource_Setup_125
     */
    public function restoreGcTable($conn)
    {
        $table = $this->getGcTableName();
        if ($conn->tableColumnExists($table, 'pdf_template_id')) {
            $conn->changeColumn($table, 'pdf_template_id', 'pdf_settings', 'TEXT NULL'); // revert pdf settings column definition
        } else {
            $conn->modifyColumn($this->getGcTableName(), 'pdf_settings', 'TEXT NULL'); // revert pdf settings column definition

        }
        return $this;
    }

    /**
     * @param Varien_Db_Adapter_Interface $conn
     * @return \Unirgy_Giftcert_Model_Resource_Setup_125
     */
    public function restoreGcPdfSettings($conn)
    {
        $table  = $this->getGcTableName();
        $ids = array();
        try {
            $column = $conn->tableColumnExists($table, 'pdf_template_id') ? 'pdf_template_id' : 'pdf_settings';
            $ids    = $conn->fetchPairs(
                $conn->select()
                    ->from($table, array('cert_number', $column))
                    ->where($conn->quoteIdentifier($column) . ' IS NOT NULL')
                    ->where($conn->quoteIdentifier($column) . ' <> \'\'')
            );

            $this->restoreGcTable($conn);
        } catch (Exception $e) {
            $this->setup->log($e);
        }

        if (!empty($ids)) {
            $settings = $this->getPdfSettings($conn, $ids);

            foreach ($ids as $code => $id) {
                if (isset($settings[$id])) {
                    $setting = Zend_Json::decode($settings[$id]);
                    $conn->update($table, array(
                                               'pdf_settings' => serialize($setting)
                                          ), array('cert_number = ?' => $code));
                }
            }
        }
        return $this;
    }

    /**
     * @param Varien_Db_Adapter_Interface $conn
     * @param array $ids
     * @return array
     */
    public function getPdfSettings($conn, $ids)
    {
        $table = $this->getGpTableName();
        if (!$conn->showTableStatus($table)) {
            return array();
        }
        $settings = $conn->fetchPairs(
            $conn->select()
                ->from($this->getGpTableName(), array('template_id', 'settings'))
                ->where('template_id IN (?)', $ids)
                ->orWhere('hash IN (?)', $ids)
        );
        return $settings;
    }

    /**
     * @param Varien_Db_Adapter_Interface $conn
     * @return \Unirgy_Giftcert_Model_Resource_Setup_125
     */
    public function restoreProductPdfSettings($conn)
    {
        try {
            $prodTable  = $this->getTable(array('catalog/product', 'text'));
            $prodSelect = $conn->select()
                ->from(array('cp' => $prodTable), array('value_id', 'value'))
                ->join(array('ea' => $this->getTable('eav/attribute')), 'ea.attribute_id = cp.attribute_id', null)
                ->where('ea.attribute_code = ?', 'ugiftcert_pdf_settings');
            $ids        = $conn->fetchPairs($prodSelect);
            $settings   = $this->getPdfSettings($conn, $ids);
            foreach ($ids as $value_id => $id) {
                if (isset($settings[$id])) {
                    $setting = Zend_Json::decode($settings[$id]);
                    $conn->update($prodTable, array(
                        'value' => serialize($setting)
                    ), array('value_id = ?' => $value_id));
                }
            }
        } catch (Exception $e) {
            $this->setup->log($e);
        }
        return $this;
    }

    /**
     * @param Varien_Db_Adapter_Interface $conn
     * @return \Unirgy_Giftcert_Model_Resource_Setup_125
     */
    public function restoreMainPdfSettings($conn)
    {
        try {
            $table     = $this->getTable('core/config_data');
            $path      = 'ugiftcert/email/pdf_template';
            $pdfTplIds = $conn->fetchAll(
                $conn->select()->from($table, array('config_id', 'scope', 'scope_id', 'value'))->where('path=?', $path)
            );
            $ids       = array();
            foreach ($pdfTplIds as $row) {
                $ids[$row['config_id']]       = $row['value'];
                $pdfTplIds[$row['config_id']] = $row;
            }

            $settings = $this->getPdfSettings($conn, $ids);
            $pdfPath  = 'ugiftcert/pdf/';
            foreach ($ids as $config_id => $id) {
                if (isset($settings[$id])) {
                    $setting  = Zend_Json::decode($settings[$id]);
                    $row      = $pdfTplIds[$config_id];
                    $scope    = $row['scope'];
                    $scope_id = $row['scope_id'];
                    foreach ($setting as $key => $value) {
                        if (is_array($value)) {
                            $value = serialize($value);
                        }
                        $realPath = $pdfPath . $key;
                        $conn->insertOnDuplicate($table, array(
                            'scope'    => $scope,
                            'scope_id' => $scope_id,
                            'path'     => $realPath,
                            'value'    => $value
                        ), array('value'));
                    }
                }
            }
        } catch (Exception $e) {
            $this->setup->log($e);
        }

        return $this;
    }

    public function addTransactionalEmailTemplates()
    {
        try {
            $emailTemplates = array(
                array(
                    'template_code'           => 'Blue GC Template',
                    'template_text'           => '<style type="text/css">
    body,td { color:#2f2f2f; font:11px/1.35em Verdana, Arial, Helvetica, sans-serif; }
</style>

<div style="font:11px/1.35em Verdana, Arial, Helvetica, sans-serif;">
<table cellspacing="0" cellpadding="0" border="0" width="98%" style="margin-top:10px; font:11px/1.35em Verdana, Arial, Helvetica, sans-serif; margin-bottom:10px;">
<tr>
    <td align="center" valign="top">
        <!-- [ header starts here] -->
        <table cellspacing="0" cellpadding="0" border="0" width="650">
            <tr>
                <td valign="top"><a href="{{store url=""}}"><img src="{{media url="unirgy/giftcert/pdf/stores/0/blue_big_gift.png" _area=\'frontend\'}}" alt=""  style="margin-bottom:10px;" border="0"/></a></td>
            </tr>
        </table>
        <!-- [ middle starts here] -->
        <table cellspacing="0" cellpadding="0" border="0" width="650">
            <tr>
                <td valign="top">
                    <p><strong>Hello {{var recipient_name}},</strong></p>
                    <p>The following is the information regarding the gift certificate(s) you purchased from {{var store_name}}<br/>
                    You can easily use the gift certificates to make purchases at <a href="{{store url=""}}">{{store url=""}}</a></p>

                    <p>When you\'re checking out, be sure to use the following gift certificate number(s)!<br/>
                    {{var certificate_numbers}}</p>

                    <p>Each gift certificate has a balance of {{var amount}}.</p>

                    {{depend expire_on}}
                    <p>The gift certificate(s) will expire on {{var expire_on}}</p>
                    {{/depend}}

                    <p>Thank you!</p>

                    <p><a href="{{store url=""}}">{{var store_name}}</a></p>
               </td>
           </tr>
       </table>
   </td>
</tr>
</table>
</div>',
                    'template_styles'         => NULL,
                    'template_type'           => '2',
                    'template_subject'        => 'Your gift certificate(s) from {{var store_name}}',
                    'template_sender_name'    => NULL,
                    'template_sender_email'   => NULL,
                    'added_at'                => NULL,
                    'modified_at'             => '2012-11-13 16:38:14',
                    'orig_template_code'      => 'ugiftcert_email_template_self',
                    'orig_template_variables' => '{"var recipient_name":"Recipient Name","var recipient_email":"Recipient Email","var custom_message":"Gift Message","var amount":"Gift amount","var expire_on":"Expiration date","var store_name":"Store name","var sender_name":"Sender name","var certificate_numbers":"Gift codes","var website_name":"Website name","var group_name":"Store group name"}'
                ),
                array(
                    'template_code'           => 'Red GC Template',
                    'template_text'           => '<style type="text/css">
    body,td { color:#2f2f2f; font:11px/1.35em Verdana, Arial, Helvetica, sans-serif; }
</style>

<div style="font:11px/1.35em Verdana, Arial, Helvetica, sans-serif;">
<table cellspacing="0" cellpadding="0" border="0" width="98%" style="margin-top:10px; font:11px/1.35em Verdana, Arial, Helvetica, sans-serif; margin-bottom:10px;">
<tr>
    <td align="center" valign="top">
        <!-- [ header starts here] -->
        <table cellspacing="0" cellpadding="0" border="0" width="650">
            <tr>
                <td valign="top"><a href="{{store url=""}}"><img src="{{media url="unirgy/giftcert/pdf/stores/0/red_big_gift.png" _area=\'frontend\'}}" alt=""  style="margin-bottom:10px;" border="0"/></a></td>
            </tr>
        </table>
        <!-- [ middle starts here] -->
        <table cellspacing="0" cellpadding="0" border="0" width="650">
            <tr>
                <td valign="top">
                    <p><strong>Hello {{var recipient_name}},</strong></p>
                    <p>The following is the information regarding the gift certificate(s) you purchased from {{var store_name}}<br/>
                    You can easily use the gift certificates to make purchases at <a href="{{store url=""}}">{{store url=""}}</a></p>

                    <p>When you\'re checking out, be sure to use the following gift certificate number(s)!<br/>
                    {{var certificate_numbers}}</p>

                    <p>Each gift certificate has a balance of {{var amount}}.</p>

                    {{depend expire_on}}
                    <p>The gift certificate(s) will expire on {{var expire_on}}</p>
                    {{/depend}}

                    <p>Thank you!</p>

                    <p><a href="{{store url=""}}">{{var store_name}}</a></p>
               </td>
           </tr>
       </table>
   </td>
</tr>
</table>
</div>',
                    'template_styles'         => NULL,
                    'template_type'           => '2',
                    'template_subject'        => 'Your gift certificate(s) from {{var store_name}}',
                    'template_sender_name'    => NULL,
                    'template_sender_email'   => NULL,
                    'added_at'                => NULL,
                    'modified_at'             => '2012-11-13 16:40:01',
                    'orig_template_code'      => 'ugiftcert_email_template_self',
                    'orig_template_variables' => '{"var recipient_name":"Recipient Name","var recipient_email":"Recipient Email","var custom_message":"Gift Message","var amount":"Gift amount","var expire_on":"Expiration date","var store_name":"Store name","var sender_name":"Sender name","var certificate_numbers":"Gift codes","var website_name":"Website name","var group_name":"Store group name"}'
                ),
                array(
                    'template_code'           => 'Green GC Template',
                    'template_text'           => '<style type="text/css">
    body,td { color:#2f2f2f; font:11px/1.35em Verdana, Arial, Helvetica, sans-serif; }
</style>

<div style="font:11px/1.35em Verdana, Arial, Helvetica, sans-serif;">
<table cellspacing="0" cellpadding="0" border="0" width="98%" style="margin-top:10px; font:11px/1.35em Verdana, Arial, Helvetica, sans-serif; margin-bottom:10px;">
<tr>
    <td align="center" valign="top">
        <!-- [ header starts here] -->
        <table cellspacing="0" cellpadding="0" border="0" width="650">
            <tr>
                <td valign="top"><a href="{{store url=""}}"><img src="{{media url="unirgy/giftcert/pdf/stores/0/green_big_gift.png"}}" alt=""  style="margin-bottom:10px;" border="0"/></a></td>
            </tr>
        </table>
        <!-- [ middle starts here] -->
        <table cellspacing="0" cellpadding="0" border="0" width="650">
            <tr>
                <td valign="top">
                    <p><strong>Hello {{var recipient_name}},</strong></p>
                    <p>The following is the information regarding the gift certificate(s) you purchased from {{var store_name}}<br/>
                    You can easily use the gift certificates to make purchases at <a href="{{store url=""}}">{{store url=""}}</a></p>

                    <p>When you\'re checking out, be sure to use the following gift certificate number(s)!<br/>
                    {{var certificate_numbers}}</p>

                    <p>Each gift certificate has a balance of {{var amount}}.</p>

                    {{depend expire_on}}
                    <p>The gift certificate(s) will expire on {{var expire_on}}</p>
                    {{/depend}}

                    <p>Thank you!</p>

                    <p><a href="{{store url=""}}">{{var store_name}}</a></p>
               </td>
           </tr>
       </table>
   </td>
</tr>
</table>
</div>',
                    'template_styles'         => NULL,
                    'template_type'           => '2',
                    'template_subject'        => 'Your gift certificate(s) from {{var store_name}}',
                    'template_sender_name'    => NULL,
                    'template_sender_email'   => NULL,
                    'added_at'                => NULL,
                    'modified_at'             => '2012-11-13 16:42:13',
                    'orig_template_code'      => 'ugiftcert_email_template_self',
                    'orig_template_variables' => '{"var recipient_name":"Recipient Name","var recipient_email":"Recipient Email","var custom_message":"Gift Message","var amount":"Gift amount","var expire_on":"Expiration date","var store_name":"Store name","var sender_name":"Sender name","var certificate_numbers":"Gift codes","var website_name":"Website name","var group_name":"Store group name"}'
                ),
                array(
                    'template_code'           => 'Gold GC Template',
                    'template_text'           => '<style type="text/css">
    body,td { color:#2f2f2f; font:11px/1.35em Verdana, Arial, Helvetica, sans-serif; }
</style>

<div style="font:11px/1.35em Verdana, Arial, Helvetica, sans-serif;">
<table cellspacing="0" cellpadding="0" border="0" width="98%" style="margin-top:10px; font:11px/1.35em Verdana, Arial, Helvetica, sans-serif; margin-bottom:10px;">
<tr>
    <td align="center" valign="top">
        <!-- [ header starts here] -->
        <table cellspacing="0" cellpadding="0" border="0" width="650">
            <tr>
                <td valign="top"><a href="{{store url=""}}"><img src="{{media url="unirgy/giftcert/pdf/stores/0/gold_big_gift.png"\'}}" alt=""  style="margin-bottom:10px;" border="0"/></a></td>
            </tr>
        </table>
        <!-- [ middle starts here] -->
        <table cellspacing="0" cellpadding="0" border="0" width="650">
            <tr>
                <td valign="top">
                    <p><strong>Hello {{var recipient_name}},</strong></p>
                    <p>The following is the information regarding the gift certificate(s) you purchased from {{var store_name}}<br/>
                    You can easily use the gift certificates to make purchases at <a href="{{store url=""}}">{{store url=""}}</a></p>

                    <p>When you\'re checking out, be sure to use the following gift certificate number(s)!<br/>
                    {{var certificate_numbers}}</p>

                    <p>Each gift certificate has a balance of {{var amount}}.</p>

                    {{depend expire_on}}
                    <p>The gift certificate(s) will expire on {{var expire_on}}</p>
                    {{/depend}}

                    <p>Thank you!</p>

                    <p><a href="{{store url=""}}">{{var store_name}}</a></p>
               </td>
           </tr>
       </table>
   </td>
</tr>
</table>
</div>',
                    'template_styles'         => NULL,
                    'template_type'           => '2',
                    'template_subject'        => 'Your gift certificate(s) from {{var store_name}}',
                    'template_sender_name'    => NULL,
                    'template_sender_email'   => NULL,
                    'added_at'                => NULL,
                    'modified_at'             => '2012-11-13 16:43:29',
                    'orig_template_code'      => 'ugiftcert_email_template_self',
                    'orig_template_variables' => '{"var recipient_name":"Recipient Name","var recipient_email":"Recipient Email","var custom_message":"Gift Message","var amount":"Gift amount","var expire_on":"Expiration date","var store_name":"Store name","var sender_name":"Sender name","var certificate_numbers":"Gift codes","var website_name":"Website name","var group_name":"Store group name"}'
                )
            );

            $conn  = $this->getConnection();
            $table = $this->getTable('core/email_template');
            $conn->insertMultiple($table, $emailTemplates);
        } catch (Exception $e) {
            Mage::log($e->getMessage(), Zend_Log::ERR, Unirgy_Giftcert_Helper_Data::LOG_NAME, true);
            Mage::log($e->getTraceAsString(), Zend_Log::ERR, Unirgy_Giftcert_Helper_Data::LOG_NAME, true);
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }
    }

    public function addPdfTemplates()
    {
        try {
            $pdfTemplates = array(
                array(
                    'hash'        => '238d9ca74165bc41f5fb05d63914d1759dcbcba5',
                    'title'       => 'Blue card PDF',
                    'settings'    => '{"units":"pts","use_font":"COURIER","page_width":"392","page_height":"250","text_settings":{"1":{"field":"cert_number","template":"Card code: %s","x_pos":"140.0000","y_pos":"120.0000","font_size":"16","font_variant":"r","color":"000000"},"2":{"field":"balance","template":"Amount: %s","x_pos":"140.0000","y_pos":"100.0000","font_size":"16","font_variant":"r","color":"000000"},"3":{"field":"recipient_name","template":"Owner: %s","x_pos":"140.0000","y_pos":"80.0000","font_size":"16","font_variant":"r","color":"000000"}},"image_settings":[{"value":"blue_big_gift.png\\/","url":"unirgy\\/giftcert\\/pdf\\/stores\\/0\\/blue_big_gift.png","width":"392","height":"250","x_pos":"0.0000","y_pos":"0.0000"}]}',
                    'modified_at' => '2012-11-13 17:52:12', 'added_at' => '2012-11-01 14:28:04', 'added_by' => 'N/A',
                    'modified_by' => 'Installer'
                ),
                array(
                    'hash'        => '65899e7c253ccd974713712dc5174442ee55ddde',
                    'title'       => 'Red card PDF',
                    'settings'    => '{"units":"pts","use_font":"TIMES","page_width":"392","page_height":"250","text_settings":{"1":{"field":"cert_number","template":"Card code: %s","x_pos":"140.0000","y_pos":"120.0000","font_size":"16","font_variant":"r","color":"000000"},"2":{"field":"balance","template":"Amount: %s","x_pos":"140.0000","y_pos":"100.0000","font_size":"16","font_variant":"r","color":"000000"},"3":{"field":"recipient_name","template":"Owner: %s","x_pos":"140.0000","y_pos":"80.0000","font_size":"16","font_variant":"r","color":"000000"}},"image_settings":[{"value":"red_big_gift.png\\/","url":"unirgy\\/giftcert\\/pdf\\/stores\\/0\\/red_big_gift.png","width":"392","height":"250","x_pos":"0.0000","y_pos":"0.0000"}]}',
                    'modified_at' => '2012-11-13 17:55:22', 'added_at' => '2012-11-01 14:28:04', 'added_by' => 'N/A',
                    'modified_by' => 'Installer'
                ),
                array(
                    'hash'        => 'b74198a48a4b9d13e5f52c92dab84ce83a556c89',
                    'title'       => 'Green card PDF',
                    'settings'    => '{"units":"pts","use_font":"HELVETICA","page_width":"392","page_height":"250","text_settings":{"1":{"field":"cert_number","template":"Card code: %s","x_pos":"140.0000","y_pos":"120.0000","font_size":"16","font_variant":"r","color":"000000"},"2":{"field":"balance","template":"Amount: %s","x_pos":"140.0000","y_pos":"100.0000","font_size":"16","font_variant":"r","color":"000000"},"3":{"field":"recipient_name","template":"Owner: %s","x_pos":"140.0000","y_pos":"80.0000","font_size":"16","font_variant":"r","color":"000000"}},"image_settings":[{"value":"green_big_gift.png\\/","url":"unirgy\\/giftcert\\/pdf\\/stores\\/0\\/green_big_gift.png","width":"392","height":"250","x_pos":"0.0000","y_pos":"0.0000"}]}',
                    'modified_at' => '2012-11-13 17:58:57', 'added_at' => '2012-11-01 14:28:04', 'added_by' => 'N/A',
                    'modified_by' => 'Installer'
                ),
                array(
                    'hash'        => 'daf7325d52b325d073618a3c3fb20fad6bddd948',
                    'title'       => 'Gold card PDF',
                    'settings'    => '{"units":"pts","use_font":"HELVETICA","page_width":"392","page_height":"250","text_settings":{"1":{"field":"cert_number","template":"Card code: %s","x_pos":"140","y_pos":"120","font_size":"16","font_variant":"r","color":"000000"},"2":{"field":"balance","template":"Amount: %s","x_pos":"140","y_pos":"100","font_size":"16","font_variant":"r","color":"000000"},"3":{"field":"recipient_name","template":"Owner: %s","x_pos":"140","y_pos":"80","font_size":"16","font_variant":"r","color":"000000"}},"image_settings":[{"width":"392","height":"250","x_pos":"0","y_pos":"0","url":"unirgy\\/giftcert\\/pdf\\/stores\\/0\\/gold_big_gift.png","value":"gold_big_gift.png"}]}',
                    'modified_at' => '2012-11-13 18:00:45', 'added_at' => '2012-11-01 14:28:04', 'added_by' => 'N/A',
                    'modified_by' => 'Installer'
                )
            );

            $conn  = $this->getConnection();
            $table = $this->getGpTableName();
            $conn->insertMultiple($table, $pdfTemplates);
        } catch (Exception $e) {
            Mage::log($e->getMessage(), Zend_Log::ERR, Unirgy_Giftcert_Helper_Data::LOG_NAME, true);
            Mage::log($e->getTraceAsString(), Zend_Log::ERR, Unirgy_Giftcert_Helper_Data::LOG_NAME, true);
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }
    }
}
