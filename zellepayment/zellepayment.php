<?php
/**
 * 2007-2014 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * No redistribute in other sites, or copy.
 *
 * @author    Slaus <mister.slaus@gmail.com>
 * @copyright 2020 Slaus
 * @license   http://site404.in.ua
 */

class ZellePayment extends PaymentModule
{
	private $_html = '';
	private $_postErrors = array();

	public  $details;
	public  $owner;
	public	$address;
	public	$state1;
	public $extra_mail_vars;

	public function __construct()
	{
		$this->name = 'zellepayment';
		if(_PS_VERSION_ > '1.4.0.0'){
					$this->author = 'Slaus';

		$this->tab = 'payments_gateways';
		}
		else
		{
		$this->tab = 'Payment';
		}
		$this->version = '2.2.0';
			if (_PS_VERSION_ > '1.6.0.0')
			$this->bootstrap = true;
		$this->currencies = true;
		$this->currencies_mode = 'checkbox';

		$config = Configuration::getMultiple(array('ZELLEPAYMENT_DETAILS', 'ZELLEPAYMENT_OWNER', 'ZELLEPAYMENT_ADDRESS','ZELLEPAYMENT_STATE1'));
		if (isset($config['ZELLEPAYMENT_OWNER']))
			$this->owner = $config['ZELLEPAYMENT_OWNER'];
			if (isset($config['ZELLEPAYMENT_STATE1']))
			$this->state1 = $config['ZELLEPAYMENT_STATE1'];
		if (isset($config['ZELLEPAYMENT_DETAILS']))
			$this->details = $config['ZELLEPAYMENT_DETAILS'];
		if (isset($config['ZELLEPAYMENT_ADDRESS']))
			$this->address = $config['ZELLEPAYMENT_ADDRESS'];

		parent::__construct();

		$this->displayName = $this->l('ZellePayment');
		$this->description = $this->l('Accept payments by ZellePayment');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details?');
		if (!isset($this->owner) || !isset($this->details) || !isset($this->address))
			$this->warning = $this->l('Account owner and details must be configured in order to use this module correctly');
		if (!sizeof(Currency::checkPaymentCurrencies($this->id)))
			$this->warning = $this->l('No currency set for this module');
				$this->extra_mail_vars = array(
											'{zellepayment_owner}' => Configuration::get('ZELLEPAYMENT_OWNER'),
											'{zellepayment_details}' => Configuration::get('ZELLEPAYMENT_DETAILS'),
											'{zellepayment_address}' => Configuration::get('ZELLEPAYMENT_ADDRESS')
											);
	}

	public function install()
	{
		if (!parent::install() || !$this->registerHook('payment') || !$this->registerHook('paymentReturn'))
			return false;
			return true;
	}

	public function uninstall()
	{
		if (!Configuration::deleteByName('ZELLEPAYMENT_DETAILS')
				|| !Configuration::deleteByName('ZELLEPAYMENT_OWNER')
				|| !Configuration::deleteByName('ZELLEPAYMENT_ADDRESS')
				|| !Configuration::deleteByName('ZELLEPAYMENT_STATE1')
				|| !parent::uninstall())
			return false;
	}

	private function _postValidation()
	{
		if (isset($_POST['btnSubmit']))
		{
			if (empty($_POST['details']))
				$this->_postErrors[] = $this->l('account details are required.');
			elseif (empty($_POST['owner']))
				$this->_postErrors[] = $this->l('Account owner is required.');
		}
	}

	private function _postProcess()
	{
		if (isset($_POST['btnSubmit']))
		{
			Configuration::updateValue('ZELLEPAYMENT_DETAILS', $_POST['details']);
			Configuration::updateValue('ZELLEPAYMENT_OWNER', $_POST['owner']);
			Configuration::updateValue('ZELLEPAYMENT_ADDRESS', $_POST['address']);
			Configuration::updateValue('ZELLEPAYMENT_STATE1', $_POST['state1']);
		}
		$this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('ok').'" /> '.$this->l('Settings updated').'</div>';
	}

