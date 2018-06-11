<?php
class ControllerExtensionModuleNotificationOnesignal extends Controller {
	private $error = array();

	public function index() {


//        echo ($this->request->get['route']);

		$this->load->language('extension/module/notificationonesignal');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('notificationonesignal', $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}




		if (isset($this->error['notificationonesignal_app_id'])) {
			$data['error_no_key_app_id'] = $this->error['notificationonesignal_app_id'];
		} else {
			$data['error_no_key_app_id'] = '';
		}




		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
			);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
			);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/notificationonesignal', 'user_token=' . $this->session->data['user_token'], true)
			);

		$data['action'] = $this->url->link('extension/module/notificationonesignal', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);



        $this->load->model('setting/setting');
        $setting = $this->model_setting_setting->getSetting('notificationonesignal');








		//الكي الخاص باالتطبيق
		if(isset($this->request->post['notificationonesignal_app_id'])) {
			$data['notificationonesignal_app_id'] = $this->request->post['notificationonesignal_app_id'];
		} elseif ($this->config->get('notificationonesignal_app_id')){
			$data['notificationonesignal_app_id'] = $this->config->get('notificationonesignal_app_id');
		} else{
			$data['notificationonesignal_app_id'] = '';
		}





			//الكي الخاص باالتطبيق
		if(isset($this->request->post['notificationonesignal_api_key'])) {
			$data['notificationonesignal_api_key'] = $this->request->post['notificationonesignal_api_key'];
		} elseif ($this->config->get('notificationonesignal_api_key')){
			$data['notificationonesignal_api_key'] = $this->config->get('notificationonesignal_api_key');
		} else{
			$data['notificationonesignal_api_key'] = '';
		}



			//الكي الخاص باالتطبيق
		if(isset($this->request->post['notificationonesignal_status'])) {
			$data['notificationonesignal_status'] = $this->request->post['notificationonesignal_status'];
		} elseif ($this->config->get('notificationonesignal_status')){
			$data['notificationonesignal_status'] = $this->config->get('notificationonesignal_status');
		} else{
			$data['notificationonesignal_status'] = '';
		}



		$data['header'] =      $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] =      $this->load->controller('common/footer');


		$this->response->setOutput($this->load->view('extension/module/notificationonesignal', $data));
	}



    public function uninstall() {
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
    public function send(){



        $id = $_GET['id'];
        $msg = $_GET['msg'];
        $response = $this->sendMessage($id,$msg);
        $return["allresponses"] = $response;
        $return = json_encode($return);
        $data = json_decode($response, true);

        echo $response;

    }

 public function getProduct(){

     global $loader, $registry;
     $id = $_GET['id'];
     $loader->model('catalog/product');
     $model = $registry->get('model_catalog_product');
     $result = $model->getProduct($id);


     $nameProduct = $result["name"];
     $nameImage = 'https://'.$_SERVER['HTTP_HOST'].'/image/'.$result["image"];
     echo json_encode(array('text'=>$nameProduct,'imgUrl'=>$nameImage,'ID'=>$id));


    }



    function sendMessage() {


        $this->load->model('setting/setting');
        $setting = $this->model_setting_setting->getSetting('notificationonesignal');




        $nameProduct = $_GET["name"];
        $ImageUrl = $_GET["imgUrl"];
        $id = $_GET["id"];


        $content      = array(
            "en" => $nameProduct,
            "ar" => $nameProduct
        );






        $fields = array(
            'app_id' => $setting['notificationonesignal_app_id'],
            'included_segments' => array(
                'Active Users'
            ),
//            'include_player_ids' => array("c432e10e-b414-43ea-b51f-"),

            'data' => array(
                "p" => $id
            ),
            'ios_attachments' => array(
                'id' => $ImageUrl,
            ),
            'contents' => $content
        );

        $fields = json_encode($fields);
//        print("\nJSON sent:\n");
//        print($fields);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            "Authorization: Basic ".$setting['notificationonesignal_app_id']
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }




    protected function validate() {

		if (!$this->user->hasPermission('modify', 'extension/module/notificationonesignal')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}


		if (!$this->request->post['notificationonesignal_app_id']) {
			$this->error['notificationonesignal_app_id'] = $this->language->get('error_no_key_api_key');
		}

		if (!$this->request->post['notificationonesignal_api_key']) {
			$this->error['notificationonesignal_api_key'] = $this->language->get('error_no_key_app_id');
		}



		return !$this->error;
	}
}