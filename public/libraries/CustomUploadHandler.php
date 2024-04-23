<?php 
error_reporting(E_ALL | E_STRICT);
require('UploadHandler.php');

class CustomUploadHandler extends UploadHandler {

    protected function handle_form_data($file, $index) {
    	$file->title = @$_REQUEST['title'][$index];
 		//$file->title = $this->generate_unique_filename($name);
    	$file->description = @$_REQUEST['description'][$index];
    } 
	protected function generate_unique_filename($filename = "") {
		$extension = "";
		if ( $filename != "" )
		{
			$extension = pathinfo($filename , PATHINFO_EXTENSION);
 			if ( $extension != "" )
			{
				$extension = "." . $extension;
			}
			$filename = md5(date('YmdHisu')) . $extension; 
		}
 		return $filename ;
	}
    protected function handle_file_upload($uploaded_file, $name, $size, $type, $error,
        $index = null, $content_range = null) {
        $file = parent::handle_file_upload(
        	$uploaded_file, $name, $size, $type, $error, $index, $content_range
        );
 		$this->CI =& get_instance();
		/*$fid   =  basename($this->options['upload_dir']);
        $fname = $file->name;
        $path = $this->options['upload_dir'].$fname;
        $ext = pathinfo($path, PATHINFO_EXTENSION); //echo $this->options['model'];*/
		$model = $this->options['model'];
		$this->CI->load->model($model);		 
		$data = $this->options['insertdata']; 
		$data['image'] =$file_path='uploads/images/'.$file->name; 
        //if (empty($file->error)) {
			$insid = $this->CI->$model->save_images($data,'insert');  
	        $file->id = $insid;  
			$weburl = str_replace('lpcrm/','',site_url()); 
		
        return $file;
    }

    protected function set_additional_file_properties($file) {
        parent::set_additional_file_properties($file);
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        	$sql = 'SELECT `id`, `type`, `title`, `description` FROM `'
        		.$this->options['db_table'].'` WHERE `name`=?';
        	$query = $this->db->prepare($sql);
 	        $query->bind_param('s', $file->name);
	        $query->execute();
	        // $query->bind_result(
	        // 	$id,
	        // 	$type,
	        // 	$title,
	        // 	$description
	        // );
	        // while ($query->fetch()) {
	        // 	$file->id = $id;
        	// 	$file->type = $type;
        	// 	$file->title = $title;
        	// 	$file->description = $description;
    		// }
        }
    }

    public function delete($print_response = true) {
        $response = parent::delete(false); print_r($response);
        foreach ($response as $name => $deleted) {
        	if ($deleted) {        	}
        } 
        return $this->generate_response($response, $print_response);
    }

}
?>