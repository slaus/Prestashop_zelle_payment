<p class="payment_module">
{if $psversion < "1.5.0.0"}
	<a href="{$this_path_ssl}payment.php" title="{l s='Pay by ZellePayment' mod='zellepayment'}">
    {else}
    	<a href="{$link->getModuleLink('zellepayment', 'payment', [], true)}" title="{l s='Pay by ZellePayment' mod='zellepayment'}">


    {/if}
    
		<img src="{$this_path}zellepayment.jpg" alt="{l s='Pay by ZellePayment' mod='zellepayment'}" />
		{l s='Pay by ZellePayment.' mod='zellepayment'}	</a>
</p>
