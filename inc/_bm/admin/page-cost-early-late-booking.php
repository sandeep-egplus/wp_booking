<?php /**
 * @version 1.0
 * @package Booking > Resources > Cost and rates page > "Early / Late Booking" section
 * @category Settings page 
 * @author wpdevelop
 *
 * @web-site https://wpbookingcalendar.com/
 * @email info@wpbookingcalendar.com 
 * 
 * @modified 2016-09-10
 * 
 * This is COMMERCIAL SCRIPT
 * We are do not guarantee correct work and support of Booking Calendar, if some file(s) was modified by someone else then wpdevelop.
 */

//FixIn: 8.2.1.17
if ( ! defined( 'ABSPATH' ) ) exit;                                             // Exit if accessed directly

class WPBC_Section_Early_Late_Booking {
    
    const HTML_PREFIX     = 'rearly_late_booking_';
    const HTML_SECTION_ID = 'set_early_late_booking';
    
    private $settings;
    private $loaded_meta_data = array();                                        /** array(  [amount] => 10
                                                                                            [type] => %
                                                                                            [active] => On
                                                                                            [days_condition] => 0
                                                                                            [season_filter] => 0         - if no season filter,  otherwise: [season_filter] => 33
                                                                                        )  
                                                                                */
    
    
    function __construct( $resource_id, $params ) {
        
        $defaults = array( 
                              'resource_id'     => 0
                            , 'resource_id_arr' => array()
                        );
        $params = wp_parse_args( $params, $defaults );
      
        if ( ! empty( $resource_id ) ) {
            
            $params[ 'resource_id_arr' ] = explode( ',', (string) $resource_id ); 
        
            $params[ 'resource_id' ]     = $params[ 'resource_id_arr' ][0];     // If we selected several booking resources, so by default we will show settings of first selected resource 
        }

        $this->settings = $params;
    }
    
    
    /** Show MetaBox */
    public function display() {
        
        ?><div class="clear" style="margin-top:20px;"></div><?php 
        ?><div id="wpbc_<?php echo self::HTML_PREFIX; ?>table_<?php echo self::HTML_SECTION_ID; ?>" class="wpbc_settings_row wpbc_settings_row_rightNO"><?php                   

            // Get data
            $resource_titles = array();                    
            $wpbc_br_cache = wpbc_br_cache();
            foreach ( $this->settings[ 'resource_id_arr' ] as $bk_res_id ) {
                
                $title_res = $wpbc_br_cache->get_resource_attr( $bk_res_id, 'title');
                if (! empty($title_res) ) {
                    $title_res =  apply_bk_filter('wpdev_check_for_active_language', $title_res );

                    $resource_titles[]= $title_res;
                }
            }
       
            if (  ( ! empty( $this->settings[ 'resource_id_arr' ] ) ) && ( ! empty( $resource_titles ) )  ){

                wpbc_open_meta_box_section( self::HTML_SECTION_ID , __('Set Early / Late Booking', 'booking') );

                    $this->costs_section( $resource_titles );

                wpbc_close_meta_box_section();
                
                
            } else {                                                            // No such resource(s)
                wpbc_show_message_in_settings( __( 'Nothing Found', 'booking' ) . '.', 'warning', __('Error' ,'booking') . '.' );
            }
        
            ?><div class="clear" style="margin-top:20px;"></div><?php 
        ?></div><?php                         
    }

    
    /**
	 * Section Content, Define Headers
     * 
     * @param string $resource_titles
     */
    private function costs_section( $resource_titles ){
        
        $meta_data = wpbc_get_resource_meta( $this->settings[ 'resource_id' ], 'costs_early_late_booking' );
        if ( count( $meta_data ) > 0 ) {                                        

            $this->loaded_meta_data = maybe_unserialize( $meta_data[0]->value );                        
            /*
                [amount] => 10
                [type] => %
                [active] => On
                [days_condition] => 0
                [season_filter] => 33
             */
        }  
//debuge($this->loaded_meta_data);
        ////////////////////////////////////////////////////////////////////////
        
        
            $resource_titles_text = array();
            foreach ( $resource_titles as $single_resource_title ) {
                $resource_titles_text[] = '<span class="label label-default label-info" >' . $single_resource_title . '</span>';
            }
            $resource_titles_text = '<span class="wpdevelop">' . implode(' ', $resource_titles_text ) . '</span>';
            
            $wpbc_br_cache = wpbc_br_cache();
            $resource_attr = $wpbc_br_cache->get_resource_attr( $this->settings['resource_id'] );

            $is_super_admin = apply_bk_filter( 'multiuser_is_user_can_be_here', true, 'only_super_admin' );    

            $currency = wpbc_get_currency_symbol_for_user( $this->settings['resource_id'] ); 


        ////////////////////////////////////////////////////////////////////////        

        ?><table class="form-table"><tbody><?php   

			////////////////////////////////////////////////////////////////////////////////////////////////////////////
            // Early booking discount		Check in - Today  >= X
		    ////////////////////////////////////////////////////////////////////////////////////////////////////////////

            WPBC_Settings_API::field_checkbox_row_static(   self::HTML_PREFIX . 'early_booking_active'
                                                            , array(
                                                                    'type'              => 'checkbox'
                                                                    , 'title'             => __('Enable / Disable', 'booking')
                                                                    , 'label'             => sprintf( __('%sEarly booking discount%s for booking resource' ,'booking'), '<b>', '</b>' ) . ' ' . $resource_titles_text
                                                                    , 'disabled'          => false
                                                                    , 'class'             => ''
                                                                    , 'css'               => ''
                                                                    , 'description'       => ''
                                                                    , 'attr'              => array()
                                                                    , 'group'             => 'general'
                                                                    , 'tr_class'          => ''
                                                                    , 'only_field'        => false
                                                                    , 'is_new_line'       => true
                                                                    , 'description_tag'   => 'span'
                                                                    , 'value' => (  ( isset($this->loaded_meta_data['early_booking_active'] ) ) ? $this->loaded_meta_data['early_booking_active'] : 'Off'  )
                                                            )
                                                    );
            ?>
            <tr valign="top" >
                <th scope="row" style="vertical-align: middle;">
                    <?php 
                     _e('Discount', 'booking')
                    ?>
                </th>
                <td class="description wpbc_edited_resource_label">
                <?php 
                    WPBC_Settings_API::field_text_row_static(                                              
                                                      self::HTML_PREFIX . 'early_booking_amount'
                                            , array(  
                                                      'type'              => 'text'
                                                    , 'title'             => __('Discount', 'booking')
                                                    , 'description'       => ''
                                                    , 'placeholder'       => ''
                                                    , 'description_tag'   => 'span'
                                                    , 'tr_class'          => ''
                                                    , 'class'             => ''
                                                    , 'css'               => 'float:left;width:6em;'
                                                    , 'only_field'        => !false
                                                    , 'attr'              => array()                                                    
                                                    //, 'validate_as'       => array( 'required' )
                                                    , 'value'             => (  ( isset($this->loaded_meta_data['early_booking_amount'] ) ) ? $this->loaded_meta_data['early_booking_amount'] : '10'  )
                                                )
                                    );                  
            
                    WPBC_Settings_API::field_select_row_static(                                              
                                                      self::HTML_PREFIX . 'early_booking_type'
                                            , array(  
                                                      'type'              => 'select'
                                                
                                                    , 'title'             => __('Discount', 'booking')
                                                    , 'label'             => ''
                                                    , 'disabled'          => false
                                                    , 'disabled_options'  => array()
                                                    , 'multiple'          => false
                                                    
                                                    , 'description'       => ''
                                                    , 'description_tag'   => 'span'
                                                
                                                    , 'group'             => 'general'
                                                    , 'tr_class'          => ''
                                                    , 'class'             => ''
                                                    , 'css'               => 'float:left;width:10em;'
                                                    , 'only_field'        => ! false                                                
                                                    , 'attr'              => array()                                                    
                                                
                                                    , 'value'             => (  ( isset($this->loaded_meta_data['early_booking_type'] ) ) ? $this->loaded_meta_data['early_booking_type'] : '%'  )
                                                    , 'options'           => array(
                                                                                    'fixed' => __('fixed total in' ,'booking') . ' ' . $currency
                                                                                  , '%'     => '% ' . __('of payment' ,'booking')
                                                                                )
                                                )
                                    );                  
            ?></td>
            </tr>
            <tr valign="top"  style="border-bottom: 1px solid #ccc;">
                <th scope="row">
                    <?php 
                     _e('Conditions', 'booking')
                    ?>
                </th>
                <td class="description wpbc_edited_resource_label">
                    <?php 
                    
                    ////////////////////////////////////////////////////////////
                    // Days more than .... 
                    ////////////////////////////////////////////////////////////
                    
                    ?><span for="description" ><?php echo strtolower( sprintf(__('Apply discount, only if difference between %stoday%s and %scheck in%s day %smore%s than' ,'booking'), '<i>"', '"</i>', '<i>"', '"</i>', '<code style="text-transform: uppercase;font-weight: 600;">', '</code>' ) ); ?> &nbsp; </span><?php

                    $options = array();                                         //array_combine( range(0, 365) ,range(0, 365) );
                    $options[0] = '---';
                    for ($i = 365; $i >= 2; $i--) {
                        $options[$i] = $i . ' ' . __('days' ,'booking');
                    }
                    $options[1] = '1 ' . __('day' ,'booking');

                    WPBC_Settings_API::field_select_row_static(                                              
                                                      self::HTML_PREFIX . 'early_booking_days_condition'
                                            , array(  
                                                      'type'              => 'select'                                                
                                                    , 'title'             => ''
                                                    , 'label'             => ''
                                                    , 'disabled'          => false
                                                    , 'disabled_options'  => array()
                                                    , 'multiple'          => false                                                    
                                                    , 'description'       => ''
                                                    , 'description_tag'   => 'span'                                                
                                                    , 'group'             => 'general'
                                                    , 'tr_class'          => ''
                                                    , 'class'             => ''
                                                    , 'css'               => 'width:7em;'
                                                    , 'only_field'        => true
                                                    , 'attr'              => array()                                                                                                    
                                                    , 'value'             => (  ( isset($this->loaded_meta_data['early_booking_days_condition'] ) ) ? $this->loaded_meta_data['early_booking_days_condition'] : '60'  )
                                                    , 'options'           => $options
                                                )
                                    );                  
                    
                    ?><div class="clear" style="height: 10px;"></div><?php 
                    
                    ////////////////////////////////////////////////////////////
                    // Apply  if in specific season...
                    ////////////////////////////////////////////////////////////
                    
                    $link_season = wpbc_get_resources_url() . '&tab=filter'; 
                    
                    $available_sf = new WPBC_SF_Table_all_seasons( 
                                    'rate' 
                                    , array(
                                          'url_sufix'   =>  '#wpbc_' . self::HTML_PREFIX . 'sf_table'  // Link to  scroll
                                        //, 'rows_func'   =>  array( $this, 'seasonfilters_table__show_rows' ) 
                                        , 'columns'     =>  array()
                                        , 'is_show_pseudo_search_form' => false
                                        , 'edit_booking_resource_id_arr'    => $this->settings[ 'resource_id_arr' ]

                                    )
                                );       
                    $filter_list = $available_sf->get_linear_data_for_one_page();


                    ?><span for="description" ><?php echo strtolower( sprintf(__('Apply discount, only if %scheck in%s day inside of this %sseason filter%s' ,'booking')
                                                                    , '<b>"', '"</b>'
                                                                    ,  '<a class="wpbc_season_filer_link" style="text-decoration:none;" href="' . $link_season . '">', '</a>' ) ); ?></span><?php
                                                                    
                    $options = array( __('Any days' ,'booking') );                                                
                    foreach ( $filter_list as $key => $value_filter ) {
                        
                        $options[ $value_filter['id'] ] = $value_filter;
                        
                        if ( ! empty( $value_filter['hidded'] ) ) { 
                            $options[ $value_filter['id'] ]['attr'] = array( 'class' => 'hidden_items wpbc_seasonfilters_row_to_hide' );
                        }
                    }     
                    
                    WPBC_Settings_API::field_select_row_static(                                              
                                                      self::HTML_PREFIX . 'early_booking_season_filter'
                                            , array(  
                                                      'type'              => 'select'                                                
                                                    , 'title'             => ''
                                                    , 'label'             => ''
                                                    , 'disabled'          => false
                                                    , 'disabled_options'  => array()
                                                    , 'multiple'          => false                                                    
                                                    , 'description'       => ''
                                                    , 'description_tag'   => 'span'                                                
                                                    , 'group'             => 'general'
                                                    , 'tr_class'          => ''
                                                    , 'class'             => ''
                                                    , 'css'               => 'margin:0 0 3px;'
                                                    , 'only_field'        => true
                                                    , 'attr'              => array()                                                                                                    
                                                    , 'value'             => (  ( isset($this->loaded_meta_data['early_booking_season_filter'] ) ) ? $this->loaded_meta_data['early_booking_season_filter'] : '0'  )
                                                    , 'options'           => $options
                                                )
                                    );                  
                    
                    if ( ( $is_super_admin ) && ( class_exists( 'wpdev_bk_multiuser' ) ) ) {
                        ?><span class="wpdevelop">
                        <a data-original-title="<?php echo esc_js( __('Hide season filters', 'booking') ); ?>" 
                           class="button wpbc_show_hide_children tooltip_right wpbc_seasonfilters_btn_to_hide" style="display:none; "
                           onclick="javascript: jQuery('.wpbc_seasonfilters_row_to_hide').addClass('hidden_items');jQuery(this).hide();jQuery('.wpbc_seasonfilters_btn_to_show').show();" 
                           href="javascript:void(0);" 
                           style="display: inline-block;"><span aria-hidden="true" class="glyphicon glyphicon-eye-close"></span></a>            
                        <a data-original-title="<?php echo esc_js( __('Show all exist season filters', 'booking') ); ?>" 
                           class="button wpbc_show_hide_children tooltip_right wpbc_seasonfilters_btn_to_show" style=" "
                           onclick="javascript: jQuery('.wpbc_seasonfilters_row_to_hide').removeClass('hidden_items');jQuery(this).hide();jQuery('.wpbc_seasonfilters_btn_to_hide').show();" 
                           href="javascript:void(0);" 
                           style="display: inline-block;"><span aria-hidden="true" class="glyphicon glyphicon-eye-open"></span></a>
                        </span>
                        <?php
                    }                                                
                    ?>
                </td>
            </tr>
			<?php

			////////////////////////////////////////////////////////////////////////////////////////////////////////////
            // Last minute booking discount
		    ////////////////////////////////////////////////////////////////////////////////////////////////////////////

            WPBC_Settings_API::field_checkbox_row_static(   self::HTML_PREFIX . 'last_min_booking_active'
                                                            , array(
                                                                    'type'              => 'checkbox'
                                                                    , 'title'             => __('Enable / Disable', 'booking')
                                                                    , 'label'             => sprintf( __('%sLast minute booking discount%s for booking resource' ,'booking'), '<b>', '</b>' ) . ' ' . $resource_titles_text
                                                                    , 'disabled'          => false
                                                                    , 'class'             => ''
                                                                    , 'css'               => ''
                                                                    , 'description'       => ''
                                                                    , 'attr'              => array()
                                                                    , 'group'             => 'general'
                                                                    , 'tr_class'          => ''
                                                                    , 'only_field'        => false
                                                                    , 'is_new_line'       => true
                                                                    , 'description_tag'   => 'span'
                                                                    , 'value' => (  ( isset($this->loaded_meta_data['last_min_booking_active'] ) ) ? $this->loaded_meta_data['last_min_booking_active'] : 'Off'  )
                                                            )
                                                    );
            ?>
            <tr valign="top" >
                <th scope="row" style="vertical-align: middle;">
                    <?php
                     _e('Discount', 'booking')
                    ?>
                </th>
                <td class="description wpbc_edited_resource_label">
                <?php
                    WPBC_Settings_API::field_text_row_static(
                                                      self::HTML_PREFIX . 'last_min_booking_amount'
                                            , array(
                                                      'type'              => 'text'
                                                    , 'title'             => __('Discount', 'booking')
                                                    , 'description'       => ''
                                                    , 'placeholder'       => ''
                                                    , 'description_tag'   => 'span'
                                                    , 'tr_class'          => ''
                                                    , 'class'             => ''
                                                    , 'css'               => 'float:left;width:6em;'
                                                    , 'only_field'        => !false
                                                    , 'attr'              => array()
                                                    //, 'validate_as'       => array( 'required' )
                                                    , 'value'             => (  ( isset($this->loaded_meta_data['last_min_booking_amount'] ) ) ? $this->loaded_meta_data['last_min_booking_amount'] : '10'  )
                                                )
                                    );

                    WPBC_Settings_API::field_select_row_static(
                                                      self::HTML_PREFIX . 'last_min_booking_type'
                                            , array(
                                                      'type'              => 'select'

                                                    , 'title'             => __('Discount', 'booking')
                                                    , 'label'             => ''
                                                    , 'disabled'          => false
                                                    , 'disabled_options'  => array()
                                                    , 'multiple'          => false

                                                    , 'description'       => ''
                                                    , 'description_tag'   => 'span'

                                                    , 'group'             => 'general'
                                                    , 'tr_class'          => ''
                                                    , 'class'             => ''
                                                    , 'css'               => 'float:left;width:10em;'
                                                    , 'only_field'        => ! false
                                                    , 'attr'              => array()

                                                    , 'value'             => (  ( isset($this->loaded_meta_data['last_min_booking_type'] ) ) ? $this->loaded_meta_data['last_min_booking_type'] : '%'  )
                                                    , 'options'           => array(
                                                                                    'fixed' => __('fixed total in' ,'booking') . ' ' . $currency
                                                                                  , '%'     => '% ' . __('of payment' ,'booking')
                                                                                )
                                                )
                                    );
            ?></td>
            </tr>
            <tr valign="top"  >
                <th scope="row">
                    <?php
                     _e('Conditions', 'booking')
                    ?>
                </th>
                <td class="description wpbc_edited_resource_label">
                    <?php

                    ////////////////////////////////////////////////////////////
                    // Days more than ....
                    ////////////////////////////////////////////////////////////

                    ?><span for="description" ><?php echo strtolower( sprintf(__('Apply discount, only if difference between %stoday%s and %scheck in%s day %sless%s than' ,'booking'), '<i>"', '"</i>', '<i>"', '"</i>', '<code style="text-transform: uppercase;font-weight: 600;">', '</code>' ) ); ?> &nbsp; </span><?php

                    $options = array();                                         //array_combine( range(0, 365) ,range(0, 365) );
                    $options[0] = '---';
                    $options[1] = '1 ' . __('day' ,'booking');
                    for ($i = 2; $i < 365; $i++) {
                        $options[$i] = $i . ' ' . __('days' ,'booking');
                    }
                    WPBC_Settings_API::field_select_row_static(
                                                      self::HTML_PREFIX . 'last_min_booking_days_condition'
                                            , array(
                                                      'type'              => 'select'
                                                    , 'title'             => ''
                                                    , 'label'             => ''
                                                    , 'disabled'          => false
                                                    , 'disabled_options'  => array()
                                                    , 'multiple'          => false
                                                    , 'description'       => ''
                                                    , 'description_tag'   => 'span'
                                                    , 'group'             => 'general'
                                                    , 'tr_class'          => ''
                                                    , 'class'             => ''
                                                    , 'css'               => 'width:7em;'
                                                    , 'only_field'        => true
                                                    , 'attr'              => array()
                                                    , 'value'             => (  ( isset($this->loaded_meta_data['last_min_booking_days_condition'] ) ) ? $this->loaded_meta_data['last_min_booking_days_condition'] : '14'  )
                                                    , 'options'           => $options
                                                )
                                    );

                    ?><div class="clear" style="height: 10px;"></div><?php

                    ////////////////////////////////////////////////////////////
                    // Apply  if in specific season...
                    ////////////////////////////////////////////////////////////

                    $link_season = wpbc_get_resources_url() . '&tab=filter';

                    $available_sf = new WPBC_SF_Table_all_seasons(
                                    'rate'
                                    , array(
                                          'url_sufix'   =>  '#wpbc_' . self::HTML_PREFIX . 'sf_table'  // Link to  scroll
                                        //, 'rows_func'   =>  array( $this, 'seasonfilters_table__show_rows' )
                                        , 'columns'     =>  array()
                                        , 'is_show_pseudo_search_form' => false
                                        , 'edit_booking_resource_id_arr'    => $this->settings[ 'resource_id_arr' ]

                                    )
                                );
                    $filter_list = $available_sf->get_linear_data_for_one_page();


                    ?><span for="description" ><?php echo strtolower( sprintf(__('Apply discount, only if %scheck in%s day inside of this %sseason filter%s' ,'booking')
                                                                    , '<b>"', '"</b>'
                                                                    ,  '<a class="wpbc_season_filer_link" style="text-decoration:none;" href="' . $link_season . '">', '</a>' ) ); ?></span><?php

                    $options = array( __('Any days' ,'booking') );
                    foreach ( $filter_list as $key => $value_filter ) {

                        $options[ $value_filter['id'] ] = $value_filter;

                        if ( ! empty( $value_filter['hidded'] ) ) {
                            $options[ $value_filter['id'] ]['attr'] = array( 'class' => 'hidden_items wpbc_seasonfilters_row_to_hide' );
                        }
                    }

                    WPBC_Settings_API::field_select_row_static(
                                                      self::HTML_PREFIX . 'last_min_booking_season_filter'
                                            , array(
                                                      'type'              => 'select'
                                                    , 'title'             => ''
                                                    , 'label'             => ''
                                                    , 'disabled'          => false
                                                    , 'disabled_options'  => array()
                                                    , 'multiple'          => false
                                                    , 'description'       => ''
                                                    , 'description_tag'   => 'span'
                                                    , 'group'             => 'general'
                                                    , 'tr_class'          => ''
                                                    , 'class'             => ''
                                                    , 'css'               => 'margin:0 0 3px;'
                                                    , 'only_field'        => true
                                                    , 'attr'              => array()
                                                    , 'value'             => (  ( isset($this->loaded_meta_data['last_min_booking_season_filter'] ) ) ? $this->loaded_meta_data['last_min_booking_season_filter'] : '0'  )
                                                    , 'options'           => $options
                                                )
                                    );

                    if ( ( $is_super_admin ) && ( class_exists( 'wpdev_bk_multiuser' ) ) ) {
                        ?><span class="wpdevelop">
                        <a data-original-title="<?php echo esc_js( __('Hide season filters', 'booking') ); ?>"
                           class="button wpbc_show_hide_children tooltip_right wpbc_seasonfilters_btn_to_hide" style="display:none; "
                           onclick="javascript: jQuery('.wpbc_seasonfilters_row_to_hide').addClass('hidden_items');jQuery(this).hide();jQuery('.wpbc_seasonfilters_btn_to_show').show();"
                           href="javascript:void(0);"
                           style="display: inline-block;"><span aria-hidden="true" class="glyphicon glyphicon-eye-close"></span></a>
                        <a data-original-title="<?php echo esc_js( __('Show all exist season filters', 'booking') ); ?>"
                           class="button wpbc_show_hide_children tooltip_right wpbc_seasonfilters_btn_to_show" style=" "
                           onclick="javascript: jQuery('.wpbc_seasonfilters_row_to_hide').removeClass('hidden_items');jQuery(this).hide();jQuery('.wpbc_seasonfilters_btn_to_hide').show();"
                           href="javascript:void(0);"
                           style="display: inline-block;"><span aria-hidden="true" class="glyphicon glyphicon-eye-open"></span></a>
                        </span>
                        <?php
                    }
                    ?>
                </td>
            </tr>


        </tbody></table><?php

        
        ?><div class="clear"></div><?php                 
        ?>
        <div class="clear"></div>
        <a href="javascript:void(0);" 
           class="button button-primary"
           onclick="javascript: //if ( jQuery('#sfd_days_filter_name').val() == '') { wpbc_field_highlight( '#sfd_days_filter_name' );  return false; }
                                jQuery('#action_<?php echo $this->settings['action_form']; ?>').val('update_sql_early_late_booking');
                                jQuery('#edit_resource_id_<?php echo $this->settings['action_form']; ?>').val('<?php echo implode( ',', $this->settings[ 'resource_id_arr' ] ); ?>');
                                jQuery(this).closest('form').submit();" 
            ><?php _e('Save Changes', 'booking') ?></a>
        <?php            

    }


           
    /** Save changes */
    public function update_sql() {
        
        $costs_early_late_booking_value = array();                              /*  [active] => On
                                                                                    [amount] => 10
                                                                                    [type] => %                
                                                                                    [days_condition] => 0
                                                                                    [season_filter] => 33
                                                                                 */
        

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Early booking discount		Check in - Today  >= X
		////////////////////////////////////////////////////////////////////////////////////////////////////////////

        $costs_early_late_booking_value['early_booking_active'] = WPBC_Settings_API::validate_checkbox_post_static( self::HTML_PREFIX . 'early_booking_active' );            // Validate checkbox
        
        $costs_early_late_booking_value['early_booking_amount'] = str_replace( ',', '.', $_POST[ self::HTML_PREFIX . 'early_booking_amount'] );                            // In case,  if someone was make mistake and use , instead of .
        $costs_early_late_booking_value['early_booking_amount'] = floatval( $costs_early_late_booking_value['early_booking_amount'] );
        
        if (   ( isset( $_POST[ self::HTML_PREFIX . 'early_booking_type' ] ) )
            && (        $_POST[ self::HTML_PREFIX . 'early_booking_type' ] == 'fixed'  )
           ) {            
            $costs_early_late_booking_value['early_booking_type'] = 'fixed';                             // fixed
        } else {            
            $costs_early_late_booking_value['early_booking_type'] = '%';                                 // Default %
        }
        
        $costs_early_late_booking_value['early_booking_days_condition'] = intval( $_POST[ self::HTML_PREFIX . 'early_booking_days_condition'] );
        $costs_early_late_booking_value['early_booking_season_filter']  = intval( $_POST[ self::HTML_PREFIX . 'early_booking_season_filter'] );

		////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Last minute booking discount
		////////////////////////////////////////////////////////////////////////////////////////////////////////////

        $costs_early_late_booking_value['last_min_booking_active'] = WPBC_Settings_API::validate_checkbox_post_static( self::HTML_PREFIX . 'last_min_booking_active' );            // Validate checkbox

        $costs_early_late_booking_value['last_min_booking_amount'] = str_replace( ',', '.', $_POST[ self::HTML_PREFIX . 'last_min_booking_amount'] );                            // In case,  if someone was make mistake and use , instead of .
        $costs_early_late_booking_value['last_min_booking_amount'] = floatval( $costs_early_late_booking_value['last_min_booking_amount'] );

        if (   ( isset( $_POST[ self::HTML_PREFIX . 'last_min_booking_type' ] ) )
            && (        $_POST[ self::HTML_PREFIX . 'last_min_booking_type' ] == 'fixed'  )
           ) {
            $costs_early_late_booking_value['last_min_booking_type'] = 'fixed';                             // fixed
        } else {
            $costs_early_late_booking_value['last_min_booking_type'] = '%';                                 // Default %
        }

        $costs_early_late_booking_value['last_min_booking_days_condition'] = intval( $_POST[ self::HTML_PREFIX . 'last_min_booking_days_condition'] );
        $costs_early_late_booking_value['last_min_booking_season_filter']  = intval( $_POST[ self::HTML_PREFIX . 'last_min_booking_season_filter'] );



        // Loop all Resources
        foreach ( $this->settings[ 'resource_id_arr' ] as $resource_id ) {      

            // Save new meta rcosts data     
            wpbc_save_resource_meta( $resource_id, 'costs_early_late_booking', $costs_early_late_booking_value );
        }    
        
        wpbc_show_changes_saved_message();   

        make_bk_action( 'wpbc_reinit_seasonfilters_cache' );                                
    }
}