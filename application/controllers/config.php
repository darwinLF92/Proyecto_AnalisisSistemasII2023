<?php
require_once ("secure_area.php");
class Config extends Secure_area 
{
	function __construct()
	{
		parent::__construct('config');
	}
	
	function index()
	{	
		$data['controller_name']=strtolower(get_class());
		$this->load->view("config", $data);
	}
		
	function save()
	{
		if(!empty($_FILES["company_logo"]) && $_FILES["company_logo"]["error"] == UPLOAD_ERR_OK && ($_SERVER['HTTP_HOST'] !='demo.phppointofsale.com' && $_SERVER['HTTP_HOST'] !='demo.phppointofsalestaging.com'))
		{
			$allowed_extensions = array('png', 'jpg', 'jpeg', 'gif');
			$extension = strtolower(end(explode('.', $_FILES["company_logo"]["name"])));
			
			if (in_array($extension, $allowed_extensions))
			{
				$config['image_library'] = 'gd2';
				$config['source_image']	= $_FILES["company_logo"]["tmp_name"];
				$config['create_thumb'] = FALSE;
				$config['maintain_ratio'] = TRUE;
				$config['width']	 = 300;
				$config['height']	= 86;
				$this->load->library('image_lib', $config); 
				$this->image_lib->resize();
				$company_logo = $this->Appfile->save($_FILES["company_logo"]["name"], file_get_contents($_FILES["company_logo"]["tmp_name"]), $this->config->item('company_logo'));
			}
		}
		elseif($this->input->post('delete_logo'))
		{
			$this->Appfile->delete($this->config->item('company_logo'));
		}
		
		$this->load->helper('directory');
		$valid_languages = directory_map(APPPATH.'language/', 1);
		$batch_save_data=array(
		'company'=>$this->input->post('company'),
		'owners_name'=>$this->input->post('owners_name'),
		'nit'=>$this->input->post('nit'),
		'address'=>$this->input->post('address'),
		'phone'=>$this->input->post('phone'),
		'sat_resolution_number_and_date'=>$this->input->post('sat_resolution_number_and_date'),
		'facturation_range'=>$this->input->post('facturation_range'),
		'serial_facturation'=>$this->input->post('serial_facturation'),
		'email'=>$this->input->post('email'),
		'fax'=>$this->input->post('fax'),
		'website'=>$this->input->post('website'),
		'default_tax_1_rate'=>$this->input->post('default_tax_1_rate'),		
		'default_tax_1_name'=>$this->input->post('default_tax_1_name'),		
		'default_tax_2_rate'=>$this->input->post('default_tax_2_rate'),	
		'default_tax_2_name'=>$this->input->post('default_tax_2_name'),
		'default_tax_2_cumulative' => $this->input->post('default_tax_2_cumulative') ? 1 : 0,		
		'currency_symbol'=>$this->input->post('currency_symbol'),
		'return_policy'=>$this->input->post('return_policy'),
		'language'=>in_array($this->input->post('language'), $valid_languages) ? $this->input->post('language') : 'english',
		'timezone'=>$this->input->post('timezone'),
		'date_format'=>$this->input->post('date_format'),
		'time_format'=>$this->input->post('time_format'),
		'print_after_sale'=>$this->input->post('print_after_sale') ? 1 : 0,
		'track_cash' => $this->input->post('track_cash') ? 1 : 0,
		'mailchimp_api_key'=>$this->input->post('mailchimp_api_key'),
		'number_of_items_per_page'=>$this->input->post('number_of_items_per_page'),
		'additional_payment_types' => $this->input->post('additional_payment_types'),
		'hide_suspended_sales_in_reports' => $this->input->post('hide_suspended_sales_in_reports') ? 1 : 0,
		'speed_up_search_queries' => $this->input->post('speed_up_search_queries') ? 1 : 0,
		);

		if (isset($company_logo))
		{
			$batch_save_data['company_logo'] = $company_logo;
		}
		elseif($this->input->post('delete_logo'))
		{
			$batch_save_data['company_logo'] = 0;
		}
		
		if(($_SERVER['HTTP_HOST'] !='demo.phppointofsale.com' && $_SERVER['HTTP_HOST'] !='demo.phppointofsalestaging.com') && $this->Appconfig->batch_save($batch_save_data))
		{
			echo json_encode(array('success'=>true,'message'=>lang('config_saved_successfully')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>lang('config_saved_unsuccessfully')));
		}
	}
	
	function backup()
	{
		$this->load->dbutil();
		$prefs = array(
			'format'      => 'txt',             // gzip, zip, txt
			'add_drop'    => FALSE,              // Whether to add DROP TABLE statements to backup file
			'add_insert'  => TRUE,              // Whether to add INSERT data to backup file
			'newline'     => "\n"               // Newline character used in backup file
    	);
		$backup =&$this->dbutil->backup($prefs);
		$backup = 'SET FOREIGN_KEY_CHECKS = 0;'."\n".$backup."\n".'SET FOREIGN_KEY_CHECKS = 1;';
		force_download('BENASA.sql', $backup);
	}
	
	function optimize()
	{
		$this->load->dbutil();
		$this->dbutil->optimize_database();
		echo json_encode(array('success'=>true,'message'=>lang('config_database_optimize_successfully')));
	}
}
?>