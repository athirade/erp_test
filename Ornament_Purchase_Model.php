<?php
class Ornament_Purchase_Model extends CI_Model {

            public $title;
        public $content;
        public $date;
        
        public function __construct(){ 
            $this->load->database();
        } 
        public function get_result_array($table,$field='',$id=''){
            $this->db->select('*')
                    ->from($table);
            if($id==''){
                $query=$this->db->get();
                return $query->result_array();			
            }
            if($id!=''){
                $this->db->where($field,$id);
                $query=$this->db->get();
                return $query->row_array();			
            }
        }
        public function ornament_purchase_insert($data)
        { 
         $this->db->insert('tbl_ornament_purchase',$data); 
         $insert_id = $this->db->insert_id();
         return  $insert_id; 
        }
        public function ornament_purchase_details_list($branch_id) 
        {
            $this->db->select('
            tbl_ornament_purchase_details.ornament_purchase_details_id,tbl_style.style_id,
            tbl_style.variant_name as style_name,tbl_ornament_purchase_details.hm_quantity,
            tbl_ornament_purchase_details.hm_gross_weight,tbl_ornament_purchase_details.stone_weight,
            tbl_ornament_purchase_details.stone_qty,tbl_ornament_purchase_details.hm_net_wt,
            tbl_karat.karat,tbl_ornament_purchase_details.hm_purity_id,tbl_ornament_purchase_details.purchase_mc,
            tbl_ornament_purchase_details.state_id,tbl_ornament_purchase_details.gold_rate,tbl_ornament_purchase_details.cust_id,
            tbl_states.state_name')
            ->from('tbl_ornament_purchase_details');
            $this->db->join('tbl_karat', 'tbl_karat.karat_id = tbl_ornament_purchase_details.hm_purity_id', 'left');
            $this->db->join('tbl_style', 'tbl_style.style_id = tbl_ornament_purchase_details.hm_style_code', 'left');
            $this->db->join('tbl_states', 'tbl_states.id = tbl_ornament_purchase_details.state_id', 'left');
            $this->db->where('tbl_ornament_purchase_details.branch_id',$branch_id);
            $this->db->where('tbl_ornament_purchase_details.status',0);
            $query=$this->db->get();  
            return $query->result_array(); 
        }
        public function select_from_stock_invoice()
        {   
            $query = $this->db->query("SELECT MAX(CAST(SUBSTRING_INDEX(stock_invoice_no, '/', -1) AS UNSIGNED)) AS max_number 
            FROM tbl_ornament_purchase");

            $row = $query->row();

            $branch_id = $this->session->userdata("branch_id");
            $currentDay = date('d');  
            $currentMonth = date('m'); 
            $currentYear = date('y'); 

            if ($row && $row->max_number !== null) {
            $lastNumber = (int)$row->max_number;  
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT); 
            } else {
            $newNumber = '0001';
            }
            $invoice_code = 'MJOP/' . $branch_id . $currentDay . $currentMonth . $currentYear . '/' . $newNumber;
            return $invoice_code;  
        }
        public function get_tbl_ornament_purchase($ornament_purchase_id)
        {

            $this->db->select('tbl_ornament_purchase.*,tbl_customer.cust_id,
            tbl_customer.cust_name,tbl_customer.debitors_id,tbl_customer.creditors_id,
            tbl_customer.customer_staus,tbl_customer.macom_cust_id,tbl_customer.vendor_purchase_mu,
            tbl_customer_details.*,to_state.*,tbl_branch.macom_branch_id,
            from_state.macom_state_id as from_macom_state_id')
            ->from('tbl_ornament_purchase');
            $this->db->join('tbl_customer', 'tbl_customer.cust_id  = tbl_ornament_purchase.vendor_id', 'left');
            $this->db->join('tbl_customer_details', 'tbl_customer_details.customer_id  = tbl_customer.cust_id', 'left');
            $this->db->join('tbl_branch', 'tbl_branch.branch_id=tbl_ornament_purchase.branch_id', 'left');
            $this->db->join('tbl_states as to_state', 'tbl_branch.state_id = to_state.id', 'left');
            $this->db->join('tbl_states as from_state', 'tbl_customer_details.cust_state = from_state.id', 'left');
            $this->db->where('tbl_ornament_purchase.ornament_purchase_id',$ornament_purchase_id);
            $query=$this->db->get();// print_r($this->db);
            return $query->result_array();
         
        }
        public function ornament_purchase_list($ornament_purchase_id,$branch_id)
        {
            $this->db->select('*')
            ->from('tbl_ornament_purchase'); 
            $this->db->where('tbl_ornament_purchase.ornament_purchase_id',$ornament_purchase_id);
            $this->db->where('tbl_ornament_purchase.branch_id',$branch_id);
            $query=$this->db->get();
            return $query->result_array();
        }
        
        public function ornament_purchase_details_insert($data)
        { 
            $this->db->insert('tbl_ornament_purchase_details',$data);  
            $insert_id = $this->db->insert_id();
            return  $insert_id;
        }
        
        public function tbl_style_where_department($department_type)
        {
            $this->db->select('*')
            ->from('tbl_style');
            $this->db->where('tbl_style.production_type_id',$department_type); 
            $query=$this->db->get();
            return $query->result_array();
        }
        
        public function get_ornament_stock($branch_id)
        {
            $this->db->select('*')
            ->from('tbl_ornament_purchase');
            $this->db->join('tbl_customer_details', 'tbl_customer_details.customer_id = tbl_ornament_purchase.vendor_id', 'left');
            $this->db->join('tbl_customer', 'tbl_customer.cust_id = tbl_customer_details.customer_id', 'left');
            // $this->db->join('tbl_vendor', 'tbl_vendor.vendor_id  = tbl_ornament_purchase.vendor_id', 'left');
            $this->db->join('tbl_branch', 'tbl_branch.branch_id  = tbl_ornament_purchase.branch_id', 'left');
            $this->db->where('tbl_ornament_purchase.status',0);
            $this->db->where('tbl_ornament_purchase.branch_id',$branch_id);
            $this->db->order_by('tbl_ornament_purchase.ornament_purchase_id', 'DESC');
            $query=$this->db->get(); //print_r($this->db);exit;
            return $query->result_array();
        }

        public function calculate_purchase_weight($ornament_purchase_id)
        {
            $this->db->select('sum(hm_net_wt) as total_gross_weight')
            ->from('tbl_ornament_purchase_details'); 
            $this->db->where('tbl_ornament_purchase_details.ornament_purchase_id',$ornament_purchase_id); 
            $query=$this->db->get(); //print_r($this->db);exit;
            return $query->result_array(); 
        }
        public function get_style_stone_details_modal($style_id_val,$ornament_purchase_details_id)
        {  
             $this->db->select('*')
            ->from('tbl_ornament_stone_details');
            $this->db->join('tbl_stone', 'tbl_stone.stone_id = tbl_ornament_stone_details.hm_style_code', 'left'); 
            $this->db->where('tbl_ornament_stone_details.hm_style_code',$style_id_val);
            $this->db->where('tbl_ornament_stone_details.ornament_purchase_details_id',$ornament_purchase_details_id);    
            $query=$this->db->get();  //print_r($this->db);
            return $query->result_array();    
        }
        public function get_style_stone_details($style_id)
        {
            $this->db->select('tbl_style_stone.`style_id`,tbl_style_stone.`quantity_id`,tbl_style_stone.`stone_total_wt`,
            tbl_stone.stone_id,tbl_stone.variant_name,tbl_style_stone.stone_setting_type_id')
            ->from('tbl_style_stone');
            $this->db->join('tbl_stone', 'tbl_stone.stone_id = tbl_style_stone.stone_id', 'left'); 
            $this->db->where('tbl_style_stone.style_id',$style_id);  
            $query=$this->db->get(); // print_r($this->db);exit;
            return $query->result_array();   
        }
        public function hm_style_stone_insert($data)
            { 
                $this->db->insert('tbl_ornament_stone_details',$data); //print_r($this->db);exit;
                $insert_id = $this->db->insert_id(); 
                return  $insert_id;
            }
            public function update_style_stone($userData,$hm_new_ornament_id)
                {  
                    $this->db->where('hm_new_ornament_id',$hm_new_ornament_id) 
                    ->update('tbl_ornament_stone_details',$userData);  //print_r($this->db);exit;
                    return $this->db->affected_rows();
                }
            public function get_style_data($style_id_val,$ornament_purchase_details_id)
            {
                $this->db->select('*'); 
                $this->db->from('tbl_ornament_purchase_details');
                $this->db->where('tbl_ornament_purchase_details.hm_style_code',$style_id_val);
                $this->db->where('tbl_ornament_purchase_details.ornament_purchase_details_id',$ornament_purchase_details_id);
                $query=$this->db->get(); //print_r($this->db);exit;
                return $query->result_array(); 
            } 
            public function update_style($data,$style_id_val,$ornament_purchase_details_id)
            {
            
                $this->db->where('ornament_purchase_details_id',$ornament_purchase_details_id);
                $this->db->where('hm_style_code',$style_id_val)
                ->update('tbl_ornament_purchase_details',$data);  //print_r($this->db);exit;
                return $this->db->affected_rows();
            
            }
            public function update_vendor_barcode($data,$style_id_val,$ornament_purchase_details_id)
            {
            
                $this->db->where('ornament_purchase_details_id',$ornament_purchase_details_id);
                $this->db->where('style_id',$style_id_val)
                ->update('tbl_hm_barcoding_vendor',$data);  //print_r($this->db);exit;
                return $this->db->affected_rows();
            
            }
            public function get_item_details($style_list_id,$department_type)
                {
         
                 $data =  $this->db->query("SELECT
                     tbl_style.style_id,
                     tbl_style.variant_name,
                     tbl_style.net_wt,
                     tbl_style.gross_weight,
                     SUM(tbl_style_stone.stone_total_wt) AS total_stone_weight,
                     SUM(tbl_style_stone.quantity_id) AS total_quantity,
                     GROUP_CONCAT(tbl_stone.variant_name ORDER BY tbl_stone.variant_name SEPARATOR ', ') AS stone_list,
                     GROUP_CONCAT(tbl_style_stone.stone_id ORDER BY tbl_style_stone.stone_id SEPARATOR ', ') AS stone_id
                 FROM
                     tbl_style
                 LEFT JOIN
                     tbl_style_stone ON tbl_style_stone.style_id = tbl_style.style_id
                 LEFT JOIN
                     tbl_stone ON tbl_stone.stone_id = tbl_style_stone.stone_id
                 WHERE
                     tbl_style.variant_name = '$style_list_id'
                     
                 GROUP BY
                     tbl_style.style_id, tbl_style.variant_name, tbl_style.net_wt, tbl_style.gross_weight;");
                     return $data->result_array(); 
                    
                }
                public function getstyle_product_details($category_type_id)
                {
                     $this->db->select('*')
                     ->from('tbl_style');
                     $this->db->join('tbl_production_type', 'tbl_production_type.production_type_id=tbl_style.production_type_id', 'left');
                     $this->db->where('tbl_style.production_type_id',$category_type_id);
                     $query=$this->db->get();   
                     return $query->result_array();
                }
                public function get_purity_id($purity_name)
                {
                    $this->db->select('*'); 
                    $this->db->from('tbl_karat');
                    $this->db->where('karat',$purity_name);  
                    $query=$this->db->get();  
                    return $query->result_array(); 
                } 
                public function update_ornament_status($userData,$ornament_purchase_details_id)
                {
                
                    $this->db->where('ornament_purchase_details_id',$ornament_purchase_details_id)
                    ->update('tbl_ornament_purchase_details',$userData);  
                    return $this->db->affected_rows();
                
                }
   
                public function insert_to_barcoding($table,$data) 
                {
                    $this->db->insert($table,$data);  //print_r($this->db);exit;
                }
                public function ornament_purchase_history($branch_id)
                {
                    $this->db->select('*')
                    ->from('tbl_ornament_purchase');
                    $this->db->join('tbl_customer_details', 'tbl_customer_details.customer_id = tbl_ornament_purchase.vendor_id', 'left');
                    $this->db->join('tbl_customer', 'tbl_customer.cust_id = tbl_customer_details.customer_id', 'left');
                    $this->db->join('tbl_branch', 'tbl_branch.branch_id  = tbl_ornament_purchase.branch_id', 'left');
                    $this->db->where('tbl_ornament_purchase.branch_id',$branch_id);  
                    $query=$this->db->get();
                    return $query->result_array();
                }
                public function update_ornament_new_stock_status($userData1,$ornament_purchase_details_id)
                {
                    $this->db->where('ornament_purchase_dtl_id',$ornament_purchase_details_id);
                    $this->db->where('stock_status',3);
                    $this->db->update('tbl_job_work_new_stock',$userData1);  
                    return $this->db->affected_rows();
                }
                public function get_customer_state($customer_id)
                {
                    $this->db->select('*'); 
                    $this->db->from('tbl_customer_details');
                    $this->db->join('tbl_states', 'tbl_states.id = tbl_customer_details.cust_state', 'left');
                    $this->db->where('tbl_customer_details.customer_id',$customer_id);  
                    $query=$this->db->get();  
                    return $query->result_array(); 
                } 
                public function get_customer_purchase_mc($vendor_id,$style_list_id)
                {
         
                 $data =  $this->db->query("SELECT 
                 cust_mc_subcat.purchase_mc
                 FROM 
                 tbl_customer_mc AS cust_mc_subcat
                 LEFT JOIN tbl_style 
                 ON FIND_IN_SET(tbl_style.product_subcategory_id, cust_mc_subcat.sub_category_id) > 0
                 AND FIND_IN_SET(tbl_style.design_master, cust_mc_subcat.design_id) > 0
                 AND FIND_IN_SET(tbl_style.product_category_id, cust_mc_subcat.category_id) > 0
                 WHERE 
                 cust_mc_subcat.cust_id = $vendor_id
                 AND
                 tbl_style.item_name='$style_list_id'");
                //   print_r($this->db);exit;
                 return $data->result_array(); 
                    
                }
                public function get_ornament_purchase_details($ornament_purchase_details_id)
                {
                    $this->db->select('*'); 
                    $this->db->from('tbl_ornament_purchase_details');
                    $this->db->where('ornament_purchase_details_id',$ornament_purchase_details_id);  
                    $query=$this->db->get();  
                    return $query->result_array(); 
                } 
                public function get_ornamnet_purchase_dtls($ornament_purchase_details_id)
                {
                    $this->db->select('tbl_states.state_name,tbl_customer.cust_name,tbl_ornament_purchase_details.gold_rate,
                     tbl_customer.cust_id,tbl_states.id as state_id'); 
                    $this->db->from('tbl_ornament_purchase_details');
                    $this->db->join('tbl_ornament_stone_details', 'tbl_ornament_stone_details.ornament_purchase_details_id = tbl_ornament_purchase_details.ornament_purchase_details_id', 'left');
                    $this->db->join('tbl_customer', 'tbl_customer.cust_id = tbl_ornament_purchase_details.cust_id', 'left');
                    $this->db->join('tbl_states', 'tbl_states.id = tbl_ornament_purchase_details.state_id', 'left');
                    $this->db->where('tbl_ornament_purchase_details.ornament_purchase_details_id',$ornament_purchase_details_id);  
                    $query=$this->db->get();  
                    return $query->result_array(); 
                } 
                public function get_ornament_vendor_wise_details($ornament_purchase_details_id)
                {
                    $this->db->select('or.hm_style_code,tbl_style.item_name,or.hm_purity_id,or.hm_gross_weight,
                    or.hm_quantity,or.stone_weight,or.stone_qty,or.hm_net_wt'); 
                    $this->db->from('tbl_ornament_purchase_details as or');
                    $this->db->join('tbl_style', 'tbl_style.style_id = or.hm_style_code', 'left');
                    $this->db->where('or.ornament_purchase_details_id',$ornament_purchase_details_id);  
                    $query=$this->db->get();  
                    return $query->result_array(); 
                } 
    }
   
   
 