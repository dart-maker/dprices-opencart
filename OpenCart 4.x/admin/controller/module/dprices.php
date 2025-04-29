<?php
/**
 * Controller Module D.Prices Class
 *
 * @version 1.0
 * 
 * @author D.art <d.art.reply@gmail.com>
 */

namespace Opencart\Admin\Controller\Extension\DPrices\Module;
class DPrices extends \Opencart\System\Engine\Controller {
    private $error = array();

    public function index(): void {
        $this->load->language('extension/dprices/module/dprices');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->document->addScript(HTTP_SERVER . 'view/javascript/ckeditor/ckeditor.js');
        $this->document->addScript(HTTP_SERVER . 'view/javascript/ckeditor/adapters/jquery.js');

        $this->load->model('setting/module');
        $this->load->model('setting/extension');

        $x = (version_compare(VERSION, '4.0.2.0', '>=')) ? '.' : '|';

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            if (!isset($this->request->get['module_id'])) {
                $this->model_setting_module->addModule('dprices.dprices', $this->request->post);
            } else {
                $this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module'));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['name'])) {
            $data['error_name'] = $this->error['name'];
        } else {
            $data['error_name'] = '';
        }

        if (isset($this->error['prices'])) {
            $data['error_prices'] = $this->error['prices'];
        } else {
            $data['error_prices'] = array();
        }

        if (isset($this->error['module_description'])) {
            $data['error_module_description'] = $this->error['module_description'];
        } else {
            $data['error_module_description'] = array();
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module')
        );

        if (!isset($this->request->get['module_id'])) {
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/dprices/module/dprices', 'user_token=' . $this->session->data['user_token'])
            );
        } else {
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/dprices/module/dprices', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'])
            );
        }

        if (!isset($this->request->get['module_id'])) {
            $data['action'] = $this->url->link('extension/dprices/module/dprices' . $x . 'save', 'user_token=' . $this->session->data['user_token'], true);
        } else {
            $data['action'] = $this->url->link('extension/dprices/module/dprices' . $x . 'save', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
        }

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
        }

        if (isset($this->request->post['name'])) {
            $data['name'] = $this->request->post['name'];
        } elseif (!empty($module_info)) {
            $data['name'] = $module_info['name'];
        } else {
            $data['name'] = '';
        }

        if (isset($this->request->post['attr_ID'])) {
            $data['attr_ID'] = $this->request->post['attr_ID'];
        } elseif (!empty($module_info)) {
            $data['attr_ID'] = $module_info['attr_ID'];
        } else {
            $data['attr_ID'] = '';
        }

        $data['image'] = array();

        $data['image_width_default'] = 320;

        if (isset($this->request->post['image']['width'])) {
            $data['image']['width'] = $this->request->post['image']['width'];
        } elseif (!empty($module_info)) {
            $data['image']['width'] = $module_info['image']['width'];
        } else {
            $data['image']['width'] = $data['image_width_default'];
        }

        $data['image_height_default'] = 320;

        if (isset($this->request->post['image']['height'])) {
            $data['image']['height'] = $this->request->post['image']['height'];
        } elseif (!empty($module_info)) {
            $data['image']['height'] = $module_info['image']['height'];
        } else {
            $data['image']['height'] = $data['image_height_default'];
        }

        if (isset($this->request->post['view_prices'])) {
            $data['view_prices'] = $this->request->post['view_prices'];
        } elseif (!empty($module_info)) {
            $data['view_prices'] = $module_info['view_prices'];
        } else {
            $data['view_prices'] = '';
        }

        if (isset($this->request->post['currency_convertion'])) {
            $data['currency_convertion'] = $this->request->post['currency_convertion'];
        } elseif (!empty($module_info)) {
            $data['currency_convertion'] = $module_info['currency_convertion'];
        } else {
            $data['currency_convertion'] = '';
        }

        if (isset($this->request->post['currency_mode'])) {
            $data['currency_mode'] = $this->request->post['currency_mode'];
        } elseif (!empty($module_info)) {
            $data['currency_mode'] = $module_info['currency_mode'];
        } else {
            $data['currency_mode'] = '1';
        }

        if (isset($this->request->post['currency_convertion_language'])) {
            $data['currency_convertion_language'] = $this->request->post['currency_convertion_language'];
        } elseif (!empty($module_info)) {
            $data['currency_convertion_language'] = $module_info['currency_convertion_language'];
        } else {
            $data['currency_convertion_language'] = '';
        }

        if (isset($this->request->post['module_description'])) {
            $data['module_description'] = $this->request->post['module_description'];
        } elseif (!empty($module_info)) {
            $data['module_description'] = $module_info['module_description'];
        } else {
            $data['module_description'] = array();
        }

        if (isset($this->request->post['captcha'])) {
            $data['captcha'] = $this->request->post['captcha'];
        } elseif (!empty($module_info)) {
            $data['captcha'] = $module_info['captcha'];
        } else {
            $data['captcha'] = '';
        }

        if (isset($this->request->post['status'])) {
            $data['status'] = $this->request->post['status'];
        } elseif (!empty($module_info)) {
            $data['status'] = $module_info['status'];
        } else {
            $data['status'] = '';
        }

        $this->load->model('tool/image');

        /* Languages */

        $this->load->model('localisation/language');

        $data['languages'] = $this->model_localisation_language->getLanguages();

        /* Currencies */

        $this->load->model('localisation/currency');
        
        $data['currencies'] = array();

        $results = $this->model_localisation_currency->getCurrencies(array('stub' => true));

        foreach ($results as $result) {
            $data['currencies'][$result['code']] = array(
                'title'           => $result['title'],
                'code'            => $result['code'],
                'symbol_left'     => $result['symbol_left'],
                'symbol_right'    => $result['symbol_right'],
                'value'           => $result['value'],
                'status'          => $result['status'],
                'status_text'     => $result['status'] ? $this->language->get('text_cur_enabled') : $this->language->get('text_cur_disabled')
            );
        }

        /* Prices */

        if (isset($this->request->post['prices'])) {
            $prices = $this->request->post['prices'];
        } elseif (!empty($module_info) && array_key_exists('prices', $module_info)) {
            $prices = $module_info['prices'];
        } else {
            $prices = array();
        }

        $data['prices'] = array();

        foreach ($prices as $key => $value) {
            foreach ($value as $price) {
                if (is_file(DIR_IMAGE . $price['image'])) {
                    $image_price = $price['image'];
                    $thumb_price = $price['image'];
                } else {
                    $image_price = '';
                    $thumb_price = 'no_image.png';
                }

                $data['prices'][$key][] = array(
                    'title'       => $price['title'],
                    'price'       => $price['price'],
                    'description' => $price['description'],
                    'more'        => $price['more'],
                    'image'       => $image_price,
                    'thumb'       => $this->model_tool_image->resize($thumb_price, 200, 200),
                    'sort_order'  => $price['sort_order'],
                    'price_name'  => $price['price_name']
                );
            }
        }

        $data['placeholder'] = $this->model_tool_image->resize('no_image.png', 200, 200);

        /* Captchas */

        $data['captchas'] = [];

        // Get a list of installed captchas
        $extensions = $this->model_setting_extension->getExtensionsByType('captcha');

        foreach ($extensions as $extension) {
            $this->load->language('extension/' . $extension['extension'] . '/captcha/' . $extension['code'], 'extension');

            if ($this->config->get('captcha_' . $extension['code'] . '_status')) {
                $data['captchas'][] = [
                    'text'  => $this->language->get('extension_heading_title'),
                    'value' => $extension['code']
                ];
            }
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/dprices/module/dprices', $data));
    }

    /**
     * Save Module Data.
     * 
     * @return void
     */
	public function save(): void {
		$this->load->language('extension/dprices/module/dprices');

		$json = [];

		if (!$this->user->hasPermission('modify', 'extension/dprices/module/dprices')) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

        if ((mb_strlen($this->request->post['name']) < 3) || (mb_strlen($this->request->post['name']) > 64)) {
            $json['error']['name'] = $this->language->get('error_name');
        }

        if (isset($this->request->post['module_description'])) {
            foreach ($this->request->post['module_description'] as $language_id => $value) {
                if (mb_strlen($value['currency']) < 1) {
                    $json['error']['module_description_' . $language_id . '_currency'] = $this->language->get('error_currency');
                }

                if (mb_strlen($value['currency_code']) < 1) {
                    $json['error']['module_description_' . $language_id . '_currency_code'] = $this->language->get('error_currency_cod');
                }
            }
        }

        if (isset($this->request->post['prices'])) {
            foreach ($this->request->post['prices'] as $language_id => $value) {
                foreach ($value as $price) {
                    if ((mb_strlen($price['title']) < 3) || (mb_strlen($price['title']) > 64)) {
                        $json['error']['prices_' . $language_id . '_' . $price['price_name'] . '_title'] = $this->language->get('error_row_title');
                    }

                    if (trim($price['price']) == '') {
                        $json['error']['prices_' . $language_id . '_' . $price['price_name'] . '_price'] = $this->language->get('error_row_price');
                    }
                }
            }
        }

		if (!$json) {
			$this->load->model('setting/module');

            if (!isset($this->request->get['module_id'])) {
                $this->model_setting_module->addModule('dprices.dprices', $this->request->post);
            } else {
                $this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
            }

			$json['success'] = $this->language->get('text_success');
		} else {
            if (!isset($json['error']['warning'])) {
                $json['error']['warning'] = $this->language->get('error_warning');
            }
        }

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

    /**
     * Validate Module Data.
     * 
     * @return bool $this->error
     */
    protected function validate(): bool {
        if (!$this->user->hasPermission('modify', 'extension/dprices/module/dprices')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ((mb_strlen($this->request->post['name']) < 3) || (mb_strlen($this->request->post['name']) > 64)) {
            $this->error['name'] = $this->language->get('error_name');
        }

        if (isset($this->request->post['module_description'])) {
            foreach ($this->request->post['module_description'] as $language_id => $value) {
                if (mb_strlen($value['currency']) < 1) {
                    $this->error['module_description'][$language_id]['currency'] = $this->language->get('error_currency');
                }

                if (mb_strlen($value['currency_code']) < 1) {
                    $this->error['module_description'][$language_id]['currency_code'] = $this->language->get('error_currency_cod');
                }
            }
        }

        if (isset($this->request->post['prices'])) {
            foreach ($this->request->post['prices'] as $language_id => $value) {
                foreach ($value as $price) {
                    if ((mb_strlen($price['title']) < 3) || (mb_strlen($price['title']) > 64)) {
                        $this->error['prices'][$language_id][$price['price_name']]['title'] = $this->language->get('error_row_title');
                    }

                    if (trim($price['price']) == '') {
                        $this->error['prices'][$language_id][$price['price_name']]['price'] = $this->language->get('error_row_price');
                    }
                }
            }
        }

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_fields');
        }

        return !$this->error;
    }

    /**
    * Install method.
    *
    * @return void
    */
    public function install(): void {
        $this->load->model('setting/setting');

        // Add settings.
        $this->model_setting_setting->editSetting('module_dprices', array(
            'module_dprices_captcha_ed_pc' => $this->generateRandomString(16), // Password for encrypt/decrypt code.
            'module_dprices_captcha_ed_ec' => $this->generateRandomString(8)   // String for encrypt/decrypt empty code.
        ));
    }

    /**
    * Uninstall method.
    *
    * @return void
    */
    public function uninstall(): void {
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('module_dprices');
    }

    /**
    * Generate random string.
    *
    * @return string $random_string
    */
    function generateRandomString($length = 16) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $characters_length = strlen($characters);
        $random_string = '';

        for ($i = 0; $i < $length; $i++) {
            $random_string .= $characters[random_int(0, $characters_length - 1)];
        }

        return $random_string;
    }
}