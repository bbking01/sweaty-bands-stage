<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento community edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento community edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Points
 * @version    1.6.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Points_Helper_Config extends Mage_Core_Helper_Abstract
{
    /**
     * Source for "Invitation to purchase conversion"
     */

    const FIRST_ORDER_ONLY = 1;

    /**
     * Source for "Invitation to purchase conversion"
     */
    const EACH_ORDER = 2;


    /**
     * Source for collect totals order
     */
    const BEFORE_TAX = 1;

    /**
     * 
     */
    const AFTER_TAX = 2;
    
    /**
     * 
     * Balance change constants (added in 1.6)
     * 
     * Points added for the amount of money spent
     */
    const MONEY_SPENT = 1;
    
    /**
     *  Points added for reward rules
     */    
    const REWARD_RULES = 2;
    
    /**
     *  Referral Placed Order
     */
    const INVOICED_BY_REFERRAL = 3;
    
    
    /**
     * Registry flag that blocks update balance email sending
     * in case of custom templates processing
     */
    const STOP_MAIL = 'aw_points_stop_email';

    //===========================GENERAL================================

    /**
     * "Points extension enabled" from system config
     */
    const POINTS_GENERAL_ENABLE_YESNO = 'points/general/enable';

    /**
     * "Point unit name" from system config
     */
    const POINT_UNIT_NAME = 'points/general/point_unit_name';

    /**
     * "Reward points expire after, days" from system config
     */
    const POINTS_EXPIRATION_DAYS = 'points/general/points_expiration_days';

    /**
     * "Enable rewards history" from system config
     */
    const ENABLE_REWARDS_HISTORY_YESNO = 'points/general/enable_rewards_history';

    /**
     *  The order in which collect totals are applied
     */
    const POINTS_COLLECTION_ORDER = 'points/general/calculate_points_order';

    /**
     *  Cancel earned points for order on order refund
     */
    const POINTS_CANCEL_REFUND = 'points/general/cancel_earned_points_on_order_refund';

    /**
     *  Refund spent on order points on order refund
     */
    const POINTS_REFUND_REFUND = 'points/general/refund_spent_points_on_order_refund';


    /**
     * Apply earn rate if rule matched "Promotion/Rewards/Reward Rules" matched
     */
    const APPLY_EARN_RATE = 'points/general/apply_earn_rate';

    /**
     * "Minimum reward points balance to be available to redeem" from system config
     */
    const MINIMUM_POINTS_AMOUNT_TO_REDEEM = 'points/general/minimum_points_amount_for_spend';

    /**
     * "Maximum available points ballance(empty - no limitations)" from system config
     */
    const MAXIMUM_POINTS_PER_CUSTOMER = 'points/general/maximum_points_per_customer';

    /**
     * "Info Page" from system config
     */
    const WHAT_IS_IT_PAGE_ID = 'points/general/info_page';


    /* Amount available for paying at checkout by points, %  */
    const PAYING_AMOUNT_PERCENT_LIMIT = 'points/general/paying_amount_percent_limit';

    /* Amount available for paying at checkout by points, %  */
    const ALLOW_USE_WITH_COUPON = 'points/general/use_with_coupons';


    //===========================EARNING POINTS==============================

    /**
     * "Registration" from system config
     */
    const POINTS_EARNING_FOR_REGISTRATION = 'points/earning_points/for_registration';

    /**
     * "Newsletter signup" from system config
     */
    const POINTS_EARNING_FOR_NEWSLETTER_SINGUP = 'points/earning_points/for_newsletter_signup';

    /**
     * "Newsletter signup" from system config
     */
    const POINTS_EARNING_CONSIDER_NEWSLETTER_SIGNUP_BY_ADMIN = 'points/earning_points/consider_newsletter_signup_by_admin';

    /**
     * "Reviewing product" from system config
     */
    const POINTS_EARNING_FOR_REVIEWING_PRODUCT = 'points/earning_points/for_reviewing_product';

    /**
     * "For Video Testimonial" from system config
     */
    const POINTS_EARNING_FOR_VIDEO_TESTIMONIAL = 'points/earning_points/for_video_testimonial';

    /**
     * "Reviewing product points/day limit" from system config
     */
    const POINTS_EARNING_LIMIT_FOR_REVIEWING_PRODUCT = 'points/earning_points/reviewing_product_points_limit';

    /**
     * "Reviewing product points/day limit" from system config
     */
    const POINTS_EARNING_LIMIT_FOR_VIDEO_TESTIMONIAL = 'points/earning_points/for_video_testimonial_limit';

    /**
     * "Restrict reviews points gain only for persons purchased product" from system config
     */
    const POINTS_EARNING_RESTRICTION_YESNO = 'points/earning_points/restriction';


    /**
     * "Tagging product" from system config
     */
    const POINTS_EARNING_FOR_TAGGING_PRODUCT = 'points/earning_points/for_tagging_product';

    /**
     * Customer birthday present
     */
    const POINTS_EARNING_FOR_BIRTHDAY = 'points/earning_points/for_birthday';

    /**
     * "Tagging product points/day limit" from system config
     */
    const POINTS_EARNING_LIMIT_FOR_TAGGING_PRODUCT = 'points/earning_points/tagging_product_points_limit';

    /**
     * "Participating in poll" from system config
     */
    const POINTS_EARNING_FOR_PARTICIPATING_IN_POLL = 'points/earning_points/for_participating_in_poll';

    /**
     * "Participating in poll points/day limit" from system config
     */
    const POINTS_EARNING_LIMIT_FOR_PARTICIPATING_IN_POLL = 'points/earning_points/participating_in_poll_points_limit';

    //===========================REFERRAL SYSTEM==============================

    /**
     * YES/NO status of referral system from system config
     */
    const REFERRAL_SYSTEM_YESNO = 'points/referal_system_configuration/enablerefsyst';

    /**
     * "Invitation to registration conversion" from system config
     */
    const PRICE_OF_INVITATION = 'points/referal_system_configuration/priceofinvitation';

    /**
     * "Invitation to registration conversion points/day limit" from system config
     */
    const PRICE_OF_INVITATION_DAY_LIMIT = 'points/referal_system_configuration/price_of_invitation_limit';

    /**
     * "Invitation to purchase conversion" from system config
     */
    const POINTS_FOR_ORDER = 'points/referal_system_configuration/pointsfororder';

    /**
     * "Invitation to purchase conversion fixed amount" from system config
     */
    const POINTS_FOR_ORDER_FIXED = 'points/referal_system_configuration/pointsfororderfixed';

    /**
     * "Invitation to purchase conversion (% from amount)" from system config
     */
    const POINTS_FOR_ORDER_PERCENT = 'points/referal_system_configuration/points_for_order_percent';

    //===========================NOTIFICATIONS==============================

    /**
     * "Enable notifications" from system config
     */
    const ENABLE_NOTIFICATIONS_YESNO = 'points/notifications/enable';

    /**
     * "Sender" from system config
     */
    const NOTIFICATIONS_SENDER = 'points/notifications/identity';

    /**
     * "Balance update email" from system config
     */
    const NOTIFICATIONS_BALANCE_UPDATE_TEMPLATE = 'points/notifications/balance_update_template';

    /**
     *  Customer birthday template
     */
    const NOTIFICATIONS_CUSTOMER_BIRTHDAY_TEMPLATE = 'points/notifications/points_customer_birthday_template';

    /**
     * "Points expire email" from system config
     */
    const NOTIFICATIONS_POINTS_EXPIRE_TEMPLATE = 'points/notifications/points_expire_template';

    /**
     * "Invitation e-mail" from system config
     */
    const NOTIFICATIONS_INVITATION_TEMPLATE = 'points/notifications/template';

    /**
     * "Subscribe customers by default" from system config
     */
    const NOTIFICATIONS_SUBSCRIBE_BY_DEFAULT_YESNO = 'points/notifications/subscribe_by_default';

    /**
     * "Send points expire email before" from system config
     */
    const NOTIFICATIONS_POINT_BEFORE_EXPIRE_EMAIL_SENT = 'points/notifications/point_before_expire_email_sent';

    //===========================GENERAL================================

    public function isPointsEnabled($storeId = null)
    {
        return (bool) (int) Mage::getStoreConfig(self::POINTS_GENERAL_ENABLE_YESNO, $storeId);
    }

    public function getPointUnitName($storeId = null)
    {
        return Mage::getStoreConfig(self::POINT_UNIT_NAME, $storeId);
    }

    public function getPointsExpirationDays($storeId = null)
    {
        return (int) Mage::getStoreConfig(self::POINTS_EXPIRATION_DAYS, $storeId);
    }

    public function getPointsCollectionOrder($storeId = null)
    {
        return (int) Mage::getStoreConfig(self::POINTS_COLLECTION_ORDER, $storeId);
    }

    public function isCancelPoints($storeId = null)
    {
        return (int) Mage::getStoreConfig(self::POINTS_CANCEL_REFUND, $storeId);
    }

    public function isRefundPoints($storeId = null)
    {
        return (int) Mage::getStoreConfig(self::POINTS_REFUND_REFUND, $storeId);
    }

    public function getIsEnabledRewardsHistory($storeId = null)
    {
        return (bool) (int) Mage::getStoreConfig(self::ENABLE_REWARDS_HISTORY_YESNO, $storeId);
    }

    public function getIsApplyEarnRates($storeId = null)
    {
        return (bool) (int) Mage::getStoreConfig(self::APPLY_EARN_RATE, $storeId);
    }

    public function getMinimumPointsToRedeem($storeId = null)
    {
        return (int) Mage::getStoreConfig(self::MINIMUM_POINTS_AMOUNT_TO_REDEEM, $storeId);
    }

    public function getMaximumPointsPerCustomer($storeId = null)
    {
        return (int) Mage::getStoreConfig(self::MAXIMUM_POINTS_PER_CUSTOMER, $storeId);
    }

    public function getInfoPageId($storeId = null)
    {
        return Mage::getStoreConfig(self::WHAT_IS_IT_PAGE_ID, $storeId);
    }

    public function getPayingAmountPercentLimit($storeId = null)
    {
        return Mage::getStoreConfig(self::PAYING_AMOUNT_PERCENT_LIMIT, $storeId);
    }

    public function getCanUseWithCoupon($storeId = null)
    {
        return (int) Mage::getStoreConfig(self::ALLOW_USE_WITH_COUPON, $storeId);
    }

    //===========================EARNING POINTS==============================


    public function getPointsForRegistration($storeId = null)
    {
        return (int) Mage::getStoreConfig(self::POINTS_EARNING_FOR_REGISTRATION, $storeId);
    }

    public function getPointsForNewsletterSingup($storeId = null)
    {
        return (int) Mage::getStoreConfig(self::POINTS_EARNING_FOR_NEWSLETTER_SINGUP, $storeId);
    }

    public function isConsiderNewsletterSignupByAdmin($storeId = null)
    {
        return (int) Mage::getStoreConfig(self::POINTS_EARNING_CONSIDER_NEWSLETTER_SIGNUP_BY_ADMIN, $storeId);
    }

    public function getPointsForReviewingProduct($storeId = null)
    {
        return (int) Mage::getStoreConfig(self::POINTS_EARNING_FOR_REVIEWING_PRODUCT, $storeId);
    }

    public function getPointsForVideoTestimonial($storeId = null)
    {
        return (int) Mage::getStoreConfig(self::POINTS_EARNING_FOR_VIDEO_TESTIMONIAL, $storeId);
    }

    public function getPointsForBirthday($storeId = null)
    {
        return (int) Mage::getStoreConfig(self::POINTS_EARNING_FOR_BIRTHDAY, $storeId);
    }

    public function getPointsLimitForReviewingProduct($storeId = null)
    {
        return (int) Mage::getStoreConfig(self::POINTS_EARNING_LIMIT_FOR_REVIEWING_PRODUCT, $storeId);
    }

    public function getPointsLimitForVideoTestimonial($storeId = null)
    {
        return (int) Mage::getStoreConfig(self::POINTS_EARNING_LIMIT_FOR_VIDEO_TESTIMONIAL, $storeId);
    }

    public function isForBuyersOnly($storeId = null)
    {
        return (bool) (int) Mage::getStoreConfig(self::POINTS_EARNING_RESTRICTION_YESNO, $storeId);
    }

    public function getPointsForTaggingProduct($storeId = null)
    {
        return (int) Mage::getStoreConfig(self::POINTS_EARNING_FOR_TAGGING_PRODUCT, $storeId);
    }

    public function getPointsLimitForTaggingProduct($storeId = null)
    {
        return (int) Mage::getStoreConfig(self::POINTS_EARNING_LIMIT_FOR_TAGGING_PRODUCT, $storeId);
    }

    public function getPointsForParticipatingInPoll($storeId = null)
    {
        return (int) Mage::getStoreConfig(self::POINTS_EARNING_FOR_PARTICIPATING_IN_POLL, $storeId);
    }

    public function getPointsLimitForParticipatingInPoll($storeId = null)
    {
        return (int) Mage::getStoreConfig(self::POINTS_EARNING_LIMIT_FOR_PARTICIPATING_IN_POLL, $storeId);
    }

    //===========================REFERRAL SYSTEM==============================

    public function isReferalSystemEnabled($storeId = null)
    {
        return Mage::getStoreConfigFlag(self::REFERRAL_SYSTEM_YESNO, $storeId);
    }

    public function getInvitationToRegistrationConversion($storeId = null)
    {
        return Mage::getStoreConfig(self::PRICE_OF_INVITATION, $storeId);
    }

    public function getLimitPointsOfInvitationForDay($storeId = null)
    {
        return Mage::getStoreConfig(self::PRICE_OF_INVITATION_DAY_LIMIT, $storeId);
    }

    public function getPointsForOrder($storeId = null)
    {
        return Mage::getStoreConfig(self::POINTS_FOR_ORDER, $storeId);
    }

    public function getFixedPointsForOrder($storeId = null)
    {
        return Mage::getStoreConfig(self::POINTS_FOR_ORDER_FIXED, $storeId);
    }

    public function getPercentPointsForOrder($storeId = null)
    {
        return Mage::getStoreConfig(self::POINTS_FOR_ORDER_PERCENT, $storeId);
    }

    //===========================NOTIFICATIONS==============================

    public function getIsEnabledNotifications($storeId = null)
    {
        return (bool) (int) Mage::getStoreConfig(self::ENABLE_NOTIFICATIONS_YESNO, $storeId);
    }

    public function getNotificatioinSender($storeId = null)
    {
        return Mage::getStoreConfig(self::NOTIFICATIONS_SENDER, $storeId);
    }

    public function getBalanceUpdateTemplate($storeId = null)
    {
        return Mage::getStoreConfig(self::NOTIFICATIONS_BALANCE_UPDATE_TEMPLATE, $storeId);
    }

    public function getPointsExpireTemplate($storeId = null)
    {
        return Mage::getStoreConfig(self::NOTIFICATIONS_POINTS_EXPIRE_TEMPLATE, $storeId);
    }

    public function getPointsBirthdayTemplate($storeId = null)
    {
        return Mage::getStoreConfig(self::NOTIFICATIONS_CUSTOMER_BIRTHDAY_TEMPLATE, $storeId);
    }

    public function getInvitationTemplate($storeId = null)
    {
        return Mage::getStoreConfig(self::NOTIFICATIONS_INVITATION_TEMPLATE, $storeId);
    }

    public function getIsSubscribedByDefault($storeId = null)
    {
        return (bool) (int) Mage::getStoreConfig(self::NOTIFICATIONS_SUBSCRIBE_BY_DEFAULT_YESNO, $storeId);
    }

    public function getDaysBeforePointExpiredToSendEmail($storeId = null)
    {
        return Mage::getStoreConfig(self::NOTIFICATIONS_POINT_BEFORE_EXPIRE_EMAIL_SENT, $storeId);
    }

}

