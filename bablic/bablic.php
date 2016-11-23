                        ),
                        array(  
                                'type' => 'hidden',
                                'name' => 'bablic_token',
                        ),
                        array(  
                                'type' => 'hidden',
                                'name' => 'bablic_data',
                        ),
                        array(  
                                'type' => 'hidden',
                                'name' => 'check',
                        )
                )
        );
        $helper = new HelperForm();
        $helper->module = $this;
        $helper->title = $this->displayName;
        $helper->name_controller = 'bablic_container';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        if (empty($this->sdk->site_id)) {
            $was_installed = Configuration::get('bablic_uninstalled');
            if ($was_installed != '') {
                array_push($fields_form[0]['form']['array'], array(
                        'type' => 'hidden',
                        'name' => 'bablic_uninstalled',
                ));
                $helper->fields_value['bablic_uninstalled'] = $this->sdk->getMeta();
            }
        }
        $helper->fields_value['bablic_raw_data'] = $this->sdk->getMeta();
        $helper->fields_value['bablic_siteId'] = $this->sdk->site_id;
        $helper->fields_value['bablic_trial'] = $this->sdk->trial_started;
        $helper->fields_value['bablic_editor'] = $this->sdk->editorUrl();
        $helper->fields_value['bablic_token'] = $this->sdk->access_token;
        $helper->fields_value['bablic_data'] = '{}';
        $helper->fields_value['check'] = 'yes';
        return $helper->generateForm($fields_form);
    }

    public function hookdisplayHeader($params)
    {   
        $alt_tags = $this->sdk->getAltTags();
        $this->context->smarty->assign('version', $this->version);
        $this->context->smarty->assign('locales', $alt_tags);
        $this->context->smarty->assign('snippet_url', $this->sdk->getSnippet());
        $this->context->smarty->assign('async', ($this->sdk->getLocale() == $this->sdk->getOriginal()));
        return $this->display(__FILE__, 'altTags.tpl');
    }

    public function hookDisplayBackOfficeHeader()
    {
//       $this->context->controller->addJS('//dev.bablic.com/js/sdk.js');
//       $this->context->controller->addJS('//dev.bablic.com/js/addons/prestashop.js');
         $this->context->controller->addJS('//cdn2.bablic.com/addons/prestashop.js');
         $this->context->controller->addCSS('//cdn2.bablic.com/addons/prestashop.css');
    }
}