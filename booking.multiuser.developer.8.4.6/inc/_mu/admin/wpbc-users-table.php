<?php
/**
 * @version 1.0
 * @package Caching Booking Resources
 * @category Cache
 * @author wpdevelop
 *
 * @web-site https://wpbookingcalendar.com/
 * @email info@wpbookingcalendar.com 
 * 
 * @modified 2016-08-09
 */

if ( ! defined( 'ABSPATH' ) ) exit;                                             // Exit if accessed directly


/** Booking Resources  Table for Settings page */
class WPBC_Users_Table extends WPBC_Settings_Table {
    
    private $data;
    
    public function __construct( $id, $args = array() ) {
        
        parent::__construct( $id, $args );
        
        add_action('wpbc_before_showing_settings_table', array( $this, 'wpbc_toolbar_search_by_id_booking_users_pseudo' ) );        
        add_bk_action('wpbc_reinit_booking_users_data', array($this, 'wpbc_reinit_booking_users_data' ));
    }
    
    
    /** Need to  reinit booking users cache,  because of saving new data to DataBase, or for any  other reason */
    public function wpbc_reinit_booking_users_data() {
        
        // Sorted All resources from DB
        $this->load_data();
        
        return true;
    }
    
    
    // Data  ///////////////////////////////////////////////////////////////////    
    
    /**
	 * Load Data for showing in Table
     * 
     * @return array
     */
    public function load_data(){
                
        $wpbc_users_cache = wpbc_users_cache();
        //$wpbc_users_cache->load_data_from_db();
            
        $this->data = $wpbc_users_cache->get_data();
        
        return $this->data;
    }
    
    
    /**
	 * Get sorted part of booking users array for ONE Page
     * 
     * @return array
     */
    public function get_linear_data_for_one_page() {
                
        // Get sorted part of booking users array based on $_GET paramters, like: &orderby=id&order=asc&page_num=2
        $pagination = $this->get_pagination_params();
        
        // We need to  skip "child booking resources" for calculation number of items per page
        $users = array_slice( $this->data, $pagination['start'], $pagination['items_per_page'] );
                
        return $users;
    }

    
    // Sorting /////////////////////////////////////////////////////////////////
    
    /**
	 * Get Actual sorting parameter
     *  based on version and $_GET['orderby'] & $_GET['order'] params
     * 
     * @return array( 'orderby' => 'id', 'order' => 'desc' )     ||     array('orderby' => 'title', 'order' => 'asc' ) .... 
     */    
    public function get_sorting_params() {
        
        $wpbc_users_cache = wpbc_users_cache();
        return $wpbc_users_cache->get_sorting_params();
    }
    
    
    // Pagination //////////////////////////////////////////////////////////////    
    
    /**
	 * Get ONLY the paramters that  possible to  use in pagination buttons
     * 
     * @return array( 'page', 'tab', 'wh_user_id' );
     */
    public function gate_paramters_for_pagination(){
        return array( 'page', 'tab', 'wh_user_id' );
    }
    
    
            /**
	 * Get current page
             * 
             * @return array
             */
            private function get_pagination_params() {

                $params = array( 
                            'selected_page_num' => ( isset( $_REQUEST['page_num'] ) ) ? intval( $_REQUEST['page_num'] ) : 1
                          , 'items_per_page' => intval( get_bk_option( 'booking_resourses_num_per_page' ) )
                        );

                // Start index of item for this page
                $params['start'] = ( $params['selected_page_num'] - 1 ) * $params['items_per_page'];

                // End index of item for this page
                $params['end'] = ( $params['selected_page_num'] ) * $params['items_per_page'] -1 ;

                return $params;
            }
    
        
    // Footer  /////////////////////////////////////////////////////////////////    
            
    /** Show Footer Row */
    public function show_footer(){
        
        // Footer
        ?><th colspan="<?php echo count( $this->get_columns() ); ?>" style="text-align: center;"><?php 

            // Pagination 


            $pagination_param = $this->get_pagination_params();

            $summ_number_of_items   = count( $this->data );
            $active_page_num        = $pagination_param['selected_page_num']; 
            $num_items_per_page     = $pagination_param['items_per_page'];
            $only_these_parameters  = array( 'page', 'tab', 'wh_user_id', 'orderby', 'order');

            wpbc_show_pagination(  $summ_number_of_items, $active_page_num, $num_items_per_page , $only_these_parameters, $this->url_sufix );

        ?></th><?php
        
    }
        
    
    // Support /////////////////////////////////////////////////////////////////    
    
