<?php

$eav = new Mage_Eav_Model_Entity_Setup('catalog_setup');

$eav->addAttribute('catalog_product', 'ugiftcert_email_template', array(
    'type' => 'int',
    'input' => 'select',
    'label' => 'GC Email Template',
    'source' => 'ugiftcert/source_template',
    'global' => 0,
    'user_defined' => 1,
    'apply_to' => 'ugiftcert',
    'required' => 0,
));

$eav->addAttribute('catalog_product', 'ugiftcert_email_template_self', array(
    'type' => 'int',
    'input' => 'select',
    'label' => 'GC Email Template (Self)',
    'source' => 'ugiftcert/source_template',
    'global' => 0,
    'user_defined' => 1,
    'apply_to' => 'ugiftcert',
    'required' => 0,
));