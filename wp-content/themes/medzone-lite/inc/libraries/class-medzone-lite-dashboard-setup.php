<?php
/**
 * MedZone_Lite Dashboard
 *
 * @package MedZone_Lite
 * @since   1.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class MedZone_Lite_Dashboard_Setup {
	/**
	 * Theme array
	 *
	 * @var array
	 */
	public $theme = array();
	/**
	 * Notice layout
	 *
	 * @var string
	 */
	private $notice = '';

	/**
	 * MedZone_Lite_Dashboard_Setup constructor.
	 *
	 * @param array $theme
	 */
	public function __construct( $theme = array() ) {
		$this->theme = $theme;

		$theme = wp_get_theme();
		$arr   = array(
			'theme-name'    => $theme->get( 'Name' ),
			'theme-slug'    => $theme->get( 'TextDomain' ),
			'theme-version' => $theme->get( 'Version' ),
		);

		$this->theme = wp_parse_args( $this->theme, $arr );
	}

	/**
	 * @param array $theme
	 *
	 * @return MedZone_Lite_Dashboard_Setup
	 */
	public static function get_instance( $theme = array() ) {
		static $inst;
		if ( ! $inst ) {
			$inst = new MedZone_Lite_Dashboard_Setup( $theme );
		}

		return $inst;
	}

	/**
	 * Adds an admin notice in the backend
	 *
	 * If the Epsilon Notification class does not exist, we stop
	 */
	public function add_admin_notice() {
		if ( ! class_exists( 'Epsilon_Notifications' ) ) {
			return;
		}

		if ( ! empty( $_GET ) && isset( $_GET['page'] ) && 'epsilon-onboarding' === $_GET['page'] ) {
			return;
		}

		$used_onboarding = get_theme_mod( $this->theme['theme-slug'] . '_used_onboarding', false );
		if ( $used_onboarding ) {
			return;
		}

		$imported_demo = MedZone_Lite_Notify_System::check_installed_data();
		if ( $imported_demo ) {
			return;
		}

		if ( empty( $this->notice ) ) {
			$this->notice .= '<img src="' . esc_url( get_template_directory_uri() ) . '/inc/libraries/epsilon-theme-dashboard/assets/images/macho-themes-logo-black.png" class="epsilon-author-logo" />';


			/* Translators: Notice Title */
			$this->notice .= '<h1>' . sprintf( esc_html__( 'Welcome to %1$s', 'medzone-lite' ), $this->theme['theme-name'] ) . '</h1>';
			$this->notice .= '<p>';
			$this->notice .=
				sprintf( /* Translators: Notice */
					esc_html__( 'Welcome! Thank you for choosing %3$s! To fully take advantage of the best our theme can offer please make sure you visit our %1$swelcome page%2$s.', 'medzone-lite' ),
					'<a href="' . esc_url( admin_url( 'themes.php?page=' . $this->theme['theme-slug'] . '-dashboard' ) ) . '">',
					'</a>',
					$this->theme['theme-name']
				);
			$this->notice .= '</p>';
			/* Translators: Notice URL */
			$this->notice .= '<p><a href="' . esc_url( admin_url( '?page=epsilon-onboarding' ) ) . '" class="button button-primary button-hero" style="text-decoration: none;"> ' . sprintf( esc_html__( 'Get started with %1$s', 'medzone-lite' ), $this->theme['theme-name'] ) . '</a></p>';
		}
		$notifications = Epsilon_Notifications::get_instance();
		$notifications->add_notice(
			array(
				'id'      => 'notification_testing',
				'type'    => 'notice epsilon-big',
				'message' => $this->notice,
			)
		);
	}

	/**
	 * Edd params
	 *
	 * @return array
	 */
	public function get_edd( $setup = array() ) {
		$options = get_option( $setup['theme']['theme-slug'] . '_license_object', array() );
		$options = wp_parse_args(
			$options,
			array(
				'expires'       => false,
				'licenseStatus' => false,
			)
		);

		return array(
			'license'       => trim( get_option( $setup['theme']['theme-slug'] . '_license_key', false ) ),
			'licenseOption' => $setup['theme']['theme-slug'] . '_license_key',
			'downloadId'    => '221300',
			'expires'       => $options['expires'],
			'status'        => $options['licenseStatus']
		);
	}

	/**
	 * Onboarding steps
	 *
	 * @return array
	 */
	public function get_steps() {
		return array(
			array(
				'id'       => 'landing',
				'title'    => __( 'Welcome to MedZone Lite', 'medzone-lite' ),
				'content'  => array(
					'paragraphs' => array(
						__( ' This wizard will set up your theme, install plugins and import demo content. It is optional & should take less than a minute.', 'medzone-lite' ),
					),
				),
				'progress' => __( 'Getting Started', 'medzone-lite' ),
				'buttons'  => array(
					'next' => array(
						'action' => 'next',
						'label'  => __( 'Let\'s get started <span class="dashicons dashicons-arrow-right-alt2"></span>', 'medzone-lite' ),
					),
				),
			),
			array(
				'id'       => 'plugins',
				'title'    => __( 'Install Recommended Plugins', 'medzone-lite' ),
				'content'  => array(
					'paragraphs' => array(
						__( 'MedZone Lite integrates tightly with a few plugins that we recommend installing to get the full theme experience, as we\'ve intended it to be. This is an optional step, but we recommend installing them as we think these hand-picked plugins work really nice with MedZone Lite and help enhance the overall experience.', 'medzone-lite' ),
					),
				),
				'progress' => __( 'Plugins', 'medzone-lite' ),
				'buttons'  => array(
					'next' => array(
						'action' => 'next',
						'label'  => __( 'Next <span class="dashicons dashicons-arrow-right-alt2"></span>', 'medzone-lite' ),
					),
				),
			),
			array(
				'id'       => 'demos',
				'title'    => __( 'Import Demo Content', 'medzone-lite' ),
				'content'  => array(
					'paragraphs' => array(
						wp_kses_post( __( 'We\'ve made it easy for you to get up and running in a jiffy. Just pick any of the theme demos below, click on Select, Import and you\'ll be ready in no time. Feel free to skip this step if you\'d like to create the content yourself.', 'medzone-lite' ) ),
						wp_kses_post( __( '<em>Note: This is the easiest way to see what goes where. After you\'ve finished the import, you can edit the content using the built-in Customizer, available under Appearance -> Customize.</em>', 'medzone-lite' ) )
					),
				),
				'progress' => __( 'Demos', 'medzone-lite' ),
				'demos'    => get_template_directory() . '/inc/customizer/demo/demo.json',
				'buttons'  => array(
					'next' => array(
						'action' => 'next',
						'label'  => __( 'Next <span class="dashicons dashicons-arrow-right-alt2"></span>', 'medzone-lite' ),
					),
				),
			),
			array(
				'id'       => 'finish',
				'title'    => __( 'You\'re ready', 'medzone-lite' ),
				'content'  => array(
					'paragraphs' => array(
						__( 'Your new theme has been all set up. Enjoy your new theme by <a href="https://www.machothemes.com/">MachoThemes</a>.', 'medzone-lite' ),
					),
				),
				'progress' => __( 'Finished', 'medzone-lite' ),
				'buttons'  => array(
					'next' => array(
						'action' => 'finish',
						'label'  => __( 'Finish', 'medzone-lite' ),
					),
				),
			),
		);
	}

	/**
	 * Returns a html string
	 *
	 * @return string
	 */
	public function get_permission_content() {
		$html = '<div class="permission-request">';
		$html .= '<a href="#hidden-permissions" id="hidden-permissions-toggle"> ' . __( 'What permissions are being granted', 'medzone-lite' ) . ' <span class="dashicons dashicons-arrow-down"></span></a>';
		$html .= '<div id="hidden-permissions" >
			<ul>
				<li>
					<span class="dashicons dashicons-admin-users"></span>
					<span class="content">
						<strong>' . __( 'YOUR PROFILE OVERVIEW', 'medzone-lite' ) . '</strong>
						<small>' . __( 'Name and email address', 'medzone-lite' ) . '</small>		
					</span>
				</li>
				<li>
					<span class="dashicons dashicons-admin-settings"></span>
					<span class="content">
						<strong>' . __( 'YOUR SITE OVERVIEW', 'medzone-lite' ) . '</strong>
						<small>' . __( 'Site URL, WP Version, PHP Version, plugins and themes', 'medzone-lite' ) . '</small>		
					</span>
				</li>
				<li>
					<span class="dashicons dashicons-admin-plugins"></span>
					<span class="content">
						<strong>' . __( 'CURRENT PLUGIN EVENTS', 'medzone-lite' ) . '</strong>
						<small>' . __( 'Activation, deactivation and uninstall', 'medzone-lite' ) . '</small>		
					</span>
				</li>
			</ul>
			</div>
		</div>';

		return $html;
	}

	/**
	 * @param bool $integrated
	 *
	 * @return array
	 */
	public function get_plugins( $integrated = false ) {
		$arr = array(
			'kali-forms' => array(
				'integration' => true,
				'recommended' => false,
			),

			'modula-best-grid-gallery' => array(
				'integration' => false,
				'recommended' => true,
			),

			'strong-testimonials' => array(
				'integration' => false,
				'recommended' => true,
			),
			'kb-support' => array(
				'integration' => false,
				'recommended' => true,
			),
			'rsvp' => array(
				'integration' => false,
				'recommended' => true,
			)
		);

		return $arr;
	}

	/**
	 * Dashboard actions
	 */
	public function get_actions() {
		if ( is_customize_preview() ) {
			return $this->_customizer_actions();
		}

		return array(
			array(
				'id'          => 'medzone-import-data',
				'title'       => esc_html__( 'Add sample content', 'medzone-lite' ),
				'description' => esc_html__( 'Clicking the button below will add content/sections/settings and recommended plugins to your WordPress installation. Click advanced to customize the import process in the dedicated tab.', 'medzone-lite' ),
				'check'       => MedZone_Lite_Notify_System::check_installed_data(),
				'state'       => false,
				'actions'     => array(
					array(
						'label'   => esc_html__( 'Advanced', 'medzone-lite' ),
						'type'    => 'change-page',
						'handler' => 'epsilon-demo',
					),
				),
			),
			array(
				'id'          => 'medzone-check-kali-forms',
				'title'       => MedZone_Lite_Notify_System::plugin_verifier( 'kali-forms', 'title', 'Kali Forms' ),
				'description' => MedZone_Lite_Notify_System::plugin_verifier( 'kali-forms', 'description', 'Kali Forms' ),
				'plugin_slug' => 'kali-forms',
				'state'       => false,
				'check'       => defined( 'KALIFORMS_VERSION' ),
				'actions'     => array(
					array(
						'label'   => MedZone_Lite_Notify_System::plugin_verifier( 'kali-forms', 'installed', 'Kali Forms' ) ? __( 'Activate Plugin', 'medzone-lite' ) : __( 'Install Plugin', 'medzone-lite' ),
						'type'    => 'handle-plugin',
						'handler' => MedZone_Lite_Notify_System::plugin_verifier( 'kali-forms', 'installed', 'Kali Forms' ),
					),
				),
			),
		);
	}

	/**
	 * Render customizer actions
	 */
	private function _customizer_actions() {
		return array(
			array(
				'id'          => 'medzone-import-data',
				'title'       => esc_html__( 'Add sample content', 'medzone-lite' ),
				'description' => esc_html__( 'Clicking the button below will add content/sections/settings and recommended plugins to your WordPress installation. Click advanced to customize the import process in the dedicated tab.', 'medzone-lite' ),
				'check'       => MedZone_Lite_Notify_System::check_installed_data(),
				'help'        => '<a class="button button-primary" id="" href="' . esc_url( admin_url( sprintf( 'themes.php?page=%1$s-dashboard', 'medzone-lite' ) ) ) . '">' . __( 'Import Demo Content', 'medzone-lite' ) . '</a>',
			),
			array(
				'id' 			=> 'medzone-check-kaliforms',
				'title'     	=> MedZone_Lite_Notify_System::plugin_verifier( 'kali-forms', 'title', 'Kali Forms' ),
				'description'   => MedZone_Lite_Notify_System::plugin_verifier( 'kali-forms', 'description', 'Kali Forms' ),
				'plugin_slug'	=> 'kali-forms',
				'check'			=> defined( 'KALIFORMS_VERSION' ),
			),
		);
	}

	/**
	 * Welcome Screen tabs
	 *
	 * @param $setup array
	 *
	 * @return array
	 */
	public function get_tabs( $setup = array() ) {
		$theme = wp_get_theme();

		return array(
			array(
				'id'      => 'epsilon-getting-started',
				'title'   => esc_html__( 'Getting Started', 'medzone-lite' ),
				'hidden'  => false,
				'type'    => 'info',
				'content' => array(
					array(
						'title'     => esc_html__( 'Step 1 - Implement recommended actions', 'medzone-lite' ),
						'paragraph' => esc_html__( 'We compiled a list of steps for you, to take make sure the experience you will have using one of our products is very easy to follow.', 'medzone-lite' ),
						'action'    => '<a href="' . esc_url( admin_url() . '?page=epsilon-onboarding' ) . '" class="button button-primary">' . __( 'Launch wizard', 'medzone-lite' ) . '</a>',
					),
					array(
						'title'     => esc_html__( 'Step 2 - Check our documentation', 'medzone-lite' ),
						'paragraph' => esc_html__( 'Even if you are a long-time WordPress user, we still believe you should give our documentation a very quick Read.', 'medzone-lite' ),
						'action'    => '<a target="_blank" href="http://docs.machothemes.com">' . __( 'Full documentation', 'medzone-lite' ) . '</a>',
					),
					array(
						'title'     => esc_html__( 'Step 3 - Customize everything', 'medzone-lite' ),
						'paragraph' => esc_html__( 'Using the WordPress Customizer you can easily customize every aspect of the theme.', 'medzone-lite' ),
						'action'    => '<a target="_blank" href="' . esc_url( admin_url() . 'customize.php' ) . '" class="button button-primary">' . esc_html__( 'Go to Customizer', 'medzone-lite' ) . '</a>',
					),
					array(
						'title'     => esc_html__( 'Lend a hand and share your thoughts', 'medzone-lite' ),
						'paragraph' => vsprintf(
							// Translators: 1 is Theme Name, 2 is opening Anchor, 3 is closing.
							__( 'We worked hard on making %1$s the best one out there. We are interested in hearing your thoughts about %1$s and what we could do to make it even better.<br/> <br/>', 'medzone-lite' ),
							array(
								$theme->get( 'Name' ),
							)
						),
						'action'    => '<a class="button button-feedback" target="_blank" href="https://bit.ly/medzone-feedback">Have your say</a><br/> <br/> <em>Note: A 10% discount coupon will be emailed to you after form submission. Please use a valid email address.</em>',
						'type'      => 'standout',
					),
				),
			),
			array(
				'id'      => 'epsilon-demo',
				'title'   => esc_html__( 'Demo Content', 'medzone-lite' ),
				'type'    => 'demos',
				'hidden'  => false,
				'content' => array(
					'title'          => esc_html__( 'Install your demo content', 'medzone-lite' ),
					'titleAlternate' => esc_html__( 'Demo content already installed', 'medzone-lite' ),
					'paragraph'      => esc_html__( 'Since MedZone Lite is a versatile theme we provided a few sample demo styles for you, please choose one from the following pages so you will have to work as little as you should. Click on the style and press next!', 'medzone-lite' ),
					'check'          => MedZone_Lite_Notify_System::check_installed_data(),
					'demos'          => get_template_directory() . '/inc/customizer/demo/demo.json',
				),
			),
			array(
				'id'      => 'epsilon-actions',
				'title'   => esc_html__( 'Actions', 'medzone-lite' ),
				'type'    => 'actions',
				'hidden'  => $this->theme['theme-slug'] . '_recommended_actions',
				'content' => $this->get_actions(),
			),
			array(
				'id'     => 'epsilon-plugins',
				'title'  => esc_html__( 'Recommended Plugins', 'medzone-lite' ),
				'hidden' => $this->theme['theme-slug'] . '_recommended_plugins',
				'type'   => 'plugins',
			),
			array(
				'id'       => 'epsilon-features',
				'title'    => esc_html__( 'Lite vs PRO', 'medzone-lite' ),
				'type'     => 'comparison-table',
				'hidden'   => $this->theme['theme-slug'] . '_lite_vs_pro',
				'features' => array(
					'names'      => array( 'MedZone Lite', 'MedZone Pro' ),
					'upsell'     => '<a href="https://www.machothemes.com/theme/medzone-pro/?utm_source=worg&utm_medium=about-page&utm_campaign=upsell" target="_blank" class="button button-primary button-hero"><span class="dashicons dashicons-cart"></span>
                    ' . __( 'Get MedZone Pro!', 'medzone-lite' ) . '</a>',
					'comparison' => array(
						array(
							'label' => esc_html__( 'Frontpage sections', 'medzone-lite' ),
							'one'   => esc_html__( 'Limited', 'medzone-lite' ),
							'two'   => '<span class="dashicons dashicons-yes"></span></i>',
						),
						array(
							'label' => esc_html__( 'Blog & Page layout control', 'medzone-lite' ),
							'one'   => esc_html__( 'Limited', 'medzone-lite' ),
							'two'   => '<span class="dashicons dashicons-yes"></span></i>',
						),
						array(
							'label' => esc_html__( 'Footer layout control', 'medzone-lite' ),
							'one'   => esc_html__( 'Limited', 'medzone-lite' ),
							'two'   => '<span class="dashicons dashicons-yes"></span></i>',
						),
						array(
							'label' => esc_html__( 'Post formats', 'medzone-lite' ),
							'one'   => esc_html__( 'Limited', 'medzone-lite' ),
							'two'   => '<span class="dashicons dashicons-yes"></span></i>',
						),
						array(
							'label' => esc_html__( 'Color schemes', 'medzone-lite' ),
							'one'   => '<span class="dashicons dashicons-no-alt"></span>',
							'two'   => '<span class="dashicons dashicons-yes"></span></i>',
						),
						array(
							'label' => esc_html__( 'Typography', 'medzone-lite' ),
							'one'   => esc_html__( 'Limited', 'medzone-lite' ),
							'two'   => '<span class="dashicons dashicons-yes"></span></i>',
						),
						array(
							'label' => esc_html__( 'Multiple blog layouts', 'medzone-lite' ),
							'one'   => '<span class="dashicons dashicons-no-alt"></span>',
							'two'   => '<span class="dashicons dashicons-yes"></span></i>',
						),
						array(
							'label' => esc_html__( 'Priority support', 'medzone-lite' ),
							'one'   => '<span class="dashicons dashicons-no-alt"></span>',
							'two'   => '<span class="dashicons dashicons-yes"></span></i>',
						),
						array(
							'label' => esc_html__( 'Security updates & feature releases', 'medzone-lite' ),
							'one'   => '<span class="dashicons dashicons-no-alt"></span>',
							'two'   => '<span class="dashicons dashicons-yes"></span></i>',
						),
					),
				),
			),
			array(
				'id'      => 'epsilon-privacy',
				'title'   => esc_html__( 'Privacy', 'medzone-lite' ),
				'type'    => 'option-page',
				'hidden'  => false,
				'content' => array(
					'title'      => esc_html__( 'We believe in a better and streamlined user experiences', 'medzone-lite' ),
					'paragraphs' => array(
						esc_html__( 'And as such, we\'ve made it easy for you - our user, to disable all of our theme upsells & recommendations.', 'medzone-lite' ),
						esc_html__( 'Mind you that we use these as a way to facilitate compatible product discovery - as in: plugins that enhance the
		user experience with any of our products. But, in the end, the user should always have a say in it.', 'medzone-lite' ),
						wp_kses_post( __( 'By turning any or all of the toggles below to the <span style="color: green;">ON</span> position you\'ll be able
		to hide all upsells & recommended plugin discovery sections & actions.', 'medzone-lite' ) ),
						wp_kses_post( __( '<u>We really hope</u> you\'ll enjoy using our products as much as we\'ve enjoyed building them.', 'medzone-lite' ) ),
					),
				),
				'fields'  => array(
					array(
						'id'      => $this->theme['theme-slug'] . '_recommended_actions',
						'type'    => 'epsilon-toggle',
						'value'   => true,
						'label'   => esc_html__( 'Hide Customizer Recommended Actions', 'medzone-lite' ),
						'checked' => get_option( $this->theme['theme-slug'] . '_recommended_actions', false ),
					),
					array(
						'id'      => $this->theme['theme-slug'] . '_recommended_plugins',
						'type'    => 'epsilon-toggle',
						'value'   => true,
						'label'   => esc_html__( 'Hide Plugin Recommendations', 'medzone-lite' ),
						'checked' => get_option( $this->theme['theme-slug'] . '_recommended_plugins', false ),
					),
					array(
						'id'      => $this->theme['theme-slug'] . '_lite_vs_pro',
						'value'   => true,
						'type'    => 'epsilon-toggle',
						'label'   => esc_html__( 'Hide Lite VS Pro Table', 'medzone-lite' ),
						'checked' => get_option( $this->theme['theme-slug'] . '_lite_vs_pro', false ),
					),
					array(
						'id'      => $this->theme['theme-slug'] . '_theme_upsells',
						'value'   => true,
						'label'   => esc_html__( 'Hide Theme Upsells', 'medzone-lite' ),
						'type'    => 'epsilon-toggle',
						'checked' => get_option( $this->theme['theme-slug'] . '_theme_upsells', false ),
					),
				),
			),
		);
	}

	/**
	 * Return privacy options
	 *
	 * @return array
	 */
	public function get_privacy_options() {
		$arr = array(
			$this->theme['theme-slug'] . '_recommended_actions' => get_option( $this->theme['theme-slug'] . '_recommended_actions', false ),
			$this->theme['theme-slug'] . '_recommended_plugins' => get_option( $this->theme['theme-slug'] . '_recommended_plugins', false ),
			$this->theme['theme-slug'] . '_lite_vs_pro'         => get_option( $this->theme['theme-slug'] . '_lite_vs_pro', false ),
			$this->theme['theme-slug'] . '_theme_upsells'       => get_option( $this->theme['theme-slug'] . '_theme_upsells', false ),
		);

		foreach ( $arr as $id => $val ) {
			$arr[ $id ] = empty( $val ) ? false : true;
		}

		return $arr;
	}
}
