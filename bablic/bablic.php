<?php
 /**
  * Bablic Localization
  *
  * LICENSE: This program is free software: you can redistribute it and/or modify
  * it under the terms of the GNU General Public License as published by
  * the Free Software Foundation, either version 3 of the License, or
  * (at your option) any later version.
  *
  * @category  localization
  * @package   bablic
  * @author    Ishai Jaffe <ishai@bablic.com>
  * @copyright Bablic 2016
  * @license   http://www.gnu.org/licenses/ GNU License
  */

class Bablic extends Module
{
	private $_html = '';
	private $_postErrors = array();
	
	public function __construct()
	{
		
		$version_mask = explode('.', _PS_VERSION_, 3);
		$version_test = $version_mask[0] > 0 && $version_mask[1] > 3;

		$this->name = 'bablic';
		$this->tab = 'front_office_features';//$version_test ? 'front_office_features' : 'Tools';
                $this->author = 'Ishai Jaffe';
                $this->version = '0.2.1';
                $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
                $this->bootstrap = true;
                $this->module_key = '85b91d2e4c985df4f58cdc3beeaaa87d';
		parent::__construct();

		$this->displayName = $this->l('Bablic Localization');
		$this->description = $this->l('Connects your Prestashop to every language instantly');
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
		return true;
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
			$json = Tools::jsonDecode(Configuration::get('bablic_script'), true);
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
