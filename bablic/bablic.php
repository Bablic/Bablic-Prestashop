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
        return Configuration::get('bablic'.$key);
    }
    public function set($key, $value){
        Configuration::updateValue('bablic'.$key,$value,true);
    }
}
/**

DONE:
- bring SDK to this reposotory
- put snippet correctly

TODO:
- prestashop js
- prestashop css

- admin - http://104.199.31.30/prestashop/admin132xc2qav/
- user is test@bablic.com
- password is bablic123
**/

require_once("sdk.php");

function startsWith($haystack, $needle) {
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
}

class Bablic extends Module {
	private $_html = '';
	private $_postErrors = array();
	
	public function __construct() {
          $version_mask = explode('.', _PS_VERSION_, 3);
          $version_test = $version_mask[0] > 0 && $version_mask[1] > 3;
          $this->name = 'bablic';
          $this->tab = 'front_office_features';//$version_test ? 'front_office_features' : 'Tools';
          $this->author = 'Ishai Jaffe';
          $this->version = '0.2.2';
          $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
          $this->bootstrap = true;
          $this->module_key = '85b91d2e4c985df4f58cdc3beeaaa87d';
          parent::__construct();

          $this->displayName = $this->l('Bablic Localization');
          $this->description = $this->l('Connects your Prestashop to every language instantly');

          $controller = $_GET['controller'];
          $this->sdk = new BablicSDK(
            array(
              'channel_id' => 'prestashop',
              'store' => new Bablic_Prestashop_store()
            )
          );

          if(startsWith($controller,'Admin'))
            return;
          $this->sdk->handle_request();
        }

	public function uninstall() {
	  Configuration::updateValue('bablic_uninstalled', 'true');
	}

	public function install() {
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

	private function _postValidation() {
	  if (!Validate::isCleanHtml(Tools::getValue('activate_bablic')))
	    $this->_postErrors[] = $this->l('The message you entered was not allowed, sorry');
	}

	function site_create(){
          $rslt = $this->sdk->create_site(
            array(
                'site_url' => Tools::getHttpHost(true).__PS_BASE_URI__
            )
          );
          return empty($rslt['error']);
	}

	private function _postProcess() {
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
                $site =$data['site'];
                $this->sdk->set_site($site);
                $message = '';
                break;
	    case 'update':
		$this->sdk->refresh_site();
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
	  if($message != '')
	    $this->_html .= '<div class="conf confirm">'.$message.'</div>';
	}
	
	public function getContent() {
		$this->_html .= '<div style="position: relative; height: 78.5vh; margin-left: -10px; margin-top: -20px;" class="bablic_container">';
		if (Tools::getValue('check') == 'yes') {			
			$this->_postValidation();
			
			if (!sizeof($this->_postErrors)) {
				$this->_postProcess();
			} else {
				foreach ($this->_postErrors AS $err) {
					$this->_html .= '<div class="alert error">'.$err.'</div>';
				}
			}
		}
		
		$this->_displayForm();
		$this->_html .= '</div>';
		return $this->_html;
	}
	
	private function create_site_form(){
	  return '
		<div style="padding: 10px">
		<input type="button" class="bablic_button" onClick="create_site();" value="Create Site">
		<input type="button" class="bablic_button" onClick="select_site();" value="I already have a site">
		</div>';
	}

	private function _displayForm() {
	  $this->_html .= '
	    <form id="bablicForm" action="'.$_SERVER['REQUEST_URI'].'" method="post" enctype="multipart/form-data">
	      <fieldset>
		<input type="hidden" name="check" value="yes" />
		<input type="hidden" id="bablic_raw_data" value=\''. $this->sdk->get_meta() . '\' />
		<input type="hidden" id="bablic_siteid" value="'. $this->sdk->site_id . '" />
		<input type="hidden" id="bablic_trial" value="'.$this->sdk->trialStarted.'" />
		<input type="hidden" id="bablic_editor" value="'.$this->sdk->editor_url().'" />
		<input type="hidden" id="bablic_token" value="'.$this->sdk->access_token.'" />
		<input type="hidden" id="bablic_data" name="bablic_data" value="{}" />';
	  if (empty($this->sdk->site_id)) {
	    $was_installed = Configuration::get('bablic_uninstalled');
	    if ($was_installed!='')
	      $this->_html .= '<span>Bablic was installed here</span>';
	    $this->_html .= $this->create_site_form();
	    $this->_html .= ' </fieldset> </form>';
            return;
	  }
	  $this->_html .= ' </fieldset> </form>';
          if ($this->sdk->trialStarted == true) {
	    $this->_html .= "<iframe frameborder='0' style='width: 100%; height:100%;' class='bablic_iframe' src='http://staging.bablic.com/channels/dashboard?site=".$this->sdk->site_id."&access_token=".$this->sdk->access_token."' />";
	    return;
	  }
	  $url = $this->sdk->editor_url();
	  $url = str_replace('www.','staging.', $url);
	  $this->_html .= "<input type='button' onClick='open_editor();'a class='bablic_button' value='Open Editor'>";
	  return;
	}

	public function hookdisplayHeader($params){
	  $html = "<!-- start Bablic Head V$this->version -->";
	  try{
	      $html = $html .$this->sdk->alt_tags(false);
	  } catch (Exception $e) {
	      $html = $html. '<!-- Bablic No Alt Tags -->';
	  }
    	  try{
            if($this->sdk->get_locale() != $this->sdk->get_original()){
                $snippet = $this->sdk->get_snippet();
                if($snippet != ''){
                    $html = $html.$snippet;
                }
            }
          } catch (Exception $e) {
            $html = $html . '<!-- Bablic No Head -->';
          }
          $html = $html . '<!-- end Bablic Head -->';
          return htmlspecialchars_decode($html);
	}

	public function hookdisplayFooter($params){
          $html = '';
          try{
            if($this->sdk->get_locale() == $this->sdk->get_original()){
                $html = '<!-- start Bablic Footer -->';
                $snippet = $this->sdk->get_snippet();
                if($snippet != ''){
                    $html = $html.$snippet;
                }
                $html = $html. '<!-- end Bablic Footer -->';
            }
          } catch (Exception $e) {
            $html = $html . '<!-- Bablic No Footer -->'; 
          }
          return htmlspecialchars_decode($html);
	}

	public function hookDisplayBackOfficeHeader()
	{
		 $this->context->controller->addJS('http://cdn2.bablic.com/addons/prestashop.js');
		  $this->context->controller->addCSS('https://cdn2.bablic.com/addons/prestashop.css');
		
	
	}
	
	
	
}
