<?php
/* 
Plugin Name: WP Network Recents Posts
Plugin URI: http://www.masquewordpress.com/plugins/wordpress-mu-recents-posts/
Version: v1.0
Author: <a href="http://www.timersys.com/">Damian Logghe</a>
Description: Adds a widget to Displays latest posts + thumbnails from seleceted Wordpress Network Blogs .
Author URI: http://www.masquewordpress.com
*/


class mqw_recent_mu_posts extends WP_Widget {
 

	public function __construct()
	{
		$widget_ops = array( 'classname' => 'rmp_widget', 'description' => 'Displays recents posts from your Mu Network' ); // Widget Settings

        $control_ops = array( 'id_base' => 'rmp_widget' ); // Widget Control Settings

        $this->WP_Widget( 'rmp_widget', 'Recent Wp Network Posts', $widget_ops, $control_ops ); 
        
        add_action( 'admin_init', array(&$this,'register_options' ));
		
		add_action( 'admin_menu',array(&$this,'register_menu' ) );
		
		add_action( 'init',array(&$this,'load_style' ) );	
		
    }

	//Function to init the widget values and call the display widget function
	function widget($args,$instance)
	{
		$title 		= apply_filters('widget_title', $instance['title']); // the widget title
		$total_number 	= $instance['total_number']; // the number of posts to show
		$show_thumbs 	= isset($instance['show_thumbs']) ? $instance['show_thumbs'] : false; // show thumbs or not
		$blogs_ids		= isset($instance['blogs_ids']) ? $instance['blogs_ids'] : array('1');
		$authorcredit	= isset($instance['author_credit']) ? $instance['author_credit'] : false ; // give plugin author credit
		$thumb_size		= isset($instance['width']) && isset($instance['height']) ? array($instance['width'],$instance['height']) : false ;
		$show_titles	= isset($instance['show_titles']) ? $instance['show_titles'] : false;
		
		$widget= array ( 'ids' => $blogs_ids, 'total' => $total_number , 'show_titles' => $show_titles, 'size' => $thumb_size, 'credit' => $authorcredit );
		
		echo $args['before_widget'];
	    
		if ( $title )
		echo $args['before_title'] . $title . $args['after_title']; 
	
		$this->display_widget($widget);
	
	    echo $args['after_widget'];
			
	}
	 
	//function that display the widget form 
	function form($instance) 
	{

 		$defaults = array( 'title' => 'Recent Network Posts', 'total_number' => 3, 'show_thumbs' => 'on','width' => 50, 'height' => 50,'show_titles' => 'on', 'author_credit' => 'off' );
 		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

 		<p>
 			<label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
 			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>'" type="text" value="<?php echo $instance['title']; ?>" />
 		</p>
 		
 		<p>
 			<label for="<?php echo $this->get_field_id('total_number'); ?>"><?php _e('Total posts per blog'); ?></label>
 			<input class="widefat" id="<?php echo $this->get_field_id('total_number'); ?>" name="<?php echo $this->get_field_name('total_number'); ?>" type="text" value="<?php echo $instance['total_number']; ?>" />
 		</p>
 		
 		<p>
 			<label for="<?php echo $this->get_field_id('show_thumbs'); ?>"><?php _e('Show Thumbnails?'); ?></label>
 			<input type="checkbox" class="checkbox" <?php checked( $instance['show_thumbs'], 'on' ); ?> id="<?php echo $this->get_field_id('show_thumbs'); ?>" name="<?php echo $this->get_field_name('show_thumbs'); ?>" />
 		</p>
 		
 		<p>
 			<label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Enter thumbnails size:'); ?></label>
 			<input  id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" value="<?php echo $instance['width']; ?>" style="width:30px;" /> x 
			<input  id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="text" value="<?php echo $instance['height']; ?>" style="width:30px;" />
 		</p>
 		
 		<p>
 			<label for="<?php echo $this->get_field_id('blogs_ids'); ?>">Select Blogs:</label>
 			<select id="<?php echo $this->get_field_id('blogs_ids'); ?>" name="<?php echo $this->get_field_name('blogs_ids'); ?>[]" class="widefat" style="width:100%;height: 200px;" multiple="multiple">
				<?	 			
				
 				$blogs = get_last_updated();
 				foreach ($blogs AS $blog) 
 				{
			      echo '<option value="'.$blog['blog_id'].'" ' . ( in_array( $blog['blog_id'], (array) $instance['blogs_ids'] ) ? 'selected="selected"' : '' ) .'>' .  get_blog_details($blog['blog_id'])->blogname . '</option>';
			    }
 				?>
  			</select>
 		</p>
 		
		<p>
 			<label for="<?php echo $this->get_field_id('show_titles'); ?>"><?php _e('Show Blog titles?'); ?></label>
 			<input type="checkbox" class="checkbox" <?php checked( $instance['show_titles'], 'on' ); ?> id="<?php echo $this->get_field_id('show_titles'); ?>" name="<?php echo $this->get_field_name('show_titles'); ?>" />
 		</p>
 		
 		
 		<p>
 			<label for="<?php echo $this->get_field_id('author_credit'); ?>"><?php _e('Give credit to plugin author?'); ?></label>
 			<input type="checkbox" class="checkbox" <?php checked( $instance['author_credit'], 'on' ); ?> id="<?php echo $this->get_field_id('author_credit'); ?>" name="<?php echo $this->get_field_name('author_credit'); ?>" />
 		</p>
 		
 		<p>
 			<a href="<? echo site_url();?>/wp-admin/options-general.php?page=recent-mu-posts" title="Change style" target="_blank">Change style</a>
 		</p>
        <?php 

	} 
	 
