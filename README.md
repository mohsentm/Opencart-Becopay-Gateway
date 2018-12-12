Becopay payment gateway for Opencart
====================

Version: 1.0.0

Tags: online payment, payment, payment gateway, becopay

Requires at least: 3.0.0

Tested up to: 3.0.2

Requires PHP: 5.4

License: [Apache 2.0](https://opensource.org/licenses/Apache-2.0)


## Prerequisites


You must have a Becopay merchant account to use this plugin.  It's free and easy to [sign-up for a becopay merchant account](https://becopay.com/en/merchant-register/).


## Installation

**Installation by FTP**

1. Download Becopay plugin zip.
2. Connect to server and go to __Opencart base directory__.
3. Open __upload directory__ inside downloaded archive.
4. Extract files and directories to base directory.
4. Log in to your admin panel.
5. On the left navigation bar, go to ``Extensions > Extensions.``
6. find the `becopay payment gateway` then click on install button 


**Installation from admin panel**

1. Download [becopay.ocmod.zip](https://github.com/becopay/Opencart-Becopay-Gateway/releases/download/v1.0.0/becopay.ocmod.zip)
2. Log in to your admin panel.
3. In the left navigation bar, go to ``Extensions > Installer.``
4. Uploading the Becopay plugin
5. After the upload finished on the left navigation bar, go to ``Extensions > Extensions.``
6. find the `becopay payment gateway` then click on install button 

## Configure the plugin

Before you begin, make sure that you have install your Becopay payment gateway.

Configure the Becopay plugin in your opencart admin panel: 

1. Log in to your admin panel. 
2. In the left navigation bar, go to ``Extensions > Extensions.``
3. On Extensions page choos the `payment` extension type
4. find the `becopay payment gateway` then click on edit button
5. Enter your becopay configuration
	1. Main Configuration  
		* __Status__ - Select ``Enable`` to enable Becopay Payment Gateway.
		* __Mobile__  - Enter your phone number  who registered on the website.If you don't have Becopay merchat account register [here](https://becopay.com/en/merchant-register/).
		* __Api Base Url__  - Enter Becopay api base url here. If you don't have Becopay merchat account register [here](https://becopay.com/en/merchant-register/).
		* __Merchant Api Key__  - Enter your Becopay Api Key here. If you don't have Becopay merchat account register [here](https://becopay.com/en/merchant-register/).
		* __Sort Order__ - Add Gateway list sort number
	2.  Extra Configuration
		* __Merchant Currency__ - Enter your currency want to receive money. eg. IRR, US, EUR
		* __Total__ - The checkout total the order must reach before this payment method becomes active.
		* __Title__ - Allows you to determine what your customers will see this payment title as on the payment method tab.
		* __Description__ - Allows you to determine what your customers will see on confirm order tab.
	3. Order Status
		*  __Paid order status__ - Set your custom status for paid orders with becopay gateway
		*  __Pending order status__ - Set your custom status for pending orders are getting to pay with becopay gateway
 6. Click __Save__ button on top of the page for saving your configuration.

## Becopay Support:

* [GitHub Issues](https://github.com/becopay/Opencart-Becopay-Gateway/issues)
  * Open an issue if you are having issues with this plugin
* [Support](https://becopay.com/en/support/#contact-us)
  * Becopay support
* [Documentation](https://becopay.com/en/io#api)
  * Technical documentation
* [PHP Package](https://github.com/becopay/Invoice_SDK_PHP)
	* PHP SDK for integrating with Becopay Invoicing API  
## Contribute

Would you like to help with this project?  Great!  You don't have to be a developer, either.  If you've found a bug or have an idea for an improvement, please open an [issue](https://github.com/becopay/Opencart-Becopay-Gateway/issues) and tell us about it.

If you *are* a developer wanting contribute an enhancement, bugfix or other patch to this project, please fork this repository and submit a pull request detailing your changes. We review all PRs!

This open source project is released under the [Apache 2.0 license](https://opensource.org/licenses/Apache-2.0) which means if you would like to use this project's code in your own project you are free to do so.  Speaking of, if you have used our code in a cool new project we would like to hear about it!  [Please send us an email](mailto:io@becopay.com).

## License

Please refer to the [LICENSE](https://github.com/becopay/Opencart-Becopay-Gateway/LICENSE.txt) file that came with this project.
