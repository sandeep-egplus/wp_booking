<?php /**
 * @version 1.0
 * @package Booking > Resources > Cost and rates page > "Rates" section
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

class WPBC_Section_Rate {
    
    const HTML_PREFIX     = 'rrate_';
    const HTML_SECTION_ID = 'set_rate';
    
    private $settings;
    private $loaded_meta_data = array();                                        /** array(    [filter]      => array ( [33] => On,      [29] => Off,    [27] => Off )
                                                                                            , [rate]        => array ( [33] => 100,     [29] => 0,      [27] => 0   )
                                                                                            , [rate_type]   => array ( [33] => curency, [29] => %,      [27] => %   )    )  */
    
    
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

                wpbc_open_meta_box_section( self::HTML_SECTION_ID , __('Set Rates', 'booking') );

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

        ?><table class="form-table"><tbody><?php   

            $resource_titles_text = array();
            foreach ( $resource_titles as $single_resource_title ) {
                $resource_titles_text[] = '<span class="label label-default label-info" >' . $single_resource_title . '</span>';
            }
            $resource_titles_text = '<span class="wpdevelop">' . implode(' ', $resource_titles_text ) . '</span>';


            ?>
            <tr valign="top" >
                <th scope="row" style="vertical-align:middle;">
                    <?php 
                    if ( count($resource_titles) > 1 )  _e('Resources', 'booking'); 
                    else                                _e('Resource', 'booking'); 
                    ?>
                </th>
                <td class="description wpbc_edited_resource_label">
                    <?php echo $resource_titles_text; ?>
                    <?php                     
                    $wpbc_br_cache = wpbc_br_cache();
                    $resource_attr = $wpbc_br_cache->get_resource_attr( $this->settings['resource_id'] );
                    
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


        ////////////////////////////////////////////////////////////////////
        // Season Filters Table     
        ////////////////////////////////////////////////////////////////////
        $columns = array();
        $columns[ 'check' ] = array( 'title' => '<input type="checkbox" value="" id="' . self::HTML_PREFIX . 'select_all" name="' . self::HTML_PREFIX . 'select_id_all" />'
                                        , 'class' => 'check-column' 
                                    );
        $columns[ 'id' ] = array(         'title' => __( 'ID' )
                                        , 'style' => 'width:4em;'
                                        , 'class' => 'wpbc_hide_mobile'
                                        //, 'sortable' => true 
                                    );

        $columns = apply_filters ('wpbc_' . self::HTML_SECTION_ID . '_sf_table_header__after_id' , $columns );


        $columns[ 'enabled' ] = array(    'title' => __( 'Enabled', 'booking' )
                                        , 'class' => 'wpbc_hide_mobile'
                                        , 'style' => 'width:5em'
                                        //, 'sortable' => true 
                                    );
        
        $columns[ 'rates' ] = array(    'title' => __( 'Rates', 'booking' )
                                        , 'style' => 'width:15em;text-align:center;'
                                        //, 'sortable' => true 
                                    );
        $columns[ 'final_cost' ] = array(    'title' => ucwords(strtolower( __( 'Seasonal price', 'booking' ) ))
                                        , 'style' => 'width:10em;'
                                        //, 'sortable' => true 
                                    );
        $columns[ 'title' ] = array(      'title' => __( 'Season', 'booking' )
                                        , 'style' => 'width:20%;'
                                        //, 'sortable' => true 
                                    );

        $columns[ 'info' ] = array(      'title' => __( 'Info', 'booking' )
                                        , 'style' => 'text-align:center;'
                                        , 'class' => 'wpbc_hide_mobile'
                                        //, 'sortable' => true 
                                    );

        $columns = apply_filters ('wpbc_seasonfilters_table_header__user_title' , $columns );                
        if ( isset($columns[ 'users' ] ) ) {
            $columns[ 'users' ][ 'sortable' ] = false;
        }

        $columns = apply_filters ('wpbc_' . self::HTML_SECTION_ID . '_sf_table_header__last' , $columns );        


        // We need to  load meatadata here (before WPBC_SF_Table_all_seasons ), because because after  saving data acn  be updated. Saving is processing before this function.
        $meta_data = wpbc_get_resource_meta( $this->settings[ 'resource_id' ], 'rates' );
        if ( count( $meta_data ) > 0 ) {                                        

            $this->loaded_meta_data = maybe_unserialize( $meta_data[0]->value );                        
        }  
        
        $wpbc_sf_table = new WPBC_SF_Table_all_seasons( 
                            'rate' 
                            , array(
                                  'url_sufix'   =>  '#wpbc_' . self::HTML_PREFIX . 'sf_table'  // Link to  scroll
                                , 'rows_func'   =>  array( $this, 'seasonfilters_table__show_rows' ) 
                                , 'columns'     =>  $columns
                                , 'is_show_pseudo_search_form' => false

                                , 'edit_booking_resource_id_arr'    => $this->settings[ 'resource_id_arr' ]
                            )
                        );

        $wpbc_sf_table->display();             
        
        ?>
        <div class="clear"></div>
        <p style="text-align: left;line-height:2em;padding:5px 15px;" class="wpbc-settings-notice notice-info">        
            <strong><?php _e('Note!' ,'booking'); ?></strong> <?php 
                    printf( __( 'Enter seasonal rate(s) (cost diference in %s from standard cost %s or a fixed cost) of the booking resource (%s) or %sAdd a new seasonal filter%s' ,'booking')
                            , '<span style="font-weight:600;color:#F90;">%</span>' 
                            , '<span style="font-weight:600;color:#F90;">' . $resource_attr['cost'] . '</span>' 
                            , ' <span style="font-weight:600;color:#F90;">' . $resource_titles_text .'</span> ' 
                            , '<a class="button button-secondary" href="' . wpbc_get_resources_url() . '&tab=filter" style="font-size: 0.95em;font-weight: 600;text-transform: lowercase;">'
                            ,'</a>'
                        );
            ?>
        </p>
        <div class="clear" style="height:20px;"></div>
        <a href="javascript:void(0);" 
           class="button button-primary"
           onclick="javascript: //if ( jQuery('#sfd_days_filter_name').val() == '') { wpbc_field_highlight( '#sfd_days_filter_name' );  return false; }
                                jQuery('#action_<?php echo $this->settings['action_form']; ?>').val('update_sql_rate');
                                jQuery('#edit_resource_id_<?php echo $this->settings['action_form']; ?>').val('<?php echo implode( ',', $this->settings[ 'resource_id_arr' ] ); ?>');
                                jQuery(this).closest('form').submit();" 
            ><?php _e('Save Changes', 'booking') ?></a>
        <?php            

    }


    /**
	 * Show   R O W S   for booking resource table
     * 
     * @param int $row_num
     * @param array $resource
     */
    public function seasonfilters_table__show_rows( $row_num, $item ) {

        $css_class = ' wpbc_seasonfilters_row';

        ?><tr class="wpbc_row<?php echo $css_class; ?>  wpbc_<?php

                    if ( ! empty( $item['hidded'] ) ) {
                        echo ' hidden_items wpbc_seasonfilters_row_to_hide';
                    }

                    ?>" id="resource_<?php echo $item['id']; ?>"><?php

                    ?><th class="check-column">
                            <label class="screen-reader-text" for="<?php echo self::HTML_PREFIX; ?>select_<?php echo $item['id' ]; ?>"><?php echo esc_js(__('Select Booking Resource', 'booking')); ?></label>
                            <input type="checkbox" 
                                           id="<?php echo self::HTML_PREFIX; ?>select_<?php echo $item['id' ]; ?>" 
                                           name="<?php echo self::HTML_PREFIX; ?>select_<?php echo $item['id' ]; ?>" 
                                           value="<?php echo self::HTML_PREFIX; ?>id_<?php echo $item['id' ]; ?>" 
                                           <?php    
                                            $is_checked = false;
                                            if (   ( isset( $this->loaded_meta_data['filter'] ) ) 
                                                && ( isset( $this->loaded_meta_data['filter'][ $item['id' ] ] ) )  
                                                && ( $this->loaded_meta_data['filter'][ $item['id' ] ] == 'On' )  
                                                ) {
                                                $is_checked = true;
                                            }
                                            checked( $is_checked ); 
                                            ?>
                                />       
                    </th>
                    <td class="wpbc_hide_mobile"><?php echo $item['id' ]; ?></td><?php 
                                                
                    do_action( 'wpbc_' . self::HTML_SECTION_ID . '_sf_table_show_col__after_id', $row_num, $item ); 
                    
                    ?><td style="text-align:left;" class="wpbc_hide_mobile">&nbsp;&nbsp;&nbsp;<?php 
                        if (  $is_checked ) 
                            echo '<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>';
                        else
                            echo '<span class="glyphicon glyphicon-minus" aria-hidden="true"></span>'; 
                    ?>
                    </td>
                    <td class="wpbc_2_fields_in_collumn"><fieldset><?php 
                            
                            $rate = 0;
                            if (       ( isset( $this->loaded_meta_data['rate'] ) ) 
                                    && ( isset( $this->loaded_meta_data['rate'][ $item['id' ] ] ) )                                  
                                ) {
                                $rate = $this->loaded_meta_data['rate'][ $item['id' ] ];
                            } 
                            
                          ?><input type="text" 
                                   value="<?php echo floatval( $rate ); ?>" 
                                   id="rate_<?php echo $item['id' ]; ?>" 
                                   name="rate_<?php echo $item['id' ]; ?>" 
                                /><?php 
                              
                            $rate_type = '%';
                            if (       ( isset( $this->loaded_meta_data['rate_type'] ) ) 
                                    && ( isset( $this->loaded_meta_data['rate_type'][ $item['id' ] ] ) )                                  
                                ) {
                                $rate_type = $this->loaded_meta_data['rate_type'][ $item['id' ] ];
                            }                            
                                
                            $currency = wpbc_get_currency_symbol_for_user( $this->settings['resource_id'] );    
                       
                            $price_period = wpbc_get_per_day_night_title();

                            ?><select name="rate_type_<?php echo $item['id' ]; ?>" id="rate_type_<?php echo $item['id' ]; ?>" >
                                  <option value="%" <?php selected( $rate_type, '%' );  ?> >%</option>
                                  <option value="curency" <?php selected( $rate_type, 'curency' );  ?> ><?php echo $currency, $price_period; ?></option>
                            </select>
                         </fieldset>
                    </td>
                    <td><?php
                    
                    //Final Cost
                    
                        if ( $rate_type == 'curency') {
                           $rate_cost = floatval( $rate );
                        } else  {
                            
                            $wpbc_br_cache = wpbc_br_cache();
                            $resource_attr = $wpbc_br_cache->get_resource_attr( $this->settings['resource_id'] );
                                                
                            $rate_cost = ( $resource_attr['cost'] * floatval( $rate ) / 100 );
                        }
                   
                        echo wpbc_get_cost_with_currency_for_user( $rate_cost, $this->settings['resource_id'] ) . $price_period;
                                        
                    ?></td>
                    <td>
                        <a href="<?php echo wpbc_get_resources_url() . '&tab=filter&edit_season_id=' . $item['id' ] . '&wh_search_id=' . $item['id' ]; ?>"
                           style="font-weight:600;"
                           ><?php echo esc_attr( $item['title'] ); ?></a>
                    </td>
                    <td class="wpbc_hide_mobile"><?php 

                        if (  (  $is_checked ) && ( isset( $this->loaded_meta_data['general'] ) )  ){

                            echo '<span class="label label-default label-danger ' 
                                    . ( ( $this->loaded_meta_data[ 'general' ] == 'On' ) ? '' : 'hidden_items' ) 
                                    . ' wpbc_unavailable_item  wpbc_rcosts_item" >'  . __('unavailable', 'booking' ) . '</span>';

                            echo '<span class="label label-default label-success ' 
                                    . ( ( $this->loaded_meta_data[ 'general' ] == 'Off' ) ? '' : 'hidden_items' ) 
                                    . ' wpbc_available_item wpbc_rcosts_item" >' . __('available', 'booking' )   . '</span>';

                        }
                            echo '&nbsp;&nbsp;&nbsp;' . wpbc_get_filter_description( $item['filter' ] ); 
                    ?></td>
                    <?php do_action( 'wpbc_seasonfilters_table_show_col__user_text',  $row_num, $item ); ?>
                    <?php do_action( 'wpbc_' . self::HTML_SECTION_ID . '_sf_table_show_show_col__last', $row_num, $item ); ?>
        </tr>
        <?php    
    }
    
           
    /** Save changes */
    public function update_sql() {
        
        // $validated_title = WPBC_Settings_API::validate_text_post_static( $html_prefix . 'days_filter_name' );        //  Validate Title

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
        
        $season_filters_for_br = $available_sf->get_linear_data_for_one_page();
        
        foreach ( $this->settings[ 'resource_id_arr' ] as $resource_id ) {      // Loop all  selected booking resources.
            
            $rcosts = array();
            $rcosts[ 'filter' ]     = array();
            $rcosts[ 'rate' ]       = array();
            $rcosts[ 'rate_type' ]  = array();

            
            // List  of season filters that  available (listed in table)  for this booking resource(s)
            // And Validate $_POST for saving
            foreach ( $season_filters_for_br as $season_filter_id => $season_filter_data) {

                if ( isset( $_POST[ 'rate_' . $season_filter_id ] ) ) {
                    
                        // On | Off
                        if ( isset( $_POST[ self::HTML_PREFIX . 'select_' . $season_filter_id ] ) ) {                    
                                $rcosts[ 'filter' ][ $season_filter_id ] = 'On'; 
                        } else  $rcosts[ 'filter' ][ $season_filter_id ] = 'Off'; 

                        // Rate Value
                        $rcosts[ 'rate' ][ $season_filter_id ] = str_replace( ',', '.', $_POST[ 'rate_' . $season_filter_id ] );                            // In case,  if someone was make mistake and use , instead of .
                        $rcosts[ 'rate' ][ $season_filter_id ] = floatval( $rcosts[ 'rate' ][ $season_filter_id ] );

                        

                        // Rate Type
                        switch ( $_POST[ 'rate_type_' . $season_filter_id ] ) {
                            case 'curency':
                                $rcosts[ 'rate_type' ][ $season_filter_id ] = 'curency';
                                break;
                            default: 
                                $rcosts[ 'rate_type' ][ $season_filter_id ] = '%';
                                break;
                        }                        
                }
            }

            // Save new meta rcosts data     
            wpbc_save_resource_meta( $resource_id, 'rates', $rcosts );

            wpbc_show_changes_saved_message();   

            make_bk_action( 'wpbc_reinit_seasonfilters_cache' );                        
        }    
        
    }
}