	private function _displayZellePayment()
	{
		$this->_html .= '<img src="../modules/zellepayment/zellepayment.jpg" style="float:left; margin-right:15px;"><b>'.$this->l('This module allows you to accept payments by ZellePayment.').'</b><br /><br />
		'.$this->l('If the client chooses this payment mode, the order will change its status into a \'Waiting for payment\' status.').'<br />
		'.$this->l('Therefore, you will need to manually confirm the order as soon as you receive a wire..').'<br /><br /><br />';
	}

	private function _displayForm()
	{
		$this->_html .=
		'<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset>
			<legend><img src="../img/admin/contact.gif" />'.$this->l('Contact details').'</legend>
				<table border="0" width="500" cellpadding="0" cellspacing="0" id="form">
									<tr><td width="130" style="height: 35px;">'.$this->l('Status ID').'</td><td><input type="text" name="state1" value="'.htmlentities(Tools::getValue('state1', $this->state1), ENT_COMPAT, 'UTF-8').'" style="width: 300px;" /></td>

					<tr><td colspan="2">'.$this->l('Please specify the Zelle account details for customers').'.<br /><br /></td></tr>
					<tr><td width="130" style="height: 35px;">'.$this->l('Owner name').'</td><td><input type="text" name="owner" value="'.htmlentities(Tools::getValue('owner', $this->owner), ENT_COMPAT, 'UTF-8').'" style="width: 300px;" /></td></tr>
					<p>'.$this->l('Zelle Payment status ID. When installing this module, the status «Waiting for Zelle payment» should be added. If the status has not been created, then create it yourself on the «Statuses» page in the «Orders» section and put its ID number in this field.').'</p>
					</tr>
					<tr>
						<td width="130" style="vertical-align: top;">'.$this->l('Details').'</td>
						<td style="padding-bottom:15px;">
							<textarea name="details" rows="4" cols="53">'.htmlentities(Tools::getValue('details', $this->details), ENT_COMPAT, 'UTF-8').'</textarea>
							<p>'.$this->l('Such as country, state, postal code and  tel.').'</p>
						</td>
					</tr>
					<tr>
						<td width="130" style="vertical-align: top;">'.$this->l('CIF/NF/DNI').'</td>
						<td style="padding-bottom:15px;">
							<textarea name="address" rows="4" cols="53">'.htmlentities(Tools::getValue('address', $this->address), ENT_COMPAT, 'UTF-8').'</textarea>
						</td>
					</tr>
					<tr><td colspan="2" align="center"><input class="button" name="btnSubmit" value="'.$this->l('Update settings').'" type="submit" /></td></tr>
				</table>	
				 <center><p class="clear">'.$this->l('For a full version of this module, with auto installation, and confirmation when customer make the payment, check ZellePayment+').'</p></center>

			</fieldset>
		
		</form>
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<fieldset><legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Contribute').'</legend>
				<p class="clear">'.$this->l('You can contribute with a donation if our free modules and themes are usefull for you. Clic on the link and support us!').'</p>
				<p class="clear">'.$this->l('For more modules & themes visit: www.site404.in.ua').'</p>
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="HMBZNQAHN9UMJ">
<input type="image" src="https://www.paypalobjects.com/WEBSCR-640-20110401-1/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/WEBSCR-640-20110401-1/en_US/i/scr/pixel.gif" width="1" height="1">
	</fieldset>
</form>';
	}
