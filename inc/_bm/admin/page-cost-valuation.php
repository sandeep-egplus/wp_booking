<?php /**
 * @version 1.0
 * @package Booking > Resources > Cost and rates page > "Valuation days" section
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

if ( ! defined( 'ABSPATH' ) ) exit;                                             // Exit if accessed directly

class WPBC_Section_Valuation {
    
    const HTML_PREFIX     = 'rvaluation_';
    const HTML_SECTION_ID = 'set_valuation';
    
    private $settings;
    private $loaded_meta_data = array();                                        /**
	 																				* array(
                                                                                            [0] => Array (
                                                                                                    [active] => On
                                                                                                    [type] => >
                                                                                                    [from] => 3
                                                                                                    [to] => 6
                                                                                                    [cost] => 20
                                                                                                    [cost_apply_to] => fixed
                                                                                                    [season_filter] => 0
                                                                                                )
                                                                                            [1] => Array (
                                                                                                    [active] => On
                                                                                                    [type] => summ
                                                                                                    [from] => 7
                                                                                                    [to] => 14
                                                                                                    [cost] => 90
                                                                                                    [cost_apply_to] => %
                                                                                                    [season_filter] => 33
                                                                                                )
                                                                                            [2] => Array (
                                                                                                    [active] => On
                                                                                                    [type] => =
                                                                                                    [from] => LAST
                                                                                                    [to] => 14
                                                                                                    [cost] => 0
                                                                                                    [cost_apply_to] => fixed
                                                                                                    [season_filter] => 0
                                                                                                )
                                                                                    )  */
    
    
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
                if ( ! empty( $title_res ) ) {
                    
                    $title_res =  apply_bk_filter('wpdev_check_for_active_language', $title_res );
                    $resource_titles[]= $title_res;
                }
            }
       
            if (  ( ! empty( $this->settings[ 'resource_id_arr' ] ) ) && ( ! empty( $resource_titles ) )  ){

                wpbc_open_meta_box_section( self::HTML_SECTION_ID , __('Set Valuation Days', 'booking') );

                    $this->seasonfilters_section( $resource_titles );

                wpbc_close_meta_box_section();
                                
            } else {
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
    private function seasonfilters_section( $resource_titles ){

        $resource_titles_text = array();
        foreach ( $resource_titles as $single_resource_title ) {
            $resource_titles_text[] = '<span class="label label-default label-info" >' . $single_resource_title . '</span>';
        }
        $resource_titles_text = '<span class="wpdevelop">' . implode(' ', $resource_titles_text ) . '</span>';
        
        
        ////////////////////////////////////////////////////////////////////////
        // Title of  Resource(s)
        ////////////////////////////////////////////////////////////////////////
        ?><table class="form-table">
            <tbody>
            <tr valign="top" >
                <th scope="row" style="vertical-align:middle;">
                    <?php 
                    if ( count( $resource_titles ) > 1 ) _e('Resources', 'booking'); 
                    else                                 _e('Resource', 'booking'); 
                    ?>
                </th>
                <td class="description wpbc_edited_resource_label">
                <?php 
                    echo $resource_titles_text;
                    
                    $wpbc_br_cache = wpbc_br_cache();
                    $resource_attr = $wpbc_br_cache->get_resource_attr( $this->settings['resource_id'] );
                    
                    // Show or Hide all Season Filters in MU for Super Admin
                    $is_can = apply_bk_filter( 'multiuser_is_user_can_be_here', true, 'only_super_admin' );    
                    if ( ( $is_can ) && ( class_exists( 'wpdev_bk_multiuser' ) ) ) {
                        ?><span class="wpdevelop">
                        <a data-original-title="<?php echo esc_js( __('Hide season filters', 'booking') ); ?>" 
                           class="button wpbc_show_hide_children tooltip_left wpbc_seasonfilters_btn_to_hide" style="display:none;float:right;"
                           onclick="javascript: jQuery('.wpbc_seasonfilters_row_to_hide').addClass('hidden_items');jQuery(this).hide();jQuery('.wpbc_seasonfilters_btn_to_show').show();" 
                           href="javascript:void(0);" 
                           style="display: inline-block;"><span aria-hidden="true" class="glyphicon glyphicon-eye-close"></span></a>            
                        <a data-original-title="<?php echo esc_js( __('Show all exist season filters', 'booking') ); ?>" 
                           class="button wpbc_show_hide_children tooltip_left wpbc_seasonfilters_btn_to_show" style="float:right;"
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

        ?></tbody></table><?php


        
        ////////////////////////////////////////////////////////////////////////
        // Columns
        ////////////////////////////////////////////////////////////////////////
        $columns = array();
        $columns[ 'sort' ] = array( 'title' => ''
                                    , 'class' => 'sort wpbc_hide_mobile' 
                                    );

        $columns[ 'check' ] = array( 'title' => '<input type="checkbox" value="" id="' . self::HTML_PREFIX . 'select_all" name="' . self::HTML_PREFIX . 'select_id_all" />'
                                        , 'class' => 'check-column' 
                                    );

                $columns = apply_filters ('wpbc_' . self::HTML_SECTION_ID . '_vd_table_header__after_check' , $columns );

        $columns[ 'enabled' ] = array(    'title' => __( 'Status', 'booking' )
                                        , 'class' => 'wpbc_hide_mobile'
                                        , 'style' => 'width:5em;text-align:center;'
                                        //, 'sortable' => true 
                                    );        
        $columns[ 'days' ] = array(    'title' => ucwords(strtolower( __( 'days', 'booking' ) ))
                                        , 'style' => 'width:30%;'
                                        //, 'sortable' => true 
                                    );
        $columns[ 'cost' ] = array(      'title' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . __( 'Costs', 'booking' )
                                        , 'style' => 'width:30%;'
                                        //, 'sortable' => true 
                                    );
        $columns[ 'seasons' ] = array(      'title' => __( 'Season', 'booking' )
                                        , 'style' => 'width:15em;'
                                        //, 'class' => 'wpbc_hide_mobile'
                                        //, 'sortable' => true 
                                    );
        $columns[ 'actions' ] = array(    'title' => __( 'Actions', 'booking' )
                                        , 'style' => 'text-align:center;width:4em;'
                                        , 'class' => 'wpbc_hide_mobile'
                                        //, 'sortable' => true 
                                    );
                $columns = apply_filters ('wpbc_' . self::HTML_SECTION_ID . '_vd_table_header__last' , $columns );        

                                                                                /* We need to load meatadata here (before WPBC_SF_Table_all_seasons ), 
                                                                                 * because because after  saving data it can be updated. Saving is processing before this function. */
        $meta_data = wpbc_get_resource_meta( $this->settings[ 'resource_id' ], 'costs_depends' );
        if ( count( $meta_data ) > 0 ) {                                        

            $this->loaded_meta_data = maybe_unserialize( $meta_data[0]->value );                                    
        }  
        
        ////////////////////////////////////////////////////////////////////////
        // Blank Table      -   with Row for ability to  ADD new "Valuation days" cost  settings - system  clone this row for adding new cost  setting, and make fix relative "-1" number
        ////////////////////////////////////////////////////////////////////////
        ?><div class="wpdevelop wpbc_selectable_table wpbc_resources_table wpbc_sortable_table wpdevelop" style="display:none;"><?php
        ?><table id="wpbc_blank_table_for_clone" class="table table-striped widefat widefat wpbc_input_table sortable table table-striped "><?php 
        
            $this->seasonfilters_table__show_rows( -1 );
            
        ?></table></div><?php     
        
        ////////////////////////////////////////////////////////////////////////
        // "Valuation days" Table
        ////////////////////////////////////////////////////////////////////////
        $wpbc_vd_table = new WPBC_VD_Table( 
                            'valuation' 
                            , array(
                                  'url_sufix'   =>  '#wpbc_' . self::HTML_PREFIX . 'sf_table'  // Link to  scroll
                                , 'rows_func'   =>  array( $this, 'seasonfilters_table__show_rows' ) 
                                , 'columns'     =>  $columns
                                , 'is_show_pseudo_search_form' => false

                                , 'loaded_meta_data'                => $this->loaded_meta_data
                                , 'edit_booking_resource_id_arr'    => $this->settings[ 'resource_id_arr' ]
                            )
                        );

        $wpbc_vd_table->display();             
       
        ?>
        <div class="clear"></div>
        <span class="wpdevelop"><a href="javascript:void(0);"  class="button button-secondary wpbc_vd_add_new_button"
            ><i class="menu_icon icon-1x glyphicon glyphicon-plus"></i> &nbsp;<?php _e('Add new cost', 'booking') ?></a></span>
        <a href="javascript:void(0);" 
           class="button button-primary"
           onclick="javascript: //if ( jQuery('#sfd_days_filter_name').val() == '') { wpbc_field_highlight( '#sfd_days_filter_name' );  return false; }
                                jQuery('#action_<?php echo $this->settings['action_form']; ?>').val('update_sql_valuation');                                
                                jQuery('#edit_resource_id_<?php echo $this->settings['action_form']; ?>').val('<?php echo implode( ',', $this->settings[ 'resource_id_arr' ] ); ?>');
                                jQuery( '#wpbc_blank_table_for_clone' ).remove();
                                jQuery(this).closest('form').submit();" 
            ><?php _e('Save Changes', 'booking') ?></a><?php            
        
        wpbc_sortable_js();
        
        $this->js();
        
        
        ////////////////////////////////////////////////////////////////////////
        // Help  info
        ////////////////////////////////////////////////////////////////////////
        ?>  
        <div class="clear"  style="height:20px;"></div>
        <div class="wpbc-settings-notice notice-info">                    
            <ul style="list-style: disc inside;margin:0;">
                <li>
                    <?php printf(__('Cost setings at %stop have higher priority%s than other costs of same type at the %sbottom%s of the list.' ,'booking'), '<b>','</b>', '<b>','</b>'); ?>
                </li>
                <li>
                    <?php printf(__('Please create all %s terms firstly %s(from higher priority to lower)%s, then terms %s and after terms %s' ,'booking'), '<b>"'.__('Together' ,'booking').'"</b>', '<em>', '</em>', '<b>"'.__('For' ,'booking').'"</b>', '<b>"'.__('From' ,'booking').' - '. __('To' ,'booking').'"</b>'); ?>
                </li>
                <li>
                    <?php printf(__('%s and %s terms have higher priority than a range %s days.' ,'booking'), '<b>"'.__('Together' ,'booking').'"</b>','<b>"'.__('For' ,'booking').'"</b>', '<b>"'.__('From' ,'booking').' - '. __('To' ,'booking').'"</b>'); ?>
                </li>
                <li>
                    <?php 
                        printf(__('%s - definition of check-out date.' ,'booking'),  '<code>LAST</code>', '<b>"'.__('For' ,'booking').'"</b>' );
                        echo ' ';
                        printf( __('Example' ,'booking') . ': <code><b>"'.__('For' ,'booking').'"</b> <b>LAST</b> day = 0 "$ per 1 day"</code>'  ); ?>
                </li>
            </ul>
            <div class="clear"></div>
        </div> 
        <p style="text-align: left;line-height:2em;padding:5px 15px;" class="wpbc-settings-notice notice-warning">        
            <strong><?php _e('Warning!' ,'booking'); ?></strong> <?php 
                  printf(__('Specific cost will take affect, only if it active (the box at the left side is checked) and if "Check In" (start) date belong to selected season filter or if set "Any days".' ,'booking'), '<span style="color:#d11;">','</span>', '<b>','</b>')
            ?>
        </p>
        <div class="clear"></div><?php 
        
    }
      
    
    /** JavaScript - Add | Delete | Check  selections  */    
    private function js() {
        
        $currency = wpbc_get_currency_symbol_for_user( $this->settings['resource_id'] ); 
        
        ?>
        <script type="text/javascript">
            
            //Show | Hide elemnts depend from  selected option  in days selection  type after loading of page
            jQuery(document).ready(function(){
                jQuery('.wpbc_vd_type_select').each(function() {
                    wpbc_check_vd_type_selection( this );                 
                });
            });      
            
            //Show | Hide elemnts depend from  selected option  in days selection  type
            jQuery('.wpbc_vd_type_select').on( 'change', function(){    
                wpbc_check_vd_type_selection( this );
            });

            // Show hide specific options and chnage text  in selectboxes element - basically its "this" of selectbox              
            function wpbc_check_vd_type_selection( element ){

                var selected_val = jQuery( element ).val();                            //jQuery.find('[name="'+element.name+'"] option:selected');
                if ( selected_val != '>') {
                    jQuery( element ).parent().find('.wpbc_vd_to_label,.wpbc_vd_to_field').hide();
                } else {
                    jQuery( element ).parent().find('.wpbc_vd_to_label,.wpbc_vd_to_field').show();
                }            
                if ( selected_val == 'summ') {

                    if ( jQuery( element ).parents('tr').find('.wpbc_vd_cost_apply_to_select option:selected').val() == 'add' )
                        jQuery( element ).parents('tr').find('.wpbc_vd_cost_apply_to_select option:eq(0)').prop('selected', true);
                    jQuery( element ).parents('tr').find('.wpbc_vd_cost_apply_to_option').hide();

                    // Change Text in options (type of cost) relative selected "Togather"
                    jQuery( element ).parents('tr').find('.wpbc_vd_cost_apply_to_select option:eq(0)').html('<?php echo esc_js( '% ' . __(' for all days!' ,'booking') ); ?>');
                    jQuery( element ).parents('tr').find('.wpbc_vd_cost_apply_to_select option:eq(1)').html('<?php echo esc_js( $currency. ' '.  __(' for all days!' ,'booking') ); ?>');                
                } else {
                    jQuery( element ).parents('tr').find('.wpbc_vd_cost_apply_to_option').show();

                    // Change Text in options (type of cost) relative selected not "Togather"
                    jQuery( element ).parents('tr').find('.wpbc_vd_cost_apply_to_select option:eq(0)').html('<?php echo esc_js( '% ' . __('from the cost of 1 day ' ,'booking') ); ?>');
                    jQuery( element ).parents('tr').find('.wpbc_vd_cost_apply_to_select option:eq(1)').html('<?php echo esc_js( $currency. ' '.  __('per 1 day' ,'booking') ); ?>');
                } 

                if ( selected_val == '=') {
                    jQuery( element ).parents('tr').find('.wpbc_vd_days_label').html('<?php echo esc_js( __('day' ,'booking') ); ?>');
                } else {
                    jQuery( element ).parents('tr').find('.wpbc_vd_days_label').html('<?php echo esc_js( __('days' ,'booking') ); ?>');
                }
            }

            // Delete 
            jQuery('.wpbc_vd_delete_button').on( 'click', function(){  
                //if ( wpbc_are_you_sure('<?php echo esc_js(__('Do you really want to do this ?' ,'booking')); ?>') ) {         
                    jQuery( this ).closest('tr').remove(); 
                //}            
            });   
            
            // Add New
            jQuery('.wpbc_vd_add_new_button').on( 'click', function(){  
                
                jQuery( '.wpbc_vd_table tbody .wpbc_no_results_row' ).remove(); // Remove line of "No results"
                
                var size = jQuery('.wpbc_vd_table tbody .wpbc_row').size();     // Get size
                size++;
                var $clone = jQuery('#wpbc_blank_table_for_clone tr').clone( true );    // Deep clone element with  behaviours
                    $clone.attr( 'id', 'resource_' + size );                            // Change row ID to  latest num
                    $clone.find( 'input[name^=previous_num]' ).val( size );             // Set  correct value of last element
                    $clone.find( 'input[name^=<?php echo self::HTML_PREFIX; ?>select]' ).val( size );                        
                    $clone.appendTo('.wpbc_vd_table tbody');
            });               
        </script>
        <?php
    }

    
    /**
	 * Show   R O W S   for booking resource table
     * 
     * @param int $row_num
     * @param array $resource
     */
    public function seasonfilters_table__show_rows( $row_num, $item = array() ) {
        
        $wpbc_br_cache = wpbc_br_cache();
        $resource_attr = $wpbc_br_cache->get_resource_attr( $this->settings['resource_id'] );

        $currency = wpbc_get_currency_symbol_for_user( $this->settings['resource_id'] ); 
        // $price_period = wpbc_get_per_day_night_title();
        
        $default = array(
                          'active'  => 'On'
                        , 'type'    => '='
                        , 'from'    => 7
                        , 'to'      => 14
                        , 'cost'    => $resource_attr['cost']
                        , 'cost_apply_to' => 'fixed'
                        , 'season_filter' => 0
                    );
        $item = wp_parse_args( $item, $default );
        
        $css_class = ' wpbc_seasonfilters_row';

        $item['id' ] = $row_num;
                    
        ?><tr class="wpbc_row<?php echo $css_class; ?>  wpbc_<?php

                if ( ! empty( $item['hidded'] ) ) {
                    echo ' hidden_items wpbc_seasonfilters_row_to_hide';
                }

                ?>" id="resource_<?php echo $item['id']; ?>"><?php
                ?><td class="sort wpbc_hide_mobile" style="cursor: move;"></td><?php 
                ?><th class="check-column">
                        <input type="hidden" id="previous_num[]" name="previous_num[]" value="<?php echo $item['id' ]; ?>" />
                        <label class="screen-reader-text" for="<?php echo self::HTML_PREFIX; ?>select[]"><?php echo esc_js(__('Select Booking Resource', 'booking')); ?></label>
                        <input type="checkbox" 
                                       id="<?php echo self::HTML_PREFIX; ?>select[]" 
                                       name="<?php echo self::HTML_PREFIX; ?>select[]" 
                                       value="<?php echo $item['id' ]; ?>" 
                                       <?php                                                
                                        $is_checked = (  ( ( isset( $item['active' ] ) ) && ( $item['active' ] == 'On' ) ) ? true : false  );                                            
                                        checked( $is_checked ); 
                                        ?>
                            />       
                </th><?php 

                        do_action( 'wpbc_' . self::HTML_SECTION_ID . '_vd_table_show_col__after_check', $row_num, $item ); 

                ?><td style="text-align:center;padding:0 25px;" class="wpbc_hide_mobile">
                    <span class="glyphicon glyphicon-<?php echo ( ( $is_checked ) ? 'ok' : 'minus' ); ?>" aria-hidden="true"></span>
                </td>
                <td><?php 

                ////////////////////////////////////////////////////////////
                //  T y p e
                //////////////////////////////////////////////////////////// 
                WPBC_Settings_API::field_select_row_static( 
                                                          self::HTML_PREFIX . 'type[]'
                                                , array(  
                                                          'type'              => 'select'                                                
                                                        , 'multiple'          => false
                                                        , 'class'             => 'wpbc_vd_type_select'  
                                                        , 'css'               => 'float:left;width:7em;'
                                                        , 'only_field'        => true                                                
                                                        , 'value'             => $item['type']
                                                        , 'options'           => array(
                                                                                        '=' => __('For' ,'booking')
                                                                                      , '>' => __('From' ,'booking')
                                                                                      , 'summ' => __('Together' ,'booking')
                                                                                    )
                                                    )
                                        );
                ?><fieldset class="wpbc_vd_days_fieldset"><?php
                ////////////////////////////////////////////////////////////
                //  F r o m
                ////////////////////////////////////////////////////////////
                WPBC_Settings_API::field_text_row_static(                                              
                                                        self::HTML_PREFIX . 'from[]'
                                                , array(  
                                                          'type'              => 'text'
                                                        , 'css'               => 'float:left;width:4em;'
                                                        , 'only_field'        => true
                                                        , 'value'             => $item['from']
                                                    )
                                        );    
                ////////////////////////////////////////////////////////////
                //  T o
                ////////////////////////////////////////////////////////////
                ?><label for="<?php echo esc_attr( self::HTML_PREFIX . 'to[]' ); ?>" class="wpbc_vd_to_label"><?php echo wp_kses_post( __( 'to', 'booking' ) ); ?></label><?php
                    WPBC_Settings_API::field_text_row_static(                                              
                                                        self::HTML_PREFIX . 'to[]'
                                                , array(  
                                                          'type'              => 'text'
                                                        , 'class'             => 'wpbc_vd_to_field'
                                                        , 'css'               => 'width:4em;float:left;'
                                                        , 'only_field'        => true
                                                        , 'value'             => $item['to']
                                                    )
                                        );    
                ?><label class="wpbc_vd_days_label"><?php echo wp_kses_post( __( 'days', 'booking' ) );  ?></label><?php
                ?></fieldset><?php

              ?></td>
                <td><?php 
                ?><label class="wpbc_vd_to_label in-button-text"> = </label><?php
                ////////////////////////////////////////////////////////////
                //  Cost
                ////////////////////////////////////////////////////////////
                WPBC_Settings_API::field_text_row_static(                                              
                                                        self::HTML_PREFIX . 'cost[]'
                                                , array(  
                                                          'type'              => 'text'
                                                        , 'css'               => 'float:left;margin:1px 10px 4px 1px;width:6em;'
                                                        , 'only_field'        => true
                                                        , 'value'             => $item['cost']
                                                    )
                                        );    
                ////////////////////////////////////////////////////////////
                //  C o s t   T y p e
                ////////////////////////////////////////////////////////////    
                $options = array(   '%'     => array( 'title' => '% ' . __('from the cost of 1 day ' ,'booking')                        , 'attr' => array( 'class' => '') )
                                  , 'fixed' => array( 'title' => $currency . ' ' . __('per 1 day' ,'booking')                           , 'attr' => array( 'class' => '') )
                                  , 'add'   => array( 'title' => sprintf( __('Additional cost in %s per 1 day' ,'booking'), $currency ) , 'attr' => array( 'class' => 'wpbc_vd_cost_apply_to_option') )
                                );    
                // $currency. ' '.  __(' for all days!' ,'booking')             // fixed    
                // '% '.__(' for all days!' ,'booking')                         // %
                
                WPBC_Settings_API::field_select_row_static( 
                                                          self::HTML_PREFIX . 'cost_apply_to[]'
                                                , array(  
                                                          'type'              => 'select'                                                
                                                        , 'multiple'          => false
                                                        , 'class'             => 'wpbc_vd_cost_apply_to_select'
                                                        , 'css'               => 'float:left;margin:1px 10px 1px 1px;width:15em;'
                                                        , 'only_field'        => true                                                
                                                        , 'value'             => $item['cost_apply_to']
                                                        , 'options'           => $options
                                                    )
                                        );
                ?>                
                </td>
                <td><?php                 
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

                $options = array( __('Any days' ,'booking') );                                                
                foreach ( $filter_list as $key => $value_filter ) {

                    $options[ $value_filter['id'] ] = $value_filter;

                    if ( ! empty( $value_filter['hidded'] ) ) { 
                        $options[ $value_filter['id'] ]['attr'] = array( 'class' => 'hidden_items wpbc_seasonfilters_row_to_hide' );
                    }
                }     

                WPBC_Settings_API::field_select_row_static(                                              
                                                  self::HTML_PREFIX . 'season_filter[]'
                                        , array(  
                                                  'type'              => 'select'                                                
                                                , 'css'               => 'margin:0 0 3px;'
                                                , 'only_field'        => true                                                                                             
                                                , 'value'             =>  $item['season_filter']
                                                , 'options'           => $options
                                            )
                                );                  
                ?></td>
                <td style="text-align:center;" class="wpbc_hide_mobile">
                    <a  
                        href="javascript:void(0);" 
                        class="tooltip_top button-secondary button wpbc_vd_delete_button" 
                        title="<?php _e('Delete' ,'booking'); ?>"                        
                    ><i class="glyphicon glyphicon-remove"></i></a>
                </td><?php 
                        
                        do_action( 'wpbc_' . self::HTML_SECTION_ID . '_vd_table_show_show_col__last', $row_num, $item ); 
                ?>
        </tr>
        <?php    
    }
    
           
    /** Save changes */
    public function update_sql() {
        
//debuge('$_POST', $_POST); 

        $valuation_days = array();

        if ( isset( $_POST['previous_num'] ) ) {                                // We are having some vlues

            foreach ( $_POST['previous_num'] as $current_num => $previos_num ) {

                $meta_data = array(); 

                // Enabled
                $meta_data['active']        = 'Off';
                if ( isset( $_POST[ self::HTML_PREFIX . 'select'] ) ) {
                    if (  in_array( $previos_num, $_POST[ self::HTML_PREFIX . 'select'] ) ) {
                        
                        $meta_data['active'] = 'On';                            // previous number was exist so  its checked.
                    }
                }

                // Type
                $meta_data['type'] = '=';                                       // '>', '=', 'summ'
                if (   ( isset( $_POST[ self::HTML_PREFIX . 'type'] ) ) && isset( $_POST[ self::HTML_PREFIX . 'type' ][ $current_num ] )   ){

                    if ( $_POST[ self::HTML_PREFIX . 'type' ][ $current_num ] == '>')       $meta_data['type'] = '>';
                    if ( $_POST[ self::HTML_PREFIX . 'type' ][ $current_num ] == '=')       $meta_data['type'] = '=';
                    if ( $_POST[ self::HTML_PREFIX . 'type' ][ $current_num ] == 'summ')    $meta_data['type'] = 'summ';
                    //$meta_data['type'] = WPBC_Settings_API::validate_text_post_static( self::HTML_PREFIX . 'type',  $current_num );        //  Validate Title                
                }

                // From
                $meta_data['from'] = 0;                                             // int or 'LAST'
                if (   ( isset( $_POST[ self::HTML_PREFIX . 'from'] ) ) && isset( $_POST[ self::HTML_PREFIX . 'from' ][ $current_num ] )   ){

                    if ( strtolower( $_POST[ self::HTML_PREFIX . 'from' ][ $current_num ] ) == 'last' ) {       // Exception  here abot LAST
                        $meta_data['from'] = 'LAST';            
                    } else {
                        $meta_data['from'] = intval( $_POST[ self::HTML_PREFIX . 'from' ][ $current_num ] );
                    }                 
                }

                // To
                $meta_data['to'] = 1;                                           // int
                if (   ( isset( $_POST[ self::HTML_PREFIX . 'to'] ) ) && isset( $_POST[ self::HTML_PREFIX . 'to' ][ $current_num ] )   ){

                    $meta_data['to'] = intval( $_POST[ self::HTML_PREFIX . 'to' ][ $current_num ] );
                }

                // Cost
                $meta_data['cost'] = 0;                                             // float
                if (   ( isset( $_POST[ self::HTML_PREFIX . 'cost'] ) ) && isset( $_POST[ self::HTML_PREFIX . 'cost' ][ $current_num ] )   ){

                    $meta_data['cost'] = str_replace( ',', '.', $_POST[ self::HTML_PREFIX . 'cost' ][ $current_num ]  );                            // In case,  if someone was make mistake and use , instead of .
                    $meta_data['cost'] = floatval( $meta_data['cost'] );
                }

                // Cost  type
                $meta_data['cost_apply_to'] = '%';                              // '%', 'fixed', 'add'
                if (   ( isset( $_POST[ self::HTML_PREFIX . 'cost_apply_to'] ) ) && isset( $_POST[ self::HTML_PREFIX . 'cost_apply_to' ][ $current_num ] )   ){

                    if ( $_POST[ self::HTML_PREFIX . 'cost_apply_to' ][ $current_num ] == '%')      $meta_data['cost_apply_to'] = '%';
                    if ( $_POST[ self::HTML_PREFIX . 'cost_apply_to' ][ $current_num ] == 'fixed')  $meta_data['cost_apply_to'] = 'fixed';
                    if ( $_POST[ self::HTML_PREFIX . 'cost_apply_to' ][ $current_num ] == 'add')    $meta_data['cost_apply_to'] = 'add';
                    //$meta_data['cost_apply_to'] = WPBC_Settings_API::validate_text_post_static( self::HTML_PREFIX . 'cost_apply_to',  $current_num );        //  Validate Title                
                }

                // Season filter
                $meta_data['season_filter'] = 0;                                               // int
                if (   ( isset( $_POST[ self::HTML_PREFIX . 'season_filter'] ) ) && isset( $_POST[ self::HTML_PREFIX . 'season_filter' ][ $current_num ] )   ){

                    $meta_data['season_filter'] = intval( $_POST[ self::HTML_PREFIX . 'season_filter' ][ $current_num ] );
                }
                
                $valuation_days[] = $meta_data;
            }
        }
// debuge('$valuation_days', $valuation_days);
        
        foreach ( $this->settings[ 'resource_id_arr' ] as $resource_id ) {      // Loop all  selected booking resources.
            
            wpbc_save_resource_meta( $resource_id, 'costs_depends', $valuation_days );

            wpbc_show_changes_saved_message();   

            make_bk_action( 'wpbc_reinit_seasonfilters_cache' );                        
        }    
    }
}



/** Booking Resources  Table for Settings page */
class WPBC_VD_Table extends WPBC_Settings_Table {
    
    protected $search_get_key = 'wh_search_vd_id';
    
    
    /**
	 * Load Data from DB for showing in Table and return array
     * 
     * @return array
     */
    public function load_data(){
        
        return $this->parameters['loaded_meta_data'];
    }
    
    
    /**
	 * Get sorted part of booking data array for ONE Page
     * 
     * @return array
     */
    public function get_linear_data_for_one_page() {
                
        // Get sorted part of booking data array based on $_GET paramters, like: &orderby=id&order=asc&page_num=2
        //$data = $this->loaded_data->get_data_for_one_page();

        return $this->loaded_data;
    }
    
    
    /**
	 * Define CSS Classes for Div before table
     * 
     * @return array
     */
    public function div_before_table_css_classes() {

        return array( 'wpbc_resources_table wpbc_sortable_table wpdevelop' );   
    }
    
    
    /**
	 * Define CSS Classes for Table
     * 
     * @return array
     */
    public function table_css_classes() {

        return array( 'widefat wpbc_input_table sortable table table-striped wpbc_vd_table' );                     // array( 'sortable' );   
    }
    
    
    //                                                                              <editor-fold   defaultstate="collapsed"   desc=" Reset functions " >    
    
    /**
	 * Reset
     *  Useful only  for Header of this table for ability to  click on Sort Title  and its define ACTUAL SORT from GET
     *  Get Actual sorting parameter
     *  based on version and $_GET['orderby'] & $_GET['order'] params
     * 
     * @return array( 'orderby' => 'id', 'order' => 'desc' )     ||     array('orderby' => 'title', 'order' => 'asc' ) .... 
     */    
    public function get_sorting_params() {
        return  array();
        //$active_sort = $this->loaded_data->get_sorting_params();                // array( 'orderby' => 'id', 'order' => 'asc');
        //return $active_sort;
    }
    
    
    /**
	 * Reset
     *  Useful only  for Header of this table for ability to  click on Sort Title  and its define - $only_these_parameters for function  of generation link
     *  Get ONLY the paramters that  possible to  use in pagination buttons
     * 
     * @return array( 'page', 'tab', $this->search_get_key );
     */
    public function gate_paramters_for_pagination(){
        return  array();
        //return array( 'page', 'tab', $this->search_get_key );
    }
    
   
    /**
	 * Reset
     * Show Footer Row */
    public function show_footer(){
        
        // Nothing ....        
    }
    //                                                                              </editor-fold>        
}