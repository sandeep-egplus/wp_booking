<?php
/**
 * @version 1.0
 * @package Booking > Resources > Filters page 
 * @category Seasons Table
 * @author wpdevelop
 *
 * @web-site https://wpbookingcalendar.com/
 * @email info@wpbookingcalendar.com 
 * 
 * @modified 2016-08-24
 */

if ( ! defined( 'ABSPATH' ) ) exit;                                             // Exit if accessed directly


/** Booking Resources  Table for Settings page */
class WPBC_DC_Table extends WPBC_Settings_Table {
    
    protected $search_get_key = 'wh_search_id';
    
    public function __construct( $id, $args = array() ) {
        
        parent::__construct( $id, $args );
        
        add_action('wpbc_before_showing_settings_table', array( $this, 'wpbc_toolbar_search_by_id_discountcoupons_pseudo' ) );        
    }
    
    
    ////////////////////////////////////////////////////////////////////////////
    // Data
    ////////////////////////////////////////////////////////////////////////////
    
    /**
	 * Load Data from DB for showing in Table and return array
     * 
     * @return array
     */
    public function load_data(){
        
        return wpbc_dc_cache();
    }
    
    
    /**
	 * Get sorted part of booking data array for ONE Page
     * 
     * @return array
     */
    public function get_linear_data_for_one_page() {
                
        // Get sorted part of booking data array based on $_GET paramters, like: &orderby=id&order=asc&page_num=2
        $data = $this->loaded_data->get_data_for_one_page();
        
        return $data;
    }

    
    /**
	 * Get Actual sorting parameter
     *  based on version and $_GET['orderby'] & $_GET['order'] params
     * 
     * @return array( 'orderby' => 'id', 'order' => 'desc' )     ||     array('orderby' => 'title', 'order' => 'asc' ) .... 
     */    
    public function get_sorting_params() {
        $active_sort = $this->loaded_data->get_sorting_params();                // array( 'orderby' => 'id', 'order' => 'asc');
        return $active_sort;
    }
    
    
    /**
	 * Get ONLY the paramters that  possible to  use in pagination buttons
     * 
     * @return array( 'page', 'tab', $this->search_get_key );
     */
    public function gate_paramters_for_pagination(){
        return array( 'page', 'tab', $this->search_get_key );
    }
    
    
    /** Show Footer Row */
    public function show_footer(){
        
        // Footer
        ?><th colspan="<?php echo count( $this->get_columns() ); ?>" style="text-align: center;"><?php 

            // Pagination 


            $pagination_param = $this->loaded_data->get_pagination_params();        // array( 'selected_page_num' => 2, 'items_per_page' => 10, 'start' => 10, 'end' => 19 );

            $summ_number_of_items   = count( $this->loaded_data->get_data() );
            $active_page_num        = $pagination_param['selected_page_num']; 
            $num_items_per_page     = $pagination_param['items_per_page'];
            $only_these_parameters  = array_merge( $this->gate_paramters_for_pagination(), array( 'orderby', 'order' ) );

            wpbc_show_pagination(  $summ_number_of_items, $active_page_num, $num_items_per_page , $only_these_parameters, $this->url_sufix );

        ?></th><?php
        
    }
    
    
    ////////////////////////////////////////////////////////////////////////////
    // Support
    ////////////////////////////////////////////////////////////////////////////
    
    
    /**
	 * Pseudo search form above resource table and submit real form at  top of page
     * Its means that  we need to  show real  form:
     * 
        wpbc_toolbar_search_by_id__top_form( array( 
                                                    'search_form_id' => 'wpbc_discountcoupons_search_form'
                                                  , 'search_get_key' => 'wh_search_id'
                                                  , 'is_pseudo'      => false
                                            ) );
     * 
     * at  the top  of page - 
     * BEFORE SHOWING FORM  OF SAVING BOOKING RESOURCES - FOR HAVING 2 SEPAEATE HTML FORMS
     */
    public function wpbc_toolbar_search_by_id_discountcoupons_pseudo( $id ) {

        if ( $this->id != $id ) return;

        if ( ! empty( $this->parameters['is_show_pseudo_search_form'] ) ) {
            
            wpbc_toolbar_search_by_id__top_form( array( 
                                                    'search_form_id' => 'wpbc_discountcoupons_search_form'
                                                  , 'search_get_key' => 'wh_search_id'
                                                  , 'is_pseudo'      => true
                                            ) );
        }
    }

}


////////////////////////////////////////////////////////////////////////////////
// SF Cache
////////////////////////////////////////////////////////////////////////////////

/** Cache Season Filters  */
class WPBC_DC_Cache {

    
    static public $data = array();         // Sorted one dimention linear array of booking data 
    
