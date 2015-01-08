<?php

/*
/* Feature widget module
============================================*/

class WPZOOM_Feature_Box extends WP_Widget {
    private $icon_list = array('glass', 'music', 'search', 'envelope-o', 'heart', 'star', 'star-o', 'user', 'film', 'th-large', 'th', 'th-list', 'check', 'times', 'search-plus', 'search-minus', 'power-off', 'signal', 'cog', 'trash-o', 'home', 'file-o', 'clock-o', 'road', 'download', 'arrow-circle-o-down', 'arrow-circle-o-up', 'inbox', 'play-circle-o', 'repeat', 'refresh', 'list-alt', 'lock', 'flag', 'headphones', 'volume-off', 'volume-down', 'volume-up', 'qrcode', 'barcode', 'tag', 'tags', 'book', 'bookmark', 'print', 'camera', 'font', 'bold', 'italic', 'text-height', 'text-width', 'align-left', 'align-center', 'align-right', 'align-justify', 'list', 'outdent', 'indent', 'video-camera', 'picture-o', 'pencil', 'map-marker', 'adjust', 'tint', 'pencil-square-o', 'share-square-o', 'check-square-o', 'arrows', 'step-backward', 'fast-backward', 'backward', 'play', 'pause', 'stop', 'forward', 'fast-forward', 'step-forward', 'eject', 'chevron-left', 'chevron-right', 'plus-circle', 'minus-circle', 'times-circle', 'check-circle', 'question-circle', 'info-circle', 'crosshairs', 'times-circle-o', 'check-circle-o', 'ban', 'arrow-left', 'arrow-right', 'arrow-up', 'arrow-down', 'share', 'expand', 'compress', 'plus', 'minus', 'asterisk', 'exclamation-circle', 'gift', 'leaf', 'fire', 'eye', 'eye-slash', 'exclamation-triangle', 'plane', 'calendar', 'random', 'comment', 'magnet', 'chevron-up', 'chevron-down', 'retweet', 'shopping-cart', 'folder', 'folder-open', 'arrows-v', 'arrows-h', 'bar-chart-o', 'twitter-square', 'facebook-square', 'camera-retro', 'key', 'cogs', 'comments', 'thumbs-o-up', 'thumbs-o-down', 'star-half', 'heart-o', 'sign-out', 'linkedin-square', 'thumb-tack', 'external-link', 'sign-in', 'trophy', 'github-square', 'upload', 'lemon-o', 'phone', 'square-o', 'bookmark-o', 'phone-square', 'twitter', 'facebook', 'github', 'unlock', 'credit-card', 'rss', 'hdd-o', 'bullhorn', 'bell', 'certificate', 'hand-o-right', 'hand-o-left', 'hand-o-up', 'hand-o-down', 'arrow-circle-left', 'arrow-circle-right', 'arrow-circle-up', 'arrow-circle-down', 'globe', 'wrench', 'tasks', 'filter', 'briefcase', 'arrows-alt', 'users', 'link', 'cloud', 'flask', 'scissors', 'files-o', 'paperclip', 'floppy-o', 'square', 'bars', 'list-ul', 'list-ol', 'strikethrough', 'underline', 'table', 'magic', 'truck', 'pinterest', 'pinterest-square', 'google-plus-square', 'google-plus', 'money', 'caret-down', 'caret-up', 'caret-left', 'caret-right', 'columns', 'sort', 'sort-asc', 'sort-desc', 'envelope', 'linkedin', 'undo', 'gavel', 'tachometer', 'comment-o', 'comments-o', 'bolt', 'sitemap', 'umbrella', 'clipboard', 'lightbulb-o', 'exchange', 'cloud-download', 'cloud-upload', 'user-md', 'stethoscope', 'suitcase', 'bell-o', 'coffee', 'cutlery', 'file-text-o', 'building-o', 'hospital-o', 'ambulance', 'medkit', 'fighter-jet', 'beer', 'h-square', 'plus-square', 'angle-double-left', 'angle-double-right', 'angle-double-up', 'angle-double-down', 'angle-left', 'angle-right', 'angle-up', 'angle-down', 'desktop', 'laptop', 'tablet', 'mobile', 'circle-o', 'quote-left', 'quote-right', 'spinner', 'circle', 'reply', 'github-alt', 'folder-o', 'folder-open-o', 'smile-o', 'frown-o', 'meh-o', 'gamepad', 'keyboard-o', 'flag-o', 'flag-checkered', 'terminal', 'code', 'reply-all', 'mail-reply-all', 'star-half-o', 'location-arrow', 'crop', 'code-fork', 'chain-broken', 'question', 'info', 'exclamation', 'superscript', 'subscript', 'eraser', 'puzzle-piece', 'microphone', 'microphone-slash', 'shield', 'calendar-o', 'fire-extinguisher', 'rocket', 'maxcdn', 'chevron-circle-left', 'chevron-circle-right', 'chevron-circle-up', 'chevron-circle-down', 'html5', 'css3', 'anchor', 'unlock-alt', 'bullseye', 'ellipsis-h', 'ellipsis-v', 'rss-square', 'play-circle', 'ticket', 'minus-square', 'minus-square-o', 'level-up', 'level-down', 'check-square', 'pencil-square', 'external-link-square', 'share-square', 'compass', 'caret-square-o-down', 'caret-square-o-up', 'caret-square-o-right', 'eur', 'gbp', 'usd', 'inr', 'jpy', 'rub', 'krw', 'btc', 'file', 'file-text', 'sort-alpha-asc', 'sort-alpha-desc', 'sort-amount-asc', 'sort-amount-desc', 'sort-numeric-asc', 'sort-numeric-desc', 'thumbs-up', 'thumbs-down', 'youtube-square', 'youtube', 'xing', 'xing-square', 'youtube-play', 'dropbox', 'stack-overflow', 'instagram', 'flickr', 'adn', 'bitbucket', 'bitbucket-square', 'tumblr', 'tumblr-square', 'long-arrow-down', 'long-arrow-up', 'long-arrow-left', 'long-arrow-right', 'apple', 'windows', 'android', 'linux', 'dribbble', 'skype', 'foursquare', 'trello', 'female', 'male', 'gittip', 'sun-o', 'moon-o', 'archive', 'bug', 'vk', 'weibo', 'renren', 'pagelines', 'stack-exchange', 'arrow-circle-o-right', 'arrow-circle-o-left', 'caret-square-o-left', 'dot-circle-o', 'wheelchair', 'vimeo-square', 'try');

