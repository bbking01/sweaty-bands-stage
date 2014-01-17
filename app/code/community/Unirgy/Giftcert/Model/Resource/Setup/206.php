<?php
/**
 * Created by pp
 * @project gc2
 */

class Unirgy_Giftcert_Model_Resource_Setup_206
    implements Unirgy_Giftcert_Model_Resource_Setup_Interface
{
    /**
     * @var Unirgy_Giftcert_Model_Resource_Setup
     */
    protected $setup;

    public function __construct(Unirgy_Giftcert_Model_Resource_Setup $setup)
    {
        $this->setup = $setup;
    }

    public function update()
    {
        $table = $this->setup->getTable('ugiftcert/cert');
        $this->setup
            ->getConnection()
            ->addColumn($table, 'disallow_coupons', 'tinyint(2) NULL DEFAULT 0');
    }

    public function rollBack()
    {
        $table = $this->setup->getTable('ugiftcert/cert');
        $this->setup
            ->getConnection()
            ->dropColumn($table, 'disallow_coupons');
    }
}