    static private $instance = NULL;        

    private $search_get_key = 'wh_search_id';
    
    public function __construct() {

        $is_exist_data = $this->wpbc_reload_cache();
        
        add_bk_action( 'wpbc_reinit_discountcoupons_cache', array( $this, 'wpbc_reload_cache' ) );        
    }
    

    /**
	 * Get Data from    D B    .
     * 
     * @global obj $wpdb
     * @return array of booking data or false
     */
    private function get_data_from_db () {

        global $wpdb;

        ////////////////////////////////////////////////////////////////////////
        // SELECT
        ////////////////////////////////////////////////////////////////////////
        $wpbc_sql = "SELECT * FROM {$wpdb->prefix}booking_coupons as sf";          

        ////////////////////////////////////////////////////////////////////////
        // WHERE
        ////////////////////////////////////////////////////////////////////////
        $where = '';

        // MU
        $where = apply_bk_filter( 'multiuser_modify_SQL_for_current_user', $where );  // MultiUser - only specific booking data for specific Regular User in Admin panel.
        if ( $where != '' ) $where = ' WHERE ' . $where;

        // Searching
        if ( isset( $_REQUEST[ $this->search_get_key ] ) ) {

            //Escape digit or CSD
            $esc_sql_where_id = wpbc_clean_digit_or_csd( $_REQUEST[ $this->search_get_key ] );

            // Escape SQL string
            $esc_sql_where_title = wpbc_clean_like_string_for_db( $_REQUEST[ $this->search_get_key ] );


            $where_search_resource_id = '';

            if ( ! empty( $esc_sql_where_id ) )
                $where_search_resource_id .=    " ( sf.coupon_id IN (" . $esc_sql_where_id . ") ) ";

            if ( ! empty( $esc_sql_where_title ) ) {

                if ( ! empty( $where_search_resource_id ) ){ 
                    $where_search_resource_id .= " OR ";
                }                
                    $where_search_resource_id .= " ( sf.coupon_code LIKE '%" . $esc_sql_where_title . "%' ) ";
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

        ////////////////////////////////////////////////////////////////////////
        // ORDER
        ////////////////////////////////////////////////////////////////////////

        $order = $this->get_sorting_params();

        // Exceptions //////////////////////////////////////////////////////////
        if ( $order['orderby'] == 'id' )    $order['orderby'] = 'coupon_id';
        ////////////////////////////////////////////////////////////////////////

        $sql_order = ' ORDER BY ' . $order['orderby'] ;
        if ( strtolower( $order['order'] ) == 'asc' )   
            $sql_order .= ' ASC';
        else                            
            $sql_order .= ' DESC';

        $wpbc_sql .= $sql_order;


        ////////////////////////////////////////////////////////////////////////
        // RESULTS
        ////////////////////////////////////////////////////////////////////////

        $all_data = $wpdb->get_results( $wpbc_sql );

        if ( ! empty( $all_data ) ) {

            $data = array();

            foreach ( $all_data as $single_data ) {

                $single_data       = get_object_vars( $single_data );
                $single_data['id'] = $single_data['coupon_id'];             // Transfor ID

                $data[ $single_data['id'] ] = $single_data;
            }

            return array( 'data' => $data );                                        // Results      
        } 

        return false;                                                               // No results
    }

    
    /** R e l o a d    C a c h e    D a t a,    because of saving new data to DataBase, or for any other reason */
    public function wpbc_reload_cache() {
        
        // Sorted All data from DB
        $data_from_db = $this->get_data_from_db();

        if ( $data_from_db !== false ) {
            
            self::$data = $data_from_db['data'];
            
            if ( ! defined( 'WPBC_COUPONS_CACHE' ) )      define( 'WPBC_COUPONS_CACHE', true );

            return true;
        } else {
            return false;
        }        
    }
        
    
    /** Get Single Instance of this Class and Init Plugin */
    public static function init() {
    
        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WPBC_DC_Cache ) ) {
            
            self::$instance = new WPBC_DC_Cache;
        }
        
        return self::$instance;        
    }
    
    
    ////////////////////////////////////////////////////////////////////////////
    // Get
    ////////////////////////////////////////////////////////////////////////////
    