    /**
	 * Pseudo search form above resource table and submit real form at  top of page
     * Its means that  we need to  show real  form:
        wpbc_toolbar_search_by_id__top_form( array( 
                                                    'search_form_id' => 'wpbc_booking_users_search_form'
                                                  , 'search_get_key' => 'wh_user_id'
                                                  , 'is_pseudo'      => false
                                            ) );
     * at  the top  of page - 
     * BEFORE SHOWING FORM  OF SAVING BOOKING RESOURCES - FOR HAVING 2 SEPAEATE HTML FORMS
     */
    public function wpbc_toolbar_search_by_id_booking_users_pseudo( $id ) {

        
        if ( $this->id != $id ) return;

        if ( ! empty( $this->parameters['is_show_pseudo_search_form'] ) ) {

            wpbc_toolbar_search_by_id__top_form( array( 
                                                    'search_form_id' => 'wpbc_booking_users_search_form'
                                                  , 'search_get_key' => 'wh_user_id'
                                                  , 'is_pseudo'      => true
                                            ) );
        }        
               
    }
}



/** Booking Resources  Table for Settings page */
class WPBC_Users_Cache{
    
    static private $instance = NULL;
    static private $data;
    static private $is_data_loaded = false;
    private $sort_parameter = array();
    
    public function __construct() {

        
        
        add_bk_action( 'wpbc_reinit_users_cache', array( $this, 'load_data_from_db' ) );
        
    }
    
    
    public function get_data() {
        
        if ( ! self::$is_data_loaded )
            $this->load_data_from_db();
        
        return self::$data;
    }
    
