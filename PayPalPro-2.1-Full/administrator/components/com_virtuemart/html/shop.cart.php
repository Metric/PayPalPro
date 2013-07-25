<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id: shop.cart.php 1095 2007-12-19 20:19:16Z soeren_nb $
* @package VirtueMart
* @subpackage html
* @copyright Copyright (C) 2004-2007 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.net
*/
mm_showMyFileName( __FILE__ );

$manufacturer_id = vmGet( $_REQUEST, 'manufacturer_id');

$mainframe->setPageTitle( $VM_LANG->_('PHPSHOP_CART_TITLE') );
$mainframe->appendPathWay( $VM_LANG->_('PHPSHOP_CART_TITLE') );

$continue_link = '';
if( !empty( $category_id)) {
        $continue_link = $sess->url( $_SERVER['PHP_SELF'].'?page=shop.browse&amp;category_id='.$category_id );
}
elseif( empty( $category_id) && !empty($product_id)) {
        $db->query( 'SELECT `category_id` FROM `#__{vm}_product_category_xref` WHERE `product_id`='.intval($product_id) );
        $db->next_record();
        $category_id = $db->f('category_id');
        $continue_link = $sess->url( $_SERVER['PHP_SELF'].'?page=shop.browse&amp;category_id='.$category_id );
}
elseif( !empty( $manufacturer_id )) {
        $continue_link = $sess->url( $_SERVER['PHP_SELF'].'?page=shop.browse&amp;manufacturer_id='.$manufacturer_id );
}

$show_basket = true;

$tpl = new $GLOBALS['VM_THEMECLASS']();

if( file_exists( CLASSPATH.'payment/ps_paypal_api.php') ) {
	require_once( CLASSPATH.'payment/ps_paypal_api.php');
	if( ps_paypal_api::getPaymentMethodId() && ps_paypal_api::isActive()) {
		// Paypal API / Express
		$lang = jfactory::getLanguage();
		$lang_iso = str_replace( '-', '_', $lang->gettag() );
		$available_buttons = array('en_US', 'en_GB', 'de_DE', 'es_ES', 'pl_PL', 'nl_NL', 'fr_FR', 'it_IT', 'zn_CN' );
		if( !in_array( $lang_iso , $available_buttons )) {
			$lang_iso = 'en_US';
		}
		$tpl->set('ppex_img_iso', $lang_iso);
		$paypal_express = $tpl->fetch( 'basket/includes/paypal_express_cart.tpl.php');
		$tpl->set('paypal_express_button', $paypal_express );
	}
}

$tpl->set('show_basket', $show_basket );
$tpl->set('continue_link', $continue_link );
$tpl->set('category_id', $category_id );
$tpl->set('product_id', $product_id );
$tpl->set('manufacturer_id', $manufacturer_id );
$tpl->set('cart', $cart );

echo $tpl->fetch( "pages/$page.tpl.php" );

?>

