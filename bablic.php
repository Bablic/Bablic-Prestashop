<?php


class Bablic extends Module
{
	private $_html = '';
	private $_postErrors = array();
	
	function __construct()
	{
		
/*		$version_mask = explode('.', _PS_VERSION_, 3);
		$version_test = $version_mask[0] > 0 && $version_mask[1] > 3;

		$this->name = 'Bablic';
		$this->tab = $version_test ? 'front_office_features' : 'Tools';
		if ($version_test)
			$this->author = '';
		$this->version = '0.2.1';
		parent::__construct();

		$this->displayName = $this->l('Bablic Localization');
		$this->description = $this->l('Use this code as the basis for your own modules');
		*/
	}

	public function install()
	{
		parent::install();
		
		if (!$this->registerHook('displayHeader'))
			return false;
		if (!$this->registerHook('displayBackOfficeHeader'))
		return false;	
		// Set some defaults
		Configuration::updateValue('activate_bablic', 'true');
		Configuration::updateValue('bablic_script', 'Paste Snippet here');
	}

	private function _postValidation()
	{
		if (!Validate::isCleanHtml(Tools::getValue('activate_bablic')))
			$this->_postErrors[] = $this->l('The message you entered was not allowed, sorry');
	}
	
	private function _postProcess()
	{
		//echo Tools::getValue('bablic_script');exit;
		//$string = strip_tags(nl2br2( Tools::getValue('bablic_script')));
		Configuration::updateValue('activate_bablic', (Tools::getValue('activate_bablic') ? 'true' : 'false'));
		Configuration::updateValue('bablic_script', htmlentities(Tools::getValue('bablic_script')),true); // html in here gets tricky ;)
		
		$this->_html .= '<div class="conf confirm">'.$this->l('Settings updated').'</div>';
	}
	
	public function getContent()
	{
		$this->_html .= '<h2>'.$this->displayName.'</h2>';
		if (Tools::getValue('check') == 'yes')
		{			
			$this->_postValidation();
			
			if (!sizeof($this->_postErrors))
				$this->_postProcess();
			else
			{
				foreach ($this->_postErrors AS $err)
				{
					$this->_html .= '<div class="alert error">'.$err.'</div>';
				}
			}
		}
		
		$this->_displayForm();
		
		return $this->_html;
	}
	
	private function _displayForm()
	{
		$this->_html .= '
		<form id="bablicForm" action="'.$_SERVER['REQUEST_URI'].'" method="post" enctype="multipart/form-data">
			<fieldset>
				<input type="hidden" name="check" value="yes" />
				<legend><img src="../img/admin/cog.gif" alt="" class="middle" />'.$this->l('Settings').'</legend>
				<label>'.$this->l('Activate Bablic ON/OFF').'</label>
				<div class="margin-form">
					<input type="checkbox" name="activate_bablic" value="'.(Tools::getValue('activate_bablic', Configuration::get('activate_bablic')) ? "true" : "false").'"' .
					(Tools::getValue('activate_bablic', Configuration::get('activate_bablic')) == "true" ? ' checked="checked"' : '') . ' />
				</div>
				<div class="margin-form clear" style="padding:0 0 1em 0;" style="display:none;">
					<textarea rows="6" cols="80" name="bablic_script"  >'.Tools::getValue('bablic_script', Configuration::get('bablic_script'),true).'</textarea>
				</div>
				
			</fieldset>			
			<button type="submit" class="button">Save</button>
		</form>';
	}
	public function hookdisplayHeader($params){
		if(Configuration::get('activate_bablic') == 'true')
		{
			$json = json_decode(Configuration::get('bablic_script'), true);
			return htmlspecialchars_decode('<script type="text/javascript" src="'.$json['snippet_url'].'"></script>');
		}
		else
		{
			return '';
		}
	}	
	public function hookDisplayBackOfficeHeader()
	{
		 $this->context->controller->addJS('http://cdn2.bablic.com/addons/prestashop.js');
		  $this->context->controller->addCSS('https://cdn2.bablic.com/addons/prestashop.css');
		
	
	}
	
	
	
}
