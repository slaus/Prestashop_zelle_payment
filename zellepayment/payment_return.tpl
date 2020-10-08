{if $status == 'ok'}
	<p>{l s='Your order on' mod='zellepayment'} <span class="bold">{$base_dir_ssl}</span> {l s='is complete.' mod='zellepayment'}
		<br /><br />
		{l s='Please send us a ZellePayment transfer with:' mod='zellepayment'}
		<br /><br />- {l s='an amout of' mod='zellepayment'} <span class="price">{$total_to_pay}</span>
		<br /><br />- {l s='to the account owner of' mod='zellepayment'} <span class="bold">{if $zellepaymentOwner}{$zellepaymentOwner}{else}___________{/if}</span>
		<br /><br />
		- {l s='Address:' mod='zellepayment'} <span class="bold">{if $zellepaymentDetails}{$zellepaymentDetails}{else}___________{/if}</span>
		<br /><br />
		- {l s='With the ID (CIF/NIF/DNI):' mod='zellepayment'} <span class="bold">{if $zellepaymentAddress}{$zellepaymentAddress}{else}___________{/if}</span>
		<br /><br />
	    - {l s='ZellePayment: ' mod='zellepayment'}<a href="http://www.zellepay.com/" target="_blank">{l s='www.zellepay.com' mod='zellepayment'}</a>
      	<br /><br />
	{l s='You can leave message the above information in your order. Or you can contact our' mod='zellepayment'} <a href="{$base_dir_ssl}contact-form.php">{l s='customer support' mod='zellepayment'}</a>.	</p>
{else}
	<p class="warning">
		{l s='We noticed a problem with your order. If you think this is an error, you can contact our' mod='zellepayment'} 
		<a href="{$base_dir_ssl}contact-form.php">{l s='customer support' mod='zellepayment'}</a>.	</p>
{/if}
