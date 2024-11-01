<?php
	/*-----------------------------------------------------------------------------------*/
	/*	CodeGrape Widget Class
	/*-----------------------------------------------------------------------------------*/
	
	class SM_CodeGrape_Widget extends WP_Widget {
	  
	  	var $cg_cats; //CodeGrape items categories
	  	var $exclude; //Whether to exclude items or not
	  	var $defaults;
	  
		function __construct() {
			$widget_ops = array('classname' => 'sm_codegrape_widget', 'description' => __('Display CodeGrape items with this widget', 'smart'));
			$control_ops = array('id_base' => 'sm_codegrape_widget');
			parent::__construct('sm_codegrape_widget', __('Smart CodeGrape Widget', 'smart'), $widget_ops, $control_ops);
			
			$this->cg_cats = array(
				array('name' => 'scripts-code', 'title' => 'Scripts & Cod'),
				array('name' => 'themes', 'title' => 'Themes'),
				array('name' => 'plugins', 'title' => 'Plugins'),
				array('name' => 'print', 'title' => 'Print'),
				array('name' => 'graphics', 'title' => 'Graphics'),
				array('name' => 'mobile-apps', 'title' => 'Mobile Apps')
			);
	
			$this->exclude = array();
			
			if(!is_admin()) {
			  add_action( 'wp_enqueue_scripts', array($this,'enqueue_styles'));
			}
	
			$this->defaults = array( 
				'title' => 'CodeGrape',
				'description' => '',
				'items_type' => array('javascript'),
				'items_from' => 'user',
				'user' => 'flashblue',
				'num_items' => 12,
				'orderby' => 'uploaded_on',
				'ref' => 'flashblue',
				'more_link_url' => 'http://www.codegrape.com/user/flashblue/portfolio?ref=flashblue',
				'more_link_txt' => __('View more','smart'),
				'order' => 'desc',
				'target' => '_blank',
				'exclude' => ''
			);
	
			//Allow themes or plugins to modify default parameters
			$this->defaults = apply_filters('sm_cg_widget_modify_defaults', $this->defaults);				
		}	
		
		function widget($args, $instance) {
			extract($args);
			
			$instance = wp_parse_args((array)$instance, $this->defaults);
			$title = apply_filters('widget_title', $instance['title']);
			
			echo $before_widget;
	
			if (!empty($title)) {
				echo $before_title.$title.$after_title;
			} ?>
			
			<?php if(!empty($instance['description'])) : ?>
				<p><?php echo nl2br($instance['description']);?></p>
			<?php endif; ?>
			
			<?php 
					
		  	if(isset($instance['exclude']) && !empty($instance['exclude'])) {
				$this->exclude = explode(',', $instance['exclude']);
				$this->exclude = array_map('absint', $this->exclude);
		  	}
		  
		  	$items = array();
			
		  	switch($instance['items_from']) {
				case 'latest': $items = $this->get_latest_items($instance['items_type']); break;
				default: 
					if(!empty($instance['user'])) {
						$users = array_map('trim',explode(',', $instance['user']));
						$items = $this->get_items_from_users($users, $instance['items_type']);
					} break;
		 	}
		  
			if(!empty($items)):
				$this->orderby = $instance['orderby'];
				$this->items_order = $instance['order'];
				
				if($this->orderby!='random') {
					usort($items, array($this, "cmp"));
				} else {
					shuffle($items);
				}
				
				$items = array_slice($items, 0, absint($instance['num_items']));
				$ref = !empty($instance['ref']) ? '?ref='.$instance['ref'] : ''; 
				$target = !empty($instance['target']) ? $instance['target'] : '_blank';
		?>
				<ul class="sm_codegrape_widget_ul">	
					<?php foreach($items as $item) : ?>
						<li><a href="<?php echo $item['url'].$ref; ?>" title="<?php echo $item['item']; ?>" target="<?php echo $target; ?>"><img src="<?php echo $item['thumbnail'];?>" alt="<?php echo $item['item']; ?> "/></a></li>
					<?php endforeach; ?>
			 	</ul>
				
			 	<?php if(!empty($instance['more_link_url'])): ?>				
			 		<?php $more_text = isset($instance['more_link_txt']) && !empty($instance['more_link_txt']) ? $instance['more_link_txt'] : __('View more', 'smart'); ?>
			  		<p class="sm_read_more"><a href="<?php echo esc_url($instance['more_link_url']); ?>" target="_blank" class="more"><?php echo  esc_html($more_text); ?></a></p>
			 	<?php endif; ?>
				
			<?php endif; ?>
			
			<?php
			echo $after_widget;
		}
	
		function update( $new_instance, $old_instance ) {
		  	$instance = $old_instance;
		  	$instance['title'] = strip_tags($new_instance['title']);
		  	$instance['description'] = strip_tags($new_instance['description']);
		  	$instance['user'] = strip_tags($new_instance['user']);
		  	$instance['num_items'] = absint($new_instance['num_items']);
		  	$instance['exclude'] = strip_tags($new_instance['exclude']);
		  	$instance['ref'] = strip_tags($new_instance['ref']);
		  	$instance['orderby'] = strip_tags($new_instance['orderby']);
		  	$instance['more_link_url'] = $new_instance['more_link_url'];
		 	$instance['more_link_txt'] = $new_instance['more_link_txt'];
		  	$instance['order'] = $new_instance['order'];
		  	$instance['items_type'] = $new_instance['items_type'];
		  	$instance['items_from'] = $new_instance['items_from'];
		  	$instance['target'] = $new_instance['target'];
			
		  	return $instance;
		}
	
		function form($instance) {	
			$instance = wp_parse_args( (array) $instance, $this->defaults); ?>
			
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'smart'); ?>:</label>
				<input id="<?php echo $this->get_field_id('title'); ?>" type="text" name="<?php echo $this->get_field_name( 'title'); ?>" value="<?php echo $instance['title']; ?>" class="widefat" />
			</p>
			
			<p>
				<label for="<?php echo $this->get_field_id('description'); ?>"><?php _e('Description', 'smart'); ?>:</label>
				<textarea id="<?php echo $this->get_field_id('description'); ?>" rows="5" name="<?php echo $this->get_field_name( 'description'); ?>" class="widefat"><?php echo $instance['description']; ?></textarea>
			</p>
			
			<p>
				<label><?php _e('Item categories to show', 'smart'); ?>:</label><br/>
				<?php foreach($this->cg_cats as $cat) : ?>
					<input id="<?php echo $this->get_field_id($cat['name'].'_id'); ?>" type="checkbox" name="<?php echo $this->get_field_name( 'items_type'); ?>[]" value="<?php echo $cat['name']; ?>" <?php echo in_array($cat['name'], $instance['items_type']) ? 'checked' : ''; ?> /> <label for="<?php echo $this->get_field_id($cat['name'].'_id'); ?>"><?php echo $cat['title']; ?></label><br/>
				<?php endforeach; ?>
		  	</p>
		  
		  	<p>
				<label><?php _e('Select items from', 'smart'); ?>:</label><br/>
				<input id="<?php echo $this->get_field_id('select_from_latest'); ?>" type="radio" name="<?php echo $this->get_field_name( 'items_from'); ?>" value="latest" <?php checked($instance['items_from'],'latest');?> /> <label for="<?php echo $this->get_field_id('select_from_latest'); ?>"><?php _e('Latest Items', 'smart'); ?></label><br/>
				<input id="<?php echo $this->get_field_id('select_from_user'); ?>" type="radio" name="<?php echo $this->get_field_name( 'items_from'); ?>" value="user" <?php checked($instance['items_from'],'user');?> /> <label for="<?php echo $this->get_field_id('select_from_user'); ?>"><?php _e('Specific User(s)', 'smart'); ?></label>
		  	</p>
			
			<p>
				<label for="<?php echo $this->get_field_id('user'); ?>"><?php _e('CodeGrape username(s)', 'smart'); ?>:</label>
				<input id="<?php echo $this->get_field_id('user'); ?>" type="text" name="<?php echo $this->get_field_name( 'user'); ?>" value="<?php echo strip_tags($instance['user']); ?>" class="widefat" />
			  	<small class="howto"><?php _e('For multiple users, separate by comma: i.e. user1,user2,user3', 'smart'); ?></small>
			</p>	
			
			<p>
				<label for="<?php echo $this->get_field_id('num_items'); ?>"><?php _e('Number of items to show', 'smart'); ?>:</label>
				<input id="<?php echo $this->get_field_id('num_items'); ?>" type="text" name="<?php echo $this->get_field_name( 'num_items'); ?>" value="<?php echo absint($instance['num_items']); ?>" class="widefat" />
			</p>
			
			<p>
				<label><?php _e('Order by', 'smart'); ?>:</label>
					<select id="<?php echo $this->get_field_id('orderby'); ?>" name="<?php echo $this->get_field_name( 'orderby'); ?>" value="<?php echo esc_attr($instance['orderby']); ?>" class="widefat" >
						<option value="uploaded_on" <?php selected($instance['orderby'],'uploaded_on');?>><?php _e('Upload date', 'smart'); ?></option>
						<option value="last_update" <?php selected($instance['orderby'],'last_update');?>><?php _e('Last update', 'smart'); ?></option>
						<option value="sales" <?php selected($instance['orderby'],'sales');?>><?php _e('Number of sales', 'smart'); ?></option>
						<option value="cost" <?php selected($instance['orderby'],'cost');?>><?php _e('Price', 'smart'); ?></option>
						<option value="random" <?php selected($instance['orderby'],'random');?>><?php _e('Random', 'smart'); ?></option>
				</select>
			</p>
			
			<p>
				<input id="<?php echo $this->get_field_id('order_asc'); ?>" type="radio" name="<?php echo $this->get_field_name( 'order'); ?>" value="asc" <?php checked($instance['order'],'asc');?> /> <label for="<?php echo $this->get_field_id('order_asc'); ?>"><?php _e('Ascending', 'smart'); ?></label>
				<input id="<?php echo $this->get_field_id('order_desc'); ?>" type="radio" name="<?php echo $this->get_field_name( 'order'); ?>" value="desc" <?php checked($instance['order'],'desc');?> /> <label for="<?php echo $this->get_field_id('order_desc'); ?>"><?php _e('Descending', 'smart'); ?></label>
			</p>
	
			<p>
				<label for="<?php echo $this->get_field_id('exclude'); ?>"><?php _e('Exclude item(s)', 'smart'); ?>:</label>
				<input id="<?php echo $this->get_field_id('exclude'); ?>" type="text" name="<?php echo $this->get_field_name( 'exclude'); ?>" value="<?php echo strip_tags($instance['exclude']); ?>" class="widefat" />
			  	<small class="howto"><?php _e('Specify item ID to exclude specific item (separate by comma for multiple items): i.e. 8134834,7184572', 'smart'); ?></small>
			</p>
			
			<p>
				<label for="<?php echo $this->get_field_id('ref'); ?>"><?php _e('Referral user', 'smart'); ?>:</label>
				<input id="<?php echo $this->get_field_id('ref'); ?>" type="text" name="<?php echo $this->get_field_name( 'ref'); ?>" value="<?php echo strip_tags($instance['ref']); ?>" class="widefat" />
				<small class="howto"><?php _e('Specify username if you want to use items as CodeGrape affiliate links', 'smart'); ?></small>
			</p>		
			
			<p>
				<label for="<?php echo $this->get_field_id('more_link_url'); ?>"><?php _e('More link URL', 'smart'); ?>:</label>
				<input id="<?php echo $this->get_field_id('more_link_url'); ?>" type="text" name="<?php echo $this->get_field_name( 'more_link_url'); ?>" value="<?php echo esc_attr($instance['more_link_url']); ?>" class="widefat" />
				<small class="howto"><?php _e('Specify URL if you want to show "more" link under the items list', 'smart'); ?></small>
			</p>
	
			<p>
				<label for="<?php echo $this->get_field_id('more_link_txt'); ?>"><?php _e('More link text', 'smart'); ?>:</label>
				<input id="<?php echo $this->get_field_id('more_link_txt'); ?>" type="text" name="<?php echo $this->get_field_name( 'more_link_txt'); ?>" value="<?php echo esc_attr($instance['more_link_txt']); ?>" class="widefat" />
			</p>
			
			<p>
				<label for="<?php echo $this->get_field_id('target'); ?>"><?php _e('Open items in', 'smart'); ?>: </label>
				<select id="<?php echo $this->get_field_id('target'); ?>" name="<?php echo $this->get_field_name( 'target'); ?>">
					<option value="_blank" <?php selected('_blank',$instance['target']); ?>><?php _e('New Window', 'smart'); ?></option>
					<option value="_self" <?php selected('_self',$instance['target']); ?>><?php _e('Same Window', 'smart'); ?></option>
				</select>
			</p>
			
		<?php
		}
		
		function get_items_from_users($users=array('flashblue'), $type=array('wordpress')) {			
			$items = array();
			
			foreach($users as $user) {
				$cached = get_transient($this->id_base.'_'.$user);
				
				if(empty($cached)) {					
					$api_url = 'http://www.codegrape.com/api/new-files-from-user:'.$user.'.json';
					$response = wp_remote_get( $api_url);
				
					if (is_wp_error($response) || (wp_remote_retrieve_response_code($response)!=200)) {  
						continue;
					}  
				   
					$item_data = json_decode( wp_remote_retrieve_body( $response ), true);
	
					if(isset($item_data['new-files-from-user']) && !empty($item_data['new-files-from-user'])) {
						$item_data_ready = $item_data['new-files-from-user'];
						
						//Cache data for one day
						set_transient($this->id_base.'_'.$user, $item_data_ready, HOUR_IN_SECONDS*4);
					} else {
						$item_data_ready = array();
					}				
				} else {
					$item_data_ready = $cached;
				} 
				
				$type_check = (count($type)==count($this->cg_cats) ? false : true);
				
				foreach($item_data_ready as $item) {
					if(!in_array($item['id'], $this->exclude) ) {
						if($type_check) {
							if($this->item_type_check(trim($item['category']), $type)) {
								$items[] = $item;
							}
						} else {
							$items[] = $item;
						}
					}
				}
		  }	
		
		return $items;		  
	 }
	 
	 function get_latest_items($types=array('javascript')) {			
		$items = array();
		
		foreach($types as $type) {
			$cached = get_transient('smart-codegrape-widget-'.$type);
			
			if(empty($cached)) {				
				$api_url = 'http://www.codegrape.com/api/new-files:'.$type.'.json';
				
				$response = wp_remote_get($api_url);  
			
				if (is_wp_error($response) || (wp_remote_retrieve_response_code($response)!=200)) {  
					continue;
				}  
			   
				$item_data = json_decode(wp_remote_retrieve_body($response), true);
				
				if(isset($item_data['new-files']) && !empty($item_data['new-files'])) {
					$item_data_ready = $item_data['new-files'];
					
					//Cache data for one day
					set_transient('smart-codegrape-widget-'.$type, $item_data_ready, HOUR_IN_SECONDS*4);
				} else {
					$item_data_ready = array();
				}
			
			} else {
				$item_data_ready = $cached;
			} 				
					
			foreach($item_data_ready as $item) {
				if(!in_array($item['id'], $this->exclude)) {
					$items[] = $item;
				}
			}
		}
		
		return $items;		  
	 }
	 
		function item_type_check($category, $types) {		
			foreach($types as $type) {
				if(strpos('sm'.$category, $type)) {
					return true;
				}
			}
			
			return false;
		}
	 
		function enqueue_styles() {
			wp_register_style('smartcodegrapewidget', SCW_PLUGIN_URI.'css/style.css', false, SM_CG_WIDGET_VER);
			wp_enqueue_style('smartcodegrapewidget');
		}
	 
		function cmp($a, $b) {
			if($this->orderby=='last_update' || $this->orderby=='uploaded_on') {
				if($this->items_order=='desc') {
					return strcmp(strtotime($b[$this->orderby]), strtotime($a[$this->orderby]));
				} else {
					return strcmp(strtotime($a[$this->orderby]), strtotime($b[$this->orderby]));
				}
			} else {
				if($this->items_order=='desc') {
					return $b[$this->orderby]>$a[$this->orderby] ? true : false;
				} else {
					return $b[$this->orderby]>$a[$this->orderby] ? false : true;
				}			
			}
		}
	
	}
?>