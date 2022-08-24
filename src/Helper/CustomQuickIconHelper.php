<?php
/*
 *  package: Custom Quickicons
 *  copyright: Copyright (c) 2022. Jeroen Moolenschot | Joomill 
 *  license: GNU General Public License version 2 or later
 *  link: https://www.joomill-extensions.com
 */

namespace Joomill\Module\Customquickicon\Administrator\Helper;

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;


/**
 * Helper for mod_custom_quickicon
 *
 * @since  1.0.0
 */
class CustomQuickIconHelper
{
    /**
     * Stack to hold buttons
     *
     * @var     array[]
     * @since   1.0.0
     */
    protected $buttons = array();

    /**
     * Helper method to return button list.
     *
     * This method returns the array by reference so it can be
     * used to add custom buttons or remove default ones.
     *
     * @param Registry $params The module parameters
     * @param CMSApplication $application The application
     *
     * @return  array  An array of buttons
     *
     * @since   1.0.0
     */
    public function getButtons(Registry $params, CMSApplication $application = null)
    {
        if ($application == null) {
            $application = Factory::getApplication();
        }

        $key = (string)$params;
        $context = (string)$params->get('context', 'mod_custom_quickicon');

        if (!isset($this->buttons[$key])) {
            // Load mod_custom_quickicon language file in case this method is called before rendering the module
            $application->getLanguage()->load('mod_custom_quickicon');

            $this->buttons[$key] = [];


            if ($params->get('show_robots')) {
                $robotsstatus = ucwords($application->get('robots'));
                if ((empty($robotsstatus)) && ($params->get('show_robots') == 1)) {
                    $quickicon = [
                        'image' => 'icon-publish',
                        'link' => 'index.php?option=com_config',
                        'text' => 'Robots meta tag: <br/> Index, Follow',
                        'class' => 'success',
                        'group' => $context,
                        ];
                    $this->buttons[$key][] = $quickicon;
                }

                if ((!empty($robotsstatus) && ($params->get('show_robots') != 3))) {
                    $quickicon =  [
                        'image' => 'icon-warning-2',
                        'link' => 'index.php?option=com_config',
                        'text' => 'Robots meta tag: <br/>' . $robotsstatus,
                        'class' => 'danger',
                        'group' => $context,
                        ];
                    $this->buttons[$key][] = $quickicon;
                }

                if ((!empty($robotsstatus) && ($params->get('show_robots') == 3))) {
                    $alertdangertext = Text::sprintf('MOD_CUSTOM_QUICKICON_MESSAGE', $robotsstatus);
                    $application->enqueueMessage($alertdangertext, 'danger');
                }   

                
            } 

            // JOOMLA DEFAULT QUICKICONS
            if ($params->get('show_users')) {
                $quickicon = [
                    'image' => 'icon-users',
                    'link' => Route::_('index.php?option=com_users&view=users'),
                    'linkadd' => Route::_('index.php?option=com_users&task=user.add'),
                    'name' => 'MOD_CUSTOM_QUICKICON_USER_MANAGER',
                    'access' => array('core.manage', 'com_users', 'core.create', 'com_users'),
                    'group' => $context,
                ];

                if ($params->get('show_users') == 2) {
                    $quickicon['amount'] = 'index.php?option=com_users&amp;task=users.getQuickiconContent&amp;format=json';
                }

                $this->buttons[$key][] = $quickicon;
            }

            if ($params->get('show_menuitems')) {
                $quickicon = [
                    'image' => 'icon-list',
                    'link' => Route::_('index.php?option=com_menus&view=items&menutype='),
                    'linkadd' => Route::_('index.php?option=com_menus&task=item.add'),
                    'name' => 'MOD_CUSTOM_QUICKICON_MENUITEMS_MANAGER',
                    'access' => array('core.manage', 'com_menus', 'core.create', 'com_menus'),
                    'group' => $context,
                ];

                if ($params->get('show_menuitems') == 2) {
                    $quickicon['ajaxurl'] = 'index.php?option=com_menus&amp;task=items.getQuickiconContent&amp;format=json';
                }

                $this->buttons[$key][] = $quickicon;
            }

            if ($params->get('show_articles')) {
                $quickicon = [
                    'image' => 'icon-file-alt',
                    'link' => Route::_('index.php?option=com_content&view=articles'),
                    'linkadd' => Route::_('index.php?option=com_content&task=article.add'),
                    'name' => 'MOD_CUSTOM_QUICKICON_ARTICLE_MANAGER',
                    'access' => array('core.manage', 'com_content', 'core.create', 'com_content'),
                    'group' => $context,
                ];

                if ($params->get('show_articles') == 2) {
                    $quickicon['ajaxurl'] = 'index.php?option=com_content&amp;task=articles.getQuickiconContent&amp;format=json';
                }

                $this->buttons[$key][] = $quickicon;
            }

            if ($params->get('show_tags'))
            {
                $tmp = [
                    'image'   => 'icon-tag',
                    'link'    => Route::_('index.php?option=com_tags&view=tags'),
                    'linkadd' => Route::_('index.php?option=com_tags&task=tag.edit'),
                    'name'    => 'MOD_CUSTOM_QUICKICON_TAGS_MANAGER',
                    'access'  => array('core.manage', 'com_tags', 'core.create', 'com_tags'),
                    'group'   => $context,
                ];

                if ($params->get('show_tags') == 2)
                {
                    $tmp['ajaxurl'] = 'index.php?option=com_tags&amp;task=tags.getQuickiconContent&amp;format=json';
                }

                $this->buttons[$key][] = $tmp;
            }

            // ARTICLE QUICKICONS
            $items = $params->get('article_items', []);
            $items = (array)$items;

            foreach ($items as $item) {
                $db = Factory::getDbo();
                $db->setQuery('SELECT title FROM #__content WHERE id=' . $item->item_id);
                $title = $db->loadResult();

                $quickicon = [
                    'image' => $item->item_icon,
                    'link' => Route::_("index.php?option=com_content&view=article&task=article.edit&id=$item->item_id"),
                    'name' => $title,
                    'group' => $context,
                ];
                if ($item->item_return) {
                    $quickicon['link'] .= '&return=' . urlencode(base64_encode($item->item_return));
                }

                $this->buttons[$key][] = $quickicon;
            }

            // CATEGORY QUICKICONS
            $items = $params->get('category_items', []);
            $items = (array)$items;

            foreach ($items as $item) {
                if ($item->article_category != "") {
                    $db = Factory::getDbo();
                    $db->setQuery('SELECT title FROM #__categories WHERE id=' . $item->article_category);
                    $title = $db->loadResult();
                } else {
                    $title = 'MOD_CUSTOM_QUICKICON_FORM_ARTICLES_LABEL';
                }

                if ($item->article_author == "current") {
                    $title = 'MOD_CUSTOM_QUICKICON_FORM_MYARTICLES_LABEL';
                }

                if ($item->article_language != "*") {
                    $title .= ' (' . $item->article_language . ')';
                }

                $quickicon = [
                    'image' => $item->item_icon,
                    'link' => Route::_("index.php?option=com_content"),
                    'linkadd' => Route::_("index.php?option=com_content&view=article&layout=edit"),
                    'name' => $title,
                    'group' => $context,
                ];

                if ($item->article_category) {
                    $quickicon['link'] .= '&filter[category_id]=' . $item->article_category;
                    $quickicon['linkadd'] .= '&catid=' . $item->article_category;
                }
                if ($item->article_language != "*") {
                    $quickicon['link'] .= '&filter[language]=' . $item->article_language;
                    $quickicon['linkadd'] .= '&language=' . $item->article_language;
                }
                if ($item->article_author) {
                    if ($item->article_author == "current") {
                        $item->article_author = Factory::getUser()->id;
                    }
                    $quickicon['link'] .= '&filter[author_id]=' . $item->article_author;
                }

                $this->buttons[$key][] = $quickicon;
            }

            if ($params->get('show_categories')) {
                $quickicon = [
                    'image' => 'icon-folder-open',
                    'link' => Route::_('index.php?option=com_categories&view=categories&extension=com_content'),
                    'linkadd' => Route::_('index.php?option=com_categories&task=category.add'),
                    'name' => 'MOD_CUSTOM_QUICKICON_CATEGORY_MANAGER',
                    'access' => array('core.manage', 'com_categories', 'core.create', 'com_categories'),
                    'group' => $context,
                ];

                if ($params->get('show_categories') == 2) {
                    $quickicon['ajaxurl'] = 'index.php?option=com_categories&amp;task=categories.getQuickiconContent&amp;format=json';
                }

                $this->buttons[$key][] = $quickicon;
            }

            if ($params->get('show_media')) {
                $this->buttons[$key][] = [
                    'image' => 'icon-images',
                    'link' => Route::_('index.php?option=com_media'),
                    'name' => 'MOD_CUSTOM_QUICKICON_MEDIA_MANAGER',
                    'access' => array('core.manage', 'com_media'),
                    'group' => $context,
                ];
            }

            if ($params->get('show_modules')) {
                $quickicon = [
                    'image' => 'icon-cube',
                    'link' => Route::_('index.php?option=com_modules&view=modules&client_id=0'),
                    'linkadd' => Route::_('index.php?option=com_modules&view=select&client_id=0'),
                    'name' => 'MOD_CUSTOM_QUICKICON_MODULE_MANAGER',
                    'access' => array('core.manage', 'com_modules'),
                    'group' => $context
                ];

                if ($params->get('show_modules') == 2) {
                    $quickicon['ajaxurl'] = 'index.php?option=com_modules&amp;task=modules.getQuickiconContent&amp;format=json';
                }

                $this->buttons[$key][] = $quickicon;
            }
            // MODULE QUICKICONS
            $items = $params->get('module_items', []);
            $items = (array)$items;

            foreach ($items as $item) {
                $db = Factory::getDbo();
                $db->setQuery('SELECT title FROM #__modules WHERE id=' . $item->item_id);
                $title = $db->loadResult();

                $quickicon = [
                    'image' => $item->item_icon,
                    'link' => Route::_("index.php?option=com_modules&view=module&task=module.edit&id=$item->item_id"),
                    'name' => $title,
                    'group' => $context,
                ];
                if ($item->item_return) {
                    $quickicon['link'] .= '&return=' . urlencode(base64_encode($item->item_return));
                }

                $this->buttons[$key][] = $quickicon;
            }

            if ($params->get('show_plugins')) {
                $quickicon = [
                    'image' => 'icon-plug',
                    'link' => Route::_('index.php?option=com_plugins'),
                    'name' => 'MOD_CUSTOM_QUICKICON_PLUGIN_MANAGER',
                    'access' => array('core.manage', 'com_plugins'),
                    'group' => $context
                ];

                if ($params->get('show_plugins') == 2) {
                    $quickicon['ajaxurl'] = 'index.php?option=com_plugins&amp;task=plugins.getQuickiconContent&amp;format=json';
                }

                $this->buttons[$key][] = $quickicon;
            }

            if ($params->get('show_template_styles')) {
                $this->buttons[$key][] = [
                    'image' => 'icon-paint-brush',
                    'link' => Route::_('index.php?option=com_templates&view=styles&client_id=0'),
                    'name' => 'MOD_CUSTOM_QUICKICON_TEMPLATE_STYLES',
                    'access' => array('core.admin', 'com_templates'),
                    'group' => $context
                ];
            }

            if ($params->get('show_template_code')) {
                $this->buttons[$key][] = [
                    'image' => 'icon-code',
                    'link' => Route::_('index.php?option=com_templates&view=templates&client_id=0'),
                    'name' => 'MOD_CUSTOM_QUICKICON_TEMPLATE_CODE',
                    'access' => array('core.admin', 'com_templates'),
                    'group' => $context
                ];
            }

            if ($params->get('show_redirects')) {
                $this->buttons[$key][] = [
                    'image' => 'fas fa-retweet',
                    'link' => Route::_('index.php?option=com_redirect'),
                    'linkadd' => Route::_('index.php?option=com_redirect&view=link&layout=edit'),
                    'name' => 'MOD_CUSTOM_QUICKICON_REDIRECTS',
                    'access' => array('core.admin', 'com_redirect'),
                    'group' => $context
                ];
            }

            if ($params->get('show_checkin')) {
                $quickicon = [
                    'image' => 'icon-unlock-alt',
                    'link' => Route::_('index.php?option=com_checkin'),
                    'name' => 'MOD_CUSTOM_QUICKICON_CHECKINS',
                    'access' => array('core.admin', 'com_checkin'),
                    'group' => $context
                ];

                if ($params->get('show_checkin') == 2) {
                    $quickicon['ajaxurl'] = 'index.php?option=com_checkin&amp;task=getQuickiconContent&amp;format=json';
                }

                $this->buttons[$key][] = $quickicon;
            }

            if ($params->get('show_cache')) {
                $quickicon = [
                    'image' => 'icon-cloud',
                    'link' => Route::_('index.php?option=com_cache'),
                    'name' => 'MOD_CUSTOM_QUICKICON_CACHE',
                    'access' => array('core.admin', 'com_cache'),
                    'group' => $context
                ];

                if ($params->get('show_cache') == 2) {
                    $quickicon['ajaxurl'] = 'index.php?option=com_cache&amp;task=display.getQuickiconContent&amp;format=json';
                }

                $this->buttons[$key][] = $quickicon;
            }

            if ($params->get('show_global')) {
                $this->buttons[$key][] = [
                    'image' => 'icon-cog',
                    'link' => Route::_('index.php?option=com_config'),
                    'name' => 'MOD_CUSTOM_QUICKICON_GLOBAL_CONFIGURATION',
                    'access' => array('core.manage', 'com_config', 'core.admin', 'com_config'),
                    'group' => $context,
                ];
            }


            // HIKASHOP QUICKICONS
            if ($params->get('ecommerce_component') == "HikaShop") {
                if ($params->get('show_hikashop_dashboard')) {
                    $this->buttons[$key][] = [
                        'image' => 'icon-cart',
                        'link' => Route::_('index.php?option=com_hikashop'),
                        'name' => 'MOD_CUSTOM_QUICKICON_HIKASHOP',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_hikashop_products')) {
                    $this->buttons[$key][] = [
                        'image' => 'icon-cubes',
                        'link' => Route::_('index.php?option=com_hikashop&ctrl=product'),
                        'linkadd' => Route::_('index.php?option=com_hikashop&ctrl=product&task=add'),
                        'name' => 'MOD_CUSTOM_QUICKICON_PRODUCTS',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_hikashop_categories')) {
                    $this->buttons[$key][] = [
                        'image' => 'icon-folder',
                        'link' => Route::_('index.php?option=com_hikashop&ctrl=category'),
                        'linkadd' => Route::_('index.php?option=com_hikashop&ctrl=category&task=add'),
                        'name' => 'MOD_CUSTOM_QUICKICON_CATEGORIES',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_hikashop_users')) {
                    $this->buttons[$key][] = [
                        'image' => 'icon-user',
                        'link' => Route::_('index.php?option=com_hikashop&ctrl=user&filter_partner=0'),
                        'name' => 'MOD_CUSTOM_QUICKICON_USERS',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_hikashop_orders')) {
                    $this->buttons[$key][] = [
                        'image' => 'icon-credit',
                        'link' => Route::_('index.php?option=com_hikashop&ctrl=order'),
                        'linkadd' => Route::_('index.php?option=com_hikashop&ctrl=order&task=neworder'),
                        'name' => 'MOD_CUSTOM_QUICKICON_ORDERS',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_hikashop_discounts')) {
                    $this->buttons[$key][] = [
                        'image' => 'icon-tag',
                        'link' => Route::_('index.php?option=com_hikashop&ctrl=discount&filter_type=discount'),
                        'linkadd' => Route::_('index.php?option=com_hikashop&ctrl=discount&discount_type=discount&task=add'),
                        'name' => 'MOD_CUSTOM_QUICKICON_DISCOUNTS',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_hikashop_coupons')) {
                    $this->buttons[$key][] = [
                        'image' => 'icon-tags-2',
                        'link' => Route::_('index.php?option=com_hikashop&ctrl=discount&filter_type=coupon'),
                        'linkadd' => Route::_('index.php?option=com_hikashop&ctrl=discount&discount_type=coupon&task=add'),
                        'name' => 'MOD_CUSTOM_QUICKICON_COUPONS',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_hikashop_carts')) {
                    $this->buttons[$key][] = [
                        'image' => 'icon-basket',
                        'link' => Route::_('index.php?option=com_hikashop&ctrl=cart&cart_type=cart'),
                        'linkadd' => Route::_('index.php?option=com_hikashop&ctrl=cart&cart_type=cart&task=add'),
                        'name' => 'MOD_CUSTOM_QUICKICON_CARTS',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_hikashop_wishlist')) {
                    $this->buttons[$key][] = [
                        'image' => 'icon-heart-2',
                        'link' => Route::_('index.php?option=com_hikashop&ctrl=cart&cart_type=wishlist'),
                        'linkadd' => Route::_('index.php?option=com_hikashop&ctrl=cart&cart_type=wishlist&task=add'),
                        'name' => 'MOD_CUSTOM_QUICKICON_WISHLIST',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_hikashop_waitlist')) {
                    $this->buttons[$key][] = [
                        'image' => 'icon-clock',
                        'link' => Route::_('index.php?option=com_hikashop&ctrl=waitlist'),
                        'linkadd' => Route::_('index.php?option=com_hikashop&ctrl=waitlist&task=add'),
                        'name' => 'MOD_CUSTOM_QUICKICON_WAITLIST',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_hikashop_emailhistory')) {
                    $this->buttons[$key][] = [
                        'image' => 'icon-envelope-opened',
                        'link' => Route::_('index.php?option=com_hikashop&ctrl=email_history'),
                        'name' => 'MOD_CUSTOM_QUICKICON_EMAILHISTORY',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_hikashop_config')) {
                    $this->buttons[$key][] = [
                        'image' => 'icon-wrench',
                        'link' => Route::_('index.php?option=com_hikashop&ctrl=config'),
                        'name' => 'MOD_CUSTOM_QUICKICON_CONFIGURATION',
                        'group' => $context,
                    ];
                }
            }


            // VIRTUEMART QUICKICONS
            if ($params->get('ecommerce_component') == "Virtuemart") {
                if ($params->get('show_virtuemart_products')) {
                    $this->buttons[$key][] = [
                        'image' => 'icon-cubes',
                        'link' => Route::_('index.php?option=com_virtuemart&view=product'),
                        'linkadd' => Route::_('index.php?option=com_virtuemart&view=product&task=add'),
                        'name' => 'MOD_CUSTOM_QUICKICON_PRODUCTS',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_virtuemart_categories')) {
                    $this->buttons[$key][] = [
                        'image' => 'icon-folder',
                        'link' => Route::_('index.php?option=com_virtuemart&view=category'),
                        'linkadd' => Route::_('index.php?option=com_virtuemart&view=category&task=add'),
                        'name' => 'MOD_CUSTOM_QUICKICON_CATEGORIES',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_virtuemart_shoppers')) {
                    $this->buttons[$key][] = [
                        'image' => 'icon-user',
                        'link' => Route::_('index.php?option=com_virtuemart&view=user'),
                        'name' => 'MOD_CUSTOM_QUICKICON_USERS',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_virtuemart_orders')) {
                    $this->buttons[$key][] = [
                        'image' => 'icon-credit',
                        'link' => Route::_('index.php?option=com_virtuemart&view=orders'),
                        'name' => 'MOD_CUSTOM_QUICKICON_ORDERS',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_virtuemart_coupons')) {
                    $this->buttons[$key][] = [
                        'image' => 'icon-tags-2',
                        'link' => Route::_('index.php?option=com_virtuemart&view=coupon'),
                        'linkadd' => Route::_('index.php?option=com_virtuemart&view=coupon&task=add'),
                        'name' => 'MOD_CUSTOM_QUICKICON_COUPONS',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_virtuemart_reviews')) {
                    $this->buttons[$key][] = [
                        'image' => 'icon-star',
                        'link' => Route::_('index.php?option=com_virtuemart&view=ratings'),
                        'name' => 'MOD_CUSTOM_QUICKICON_REVIEWS',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_virtuemart_manufacturers')) {
                    $this->buttons[$key][] = [
                        'image' => 'fas fa-industry',
                        'link' => Route::_('index.php?option=com_virtuemart&view=manufacturer'),
                        'linkadd' => Route::_('index.php?option=com_virtuemart&view=manufacturer&task=add'),
                        'name' => 'MOD_CUSTOM_QUICKICON_MANUFACTURERS',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_virtuemart_salesreport')) {
                    $this->buttons[$key][] = [
                        'image' => 'far fa-chart-bar',
                        'link' => Route::_('index.php?option=com_virtuemart&view=report'),
                        'name' => 'MOD_CUSTOM_QUICKICON_SALESREPORT',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_virtuemart_inventory')) {
                    $this->buttons[$key][] = [
                        'image' => 'fas fa-barcode',
                        'link' => Route::_('index.php?option=com_virtuemart&view=inventory'),
                        'name' => 'MOD_CUSTOM_QUICKICON_INVENTORY',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_virtuemart_config')) {
                    $this->buttons[$key][] = [
                        'image' => 'icon-wrench',
                        'link' => Route::_('index.php?option=com_virtuemart&view=config'),
                        'name' => 'MOD_CUSTOM_QUICKICON_CONFIGURATION',
                        'group' => $context,
                    ];
                }
            }

            // DJ-CATALOG QUICKICONS
            if ($params->get('ecommerce_component') == "DJ-Catalog") {
                if ($params->get('show_djcatalog_dashboard')) {
                    $this->buttons[$key][] = [
                        'image' => 'icon-cart',
                        'link' => Route::_('index.php?option=com_djcatalog2&view=cpanel'),
                        'name' => 'MOD_CUSTOM_QUICKICON_DJCATALOG',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_djcatalog_products')) {
                    $this->buttons[$key][] = [
                        'image' => 'icon-cubes',
                        'link' => Route::_('index.php?option=com_djcatalog2&view=items'),
                        'linkadd' => Route::_('index.php?option=com_djcatalog2&task=item.add'),
                        'name' => 'MOD_CUSTOM_QUICKICON_PRODUCTS',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_djcatalog_categories')) {
                    $this->buttons[$key][] = [
                        'image' => 'icon-folder',
                        'link' => Route::_('index.php?option=com_djcatalog2&view=categories'),
                        'linkadd' => Route::_('index.php?option=com_djcatalog2&task=category.add'),
                        'name' => 'MOD_CUSTOM_QUICKICON_CATEGORIES',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_djcatalog_customers')) {
                    $this->buttons[$key][] = [
                        'image' => 'icon-user',
                        'link' => Route::_('index.php?option=com_djcatalog2&view=customers'),
                        'name' => 'MOD_CUSTOM_QUICKICON_USERS',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_djcatalog_orders')) {
                    $this->buttons[$key][] = [
                        'image' => 'icon-credit',
                        'link' => Route::_('index.php?option=com_djcatalog2&view=orders'),
                        'linkadd' => Route::_('index.php?option=com_djcatalog2&task=order.add'),
                        'name' => 'MOD_CUSTOM_QUICKICON_ORDERS',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_djcatalog_subscriptions')) {
                    $this->buttons[$key][] = [
                        'image' => 'fas fa-file-invoice-dollar',
                        'link' => Route::_('index.php?option=com_djcatalog2&view=subscriptions'),
                        'linkadd' => Route::_('index.php?option=com_djcatalog2&task=subscription.add'),
                        'name' => 'MOD_CUSTOM_QUICKICON_SUBSCRIPTIONS',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_djcatalog_pricerules')) {
                    $this->buttons[$key][] = [
                        'image' => 'icon-tag',
                        'link' => Route::_('index.php?option=com_djcatalog2&view=pricerules'),
                        'linkadd' => Route::_('index.php?option=com_djcatalog2&task=pricerule.add'),
                        'name' => 'MOD_CUSTOM_QUICKICON_PRICERULES',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_djcatalog_coupons')) {
                    $this->buttons[$key][] = [
                        'image' => 'icon-tags-2',
                        'link' => Route::_('index.php?option=com_djcatalog2&view=coupons'),
                        'linkadd' => Route::_('index.php?option=com_djcatalog2&task=coupon.add'),
                        'name' => 'MOD_CUSTOM_QUICKICON_COUPONS',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_djcatalog_producers')) {
                    $this->buttons[$key][] = [
                        'image' => 'fas fa-industry',
                        'link' => Route::_('index.php?option=com_djcatalog2&view=producers'),
                        'linkadd' => Route::_('index.php?option=com_djcatalog2&task=producer.add'),
                        'name' => 'MOD_CUSTOM_QUICKICON_PRODUCERS',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_djcatalog_vendors')) {
                    $this->buttons[$key][] = [
                        'image' => 'fas fa-user-tag',
                        'link' => Route::_('index.php?option=com_djcatalog2&view=vendors'),
                        'linkadd' => Route::_('index.php?option=com_djcatalog2&task=vendor.add'),
                        'name' => 'MOD_CUSTOM_QUICKICON_VENDORS',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_djcatalog_reviews')) {
                    $this->buttons[$key][] = [
                        'image' => 'icon-star',
                        'link' => Route::_('index.php?option=com_djcatalog2&view=reviews'),
                        'linkadd' => Route::_('index.php?option=com_djcatalog2&task=review.add'),
                        'name' => 'MOD_CUSTOM_QUICKICON_REVIEWS',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_djcatalog_messages')) {
                    $this->buttons[$key][] = [
                        'image' => 'icon-envelope-opened',
                        'link' => Route::_('index.php?option=com_djcatalog2&view=messages'),
                        'name' => 'MOD_CUSTOM_QUICKICON_MESSAGES',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_djcatalog_pricesstock')) {
                    $this->buttons[$key][] = [
                        'image' => 'fas fa-barcode',
                        'link' => Route::_('index.php?option=com_djcatalog2&view=prices'),
                        'name' => 'MOD_CUSTOM_QUICKICON_PRICESSTOCK',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_djcatalog_config')) {
                    $this->buttons[$key][] = [
                        'image' => 'icon-wrench',
                        'link' => Route::_('index.php?option=com_config&view=component&component=com_djcatalog2'),
                        'name' => 'MOD_CUSTOM_QUICKICON_CONFIGURATION',
                        'group' => $context,
                    ];
                }
            }


            // J2STORE QUICKICONS
            if ($params->get('ecommerce_component') == "J2Store") {
                if ($params->get('show_j2store_dashboard')) {
                    $this->buttons[$key][] = [
                        'image' => 'icon-cart',
                        'link' => Route::_('index.php?option=com_j2store'),
                        'name' => 'MOD_CUSTOM_QUICKICON_J2STORE',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_j2store_products')) {
                    $this->buttons[$key][] = [
                        'image' => 'icon-cubes',
                        'link' => Route::_('index.php?option=com_j2store&view=products'),
                        'linkadd' => Route::_('index.php?option=com_content&view=article&layout=edit'),
                        'name' => 'MOD_CUSTOM_QUICKICON_PRODUCTS',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_j2store_inventory')) {
                    $this->buttons[$key][] = [
                        'image' => 'fas fa-barcode',
                        'link' => Route::_('index.php?option=com_j2store&view=inventories'),
                        'name' => 'MOD_CUSTOM_QUICKICON_INVENTORY',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_j2store_vendors')) {
                    $this->buttons[$key][] = [
                        'image' => 'fas fa-user-tag',
                        'link' => Route::_('index.php?option=com_j2store&view=vendors'),
                        'linkadd' => Route::_('index.php?option=com_j2store&view=vendors&task=add'),
                        'name' => 'MOD_CUSTOM_QUICKICON_VENDORS',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_j2store_manufacturers')) {
                    $this->buttons[$key][] = [
                        'image' => 'fas fa-industry',
                        'link' => Route::_('index.php?option=com_j2store&view=manufacturers'),
                        'linkadd' => Route::_('index.php?option=com_j2store&view=manufacturers&task=add'),
                        'name' => 'MOD_CUSTOM_QUICKICON_MANUFACTURERS',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_j2store_orders')) {
                    $this->buttons[$key][] = [
                        'image' => 'icon-credit',
                        'link' => Route::_('index.php?option=com_j2store&view=orders'),
                        'name' => 'MOD_CUSTOM_QUICKICON_ORDERS',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_j2store_customers')) {
                    $this->buttons[$key][] = [
                        'image' => 'icon-user',
                        'link' => Route::_('index.php?option=com_j2store&view=customers'),
                        'name' => 'MOD_CUSTOM_QUICKICON_USERS',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_j2store_coupons')) {
                    $this->buttons[$key][] = [
                        'image' => 'icon-tags-2',
                        'link' => Route::_('index.php?option=com_j2store&view=coupons'),
                        'linkadd' => Route::_('index.php?option=com_j2store&view=coupons&task=add'),
                        'name' => 'MOD_CUSTOM_QUICKICON_COUPONS',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_j2store_vouchers')) {
                    $this->buttons[$key][] = [
                        'image' => 'icon-tag',
                        'link' => Route::_('index.php?option=com_j2store&view=vouchers'),
                        'linkadd' => Route::_('index.php?option=com_j2store&view=vouchers&task=add'),
                        'name' => 'MOD_CUSTOM_QUICKICON_VOUCHERS',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_j2store_reports')) {
                    $this->buttons[$key][] = [
                        'image' => 'far fa-chart-bar',
                        'link' => Route::_('index.php?option=com_j2store&view=reports'),
                        'name' => 'MOD_CUSTOM_QUICKICON_REPORTS',
                        'group' => $context,
                    ];
                }

                if ($params->get('show_j2store_config')) {
                    $this->buttons[$key][] = [
                        'image' => 'icon-wrench',
                        'link' => Route::_('index.php?option=com_j2store&view=configuration'),
                        'name' => 'MOD_CUSTOM_QUICKICON_CONFIGURATION',
                        'group' => $context,
                    ];
                }
            }

            // CUSTOM QUICKICONS
            $items = $params->get('custom_items', []);
            $items = (array)$items;

            foreach ($items as $item) {

                $quickicon = [
                    'image' => $item->item_icon,
                    'name' => $item->item_name,
                    'group' => $context,
                ];
                if ($item->item_link_target) {
                    $quickicon['target'] = $item->item_link_target;
                }
                if ($item->item_link_menuitem == "custom") {
                    $quickicon['link'] = Route::_($item->item_link);
                } else {
                    $quickicon['link'] = Route::_($item->item_link_menuitem);
                }
                if ($item->item_link_add) {
                    $quickicon['linkadd'] = Route::_($item->item_link_add);
                }

                $this->buttons[$key][] = $quickicon;
            }

        }

        return $this->buttons[$key];
    }
}
