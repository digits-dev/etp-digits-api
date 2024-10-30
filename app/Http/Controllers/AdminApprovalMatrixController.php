<?php namespace App\Http\Controllers;

	use Session;
	use Request;
	use DB;
	use CRUDBooster;

	class AdminApprovalMatrixController extends \crocodicstudio\crudbooster\controllers\CBController {

	    public function cbInit() {

			# START CONFIGURATION DO NOT REMOVE THIS LINE
			$this->title_field = "id";
			$this->limit = "20";
			$this->orderby = "channel_id,asc";
			$this->global_privilege = false;
			$this->button_table_action = true;
			$this->button_bulk_action = true;
			$this->button_action_style = "button_icon";
			$this->button_add = true;
			$this->button_edit = true;
			$this->button_delete = true;
			$this->button_detail = true;
			$this->button_show = true;
			$this->button_filter = true;
			$this->button_import = false;
			$this->button_export = true;
			$this->table = "approval_matrix";
			# END CONFIGURATION DO NOT REMOVE THIS LINE

			# START COLUMNS DO NOT REMOVE THIS LINE
			$this->col = [];
			$this->col[] = ["label"=>"Privilege Name","name"=>"cms_privileges_id","join"=>"cms_privileges,name"];
			$this->col[] = ["label"=>"Approver Name","name"=>"cms_users_id","join"=>"cms_users,name"];
			$this->col[] = ["label"=>"Channel","name"=>"channel_id","join"=>"channels,channel_description"];
			$this->col[] = ["label"=>"Store List","name"=>"store_list"];
			$this->col[] = ["label"=>"Viewable Channel","name"=>"channels_visibility","join"=>"channels,channel_description"];
			
			$this->col[] = ["label"=>"Status","name"=>"status"];
			$this->col[] = ["label"=>"Created Date","name"=>"created_at"];
			$this->col[] = ["label"=>"Updated Date","name"=>"updated_at"];
			# END COLUMNS DO NOT REMOVE THIS LINE

			# START FORM DO NOT REMOVE THIS LINE
			$this->form = [];
			$this->form[] = ['label'=>'Privilege Name','name'=>'id_cms_privileges','type'=>'select','validation'=>'required|integer|min:0','width'=>'col-sm-5','datatable'=>'cms_privileges,name','datatable_where'=>"id in (5,28)"];
			$this->form[] = ['label'=>'Approver Name','name'=>'cms_users_id','type'=>'select','validation'=>'required|integer|min:0','width'=>'col-sm-5','datatable'=>'cms_users,name','parent_select'=>'id_cms_privileges'];
			$this->form[] = ['label'=>'Channel','name'=>'channels_id','type'=>'select','validation'=>'required|integer|min:0','width'=>'col-sm-5','datatable'=>'channels,channel_description'];
			
			if(CRUDBooster::getCurrentMethod() == 'getEdit' || CRUDBooster::getCurrentMethod() == 'postEditSave' || CRUDBooster::getCurrentMethod() == 'getDetail'){
				$this->form[] = ['label'=>'Status','name'=>'status','type'=>'select','validation'=>'required','width'=>'col-sm-5','dataenum'=>'ACTIVE;INACTIVE'];
			}	
			$this->form[] = ['label'=>'Store List','name'=>'store_list','type'=>'select2-multi','validation'=>'required','width'=>'col-sm-5','datatable'=>'store_masters,bea_so_store_name','datatable_where'=>"status='ACTIVE'",'parent_select'=>'channels_id'];
			$this->form[] = ['label'=>'Viewable Channel Orders','name'=>'channels_visibility','type'=>'checkbox','width'=>'col-sm-5','dataenum'=>'1|RETAIL;2|FRANCHISE;3|DISTRIBUTION;4|ONLINE'];
			# END FORM DO NOT REMOVE THIS LINE

			
	        $this->addaction = array();

	        $this->index_button = array();
			$this->index_button[] = [
				"title"=>"Add Data",
				"label"=>"Add Data",
				"icon"=>"fa fa-plus-circle",
				"url"=>CRUDBooster::mainpath('add'),
				"color"=>"success"];

				$this->script_js = "
					$(document).ready(function() {
						$('#store_list').select2();
					});
					
				";
	    }


	   
	    public function hook_query_index(&$query) {
	        //Your code here
	            
	    }

	    /*
	    | ---------------------------------------------------------------------- 
	    | Hook for manipulate row of index table html 
	    | ---------------------------------------------------------------------- 
	    |
	    */    
	    public function hook_row_index($column_index,&$column_value) {	        
	    	//Your code here
	    }

	    /*
	    | ---------------------------------------------------------------------- 
	    | Hook for manipulate data input before add data is execute
	    | ---------------------------------------------------------------------- 
	    | @arr
	    |
	    */
	    public function hook_before_add(&$postdata) {        
	        //Your code here

	    }

	    /* 
	    | ---------------------------------------------------------------------- 
	    | Hook for execute command after add public static function called 
	    | ---------------------------------------------------------------------- 
	    | @id = last insert id
	    | 
	    */
	    public function hook_after_add($id) {        
	        //Your code here

	    }

	    /* 
	    | ---------------------------------------------------------------------- 
	    | Hook for manipulate data input before update data is execute
	    | ---------------------------------------------------------------------- 
	    | @postdata = input post data 
	    | @id       = current id 
	    | 
	    */
	    public function hook_before_edit(&$postdata,$id) {        
	        //Your code here

	    }

	    /* 
	    | ---------------------------------------------------------------------- 
	    | Hook for execute command after edit public static function called
	    | ----------------------------------------------------------------------     
	    | @id       = current id 
	    | 
	    */
	    public function hook_after_edit($id) {
	        //Your code here 

	    }

	    /* 
	    | ---------------------------------------------------------------------- 
	    | Hook for execute command before delete public static function called
	    | ----------------------------------------------------------------------     
	    | @id       = current id 
	    | 
	    */
	    public function hook_before_delete($id) {
	        //Your code here

	    }

	    /* 
	    | ---------------------------------------------------------------------- 
	    | Hook for execute command after delete public static function called
	    | ----------------------------------------------------------------------     
	    | @id       = current id 
	    | 
	    */
	    public function hook_after_delete($id) {
	        //Your code here

	    }

		// public function getAdd(){
		// 	dd('test');
		// }

	}