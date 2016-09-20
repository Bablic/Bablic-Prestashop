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

class Bablic_Prestashop_store {
    public function get($key){
        return Configuration::getValue('bablic'.$key);
    }
    public function set($key, $value){
        Configuration::updateValue('bablic'.$key,$value,true);
    }
}

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

		$controller = $_GET['controller'];
		if(startsWith($controller,'Admin'))
		    return;
		$this->sdk = new BablicSDK(
            array(
                'channel_id' => 'prestashop',
                'store' => new Bablic_Prestashop_store()
            )
        );
        $this->sdk->handle_request();
	}

	public function install()
	{
		parent::install();
		
		if (!$this->registerHook('displayHeader'))
			return false;
		if (!$this->registerHook('displayFooter'))
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

	function site_create(){
        $rslt = $this->sdk->create_site(
            array(
                'site_url' => __PS_BASE_URI__
            )
        );
        return empty($rslt['error']);
	}

	private function _postProcess()
	{
        $data = Tools::jsonDecode(Tools::getValue('bablic_data'), true);
        $message = '';
        $error = '';
        switch($data['action']){
            case 'create':
                $this->site_create();
                if(!$this->sdk->site_id){
                    $error = 'There was a problem registering this site, please check that website is online and there is that Bablic snippet was not integrated before.';
                }
                else {
                    $message = 'Website was registered successfully';
                }
                break;
            case 'set':
                $site = $data['site'];
                $this->sdk->set_site($site);
                $message = 'Website was registered successfully';
                break;
            case 'delete':
                $this->sdk->remove_site();
                $message = 'Website was deleted from Bablic';
                break;
        }
        $this->sdk->clear_cache();
		//echo Tools::getValue('bablic_script');exit;
		//$string = strip_tags(nl2br2( Tools::getValue('bablic_script')));
		//Configuration::updateValue('activate_bablic', (Tools::getValue('activate_bablic') ? 'true' : 'false'));
		//Configuration::updateValue('bablic_script', htmlentities(Tools::getValue('bablic_script')),true); // html in here gets tricky ;)

		if($error != '')
		    $this->_html .= '<div class="alert error">'.$error.'</div>';
		$this->_html .= '<div class="conf confirm">'.$message.'</div>';
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
                <input type="hidden" id="bablic_item_site_id"  value="<?php echo $this->sdk->site_id; ?>" />
                <input type="hidden" id="bablic_editor_url"  value="<?php echo $this->sdk->editor_url(); ?>" />
                <input type="hidden" id="bablic_data" name="bablic_data" value="{}" />
				<legend><img src="../img/admin/cog.gif" alt="" class="middle" />'.$this->l('Settings').'</legend>
			</fieldset>
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