    /**
	 * Get sorted part of Data array to show on 1 page
     *  based on $_GET paramters,  like: &orderby=id&order=asc&page_num=2
     * 
     * @return array
                    Example: array (
                                    [1] => Array (
                                                    [id] => 1
                                                    [title] => Default
                                                    [users] => 1
                                                    ...
                                    ), 
                                    [5] => Array (
                                                    [id] => 5
                                                    [title] => Default-1
                                                    [users] => 1
                                                    ...
                                    ), ...
     */
    public function get_data_for_one_page() {
     
        $pagination = $this->get_pagination_params();
        
        // We need to  skip "child booking data" for calculation number of items per page
        $slice_arr = array_slice( self::$data, $pagination['start'], $pagination['items_per_page'] );
        
        $return_array = array();
        foreach ( $slice_arr as $res ) {
                
            $return_array[ $res['id'] ] = $res;
        }
        
        return $return_array;
    }
    
    
    /**
	 * Get all already Loaded Data
     * 
     * @return array
     */
    public function get_data() {
        
        return self::$data;
    }
    
    
    /**
	 * Get Parameter of specific Item
     * 
     * @param int $item_id
     * @param string $parameter_key - name of parameter  to  get: {                 [id] => 1
                                                                                [coupon_id] => 1
                                                                                [title] => Default
                                                                                [users] => 1
                                                                                [filter] => ...
                                                              }
     * @return mixes - false | value | array
     */
    public function get_resource_attr( $item_id, $parameter_key = '' ) {
        
        if ( isset( self::$data[ $item_id ] ) ) {
            if ( empty( $parameter_key ) ) {
                return self::$data[ $item_id ];                                 // array  of parameters for this data            
            } else {
                
                if ( isset( self::$data[ $item_id ][ $parameter_key ] ) ) {
                     return self::$data[ $item_id ][ $parameter_key ];          // value of specific parameter in specific item
                } else {
                    return  false;                                              // No parameter in this item.
                }                
            }            
        } else {
            return  false;                                                      // No  itmes with this ID
        }
    }
    

    ////////////////////////////////////////////////////////////////////////////
    // Support
    ////////////////////////////////////////////////////////////////////////////
    
    /**
	 * Get Pagination parameters
     *  based on $_GET parameter and 'booking_resourses_num_per_page'
     * 
     * @return array( 'selected_page_num' => 2, 'items_per_page' => 10, 'start' => 10, 'end' => 19 );
     */
    public function get_pagination_params() {
        
        $params = array();
        
        // Current page
        $params['selected_page_num'] = (! empty( $_REQUEST['page_num'] )) ? intval( $_REQUEST['page_num'] ) : 1;         // Pagination
        if ( empty( $params['selected_page_num'] ) ) $params['selected_page_num'] = 1;
        
        // Number of items per page
        $params['items_per_page'] = intval( get_bk_option( 'booking_resourses_num_per_page' ) );                        // Based on number of booking resources per page // TODO: in future may be to add own option here
        
        // Start index of item for this page
        $params['start'] = ( $params['selected_page_num'] - 1 ) * $params['items_per_page'];
        
        // End index of item for this page
        $params['end'] = ( $params['selected_page_num'] ) * $params['items_per_page'] -1 ;
        
        return $params;
    }

    
    /**
	 * Get sorting parameter
     *  based on version and $_GET['orderby'] & $_GET['order'] params
     * 
     * @return array( 'orderby' => 'id', 'order' => 'desc' )     ||     array('orderby' => 'title', 'order' => 'asc' ) .... 
     */
    public function get_sorting_params() {
        /*
            coupon_id 	bigint(20)  -- exception  in $this->get_data_from_db
            title 	varchar(200)
            filter 	text
            users 	bigint(20)
        */
        //Default Params
        $sort_parameter = array( 
                                'orderby' => 'id'
                              , 'order' => 'desc' 
                            );
        
        // Requested params
        if ( isset( $_GET['orderby'] ) ) {
            switch ( strtolower( $_GET['orderby'] ) ) {
                
                case 'id':
                        $sort_parameter['orderby'] = 'id';
                        break;
                case 'coupon_code':
                        $sort_parameter['orderby'] = 'coupon_code';
                        break;
                case 'coupon_active':
                        $sort_parameter['orderby'] = 'coupon_active';
                        break;
                case 'coupon_value':
                        $sort_parameter['orderby'] = 'coupon_value';
                        break;
                case 'expiration_date':
                        $sort_parameter['orderby'] = 'expiration_date';
                        break;
                case 'coupon_min_sum':
                        $sort_parameter['orderby'] = 'coupon_min_sum';
                        break;
                case 'support_bk_types':
                        $sort_parameter['orderby'] = 'support_bk_types';
                        break;
                case 'users':
                        if ( class_exists( 'wpdev_bk_multiuser' ) )
                            $sort_parameter['orderby'] = 'users';
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
 * Example: <?php $wpbc_dc_cache = wpbc_dc_cache(); ?>
 */
function wpbc_dc_cache() {
    
    return WPBC_DC_Cache::init();
}