  public function __construct() {

    parent::__construct(
      'wpzoom-feature-box',
      __( 'Feature', 'zoom-builder' ),
      array(
        'description' => '',
        'wpzlb_widget' => true
      ),
      array(
        'width' => 630,
        'height' => 800
      )
    );

  }

    public function widget( $args, $instance ) {
        extract( $args );

        $title = isset( $instance['title'] ) && !empty( $instance['title'] ) ? apply_filters( 'widget_title', trim( $instance['title'] ), $instance, $this->id_base ) : '';
        $desc = isset( $instance['description'] ) && !empty( $instance['description'] ) ? apply_filters( 'widget_text', trim( $instance['description'] ), $instance ) : '';
        $caption_align = esc_attr($instance['caption_align']);

        /* Before widget (defined by themes). */
        echo $before_widget;

        echo $this->feature_box_tpl($instance);

        if ( $title || $desc ) {

            echo '<div class="inner-content align-' .$caption_align . '">';

                if ( $title ) echo '<h4>' . $title . '</h4>';

                if ( $desc ) echo '<span class="description">' . nl2br($desc) . '</span>';

            echo '</div>';

        }

        /* After widget (defined by themes). */
        echo $after_widget;
    }


    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;

        $instance['title'] = strip_tags( $new_instance['title'] );
        $instance['description'] =  $new_instance['description'];
        $instance['caption_align'] = strip_tags($new_instance['caption_align']);


    $instance['selected-tab'] = $new_instance['selected-tab'] == 'image' ? 'image' : 'icon';

        $instance['icon-name'] = esc_attr( $new_instance['icon-name'] );
        $instance['img-icon'] = intval( $new_instance['img-icon'] );
        $instance['img-icon-size'] = esc_attr( trim( $new_instance['img-icon-size'] ) );
        $instance['img-icon-align'] = $new_instance['img-icon-align'] == 'left' || $new_instance['img-icon-align'] == 'center' || $new_instance['img-icon-align'] == 'right' ? $new_instance['img-icon-align'] : 'none';

        if ( preg_match( '/#[a-f0-9]{6}/i', trim( $new_instance['background'] ) ) ) {
            $instance['background'] = $new_instance['background'];
        }

        if ( preg_match( '/#[a-f0-9]{6}/i', trim( $new_instance['color'] ) ) ) {
            $instance['color'] = $new_instance['color'];
        }

        if ( preg_match( '/[0-9]+px/i', trim( $new_instance['roundness'] ) ) ) {
            $instance['roundness'] = $new_instance['roundness'];
        }

        if ( preg_match( '/[0-9]+px/i', trim( $new_instance['width'] ) ) ) {
            $instance['width'] = $new_instance['width'];
        }

        if ( preg_match( '/[0-9]+px/i', trim( $new_instance['icon-size'] ) ) ) {
            $instance['icon-size'] = $new_instance['icon-size'];
        }

