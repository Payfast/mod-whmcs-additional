<?php
/**
 * payfastadd.php
 *
 * Copyright (c) 2008 PayFast (Pty) Ltd
 * You (being anyone who is not PayFast (Pty) Ltd) may download and use this plugin / code in your own website in conjunction with a registered and active PayFast account. If your PayFast account is terminated for any reason, you may not use this plugin / code or part thereof.
 * Except as expressly indicated in this licence, you may not use, copy, modify or distribute this plugin / code or part thereof in any way.
 * 
 * @author     Jonathan Smit
 * @link       http://www.payfast.co.za/help/whmcs
 */
 
// {{{ payfastadd_config()
/**
 * payfastadd_config()
 *
 * @return
 */
function payfastadd_config()
{
    $configArray = array(
        'FriendlyName' => array( 'Type' => 'System', 'Value' => 'PayFast (Additional)' ),

        'merchant_id' => array( 'FriendlyName' => 'Merchant ID', 'Type' => 'text', 'Size' => '20',
            'Description' => 'Your Merchant ID as given on the <a href="http://www.payfast.co.za/acc/integration">Integration</a> page on PayFast', ),
        'merchant_key'  => array( 'FriendlyName' => 'Merchant Key', 'Type' => 'text', 'Size' => '20',
            'Description' => 'Your Merchant Key as given on the <a href="http://www.payfast.co.za/acc/integration">Integration</a> page on PayFast', ),
        'test_mode' => array( 'FriendlyName' => 'Test Mode', 'Type' => 'yesno',
            'Description' => 'Check this to put the interface in test mode', ),
        'debug' => array( 'FriendlyName' => 'Debugging', 'Type' => 'yesno',
            'Description' => 'Check this to turn debugging on', ),
        );

    return( $configArray );
}
// }}}
// {{{ payfast_add_link()
/**
 * payfast_add_link()
 *
 * Function used to generate code for form to redirect to PayFast
 *
 * @param mixed $params
 * @return
 */
function payfastadd_link( $params )
{
    // Include the PayFast common file
    define( 'PF_DEBUG', ( $params['debug'] == 'on' ? true : false ) );
    require_once( 'payfast_common.inc' );
    
    $pfHost = ( ( $params['test_mode'] == 'on' ) ? 'sandbox' : 'www' ) . '.payfast.co.za';
    $payfastUrl = 'https://'. $pfHost .'/eng/process';
    
    // If NOT test mode, use normal credentials
    if( $params['test_mode'] != 'on' )
    {
        $merchantId = $params['merchant_id'];
        $merchantKey = $params['merchant_key'];
    }
    // If test mode, use generic sandbox credentials
    else
    {
        $merchantId = '10000100';
        $merchantKey = '46f0cd694581a';
    }

    // Create URLs
    $returnUrl = $params['systemurl'] .'/viewinvoice.php?id='. $params['invoiceid'];
    $cancelUrl = $params['systemurl'] .'/viewinvoice.php?id='. $params['invoiceid'];
    $notifyUrl = $params['systemurl'] .'/modules/gateways/callback/payfastadd.php';
    
    // Create description
    // Line item details are not available in the $params variable
    $description = '';

    // Construct data for the form
    $data = array(
        // Merchant details
        'merchant_id' => $merchantId,
        'merchant_key' => $merchantKey,
        'return_url' => $returnUrl,
        'cancel_url' => $cancelUrl,
        'notify_url' => $notifyUrl,

        // Buyer Details
        'name_first' => $params['clientdetails']['firstname'],
        'name_last' => $params['clientdetails']['lastname'],
        'email_address' => $params['clientdetails']['email'],

        // Item details
    	'item_name' => $params['companyname'] .' purchase, Invoice ID #'. $params['invoiceid'],
    	'item_description' => $description,
    	'amount' => number_format( $params['amount'], 2, '.', '' ),
        'm_payment_id' => $params['invoiceid'],
        'currency_code' => $params['currency'],
        
        // Other
        'user_agent' => PF_USER_AGENT,
        );

    // Output the form
    $output = '<form id="payfastadd_form" name="payfastadd_form" action="'. $payfastUrl .'" method="post">';
    foreach( $data as $name => $value )
        $output .= '<input type="hidden" name="'.$name.'" value="'. htmlspecialchars( $value ) .'">';

    $output .= '<input type="submit" value="Pay Now" />';
    $output .= '</form>';

	return( $output );
}
// }}}
?>