	//function that display the widget 
	function display_widget($widget)
	{
		
		echo '<ul class="recent_mu_posts">';
		foreach ($widget['ids'] as $blog) {
			
			switch_to_blog($blog);
				if( $widget['show_titles'] ) :
				?>
				<li><h3><? echo get_blog_details( $blog )->blogname ;?></h3></li>
				<?
				endif;
				$lastposts = get_posts('numberposts='.$widget['total']);
				global $post;
				foreach($lastposts as $post) :
					setup_postdata($post);
					?>
					<li>
						<? the_post_thumbnail($widget['size']);?>
						<h4>
							<a href="<? the_permalink();?>" title="<? the_title();?>">
								<? the_title();?>
							</a>
						</h4>
						<p><? the_excerpt();?></p>
					</li>
				    <?
					
				endforeach;
			restore_current_blog();
		}
		if ( $widget['credit'] ) echo '<li> <span style="font-size:10px">By <a href="http://www.masquewordpress.com/ayuda" title="Ayuda Wordpress">Ayuda Wordpress</a></span></li>';
		
		echo '</ul>';
	
	}
	
	//function that save the widget
	function update($new_instance, $old_instance) 
	{
 			$instance['title'] = strip_tags($new_instance['title']);
 			$instance['total_number'] = strip_tags($new_instance['total_number']);
 			$instance['show_thumbs'] = $new_instance['show_thumbs'];
 			$instance['blogs_ids'] = $new_instance['blogs_ids'];
 			$instance['author_credit'] = $new_instance['author_credit'];
 			$instance['width'] = strip_tags($new_instance['width']);
 			$instance['height'] = strip_tags($new_instance['height']);
 			$instance['show_titles'] = $new_instance['show_titles'];
 			
 			return $instance;
 	}
 	
 	//function that register options 
 	 function register_options()
	{
		register_setting( 'rmp_options', 'rmp_option' );
		
		
	}
	
	
	//function that register the menu link in the settings menu	and editor section inside the option page
	 function register_menu()
	{
		add_options_page( 'Recent Mu Posts', 'Recent Mu Posts', 'manage_options', 'recent-mu-posts',array(&$this, 'options_page') );
		
		add_settings_section('bsbm_forms', 'Recent Mu Posts Style', array(&$this, 'style_box_form'), 'rmp_style_form');
	} 
	 
	 
	 //function that display the options page
	 function options_page()
	{
		?>
		<div class="metabox-holder">
	    
	    <?php screen_icon(); echo "<h2>". __( 'Recent Network Posts' ) ."</h2>"; ?>
	 
	   
	    
	   	<form method="post" action="?page=recent-mu-posts&updated=true" style="width:50%;" >
	 
	    <?php settings_fields( array(&$this,'register_menu' ) );?>
	    
	    <div class="postbox"><?php do_settings_sections( 'rmp_style_form' ); ?></div>
	    </form>
	    </div>
	<?
	
	}
	
	
	//function that display the textarea editor form
	function style_box_form()
	{
	
		$options = get_option('rmp_options');
		echo '<div class="inside"><div class="intro"><p>Customize the css used to display post and thumbnails.</p></div>';  
		
		echo '<fieldset>';
		echo '<dl><dd>';
		
		$file = stripslashes('wp-network-recent-posts/style.css');
		$plugin_files = get_plugin_files($file);
		$file = validate_file_to_edit($file, $plugin_files);
		$real_file = WP_PLUGIN_DIR . '/' . $file;
		
		if( isset($_POST['plugin_test_settings']['newcontent']) ) {
		    $newcontent = stripslashes($_POST['plugin_test_settings']['newcontent']);
		    if ( is_writeable($real_file) ) {
		            $f = fopen($real_file, 'w+');
		            fwrite($f, $newcontent);
		            fclose($f);
		    }
		}
		
		$content = file_get_contents( $real_file );
		
		$content = esc_textarea( $content ); 
		echo '<textarea style="display:block;margin:0 auto;width: 800px; height:465px;" name="plugin_test_settings[newcontent]" id="newcontent" tabindex="1">'.  $content .'</textarea>';
		echo '</dd></dl>';
		echo '</fieldset><div style="clear:both;"></div>';
		
		if (get_bloginfo('version') >= '3.1') { submit_button('Save Changes','secondary'); } else { echo '<input type="submit" name="submit" id="submit" class="button-secondary" value="Save Changes"  />'; }	
		echo '</div>';
	} 
	
	
	//function to load custom style of widget
	function load_style()
	{
		if(!is_admin()) wp_enqueue_style('wp-mu-posts-style',plugins_url( 'style.css' , __FILE__ ));
	
	}
	 
}


add_action('widgets_init', create_function('', 'return register_widget("mqw_recent_mu_posts");'));