private function _displayInfo()
	{
		return $this->display(__FILE__, 'views/templates/hook/infos.tpl');
	}

	private function _displayAdds()
	{
		return $this->display(__FILE__, 'views/templates/hook/adds.tpl');
	}
	public function renderForm()
	{
		$this->postProcess2();

		$fields_form                      = array(
			'form' => array(
				'legend'      => array(
					'title' => $this->l('Configuration'),
					'icon'  => 'icon-cogs'
				),
				'description' => $this->l('Please specify the Zelle account details for customers'),
				'input'       => array(
				/*	array(
						'type'    => 'text',
						'label'   => $this->l('Order status ID'),
						'name'    => 'state1',
						'desc'    => $this->l('Order status (will be filled automatically)'),
						
					),*/
					array(
						'type'    => 'text',
						'label'   => $this->l('Status ID'),
						'name'    => 'state1',
						'desc'    => $this->l('Zelle Payment status ID. When installing this module, the status «Waiting for Zelle payment» should be added. If the status has not been created, then create it yourself on the «Statuses» page in the «Orders» section and put its ID number in this field.'),
						
					),
					array(
						'type'    => 'text',
						'label'   => $this->l('Owner name'),
						'name'    => 'owner',
						'desc'    => $this->l('When you enable delete orders, you can delete orders in ORDER TAB with the trash icon'),
						
					),
					array(
						'type'    => 'text',
						'label'   => $this->l('Details'),
						'name'    => 'details',
						'desc'    => $this->l('Such as country, state, postal code and  tel.'),
						
					),
					array(
						'type'    => 'text',
						'label'   => $this->l('CIF/NIF/DNI'),
						'name'    => 'address',
						'desc'    => $this->l('A identification like passport, ID, etc.'),
						
					),
				),
				'submit'      => array(
					'title' => $this->l('Update settings'),
				)


			),




		);
		$helper                           = new HelperForm();
		$helper->show_toolbar             = true;
		$helper->table                    = $this->table;
		$lang                             = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language    = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form                = array();
		$helper->identifier               = $this->identifier;
		$helper->submit_action            = 'submitWU';
		$helper->currentIndex             = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;

		$helper->token    = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFieldsValues(),
			'languages'    => $this->context->controller->getLanguages(),
			'id_language'  => $this->context->language->id
		);
		return $helper->generateForm(array($fields_form));
	}
	public function getContent()
	{
		if (_PS_VERSION_ < '1.6.0.0')
		{
		$this->_html = '<h2>'.$this->displayName.'</h2>';

		if (!empty($_POST))
		{
			$this->_postValidation();
			if (!sizeof($this->_postErrors))
				$this->_postProcess();
			else
				foreach ($this->_postErrors AS $err)
					$this->_html .= '<div class="alert error">'. $err .'</div>';
		}
		else
			$this->_html .= '<br />';

		$this->_displayZellePayment();
		$this->_displayForm();

		return $this->_html;
		}
		else
			return $this->_html.$this->_displayInfo().$this->renderForm().$this->_displayAdds();
		
	}

	public function execPayment($cart)
	{
		if (!$this->active)
			return ;

		global $cookie, $smarty;

		$smarty->assign(array(
			'nbProducts' => $cart->nbProducts(),
			'cust_currency' => $cookie->id_currency,
			'psversion' => _PS_VERSION_,
			'currencies' => $this->getCurrency(),
			'total' => number_format($cart->getOrderTotal(true, 3), 2, '.', ''),
			'isoCode' => Language::getIsoById(intval($cookie->id_lang)),
			'zellepaymentDetails' => $this->details,
			'zellepaymentAddress' => $this->address,
			'zellepaymentOwner' => $this->owner,
			'zellepaymentState1' => $this->state1,
			'this_path' => $this->_path,
			'this_path_ssl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.$this->name.'/'
		));

		return $this->display(__FILE__, 'payment_execution.tpl');
	}

	public function hookPayment($params)
	{
		if (!$this->active)
			return ;

		global $smarty;

		$smarty->assign(array(
			'this_path' => $this->_path,
			'psversion' => _PS_VERSION_,
			'this_path_ssl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.$this->name.'/'
		));
		return $this->display(__FILE__, 'payment.tpl');
	}

	public function hookPaymentReturn($params)
	{
		if (!$this->active)
			return ;

		global $smarty;
		$state = Configuration::get('ZELLEPAYMENT_STATE1');
		if ($state == Configuration::get('ZELLEPAYMENT_STATE1') || $state == _PS_OS_OUTOFSTOCK_)
				if(_PS_VERSION_ < "1.5.0.0"){
			$smarty->assign(array(
		
				'total_to_pay' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false, false),
				'zellepaymentDetails' => nl2br2($this->details),
				'zellepaymentAddress' => nl2br2($this->address),
				'zellepaymentOwner' => $this->owner,
				'zellepaymentState1' => $this->state1,
				'status' => 'ok',
				'id_order' => $params['objOrder']->id
			));
				}
				else
				{
						$smarty->assign(array(
						'total_to_pay' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),		'zellepaymentDetails' => $this->details,
				'zellepaymentAddress' => $this->address,
				'zellepaymentOwner' => $this->owner,
				'zellepaymentState1' => $this->state1,
				'status' => 'ok',
				'id_order' => $params['objOrder']->id
			));
					
					}
		else
			$smarty->assign('status', 'failed');
		return $this->display(__FILE__, 'payment_return.tpl');
	}
	public function getConfigFieldsValues()
{
		$fields_values = array(
			'details' => Tools::getValue('details', Configuration::get('ZELLEPAYMENT_DETAILS')),
			'owner' => Tools::getValue('owner', Configuration::get('ZELLEPAYMENT_OWNER')),
			'address' => Tools::getValue('address', Configuration::get('ZELLEPAYMENT_ADDRESS')),
			'state1' => Tools::getValue('state1', Configuration::get('ZELLEPAYMENT_STATE1')),
		);
		return $fields_values;

	}
	public function postProcess2()
	{
		$name    = '';
		$skipatt = '';
		$errors  = '';
		if (Tools::isSubmit('submitWU'))
		{
			

				if ($details = Tools::getValue('details'))
				Configuration::updateValue('ZELLEPAYMENT_DETAILS', $details);
			elseif (Shop::getContext() == Shop::CONTEXT_SHOP || Shop::getContext() == Shop::CONTEXT_GROUP)
				Configuration::deleteFromContext('ZELLEPAYMENT_DETAILS');
				
				
			if ($owner = Tools::getValue('owner'))
				Configuration::updateValue('ZELLEPAYMENT_OWNER', $owner);
			elseif (Shop::getContext() == Shop::CONTEXT_SHOP || Shop::getContext() == Shop::CONTEXT_GROUP)
				Configuration::deleteFromContext('ZELLEPAYMENT_OWNER');
				
				if ($address = Tools::getValue('address'))
				Configuration::updateValue('ZELLEPAYMENT_ADDRESS', $address);
			elseif (Shop::getContext() == Shop::CONTEXT_SHOP || Shop::getContext() == Shop::CONTEXT_GROUP)
				Configuration::deleteFromContext('ZELLEPAYMENT_ADDRESS');
				
				if ($state1 = Tools::getValue('state1'))
				Configuration::updateValue('ZELLEPAYMENT_STATE1', $state1);
			elseif (Shop::getContext() == Shop::CONTEXT_SHOP || Shop::getContext() == Shop::CONTEXT_GROUP)
				Configuration::deleteFromContext('ZELLEPAYMENT_STATE1');

		

			$this->_clearCache('zellepayment.tpl');

			if (!$errors)
				Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&conf=6');
			echo $this->displayError($errors);
		}
	}
	public function checkCurrency($cart)
	{
		$currency_order = new Currency((int)($cart->id_currency));
		$currencies_module = $this->getCurrency((int)$cart->id_currency);

		if (is_array($currencies_module))
			foreach ($currencies_module as $currency_module)
				if ($currency_order->id == $currency_module['id_currency'])
					return true;
		return false;
	}
}
