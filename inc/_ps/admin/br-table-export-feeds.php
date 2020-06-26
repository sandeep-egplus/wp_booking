<?php
/**
 * @version     1.0
 * @menu		Booking > Settings > (Sync) Export page
 * @category    Settings API
 * @author      wpdevelop
 *
 * @web-site    https://wpbookingcalendar.com/
 * @email       info@wpbookingcalendar.com 
 * @modified    2017-07-09
 * 
 * This is COMMERCIAL SCRIPT
 * We are do not guarantee correct work and support of Booking Calendar, if some file(s) was modified by someone else then wpdevelop.
 */

if ( ! defined( 'ABSPATH' ) ) exit;                                             // Exit if accessed directly

//FixIn: 8.0

/**
	 * Show rows for booking resource table
 * 
 * @param int $row_num
 * @param array $resource
 */
function wpbc_export_feeds__show_rows( $row_num, $resource ) {
//debuge($resource);        

    ?><tr class="wpbc_row" id="resource_<?php echo $resource['id']; ?>"><?php
            
                ?><th class="check-column wpbc_hide_mobile">
                        <label class="screen-reader-text" for="br-select-<?php echo $resource['id' ]; ?>"><?php echo esc_js(__('Select Booking Resource', 'booking')); ?></label>
                        <input type="checkbox" 
                                       id="br-select-<?php echo $resource['id' ]; ?>" 
                                       name="br-select-<?php echo $resource['id' ]; ?>" 
                                       value="resource_<?php echo $resource['id' ]; ?>" 
                            />       
                </th>
                <td><?php echo $resource['id' ]; ?></td>
                <td>
                <?php 
                        if ( ! empty( $resource['parent']) ) {
                            echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                            echo  esc_js( $resource['title'] );
                        } else {
                            echo  '<strong>' . esc_js( $resource['title'] ) . '</strong>';
                        }
                ?>
                </td>
                <td style="text-align:center;">					
					<fieldset>
						<code style="font-size:12px;line-height: 2.4em;background: #ddd;color:#000;"><a
								href="<?php echo trim( home_url(), '/' ) . '/' . trim( $resource['export' ], '/');  ?>" 
								target="_blank"><?php 																	//FixIn: 8.1.3.6
									$wpbc_h_u = home_url();
									if (strlen( $wpbc_h_u ) > 23 ) {
										echo substr( $wpbc_h_u, 0, 10 ) . '...' . substr( $wpbc_h_u, -10 );
									} else {
										echo $wpbc_h_u;
									}?></a></code>
						<input type="text" 
								value="<?php echo $resource['export' ]; ?>" 
								id="booking_export_feed<?php echo $resource['id' ]; ?>" 
								name="booking_export_feed<?php echo $resource['id' ]; ?>" 
								class="large-text" 
								style="width:75%;" 
						 />
						<a href="<?php echo trim( home_url(), '/' ) . '/' . trim( $resource['export' ], '/');  ?>" 
						   title="<?php _e( 'Open in new window', 'booking' ); ?>"
						   target="_blank"><i class="menu_icon icon-1x glyphicon glyphicon-new-window"></i></a>
					</fieldset>
                </td>
                <?php if ( class_exists( 'wpdev_bk_biz_l' ) ) { ?>
                <td style="text-align:center;" class="wpbc_hide_mobile"><?php 
                        
                        if ( $resource['count'] > 1 ) {
                            ?> <span class="label label-default label-warning"><?php 

                                echo __('Capacity' ,'booking'), ': ', intval( $resource['count'] ); 

                            ?></span><?php                                    
                        }
                    
                        if ( class_exists( 'wpdev_bk_multiuser' ) ) {
                            ?> <span class="label label-default label-info"><?php 

                                $us_data = get_userdata( $resource['users'] );

                                echo __('User' ,'booking'), ': ', esc_js( $us_data->display_name ); 

                            ?></span><?php
                        }
                        
                ?></td><?php                 
                } else {
					?><td style="text-align:center;" class="wpbc_hide_mobile"></td><?php
				}
                ?>            
    </tr>
    <?php    
}


/** Save "Google Calendar ID" for booking resources */
function wpbc_export_feeds__update() {
// debuge($_POST);
    if (  ( wpbc_is_this_demo() ) ||  ( ! class_exists( 'wpdev_bk_personal' ) )  )
        return;
    
    global $wpdb;
    
    $wpbc_br_table = new WPBC_BR_Table( 'resources_submit' );
    $linear_resources_for_one_page = $wpbc_br_table->get_linear_data_for_one_page();
    
    foreach ( $linear_resources_for_one_page as $resource_id => $resource ) {
        
        // Check posts of only visible on page booking resources 
        if ( isset( $_POST['booking_export_feed' . $resource_id ] ) ) {
            
            // Validate POST value
            $validated_value = WPBC_Settings_API::validate_text_post_static( 'booking_export_feed' . $resource_id );

			if ( ( empty( $validated_value ) ) && (1 == $resource_id ) )
				$validated_value = get_bk_option( 'booking_resource_export_ics_url' );		// Get previous value from Free version
			
			// Set default value if not defined any
			if ( empty( $validated_value ) )
				$validated_value = '/ics/' . wpbc_get_slug_format(  $resource['title'] ) . '-' . $resource_id ;
			
			$validated_value = wpbc_make_link_relative( $validated_value );
			
            // Check  if its different from  original value in DB
            if ( $validated_value != $resource['export'] ) {

                // Save to DB    
                if ( false === $wpdb->query( 
                        $wpdb->prepare( 
                                        "UPDATE {$wpdb->prefix}bookingtypes SET export = %s WHERE booking_type_id = %d "  
                                        , $validated_value
                                        , intval($resource_id) )                                        
                                    )  
                    ){ debuge_error( 'Error saving to DB' ,__FILE__ , __LINE__); }
                
            }
        }
        
    }
    
    make_bk_action( 'wpbc_reinit_booking_resource_cache' );
}


/** Show booking resources table - Export Feeds */
function wpbc_export_feeds__show_table() {
    
    echo ( ( wpbc_is_this_demo() ) ? wpbc_get_warning_text_in_demo_mode() . '<div class="clear" style="height:20px;"></div>' : '' );
 		
    $wpbc_br_table = new WPBC_BR_Table( 
                        'resources' 
                        , array(
                              'url_sufix'   =>  '&subtab=export#wpbc_resources_link'
                            , 'rows_func'   =>  'wpbc_export_feeds__show_rows'
							, 'is_show_pseudo_search_form' => false 
                            , 'columns'     =>  array(
                                                    'check' => array( 'title' => '<input type="checkbox" value="" id="br-select-all" name="resource_id_all" />'
                                                                    , 'class' => 'check-column wpbc_hide_mobile' 
                                                                )
                                                    , 'id' => array(  'title' => __( 'ID' )
                                                                    , 'style' => 'width:5em;'
                                                                    , 'sortable' => true 
                                                                )
                                                    , 'title' => array(   'title' => __( 'Resources' )
                                                                        , 'style' => 'width:20em;'
                                                                        , 'sortable' => true 
                                                                    )
                                                    , 'gcalid' => array(  'title' => __( '.ics Feed URL' )
                                                                        , 'style' => 'text-align:center;' 
                                                                    )
                                                    , 'info' => array(    'title' => __( 'Info' )
                                                                        , 'class' => 'wpbc_hide_mobile'
                                                                        , 'style' => 'width:15em;text-align:center;' 
                                                                    )
                                            )
                        )
                    );

    $wpbc_br_table->display();        
}
