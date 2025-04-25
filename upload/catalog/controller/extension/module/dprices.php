<?php
/**
 * Controller Module D.Prices Class
 *
 * @version 1.0
 * 
 * @author D.art <d.art.reply@gmail.com>
 */

class ControllerExtensionModuleDPrices extends Controller {
    private $error = array();

    public function index($setting) {
        if (isset($setting['prices'][$this->config->get('config_language_id')])) {
            $this->load->language('extension/module/dprices');
            $this->load->model('tool/image');
            $this->load->model('localisation/currency');

            if ($this->request->server['HTTPS']) {
                $http_server = HTTPS_SERVER;
            } else {
                $http_server = HTTP_SERVER;
            }

            $this->document->addStyle($http_server . 'catalog/view/javascript/module-dprices/dprices.css');

            static $module = 0;

            $data['heading_title'] = html_entity_decode($setting['module_description'][$this->config->get('config_language_id')]['title'], ENT_QUOTES, 'UTF-8');
            $data['description'] = html_entity_decode($setting['module_description'][$this->config->get('config_language_id')]['description'], ENT_QUOTES, 'UTF-8');
            $data['attr_ID'] = $setting['attr_ID'];
            $data['view_prices'] = $setting['view_prices'];
            $data['currency'] = html_entity_decode($setting['module_description'][$this->config->get('config_language_id')]['currency'], ENT_QUOTES, 'UTF-8');
            $data['currency_side'] = $setting['module_description'][$this->config->get('config_language_id')]['currency_side'];
            $data['prices'] = $setting['prices'][$this->config->get('config_language_id')];

            $data['image_main_width'] = $setting['image']['width'] ? (int)$setting['image']['width'] : 320;
            $data['image_main_height'] = $setting['image']['height'] ? (int)$setting['image']['height'] : 320;

            $currency_code = $setting['module_description'][$this->config->get('config_language_id')]['currency_code'];
            $currency_round = $setting['module_description'][$this->config->get('config_language_id')]['currency_round'][$this->session->data['currency']];
            $currency_direction = $setting['module_description'][$this->config->get('config_language_id')]['currency_direction'][$this->session->data['currency']];
            $currency_convertion_language = $setting['currency_convertion_language'];

            usort($data['prices'], function($a, $b){
                return strcmp($a['sort_order'], $b['sort_order']);
            });

            if ($data['view_prices']) {
                $currencies = array();

                $results = $this->model_localisation_currency->getCurrencies();

                foreach ($results as $result) {
                    $currencies[$result['code']] = array(
                        'code'            => $result['code'],
                        'symbol_left'     => $result['symbol_left'],
                        'symbol_right'    => $result['symbol_right'],
                        'value'           => $result['value'],
                        'status'          => $result['status']
                    );
                }

                if ((int)$setting['currency_convertion']) {
                    switch ($setting['currency_mode']) {
                        case '11':
                            if ($currency_code == $this->session->data['currency']) {
                                $currency_value = 1;
                            } else {
                                if (isset($currencies[$currency_code])) {
                                    $currency_value = $currencies[$this->session->data['currency']]['value'] / $currencies[$currency_code]['value'];
                                } else {
                                    $currency_value = 0;
                                }
                            }

                            $currency_convertion_language = $this->config->get('config_language_id');
                            break;
                        case '21':
                            $currency_code_mode = $setting['module_description'][$currency_convertion_language]['currency_code'];

                            if ($currency_code_mode == $this->session->data['currency']) {
                                $currency_value = 1;
                            } else {
                                if (isset($currencies[$currency_code_mode])) {
                                    $currency_value = $currencies[$this->session->data['currency']]['value'] / $currencies[$currency_code_mode]['value'];
                                } else {
                                    $currency_value = 0;
                                }
                            }

                            break;
                        case '22':
                            $currency_code_mode = $setting['module_description'][$currency_convertion_language]['currency_code'];

                            if (isset($currencies[$currency_code]) && isset($currencies[$currency_code_mode])) {
                                $currency_value = $currencies[$currency_code]['value'] / $currencies[$currency_code_mode]['value'];
                            } else {
                                $currency_value = 0;
                            }

                            break;
                        default:
                            break;
                    }

                    if ($currency_value) {
                        $currency_code_mode = $this->session->data['currency'];

                        switch ($setting['currency_mode']) {
                            case '11':
                            case '21':
                                $currency_code_mode = $this->session->data['currency'];
                                break;
                            case '22':
                                $currency_code_mode = $currency_code;
                                break;
                            default:
                                break;
                        }

                        if ($currencies[$currency_code_mode]['symbol_left']) {
                            $data['currency'] = $currencies[$currency_code_mode]['symbol_left'];
                            $data['currency_side'] = 'left';
                        } else {
                            $data['currency'] = $currencies[$currency_code_mode]['symbol_right'];
                            $data['currency_side'] = 'right';
                        }
                    }

                    $auto_prices = array();

                    foreach ($setting['prices'][$currency_convertion_language] as $value) {
                        $auto_prices[$value['sort_order']] = preg_replace('/\s/', '', $value['price']);
                    }
                }

                foreach ($data['prices'] as &$value) {
                    if (isset($value['image']) && !empty($value['image'])) {
                        $value['thumb'] = $this->model_tool_image->resize($value['image'], $data['image_main_width'], $data['image_main_height']);
                    } else {
                        $value['thumb'] = $this->model_tool_image->resize('no_image.png', $data['image_main_width'], $data['image_main_height']);
                    }

                    if ((int)$setting['currency_convertion']) {
                        if (isset($auto_prices[$value['sort_order']])) {
                            $price = $currency_value ? (float)$auto_prices[$value['sort_order']] * $currency_value : (float)$auto_prices[$value['sort_order']];

                            if ($currency_round == '') {
                                $price = round($price, 2, PHP_ROUND_HALF_DOWN);

                                //$value['price'] = str_replace('.00', '', (string)number_format(preg_replace('/\s/', '', $price), 2, '.', ' '));
                                $value['price'] = str_replace('.00', '', (string)number_format(preg_replace('/\s/', '', $price), 2, $this->language->get('decimal_point'), $this->language->get('thousand_point')));
                            } else {
                                switch ($currency_round) {
                                    case '0':
                                        $ten = 1;
                                        break;
                                    case '1':
                                        $ten = 10;
                                        break;
                                    case '2':
                                        $ten = 100;
                                        break;
                                    case '3':
                                        $ten = 1000;
                                        break;
                                    default:
                                        $ten = 1;
                                        break;
                                }

                                if (($price / $ten) > 0) {
                                    $price = $price / $ten;

                                    switch ($currency_direction) {
                                        case '0':
                                            $price = round($price);
                                            break;
                                        case '1':
                                            $price = floor($price);
                                            break;
                                        case '2':
                                            $price = ceil($price);
                                            break;
                                        default:
                                            $price = round($price);
                                            break;
                                    }

                                    $price = $price * $ten;
                                }

                                //$value['price'] = (string)number_format($price, 0, '.', ' ');
                                $value['price'] = (string)number_format($price, 0, $this->language->get('decimal_point'), $this->language->get('thousand_point'));
                            }
                        } else {
                            unset($data['prices'][$key]);
                        }
                    } else {
                        //$value['price'] = str_replace('.00', '', (string)number_format((float)preg_replace('/\s/', '', $value['price']), 2, '.', ' '));
                        $value['price'] = str_replace('.00', '', (string)number_format((float)preg_replace('/\s/', '', $value['price']), 2, $this->language->get('decimal_point'), $this->language->get('thousand_point')));
                    }

                    $value['description'] = html_entity_decode($value['description'], ENT_QUOTES, 'UTF-8');
                    $value['more'] = html_entity_decode($value['more'], ENT_QUOTES, 'UTF-8');
                }
            } else {
                foreach ($data['prices'] as &$value) {
                    if (isset($value['image']) && !empty($value['image'])) {
                        $value['thumb'] = $this->model_tool_image->resize($value['image'], $data['image_main_width'], $data['image_main_height']);
                    } else {
                        $value['thumb'] = $this->model_tool_image->resize('no_image.png', $data['image_main_width'], $data['image_main_height']);
                    }

                    $value['description'] = html_entity_decode($value['description'], ENT_QUOTES, 'UTF-8');
                    $value['more'] = html_entity_decode($value['more'], ENT_QUOTES, 'UTF-8');
                }
            }

            // Captcha
            if (empty($setting['captcha'])) {
                $data['captcha'] = '';
            } else {
                if ($this->config->get('captcha_' . $setting['captcha'] . '_status')) {
                    $data['captcha'] = $this->load->controller('extension/captcha/' . $setting['captcha']);
                } else {
                    $data['captcha'] = '';
                }
            }

            if (empty($setting['captcha'])) {
                $data['captcha_code'] = openssl_encrypt($this->config->get('module_dprices_captcha_ed_ec'), 'AES-128-ECB', $this->config->get('module_dprices_captcha_ed_pc'));
            } else {
                $data['captcha_code'] = openssl_encrypt($setting['captcha'], 'AES-128-ECB', $this->config->get('module_dprices_captcha_ed_pc'));
            }

            $data['action'] = $this->url->link('extension/module/dprices/submit', '', true);

            $data['module'] = $module++;

            return $this->load->view('extension/module/dprices', $data);
        } else {
            return '';
        }
    }

