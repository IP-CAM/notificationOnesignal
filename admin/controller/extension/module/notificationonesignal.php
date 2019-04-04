<?php

class ControllerExtensionModuleNotificationOnesignal extends Controller
{
    private $error = [];

    public function index()
    {


        //        echo ($this->request->get['route']);

        $this->load->language('extension/module/notificationonesignal');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('notificationonesignal', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token='.$this->session->data['user_token'].'&type=module', true));
        }

        if (isset($this->error['notificationonesignal_app_id'])) {
            $data['error_no_key_app_id'] = $this->error['notificationonesignal_app_id'];
        } else {
            $data['error_no_key_app_id'] = '';
        }

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token='.$this->session->data['user_token'], true),
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token='.$this->session->data['user_token'].'&type=module', true),
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/notificationonesignal', 'user_token='.$this->session->data['user_token'], true),
        ];

        $data['action'] = $this->url->link('extension/module/notificationonesignal', 'user_token='.$this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token='.$this->session->data['user_token'].'&type=module', true);

        $this->load->model('setting/setting');
        $setting = $this->model_setting_setting->getSetting('notificationonesignal');

        //الكي الخاص باالتطبيق
        if (isset($this->request->post['notificationonesignal_app_id'])) {
            $data['notificationonesignal_app_id'] = $this->request->post['notificationonesignal_app_id'];
        } elseif ($this->config->get('notificationonesignal_app_id')) {
            $data['notificationonesignal_app_id'] = $this->config->get('notificationonesignal_app_id');
        } else {
            $data['notificationonesignal_app_id'] = '';
        }

        //الكي الخاص باالتطبيق
        if (isset($this->request->post['notificationonesignal_api_key'])) {
            $data['notificationonesignal_api_key'] = $this->request->post['notificationonesignal_api_key'];
        } elseif ($this->config->get('notificationonesignal_api_key')) {
            $data['notificationonesignal_api_key'] = $this->config->get('notificationonesignal_api_key');
        } else {
            $data['notificationonesignal_api_key'] = '';
        }

        //الكي الخاص باالتطبيق
        if (isset($this->request->post['notificationonesignal_status'])) {
            $data['notificationonesignal_status'] = $this->request->post['notificationonesignal_status'];
        } elseif ($this->config->get('notificationonesignal_status')) {
            $data['notificationonesignal_status'] = $this->config->get('notificationonesignal_status');
        } else {
            $data['notificationonesignal_status'] = '';
        }

        if (isset($this->request->post['notificationonesignal_name_segment'])) {
            $data['notificationonesignal_name_segment'] = $this->request->post['notificationonesignal_name_segment'];
        } elseif ($this->config->get('notificationonesignal_name_segment')) {
            $data['notificationonesignal_name_segment'] = $this->config->get('notificationonesignal_name_segment');
        } else {
            $data['notificationonesignal_name_segment'] = '';
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/notificationonesignal', $data));
    }

    public function uninstall()
    {
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('Notificationonesignal');
    }


    //    public  function  test(){
    //        $this->load->model('setting/setting');
    //        $setting = $this->model_setting_setting->getSetting('notificationonesignal');
    //
    //        print_r($setting);
    //    }

    /*
     * Send test message, to see if the push functionality is working
     */
    public function send()
    {


        $id = $_GET['id'];
        $msg = $_GET['msg'];
        $response = $this->sendMessage($id, $msg);
        $return["allresponses"] = $response;
        $return = json_encode($return);
        $data = json_decode($response, true);

        echo $response;
    }

    public function getProduct()
    {

        global $loader, $registry;
        $id = $_GET['id'];
        $loader->model('catalog/product');
        $model = $registry->get('model_catalog_product');
        $result = $model->getProduct($id);

        $nameProduct = $result["name"];
        //     $nameImage = 'https://'.$_SERVER['HTTP_HOST'].'/image/'.$result["image"];
        //	 echo  $this->config->get('config_url') . 'image/' . $result['image'];

        if (defined("HTTP_IMAGE")) {
            $nameImage = HTTP_IMAGE.'image/';
        } elseif ($this->config->get('config_url')) {
            $nameImage = $this->config->get('config_url').'image/';
        } else {
            $nameImage = HTTP_CATALOG.'image/';
        }
        $nameImage .= $result['image'];

        $this->load->model('setting/setting');
        $setting = $this->model_setting_setting->getSetting('notificationonesignal');

        echo json_encode([
                'text' => $nameProduct,
                'imgUrl' => $nameImage,
                'ID' => $id,
                'segment' => $this->getSegment(),
            ]);
    }

    public function getSegment()
    {

        $this->load->model('setting/setting');
        $setting = $this->model_setting_setting->getSetting('notificationonesignal');

        return $setting['notificationonesignal_name_segment'];
    }

    function sendMessage()
    {


        $this->load->model('setting/setting');
        $setting = $this->model_setting_setting->getSetting('notificationonesignal');

        $nameProduct = $_GET["name"];
        $ImageUrl = $_GET["imgUrl"];
        $id = $_GET["id"];

        if ($_GET['sound'] == "true") {
            $sound = "nil";
        } else {
            $sound = "";
        }

        if ($_GET['active_url'] == "true") {
            $active_url = "nil";
        } else {
            $active_url = "";
        }

        $productUrl = $this->url->link('product/product', 'product_id='.$id);

        $content = [
            "en" => $nameProduct,
            "ar" => $nameProduct,
        ];

        $fields = [
            'app_id' => $setting['notificationonesignal_app_id'],
            'included_segments' => [$_GET['segment']],
            // 'include_player_ids' => array("f773099b-cdc3-46fb-b27a-"),
            'data' => ["p" => $id,],
            'ios_attachments' => ['id' => $ImageUrl,],
            'ios_badgeType' => 'Increase',
            'ios_badgeCount' => '1',
            'chrome_web_image' => $ImageUrl,
            "ios_sound" => $sound,
            'contents' => $content,
        ];

        if ($_GET['active_url'] == "true") {
            $fields['url'] = $productUrl;
        }

        $fields = json_encode($fields);
        //        print("\nJSON sent:\n");
        //        print($fields);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json; charset=utf-8',
                "Authorization: Basic ".$setting['notificationonesignal_api_key'],
            ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    protected function validate()
    {

        if (! $this->user->hasPermission('modify', 'extension/module/notificationonesignal')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (! $this->request->post['notificationonesignal_app_id']) {
            $this->error['notificationonesignal_app_id'] = $this->language->get('error_no_key_api_key');
        }

        if (! $this->request->post['notificationonesignal_api_key']) {
            $this->error['notificationonesignal_api_key'] = $this->language->get('error_no_key_app_id');
        }

        return ! $this->error;
    }
}
