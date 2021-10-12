{if $valid == 1}
<p>{l s='Your order has been completed.' mod='foloosi'}
    <br /><br />{l s='For any questions or for further information, please contact our' mod='foloosi'} <a href="{$contact_url}">{l s='customer support' mod='ippopay'}</a>.
</p>    
{else}
<p class="warning">
    {l s='We noticed a problem with your order. If you think this is an error, you can contact our' mod='foloosi'}
    <a href="{$contact_url}">{l s='customer support' mod='foloosi'}</a>.
</p>
{/if}