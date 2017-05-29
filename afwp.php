<?php
/*
Plugin Name: ACF for WooCommerce Product
Plugin URI: https://github.com/pmbaldha
Description: Displays  WooCommerce Product ACF fileds value in front end.
Version: 1.0
Author: pmbaldha
Author URI: https://github.com/pmbaldha
License: Private
Copyright: pmbaldh
*/

DEFINE('AFWP_PATH', plugin_dir_path( __FILE__ ));
add_action( 'init', 'afwp_load_textdomain' );
/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
function afwp_load_textdomain() {
  load_plugin_textdomain( 'afwp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
}

if( is_admin() ) {
	require_once( AFWP_PATH.'admin-functions.php' );
}


add_action( 'woocommerce_product_meta_end', 'afwp_product_meta' );
function afwp_product_meta() {
	if (  class_exists( 'Acf' ) ) {
		$fields = get_field_objects();
		//var_dump( $fields ); 
		
		if( $fields )
		{
			foreach( $fields as $field_name => $field )
			{
				if ($field_name == '') continue;
		?>
				<span class="posted_in">
					<?php echo $field['label'].': ';
					?>
                    &nbsp;
                    <?php					
						afwp_woo_render_acf_field( $field );
					?>
				</span>
                <?php
			}
		}
	}
}
if( !
function_exists( 'afwp_woo_render_acf_field' ) ) {
	function afwp_woo_render_acf_field( $field ) {
				switch( $field['type'] ) {
				
					case 'post_object':
						?>
						<a href="<?php echo get_the_permalink( $field['value']->ID ); ?>"><?php echo $field['value']->post_title; ?></a>
						<?php
						break;
					case 'date_picker':
						echo date( 
								get_option( 'date_format' ) , 
								strtotime( $field['value'] )
							 );
						break;
					
					case 'file':
						$file = $field['value'];
						
						if( is_array( $file ) )
						{
							$url = $file['url'];
							$title = $file['title'];
							$caption = $file['caption'];
							
							
						
							if( $caption ): ?>
						
								<div class="wp-caption">
						
							<?php endif; ?>
						
							<a href="<?php echo $url; ?>" title="<?php echo $title; ?>">
								<span><?php echo $title; ?></span>
							</a>
						
							<?php if( $caption ): ?>
						
									<p class="wp-caption-text"><?php echo $caption; ?></p>
						
								</div>
							<?php endif; 
						}
						elseif( is_numeric($file) ) {
							?>
							<a href="<?php echo wp_get_attachment_url( intval( $file ) );?>">
							<?php echo wp_get_attachment_url( intval( $file ) );?>
                            </a>	
							<?php
						}
						else
						{
							?>
							<a href="<?php echo $file;?>">
                            	<?php echo $file;?>
                            </a>	
                            <?php
						}
						break;
					
					case 'image':
						//echo $field['value']['sizes']['thumbnail']
						$image = $field['value'];
						if( is_array( $image ) )
						{
							?>
							<a class="fancybox" rel="group" href="<?php echo $image['url'];?>"><img src="<?php echo $image['sizes']['thumbnail']; ?>" alt="<?php echo $image['alt']; ?>" />			
							<?php
						}
						elseif( is_numeric($image) ) {
							echo wp_get_attachment_image( intval( $image ), 'thumbnail' );
						}
						else {
							?>
							<a class="fancybox" rel="group" href="<?php echo $image;?>"><img src="<?php echo $image;?>" />		
						<?php
						}
						break;
						
					case 'wysiwyg':
						echo wp_kses_post( $field['value'] );
						break;	
					case 'true_false':
						if( $field['value'] ) {
							esc_html_e( 'Yes', 'afwp' );
						}
						else {
							esc_html_e( 'No', 'afwp' );
						}	
						break;
					case 'relationship':
						$output_array = array();
						foreach(  $field['value'] as $sub_field ) {
							if( is_object( $sub_field ) && is_a( $sub_field, 'WP_Post' ) ) {
							
								$output_array[] = '<a href="'.get_the_permalink( $sub_field->ID ).'">'.$sub_field->post_title.'</a>'; 
							}
							?>
                       
                        <?php
						}
						echo implode( ', ',  $output_array);						
						break;
					case 'taxonomy':
						$output_array = array();
							if( is_array( $field['value'] ) ) {
								foreach( $field['value'] as $sub_field ) {
									if( is_numeric( $sub_field ) ) {
										
										$sub_field = get_term_by( 'id', intval($sub_field), $field['taxonomy'], OBJECT );
									}
									
									if( is_object( $sub_field ) ) {
										$output_array[] =  '<a href="'.get_term_link($sub_field->term_id).'">'.$sub_field->name.'</a>';
									}
									
	 
								}
							}
							elseif( is_object( $field['value'] ) ) {
								$sub_field = $field['value'];
								$output_array[] =  '<a href="'.get_term_link($sub_field->term_id).'">'.$sub_field->name.'</a>';
							}
							elseif( is_numeric( $field['value'] ) ) {
								
								$sub_field  = get_term_by( 'id', intval( $field['value']), $field['taxonomy'], OBJECT );
								$output_array[] =  '<a href="'.get_term_link($sub_field->term_id).'">'.$sub_field->name.'</a>';
								
							}
							
						echo implode( ', ', $output_array );
						break;
						
					case 'user':
						if( $field['field_type'] == 'select' )
						{
						?>
                        	<br/>
                            <a href="<?php echo get_author_posts_url( $field['value']['ID'], $field['value']['user_nicename'] );?>" title="<?php echo $field['value']['user_nicename'];?>" alt="<?php echo $field['value']['user_nicename'];?>"><?php echo $field['value']['user_avatar'];?></a>
                            
                            
                            <a href="<?php echo get_author_posts_url( $field['value']['ID'], $field['value']['user_nicename'] );?>" title="<?php echo $field['value']['user_nicename'];?>" alt="<?php echo $field['value']['user_nicename'];?>">   
                            <?php echo $field['value']['display_name'];?>
	                       	</a>
						<?php
						}
						else
						{	
							$output_array = array();
							foreach( $field['value'] as $sub_field ) {
								$output_array[] = '<a href="'.get_author_posts_url( $sub_field['ID'], $sub_field['user_nicename'] ).'" title="'.$sub_field['user_nicename'].'" alt="'.$sub_field['user_nicename'].'">'.$sub_field['user_avatar'].'</a><br/>
								<a href="'.get_author_posts_url( $sub_field['ID'], $sub_field['user_nicename'] ).'" title="'.$sub_field['user_nicename'].'" alt="'.$sub_field['user_nicename'].'">'.$sub_field['display_name'].'
	                       		</a>';
							}
							echo '<br/>'.implode( '<br/><br/>', $output_array );
						}
						
						break;
					case 'color_picker':
						?>
						<span style="width:25px; background-color:<?php echo $field['value'];?>"><?php echo 
							str_repeat('&nbsp;',12);?></span>
                        <?php
						break;
				    default:
						//select multiple
						if( is_array( $field['value'] ) ) {
							$field_val = $field['value'];
							array_filter( $field_val );
							$field_val = array_map( 'trim', $field_val);
							$field_val = array_map( 'esc_html', $field_val);
							echo implode(', ', $field_val);
						}
						else {
							echo esc_html( $field['value'] );
						}
				}
	}
}