    /**
     * Submit Form. AJAX.
     * 
     * @return void
     */
    public function submit() {
        $this->load->language('extension/module/dprices');

        $json = array();
        $data = array();

        if (isset($this->request->post['order']) && !empty(trim($this->request->post['order']))) {
            $order = trim($this->request->post['order']);
        } else {
            $order = '';
        }

        if (isset($this->request->post['price']) && !empty(trim($this->request->post['price']))) {
            $price = trim($this->request->post['price']);
        } else {
            $price = '';
        }

        if (isset($this->request->post['currency']) && !empty(trim($this->request->post['currency']))) {
            $currency = trim($this->request->post['currency']);
        } else {
            $currency = '';
        }

        $language_current = $this->language->get('code'); 

        if (!empty($order)) {
            if ($this->validate()) {
                $this->sendMail($order, $price, $currency, $language_current);

                $json['success'] = true;
                $json['text_success'] = $this->language->get('text_success');
            } else {
                $json['success'] = false;
                $json['error'] = $this->error;

                /* if (empty(trim($this->request->post['email'])) && empty(trim($this->request->post['phone']))) {
                    $json['error']['general'] = $this->language->get('error_em_ph');
                } else {
                    $json['error']['general'] = $this->language->get('error_fields');
                } */

                $json['error']['general'] = $this->language->get('error_fields');
            }
        } else {
            $json['success'] = false;
            $json['error'] = $this->error;
            $json['error']['general'] = $this->language->get('error_data');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Send Mail.
     * 
     * @param string $order
     * @param string $price
     * @param string $currency
     * @param string $language_current
     * 
     * @return void
     */
    private function sendMail($order, $price, $currency, $language_current) {
        $language = new Language($this->config->get('config_admin_language'));
        $language->load($this->config->get('config_admin_language'));
        $language->load('extension/module/dprices');

        $mail = new Mail($this->config->get('config_mail_engine'));
        $mail->parameter = $this->config->get('config_mail_parameter');
        $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
        $mail->smtp_username = $this->config->get('config_mail_smtp_username');
        $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
        $mail->smtp_port = $this->config->get('config_mail_smtp_port');
        $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

        $mail->setTo($this->config->get('config_email'));
        $mail->setFrom($this->config->get('config_email'));
        $mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
        $mail->setSubject($language->get('email_subject'));
        $mail->setHtml($this->htmlMail($order, $price, $currency, $language_current));
        $mail->send();
    }

    /**
     * HTML Mail Template.
     * 
     * @param string $order
     * @param string $price
     * @param string $currency
     * @param string $language_current
     * 
     * @return string $html
     */
    private function htmlMail($order, $price, $currency, $language_current) {
        $language = new Language($this->config->get('config_admin_language'));
        $language->load($this->config->get('config_admin_language'));
        $language->load('extension/module/dprices');

        $html = '<div>';
            $html .= '<div>' . $language->get('entry_name') . ': ' . $this->request->post['name'] . '</div>';
            $html .= '<div>' . $language->get('entry_email') . ': ' . $this->request->post['email'] . '</div>';
            $html .= '<div>' . $language->get('entry_phone') . ': ' . $this->request->post['phone'] . '</div>';
          //$html .= '<div>' . $language->get('entry_message') . ': ' . $this->request->post['message'] . '</div>';
            $html .= '<div>-------------</div>';
            $html .= '<div>' . $language->get('email_order') . ': ' . $order . '</div>';
            $html .= '<div>' . $language->get('email_price') . ': ' . $price . '</div>';
            $html .= '<div>' . $language->get('email_curr') . ': ' . $currency . '</div>';
            $html .= '<div>' . $language->get('email_lang') . ': ' . $language_current . '</div>';
            $html .= '<div>-------------</div>';
            $html .= '<div>' . $language->get('email_subject') . '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Validate Form Data.
     * 
     * @return bool $this->error
     */
    protected function validate() {
        $this->load->model('extension/module/dprices');

        /* if (empty(trim($this->request->post['email'])) && empty(trim($this->request->post['phone']))) {
            $this->error['form']['fields']['email'] = $this->language->get('error_field');
            $this->error['form']['fields']['phone'] = $this->language->get('error_tel');
        } */

        /* if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
            $this->error['form']['fields']['name'] = $this->language->get('error_text');
        } */

        if (!filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
            $this->error['form']['fields']['email'] = $this->language->get('error_field');
        }

        if (!empty(trim($this->request->post['phone'])) && !preg_match('/^[0-9]{7,}$/', preg_replace('/[^0-9]+/', '', $this->request->post['phone']))) {
            $this->error['form']['fields']['phone'] = $this->language->get('error_tel');
        }

        /* if (utf8_strlen($this->request->post['message']) < 3) {
            $this->error['form']['fields']['message'] = $this->language->get('error_tarea');
        } */

        // Captcha
        if(isset($this->request->post['ccode'])) {
            $captcha_code = openssl_decrypt($this->request->post['ccode'], 'AES-128-ECB', $this->config->get('module_dprices_captcha_ed_pc'));

            if ($captcha_code == $this->config->get('module_dprices_captcha_ed_ec')) {
                $message = 'Captcha is not set in the module!';
            } else {
                $extension_info = $this->model_extension_module_dprices->getExtensionByCode('captcha', $captcha_code);

                if ($extension_info) {
                    if ($this->config->get('captcha_' . $captcha_code . '_status')) {
                        $captcha = $this->load->controller('extension/captcha/' . $extension_info['code'] . '/validate');

                        if ($captcha) {
                            $this->error['form']['captcha'] = $captcha;
                        }
                    }
                } else {
                    $this->error['form']['captcha'] = 'Error: Captcha is not valid!';
                }
            }
        } else {
            $this->error['form']['ccode'] = 'Error: ccode is not found!';
        }

        return !$this->error;
    }
}