        return $instance;
    }

    public function form( $instance ) {

        /* Set up some default widget settings. */
        $defaults = array( 'title' => '', 'description' => '', 'caption_align' => 'left', 'selected-tab' => 'icon', 'background' => '#0087D4', 'color' => '#ffffff', 'roundness' => '4px', 'width' => '300px',  'icon-size' => '60px', 'icon-name' => 'icon-smile', 'img-icon' => '', 'img-icon-size' => 'medium', 'img-icon-align' => 'none' );
        $instance = wp_parse_args( (array) $instance, $defaults );
        $caption_align = esc_attr($instance['caption_align']);

        ?>
        <p>
        <fieldset>

            <legend>Content</legend>

            <p>
                <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Feature Title:', 'zoom-builder' ); ?></label><br />
                <input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" type="text" class="widefat" />
            </p>

            <p>
                <label for="<?php echo $this->get_field_id( 'description' ); ?>"><?php _e( 'Feature Description:', 'zoom-builder' ); ?></label><br />
                <textarea id="<?php echo $this->get_field_id( 'description' ); ?>" name="<?php echo $this->get_field_name( 'description' ); ?>" rows="4" class="widefat"><?php echo esc_textarea($instance['description']); ?></textarea>
             </p>

            <p>
                <label for="<?php echo $this->get_field_id( 'caption_align' ); ?>"><?php _e( 'Text Align:', 'zoom-builder' ); ?></label>
                <select id="<?php echo $this->get_field_id( 'caption_align' ); ?>" name="<?php echo $this->get_field_name( 'caption_align' ); ?>">
                <option value="left" <?php selected( $caption_align, 'left' ); ?>><?php _e( 'Left', 'zoom-builder' ); ?></option>
                <option value="right" <?php selected( $caption_align, 'right' ); ?>><?php _e( 'Right', 'zoom-builder' ); ?></option>
                <option value="center" <?php selected( $caption_align, 'center' ); ?>><?php _e( 'Center', 'zoom-builder' ); ?></option>
                </select><br/>
            </p>

        </fieldset>
      </p>


        <script>var wpzoom_feature_box_ctrl_id = "<?php echo $this->id; ?>";</script>
        <div class="wpzoom-feature-box-tabs">
          <input type="hidden" name="<?php echo $this->get_field_name( 'selected-tab' ); ?>" id="<?php echo $this->get_field_id( 'selected-tab' ); ?>" class="selected-tab" value="<?php echo $instance['selected-tab'] == 'image' ? 'image' : 'icon'; ?>" />
          <ul class="tab-nav">
            <li class="tab-icon<?php echo $instance['selected-tab'] != 'image' ? ' ui-tabs-active' : ''; ?>"><a href="#<?php echo $this->id; ?>-tab-icon"><?php _e('Icon', 'zoom-builder'); ?></a></li>
            <li class="tab-image<?php echo $instance['selected-tab'] == 'image' ? ' ui-tabs-active' : ''; ?>"><a href="#<?php echo $this->id; ?>-tab-image"><?php _e('Image', 'zoom-builder'); ?></a></li>
          </ul>

          <ul class="tab-tabs">
            <li id="<?php echo $this->id; ?>-tab-icon">
              <table class="wpzoom-feature-box-controls" width="100%">
                  <tr>
                      <td colspan="2">
                          <p><strong><?php _e('Select an icon', 'zoom-builder'); ?>:</strong></p>
                          <div style="height:88px;overflow:scroll; padding:10px 0 0 15px; background:#fff;">
                              <?php foreach ($this->icon_list as $icon) : ?>
                                  <a href="#" class="icon-btn">
                                      <i class="fa fa-<?php echo $icon; ?>"></i>
                                  </a>
                              <?php endforeach; ?>
                          </div>
                      </td>
                  </tr>
                  <tr>
                      <td width="270px">
                           <p style="margin-top: 10px">
                              <label for="<?php echo $this->get_field_id( 'color' ); ?>">
                                  <strong><?php _e( 'Icon Color:', 'zoom-builder' ); ?></strong>
                              </label><br />
                              <input id="<?php echo $this->get_field_id( 'color' ); ?>" name="<?php echo $this->get_field_name( 'color' ); ?>" value="<?php echo $instance['color']; ?>" type="text" class="feature-box-color-picker widefat" />
                          </p>
                          <p>
                              <label for="<?php echo $this->get_field_id( 'background' ); ?>">
                                  <strong><?php _e( 'Background:', 'zoom-builder' ); ?></strong>
                              </label><br />
                              <input id="<?php echo $this->get_field_id( 'background' ); ?>" name="<?php echo $this->get_field_name( 'background' ); ?>" value="<?php echo $instance['background']; ?>" type="text" class="feature-box-background-picker widefat" />
                          </p>
                          <p>
                              <label for="<?php echo $this->get_field_id( 'roundness' ); ?>"><strong><?php _e( 'Roundness:', 'zoom-builder' ); ?></strong></label>
                              <input id="<?php echo $this->get_field_id( 'roundness' ); ?>" name="<?php echo $this->get_field_name( 'roundness' ); ?>" value="<?php echo $instance['roundness']; ?>" type="text" size="6" />
                              <div class="fbbrs-wrap"><div class="feature-box-border-radius-slider" data-bindedto="<?php echo $this->get_field_id( 'roundness' ); ?>" data-default="<?php echo $instance['roundness']; ?>"></div></div>
                          </p>

                          <p>
                              <label for="<?php echo $this->get_field_id( 'width' ); ?>"><strong><?php _e( 'Width:', 'zoom-builder' ); ?></strong></label>
                              <input id="<?php echo $this->get_field_id( 'width' ); ?>" name="<?php echo $this->get_field_name( 'width' ); ?>" value="<?php echo $instance['width']; ?>" type="text" size="6" />
                              <div class="fbbrs-wrap"><div class="feature-box-width-slider" data-bindedto="<?php echo $this->get_field_id( 'width' ); ?>" data-default="<?php echo $instance['width']; ?>"></div></div>
                          </p>

                          <p style="margin-top: 10px">
                              <label for="<?php echo $this->get_field_id( 'icon-size' ); ?>"><strong><?php _e( 'Icon Size:', 'zoom-builder' ); ?></strong></label>
                              <input id="<?php echo $this->get_field_id( 'icon-size' ); ?>" name="<?php echo $this->get_field_name( 'icon-size' ); ?>" value="<?php echo $instance['icon-size']; ?>" type="text" size="6" />
                              <div class="fbbrs-wrap"><div class="feature-box-icon-size-slider" data-bindedto="<?php echo $this->get_field_id( 'icon-size' ); ?>" data-default="<?php echo $instance['icon-size']; ?>"></div></div>
                          </p>
                          <input class="icon-name-hidden-field" id="<?php echo $this->get_field_id( 'icon-name' ); ?>" name="<?php echo $this->get_field_name( 'icon-name' ); ?>" value="<?php echo $instance['icon-name']; ?>" type="hidden" />
                      </td>
                      <td width="330px" style="vertical-align: top">
                          <div class="wpzoom-feature-box-preview-wrap" style="padding: 0 15px;">
                              <p><?php _e( 'Preview', 'zoom-builder' ); ?></p>
                              <?php echo $this->feature_box_tpl($instance, true); ?>
                          </div>
                      </td>
                  </tr>
              </table>
            </li>

            <li id="<?php echo $this->id; ?>-tab-image">
              <p><?php _e( 'Upload an image or icon:', 'zoom-builder' ); ?> <input id="<?php echo $this->get_field_id( 'img-icon' ); ?>" name="<?php echo $this->get_field_name( 'img-icon' ); ?>" type="hidden" class="upload featured-box-upload-value" value="<?php echo intval( $instance['img-icon'] ) > 0 ? intval( $instance['img-icon'] ) : ''; ?>" /><input id="<?php echo $this->get_field_id( 'img-icon-size' ); ?>" name="<?php echo $this->get_field_name( 'img-icon-size' ); ?>" type="hidden" class="feature-box-upload-icon-size" value="<?php echo esc_attr( trim( $instance['img-icon-size'] ) ); ?>" /><input id="<?php echo $this->get_field_id( 'img-icon-align' ); ?>" name="<?php echo $this->get_field_name( 'img-icon-align' ); ?>" type="hidden" class="feature-box-upload-icon-align" value="<?php echo esc_attr( trim( $instance['img-icon-align'] ) ); ?>" /><input class="feature-box-upload-icon button" type="button" value="<?php _e( 'Upload', 'zoom-builder' ); ?>" /> <a href="" class="feature-box-upload-clear<?php echo intval( $instance['img-icon'] ) <= 0 ? ' disabled' : ''; ?>"><?php _e( 'Clear', 'zoom-builder' ); ?></a></p>
              <p><img src="<?php echo isset( $instance['img-icon'] ) && intval( $instance['img-icon'] ) > 0 && false !== ( $img = wp_get_attachment_image_src( intval( $instance['img-icon'] ), 'thumbnail' ) ) && is_array( $img ) ? $img[0] : ''; ?>" class="wpzoom-feature-box-image-preview"></p>
            </li>
          </ul>
        </div>





        <?php
    }

    private function feature_box_tpl( $options, $is_admin = false ) {
        $r = '';

        if ( !$is_admin && isset( $options['img-icon'] ) && intval( $options['img-icon'] ) > 0 && false !== ( $img = wp_get_attachment_image_src( intval( $options['img-icon'] ), isset( $options['img-icon-size'] ) ? trim( $options['img-icon-size'] ) : 'medium' ) ) && is_array( $img ) ) {
            $r.= '<div' . ( isset( $options['img-icon-align'] ) && ( $options['img-icon-align'] == 'left' || $options['img-icon-align'] == 'center' || $options['img-icon-align'] == 'right' ) ? ' class="image-align-' . $options['img-icon-align'] . '"' : '' ) . '>';
            $r.= '   <img src="' . $img[0] . '">';
            $r.= '</div>';
        } else {
            $r.= '<div class="zoom-icon" style="margin:0 auto 15px; background: ' . $options['background'] . '; color: ' . $options['color'] . '; border-radius: ' . $options['roundness'] . '; width: ' . $options['width'] . '; text-align: center; font-size: ' . $options['icon-size'] . '; line-height: 140px;">';
            $r.= '   <i class="' . $options['icon-name'] . '"></i>';
            $r.= '</div>';
        }

        return $r;
    }

    public static function init() {
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'frontend_css' ) );
    }

    public static function admin_init() {
        if ( !ZOOM_Builder_Utils::screen_is_builder() || !current_user_can( 'edit_posts' ) ) return;

        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
        add_action( 'admin_head', array( __CLASS__, 'scripts' ) );
    }

    public static function scripts() {

			?><script>

				jQuery(function($){

					var WPZFeatureBoxIconSelect,
					    uploader,
					    fontColorPickerOptions = ( typeof wpzlb_color_picker_palettes != 'undefined' ) ? { palettes: wpzlb_color_picker_palettes } : {},
					    backgroundColorPickerOptions = ( typeof wpzlb_color_picker_palettes != 'undefined' ) ? { palettes: wpzlb_color_picker_palettes } : {};

					fontColorPickerOptions.change = function(e,ui){
						var preview = $(this).parents('.wpzoom-feature-box-controls').find('.wpzoom-feature-box-preview-wrap').find('> div');
						preview.css({ color: ui.color.toCSS() });
					};
					backgroundColorPickerOptions.change = function(e,ui){
						var preview = $(this).parents('.wpzoom-feature-box-controls').find('.wpzoom-feature-box-preview-wrap').find('> div');
						preview.css({ background: ui.color.toCSS() });
					};

					WPZFeatureBoxIconSelect = wp.media.view.MediaFrame.WPZFeatureBoxIconSelect = wp.media.view.MediaFrame.Select.extend({
						createStates: function(){
							var options = this.options;

							this.states.add([
								new wp.media.controller.Library({
									library: wp.media.query( options.library ),
									multiple: options.multiple,
									title: options.title,
									priority: 20,
									editable: true,
									allowLocalEdits: true,
									displaySettings: true,
									displayUserSettings: true
								})
							]);
						}
					});

					$('#wpzlb #widgets-right .widgets-holder-wrap')
						.on('click', '.wpzoom-builder-widget-feature-box .feature-box-upload-icon', function(e){
							var self = this;

							e.preventDefault();

							if ( typeof(uploader) !== 'undefined' ) {
								uploader.close();
							}

							uploader = wp.media.frames.wpzoom_uploader = new WPZFeatureBoxIconSelect({
								title: '<?php _e( 'Feature Icon', 'zoom-builder' ); ?>',
								library: { type: 'image' },
								button: { text: '<?php _e( 'Use this icon', 'zoom-builder' ); ?>' },
								multiple: false
							});

							uploader.on('open', function(){
								var selection = uploader.state().get('selection');
								attachment = wp.media.attachment( parseInt( $(self).siblings('.upload').val(), 10 ) );
								attachment.fetch();
								selection.add( attachment ? [ attachment ] : [] );
							});

							uploader.on('select', function(){
								var attachment = uploader.state().get('selection').first().toJSON(),
								    size = getUserSetting('imgsize', 'medium'),
								    align = getUserSetting('align', 'none'),
								    thumb_url = attachment.sizes && attachment.sizes['thumbnail'] ? attachment.sizes['thumbnail'].url : attachment.url,
								    image = /(^.*\.jpg|jpeg|png|gif|ico*)/gi;

								if ( !attachment.url.match(image) ) {
									alert("<?php _e( 'You can\'t use this type of file as image.', 'zoom-builder' ); ?>");
								}

								$(self).siblings('.upload').val(attachment.id);
								$(self).siblings('.feature-box-upload-icon-size').val(attachment.sizes && attachment.sizes[size] ? size : 'medium');
								$(self).siblings('.feature-box-upload-icon-align').val(align == 'left' || align == 'center' || align == 'right' ? align : 'none');
								$(self).closest('li').find('img.wpzoom-feature-box-image-preview').attr('src', thumb_url);
								$(self).siblings('a.feature-box-upload-clear').removeClass('disabled');
							});

							uploader.open();

							return false;
						})
						.on('click', '.wpzoom-builder-widget-feature-box a.feature-box-upload-clear', function(e){
							e.preventDefault();
							if ( $(this).hasClass('disabled') ) return;
							$(this).addClass('disabled').siblings('.upload').val('');
							$(this).siblings('.feature-box-upload-icon-size').val('medium');
							$(this).siblings('.feature-box-upload-icon-align').val('none');
							$(this).closest('li').find('img.wpzoom-feature-box-image-preview').attr('src', '');
						})
						.on('click', '.wpzoom-builder-widget-feature-box .wpzoom-feature-box-controls .icon-btn', function(e){
							e.preventDefault();

							$(this).parents('.wpzoom-feature-box-controls')
								.find('.wpzoom-feature-box-preview-wrap div')
								.html($(this).html());

							$(this).parents('.wpzoom-feature-box-controls')
								.find('.icon-name-hidden-field')
								.val($(this).find('i').attr('class'));

							$(this).parents('.wpzoom-feature-box-controls')
								.find('.featured-box-upload-value')
								.val('');
						});

					function update_me() {
						$('#wpzlb #widgets-right .wpzoom-builder-widget-feature-box .feature-box-border-radius-slider:not(.ui-slider)').slider({
							range: 'max',
							min: 0,
							max: 100,
							create: function(event, ui){
								$(this).slider('option', 'value', $(this).data('default').replace('px', ''));
								var self = this,
								    preview = $(this).parents('.wpzoom-feature-box-controls').find('.wpzoom-feature-box-preview-wrap').find('> div');

								$('#' + $(this).data('bindedto')).change(function(){
									$(self).slider('option', 'value', $(this).val().replace('px', ''));
									preview.css({ borderRadius: $(this).val() });
								});
							},
							slide: function(event, ui){
								var preview = $(this).parents('.wpzoom-feature-box-controls').find('.wpzoom-feature-box-preview-wrap').find('> div');
								preview.css({ borderRadius: ui.value + 'px' });
								$('#' + $(this).data('bindedto')).val( ui.value + 'px' );
							}
						});


            $('#wpzlb #widgets-right .wpzoom-builder-widget-feature-box .feature-box-width-slider:not(.ui-slider)').slider({
              range: 'max',
              min: 0,
              max: 500,
              create: function(event, ui){
                $(this).slider('option', 'value', $(this).data('default').replace('px', ''));
                var self = this,
                    preview = $(this).parents('.wpzoom-feature-box-controls').find('.wpzoom-feature-box-preview-wrap').find('> div');

                $('#' + $(this).data('bindedto')).change(function(){
                  $(self).slider('option', 'value', $(this).val().replace('px', ''));
                  preview.css({ width: $(this).val() });
                });
              },
              slide: function(event, ui){
                var preview = $(this).parents('.wpzoom-feature-box-controls').find('.wpzoom-feature-box-preview-wrap').find('> div');
                preview.css({ width: ui.value + 'px' });
                $('#' + $(this).data('bindedto')).val( ui.value + 'px' );
              }
            });



						$('#wpzlb #widgets-right .wpzoom-builder-widget-feature-box .feature-box-icon-size-slider:not(.ui-slider)').slider({
							range: 'max',
							min: 20,
							max: 200,
							create: function(event, ui) {
								var self = this,
								    preview = $(this).parents('.wpzoom-feature-box-controls').find('.wpzoom-feature-box-preview-wrap').find('> div');

								$(this).slider('option', 'value', $(this).data('default').replace('px', ''));

								$('#' + $(this).data('bindedto')).change(function(){
									$(self).slider('option', 'value', $(this).val().replace('px', ''));
									preview.css({ fontSize: $(this).val() });
								});
							},
							slide: function(event, ui){
								var preview = $(this).parents('.wpzoom-feature-box-controls').find('.wpzoom-feature-box-preview-wrap').find('> div');
								preview.css({ fontSize: ui.value + 'px' });
								$('#' + $(this).data('bindedto')).val(ui.value+'px');
							}
						});

						$('#wpzlb #widgets-right .wpzoom-builder-widget-feature-box .feature-box-color-picker:not(.wp-color-picker)').wpColorPicker(fontColorPickerOptions);
						$('#wpzlb #widgets-right .wpzoom-builder-widget-feature-box .feature-box-background-picker:not(.wp-color-picker)').wpColorPicker(backgroundColorPickerOptions);

						$('#wpzlb #widgets-right .wpzoom-builder-widget-feature-box .wpzoom-feature-box-tabs:not(.ui-tabs)').each(function(){
							$(this).tabs({
								active: $(this).find('ul.tab-nav li.ui-tabs-active').index(),
								beforeActivate: function(e,ui){ $(this).find('input.selected-tab').val( ui.newTab.hasClass('tab-image') ? 'image' : 'icon' ); }
							});
						});
					}

					$(document.body).on('click.widgets-toggle', '.wpzoom-builder-widget-feature-box', update_me);
					wpzlbWidgetSaveCallbacks.push(update_me);

				});

			</script>

            <style>
                .fbbrs-wrap .ui-slider {
                    position: relative;
                    text-align: left;
                }
                .fbbrs-wrap .ui-slider .ui-slider-handle {
                    position: absolute;
                    z-index: 2;
                    width: 1.2em;
                    height: 1.2em;
                    cursor: default;
                }
                .fbbrs-wrap .ui-slider .ui-slider-range {
                    position: absolute;
                    z-index: 1;
                    font-size: .7em;
                    display: block;
                    border: 0;
                    background-position: 0 0;
                }
                .fbbrs-wrap .ui-slider-horizontal {
                    height: .8em;
                }
                .fbbrs-wrap .ui-slider-horizontal .ui-slider-handle {
                    top: -.3em;
                    margin-left: -.6em;
                }
                .fbbrs-wrap .ui-slider-horizontal .ui-slider-range {
                    top: 0;
                    height: 100%;
                }
                .fbbrs-wrap .ui-slider-horizontal .ui-slider-range-min {
                    left: 0;
                }
                .fbbrs-wrap .ui-slider-horizontal .ui-slider-range-max {
                    right: 0;
                }
                /* Corner radius */
                .fbbrs-wrap .ui-corner-all,
                .fbbrs-wrap .ui-corner-top,
                .fbbrs-wrap .ui-corner-left,
                .fbbrs-wrap .ui-corner-tl {
                    border-top-left-radius: 4px;
                }
                .fbbrs-wrap .ui-corner-all,
                .fbbrs-wrap .ui-corner-top,
                .fbbrs-wrap .ui-corner-right,
                .fbbrs-wrap .ui-corner-tr {
                    border-top-right-radius: 4px;
                }
                .fbbrs-wrap .ui-corner-all,
                .fbbrs-wrap .ui-corner-bottom,
                .fbbrs-wrap .ui-corner-left,
                .fbbrs-wrap .ui-corner-bl {
                    border-bottom-left-radius: 4px;
                }
                .fbbrs-wrap .ui-corner-all,
                .fbbrs-wrap .ui-corner-bottom,
                .fbbrs-wrap .ui-corner-right,
                .fbbrs-wrap .ui-corner-br {
                    border-bottom-right-radius: 4px;
                }
                .fbbrs-wrap .ui-widget-content {
                    border: 1px solid #aaaaaa;
                    background: #ffffff;
                    color: #222222;
                }
                .fbbrs-wrap .ui-widget-content a {
                    color: #222222;
                }
                /* Interaction states
                ----------------------------------*/
                .fbbrs-wrap .ui-state-default,
                .fbbrs-wrap .ui-widget-content .ui-state-default,
                .fbbrs-wrap .ui-widget-header .ui-state-default {
                    border: 1px solid #d3d3d3;
                    background: #e6e6e6;
                    font-weight: normal;
                    color: #555555;
                }
                .fbbrs-wrap .ui-state-default a,
                .fbbrs-wrap .ui-state-default a:link,
                .fbbrs-wrap .ui-state-default a:visited {
                    color: #555555;
                    text-decoration: none;
                }
                .fbbrs-wrap .ui-state-hover,
                .fbbrs-wrap .ui-widget-content .ui-state-hover,
                .fbbrs-wrap .ui-widget-header .ui-state-hover,
                .fbbrs-wrap .ui-state-focus,
                .fbbrs-wrap .ui-widget-content .ui-state-focus,
                .fbbrs-wrap .ui-widget-header .ui-state-focus {
                    border: 1px solid #999999;
                    background: #dadada;
                    font-weight: normal;
                    color: #212121;
                }
                .fbbrs-wrap .ui-state-hover a,
                .fbbrs-wrap .ui-state-hover a:hover,
                .fbbrs-wrap .ui-state-hover a:link,
                .fbbrs-wrap .ui-state-hover a:visited {
                    color: #212121;
                    text-decoration: none;
                }
                .fbbrs-wrap .ui-state-active,
                .fbbrs-wrap .ui-widget-content .ui-state-active,
                .fbbrs-wrap .ui-widget-header .ui-state-active {
                    border: 1px solid #aaaaaa;
                    background: #ffffff;
                    font-weight: normal;
                    color: #212121;
                }
                .fbbrs-wrap .ui-state-active a,
                .fbbrs-wrap .ui-state-active a:link,
                .fbbrs-wrap .ui-state-active a:visited {
                    color: #212121;
                    text-decoration: none;
                }

                /* Interaction Cues
                ----------------------------------*/
                .fbbrs-wrap .ui-state-highlight,
                .fbbrs-wrap .ui-widget-content .ui-state-highlight,
                .fbbrs-wrap .ui-widget-header .ui-state-highlight {
                    border: 1px solid #fcefa1;
                    background: #fbf9ee;
                    color: #363636;
                }
                .fbbrs-wrap .ui-state-highlight a,
                .fbbrs-wrap .ui-widget-content .ui-state-highlight a,
                .fbbrs-wrap .ui-widget-header .ui-state-highlight a {
                    color: #363636;
                }
                .fbbrs-wrap .ui-state-error,
                .fbbrs-wrap .ui-widget-content .ui-state-error,
                .fbbrs-wrap .ui-widget-header .ui-state-error {
                    border: 1px solid #cd0a0a;
                    background: #fef1ec;
                    color: #cd0a0a;
                }
                .fbbrs-wrap .ui-state-error a,
                .fbbrs-wrap .ui-widget-content .ui-state-error a,
                .fbbrs-wrap .ui-widget-header .ui-state-error a {
                    color: #cd0a0a;
                }
                .fbbrs-wrap .ui-state-error-text,
                .fbbrs-wrap .ui-widget-content .ui-state-error-text,
                .fbbrs-wrap .ui-widget-header .ui-state-error-text {
                    color: #cd0a0a;
                }
                .fbbrs-wrap .ui-priority-primary,
                .fbbrs-wrap .ui-widget-content .ui-priority-primary,
                .fbbrs-wrap .ui-widget-header .ui-priority-primary {
                    font-weight: bold;
                }
                .fbbrs-wrap .ui-priority-secondary,
                .fbbrs-wrap .ui-widget-content .ui-priority-secondary,
                .fbbrs-wrap .ui-widget-header .ui-priority-secondary {
                    opacity: .7;
                    filter:Alpha(Opacity=70);
                    font-weight: normal;
                }
                .fbbrs-wrap .ui-state-disabled,
                .fbbrs-wrap .ui-widget-content .ui-state-disabled,
                .fbbrs-wrap .ui-widget-header .ui-state-disabled {
                    opacity: .35;
                    filter:Alpha(Opacity=35);
                    background-image: none;
                }
                .fbbrs-wrap .ui-state-disabled .ui-icon {
                    filter:Alpha(Opacity=35); /* For IE8 - See #6059 */
                }

                .wpzoom-feature-box-controls .icon-btn {
                    display: inline-block;
                    font-size: 14px;
                    width: 22px;
                    line-height: 22px;
                    height: 22px;
                    margin: 0;
                    padding: 0;
                }

                .wpzoom-feature-box-tabs ul.tab-nav, .wpzoom-feature-box-tabs ul.tab-nav li, .wpzoom-feature-box-tabs ul.tab-tabs, .wpzoom-feature-box-tabs ul.tab-tabs li {
                    padding: 0;
                    margin: 0;
                }

                .wpzoom-feature-box-tabs ul.tab-nav {
                    position: relative;
                    z-index: 5;
                }

                .wpzoom-feature-box-tabs ul.tab-nav li {
                    display: inline-block;
                    margin: 0 0 -5px;
                }

                .wpzoom-feature-box-tabs ul.tab-nav li a {
                    display: block;
                    text-decoration: none;
                    color: #333;
                    padding: 6px 16px 8px;
                    -webkit-border-top-left-radius: 5px;
                    -webkit-border-top-right-radius: 5px;
                    -moz-border-radius-topleft: 5px;
                    -moz-border-radius-topright: 5px;
                    border-top-left-radius: 5px;
                    border-top-right-radius: 5px;
                }

                .wpzoom-feature-box-tabs ul.tab-nav li a:hover, .wpzoom-feature-box-tabs ul.tab-nav li a:active {
                    color: #21759b;
                    background: rgba(0, 0, 0, 0.05);
                }

                .wpzoom-feature-box-tabs ul.tab-nav li.ui-state-active a {
                    font-weight: bold;
                    background: #f5f5f5;
                }

                .wpzoom-feature-box-tabs ul.tab-tabs {
                    position: relative;
                    z-index: 10;
                    background: #f5f5f5;
                    padding: 12px;
                    margin-bottom: 15px;
                    -webkit-border-radius: 5px;
                    -moz-border-radius: 5px;
                    border-radius: 5px;
                }

                .wpzoom-feature-box-image-preview {
                    max-width: 580px;
                }

                a.feature-box-upload-clear.disabled {
                    cursor: default;
                    color: #aaa;
                }
            </style>
        <?php
    }

    public static function assets() {
        wp_enqueue_media();
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'jquery-ui' );
        wp_enqueue_script( 'jquery-ui-tabs' );
        wp_enqueue_style( 'wp-color-picker' );
    }

		public function frontend_css() {
			if ( !is_single() && !is_page() ) return;

			$current_id = get_the_ID();
			$sidebars_widgets = wp_get_sidebars_widgets();

			if ( empty( $sidebars_widgets ) || !isset( $sidebars_widgets["_wpzlb-page-$current_id-widgets"] ) ) return;

			$current_sidebar = (array)$sidebars_widgets["_wpzlb-page-$current_id-widgets"];
			$matches = preg_grep( '/^wpzoom-feature-box-[0-9]+$/i', $current_sidebar );

			if ( empty( $current_sidebar ) || empty( $matches ) ) return;

			wp_dequeue_style( 'font-awesome' );
			wp_deregister_style( 'font-awesome' );
			wp_enqueue_style( 'font-awesome', ZOOM_Builder::$assets_url . '/css/font-awesome.css', array(), ZOOM_Builder::$version );
		}
}

function wpzoom_register_featbox_widget() {
    register_widget( 'WPZOOM_Feature_Box' );
}
add_action( 'widgets_init', 'wpzoom_register_featbox_widget' );
add_action( 'init', array( 'WPZOOM_Feature_Box', 'init' ) );
add_action( 'admin_init', array( 'WPZOOM_Feature_Box', 'admin_init' ) );