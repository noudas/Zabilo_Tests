<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class MyModule extends Module
{
    public function __construct()
    {
        $this->name = 'mymodule';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Your Name';
        $this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('My Module');
        $this->description = $this->l('Displays a customizable homepage message.');
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('displayHome')
            && Configuration::updateValue('MYMODULE_MESSAGE', $this->l('Enjoy our summer sales!'));
    }

    public function uninstall()
    {
        return parent::uninstall()
            && Configuration::deleteByName('MYMODULE_MESSAGE');
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submit_mymodule')) {
            $message = Tools::getValue('MYMODULE_MESSAGE');
            Configuration::updateValue('MYMODULE_MESSAGE', $message);
            $output .= $this->displayConfirmation($this->l('Settings updated.'));
        }

        return $output . $this->renderForm();
    }

    public function renderForm()
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Homepage Message'),
                        'name' => 'MYMODULE_MESSAGE',
                        'size' => 64,
                        'required' => true,
                    ]
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right'
                ]
            ]
        ];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = true;
        $helper->title = $this->displayName;
        $helper->fields_value['MYMODULE_MESSAGE'] = Configuration::get('MYMODULE_MESSAGE');

        return $helper->generateForm([$fields_form]);
    }

    public function hookDisplayHome($params)
    {
        $message = Configuration::get('MYMODULE_MESSAGE');
        $this->context->smarty->assign([
            'mymodule_message' => $message,
        ]);

        return $this->display(__FILE__, 'display.tpl');
    }
}