    /**
	 * Get only activated or Super booking admin users.
     * @return array
     */
    public function get_activated_users_only() {
        
        $all_users = $this->get_data();
        
        $default_super_admin_id_arr = apply_bk_filter( 'get_default_super_admin_id' );  // Get list  of Super booking admin users,  that  defined by  default - e.g. uer  with  ID = 1

        $active_users = array();
        foreach ( $all_users as $user_id => $user ) {
            
            $is_booking_active_for_user = $user['active'];
            $is_user_super_admin        = ( ( $user['super'] == 'super_admin' ) ? true : false );
            if ( in_array( $user['id' ], $default_super_admin_id_arr ) )  $is_user_super_admin = true;                                    // User ID inside of Super Admin ID                

            if ( ( $is_booking_active_for_user == 'On') || ( $is_user_super_admin ) ) { 
                $active_users[ $user_id ] = $user;
            }
            
        }
        return $active_users;
    }
    
    
    public function load_data_from_db() {
        
        // Sorted All resources from DB
        $data_from_db = $this->get_data_from_db();

        if ( $data_from_db !== false ) {
            
            self::$data = $data_from_db;
            
            self::$is_data_loaded = true;
            
            return true;
        } else {
            return false;
        }        

    }
    
    
    /** Get Single Instance of this Class and Init Plugin */
    public static function init() {

    
        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WPBC_Users_Cache ) ) {
            
            self::$instance = new WPBC_Users_Cache;
        }
        
        return self::$instance;
        
    }
    
    
    /**
	 * Get All booking resources from  DB
     * 
     * @global obj $wpdb
     * @return array of booking resources or false
     */
    private function get_data_from_db () {
                
        global $wpdb;
        
        $wpbc_sql = "SELECT u.ID as id"
                            . ", u.user_login"
                            . ", u.user_nicename"
                            . ", u.user_email"
                            . ", u.display_name"
                                . ", m.meta_value as role "
                                . ", a.meta_value as active "
                                . ", r.meta_value as max_res "
                                . ", s.meta_value as super "
                         . "FROM {$wpdb->prefix}users as u "
                         . "LEFT JOIN {$wpdb->prefix}usermeta as m ON u.ID = m.user_id AND m.meta_key='{$wpdb->prefix}capabilities'  " 
                         . "LEFT JOIN {$wpdb->prefix}usermeta as a ON u.ID = a.user_id AND a.meta_key='{$wpdb->prefix}booking_is_active'  " 
                         . "LEFT JOIN {$wpdb->prefix}usermeta as r ON u.ID = r.user_id AND r.meta_key='{$wpdb->prefix}booking_max_num_of_resources'  " 
                         . "LEFT JOIN {$wpdb->prefix}usermeta as s ON u.ID = s.user_id AND s.meta_key='{$wpdb->prefix}booking_user_role'  " ;
                         
        // Where ///////////////////////////////////////////////////////////////
        $where = '';                 

        // if Searching ...
        if ( isset( $_REQUEST['wh_user_id'] ) ) {
          
            //Escape digit or CSD
            $esc_sql_where_id = wpbc_clean_digit_or_csd( $_REQUEST['wh_user_id'] );
            
            // Escape SQL string
            $esc_sql_where_title = wpbc_clean_like_string_for_db( $_REQUEST['wh_user_id'] );
            
            
            $where_search_resource_id = '';
            
            if ( ! empty( $esc_sql_where_id ) )
                $where_search_resource_id .=    " ( u.ID IN (" . $esc_sql_where_id . ") ) ";

            if ( ! empty( $esc_sql_where_title ) ) {
                
                if ( ! empty( $where_search_resource_id ) ){ 
                    $where_search_resource_id .= " OR ";
                }                
                    $where_search_resource_id .= " ( u.display_name LIKE '%" . $esc_sql_where_title . "%' ) ";
            }
            
            
            if ( ! empty( $where_search_resource_id ) ) {
                
                if ( $where == '' )
                    $where .= ' WHERE ';
                else
                    $where .= ' AND ';

                $where .= " ( " . $where_search_resource_id . " ) ";
            }
        }
        
        $wpbc_sql .= $where;
        
              
        
        // Order ///////////////////////////////////////////////////////////////
        $order = $this->get_sorting_params();

        // if ( $order['orderby'] == 'id' ) $order['orderby'] = 'u.ID';            // Exception    

        $sql_order = ' ORDER BY ' . ( isset( $order['orderby_sql'] ) ? $order['orderby_sql'] : $order['orderby'] );
        
        if ( strtolower( $order['order'] ) == 'asc' )   $sql_order .= ' ASC';
        else                                            $sql_order .= ' DESC';

        $wpbc_sql .= $sql_order;
        ////////////////////////////////////////////////////////////////////
        
        /**
	 * Pagination  inside of function get_linear_data_for_one_page()
        $page_num = ( isset( $_REQUEST['page_num'] ) ) ? $_REQUEST['page_num'] : 1;
        $page_items_count = get_bk_option( 'booking_resourses_num_per_page' );
        $page_start = ( $page_num - 1 ) * $page_items_count;
        $sql_limit .= $wpdb->prepare( " LIMIT %d, %d ", $page_start, $page_items_count );                        // Page s
        */
        
        $res = $wpdb->get_results( $wpbc_sql );


        $data = array();
        foreach ( $res as $us ) {

            $single_item = get_object_vars( $us );
            
            $data[ $single_item['id'] ] = $single_item;
        }
         
        
        if (   ( ! empty($data) ) &&  ( ! defined( 'WPBC_USERS_CACHE' ) )    )
            define( 'WPBC_USERS_CACHE', true );

        
        return $data;        
    }
    
    
    // Sorting /////////////////////////////////////////////////////////////////
    /**
	 * Set  sorting parameters.
     * 
     * @param type $orderby
     * @param type $order
     * 
     * Exmaple: $this->set_sorting_params( 'id', 'ASC')
     */
    public function set_sorting_params( $orderby = 'id', $order = 'ASC') {
     
        if (empty($orderby) && empty($order) )
            $this->sort_parameter = array();
        else
            $this->sort_parameter = array( 
                                'orderby' => $orderby
                              , 'order' => $order
                            );
        
    }
    
    /**
	 * Get Actual sorting parameter
     *  based on version and $_GET['orderby'] & $_GET['order'] params
     * 
     * @return array( 'orderby' => 'id', 'order' => 'desc' )     ||     array('orderby' => 'title', 'order' => 'asc' ) .... 
     */    
    public function get_sorting_params() {
        
        if ( ! empty( $this->sort_parameter ) )
            return $this->sort_parameter;
        
        // Otherwise get sorting parameters based on GET
        
        //Default Params
        $sort_parameter = array( 
                                'orderby' => 'id'
                              , 'order' => 'asc' 
                            );
        
        // Requested params
        if ( isset( $_GET['orderby'] ) ) {
            switch ( strtolower( $_GET['orderby'] ) ) {
                
                case 'id':
                        $sort_parameter['orderby'] = 'id';
                        $sort_parameter['orderby_sql'] = 'id';
                        break;
                case 'title':
                        $sort_parameter['orderby'] = 'title';
                        $sort_parameter['orderby_sql'] = 'display_name';
                        break;
                case 'role':
                        $sort_parameter['orderby'] = 'role';
                        $sort_parameter['orderby_sql'] = 'role';
                        break;                    
                case 'status':
                        $sort_parameter['orderby'] = 'status';
                        $sort_parameter['orderby_sql'] = 'active';
                        break;                    
                default:
                        break;
            }
        }

        if ( isset( $_GET['order'] ) ) {
            switch ( strtolower( $_GET['order'] ) ) {
                
                case 'asc':
                        $sort_parameter['order'] = 'asc';
                        break;
                case 'desc':
                        $sort_parameter['order'] = 'desc';
                        break;
                default:
                        break;
            }
        }
        
        return $sort_parameter;
    }
    
}


/**
	 * Get One True instance of WPBC Cache class
 *
 * Example: <?php $wpbc_br_cache = wpbc_br_cache(); ?>
 */
function wpbc_users_cache() {

//debuge('Resources Cache Started'); debuge_speed();

    return WPBC_Users_Cache::init();
}

//// Start
//$wpbc_users_cache = wpbc_users_cache();
//debuge( $wpbc_users_cache->get_data() ); 