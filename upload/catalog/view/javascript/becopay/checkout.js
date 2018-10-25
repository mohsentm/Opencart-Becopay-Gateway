/**
 * 2018 Becopay
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@becopay.com so we can send you a copy immediately.
 *
 *  @author    Becopay <plugins@becopay.com>
 *  @copyright 2018 Becopay
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of Becopay
 */

$(document).ready(function() {
    $('.country_select').on("click", function() {
        let id, countryBilling, countryOption;

        $('.payment-countries').hide('slow');
        countryBilling = $('input[name="becopay_billing_country"]').val();
        countryOption = countryBilling.find('option');
        if (typeof countryBilling.val() === "undefined" || countryBilling.val() === null) {
            id = countryOption.eq(1).val();
        } else {
            id = countryBilling.val().toLowerCase();
        }

        idcheck = $('#' + id).attr('class');
        if(!idcheck){
            id = 'other';
            idcheck = $('#' + id).attr('class');
            if(!idcheck) {
                id = countryOption.eq(1).val();
            }
        }

        countryOption.attr("selected", false);
        $('#becopay_country').find('option[value=\"' + id + '\"]').attr("selected", true);
        $('#' + id).show('slow');
    });

    $(document).on('change', '#becopay_country' ,function(){
        $('.payment-countries').hide('slow');
        $('#' + $('#becopay_country').val()).show('slow');
    });

    $(document).on('change', 'input[name="payment[pay_type]"]' ,function(event){
        $('.payment').removeClass('activeBecopayPayment');
        $(this).parent().parent().addClass('activeBecopayPayment');
        $('input[name="becopay_payment_method"]').val(event.target.value);
    });

    $(document).on('click', '#button-confirm' ,function(){
        $(this).button('loading');
    });
});
