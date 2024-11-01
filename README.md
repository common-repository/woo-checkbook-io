# Checkbook: Digital Checking for WooCommerce
<img src="https://raw.githubusercontent.com/varun-tandon/CheckbookWooCommercePlugin/1628bc5d480364c2ddb1c6cbf1b4666c15255426/assets/logo-dark.svg" alt="Checkbook Logo" width="300">

Author: Varun Tandon ([Checkbook Inc.](https://checkbook.io/ "Checkbook.io"))   
Tags: checkbook, woocommerce, checkbook.io,    
Requires at least: 3.8    
Tested up to: 6.1   
Stable tag: 2.0.5
Requires PHP at least: 7.4
License: GPLv2 or later  
License URI: https://www.gnu.org/licenses/gpl-2.0.html   

This plugin enables Checkbook.io Payments through WooCommerce.

## Description 

This plugin allows you to accept digital checks through Checkbook.io as payments through WooCommerce.

Simply install the plugin, configure the settings to fit your needs, and Checkbook.io will appear as a payment option in your WooCommerce cart.

 ## Installation 

First you will need to setup your Checkbook.io account and receive your API credentials.

1. Navigate to [checkbook.io](https://checkbook.io/ "Checkbook.io") and create an account if you have not already. If you have, login to your account.
2. From your account dashboard, go to Settings > Developer. On this page you can see the API credentials necessary for plugin installation.

Plugin Installation:

1. Install the plugin through the Wordpress.org Plugin Repository
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Configure screen to configure the plugin settings with your API Secret Key and Client ID.
4. On the Configure screen, a default value for the Redirect URL will be filled in. Set this link as the Callback URL on the Checkbook Developer page.

## Frequently Asked Questions

### Why do I receive an error that I cannot send checks to myself? =

While testing the plugin, create a separate Checkbook.io account to make payments from. You cannot send a check to your account from your own account.

### I am facing another issue.

Please contact our support at [support@checkbook.io](mailto://support@checkbook.io "Checkbook Support").

## Screenshots

1. Example Checkbook.io Payment Gateway

![Example Checkbook.io Payment Gateway](https://github.com/varun-tandon/CheckbookWooCommercePlugin/blob/master/assets/screenshot-1.png?raw=true)

2. Example Checkbook.io Authentication Screen

![Example Checkbook.io Authentication Screen](https://github.com/varun-tandon/CheckbookWooCommercePlugin/blob/master/assets/screenshot-2.png?raw=true)
