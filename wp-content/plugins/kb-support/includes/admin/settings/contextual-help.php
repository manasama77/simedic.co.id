<?php
/**
 * Contextual Help
 *
 * @package     KBS
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Settings contextual help.
 *
 * @since       1.0
 * @return      void
 */
function kbs_settings_contextual_help() {
	$screen = get_current_screen();

	$article_singular = kbs_get_article_label_singular();
	$article_plural   = kbs_get_article_label_plural();
	$ticket_singular  = kbs_get_ticket_label_singular();
	$ticket_plural    = kbs_get_ticket_label_plural();
	$settings         = kbs_get_registered_settings();

	$screen->set_help_sidebar( kbs_get_contextual_help_sidebar_text() );

	$screen->add_help_tab( array(
		'id'      => 'kbs-settings-general',
		'title'   => __( 'General', 'kb-support' ),
		'content' => apply_filters( 'kbs_settings_general_contextual_help',
			'<p>' . __( '<strong>Page Settings</strong>', 'kb-support' ) . '</p>' .
			'<ul>' .
				'<li>' . sprintf( 
					__( '<strong>Submissions Page</strong> - The selected page should contain the shortcode <code>[kbs_submit]</code> and will be the page that your customers use to submit their %1$s.', 'kb-support' ),
					strtolower( $ticket_plural )
				) . '</li>' .
				'<li>' . sprintf( 
					__( '<strong>%1$s</strong> - The selected page should contain the shortcode <code>[kbs_tickets]</code> and will be the page that customers can use view and manage their %2$s, including submitting replies.', 'kb-support' ),
					$ticket_plural,
					strtolower( $ticket_plural )
				) . '</li>' .
			'</ul>' .
			'<p>' . __( '<strong>Customer Settings</strong>', 'kb-support' ) .
			'<p>' . __( '<em>Registration Settings</em>', 'kb-support' ) . '<br />' .
			'<ul>' .
				'<li>' . 
					__( '<strong>Name Fields</strong> - You can choose which name fields to display on your KBS registration form. This is the page which contains the shortcode <code>[kbs_register].</code>', 'kb-support'
				) . '</li>' .
				'<li>' .
					__( '<strong>Required Name Fields</strong> - Specify which name fields are required to be completed during registration. If any of the specified fields are left empty when the registration form is submitted, registration will fail and an error will be displayed.', 'kb-support'
				) . '</li>' .
				'<li>' .
					__( '<strong>Username Format</strong> - When a customer successfully registers, KBS will auto generate a username based on your selection here. Using the email address of the customer is the default option.', 'kb-support'
				) . '</li>' .
				'<li>' .
					__( '<strong>Default Role</strong> - Choose which WordPress role will be assigned to customers who register via the KBS registration form. <code>Support Customer</code> is the default and is very similar to the built-in <code>Subscriber</code> WordPress role.', 'kb-support'
				) . '</li>' .
			'</ul>' .
			'<p>' . sprintf( __( '<em>%s Manager Settings</em>', 'kb-support' ), $ticket_singular ) . '<br />' .
				__( '<em>The following options can be customized by registered customers when they edit their profile.</em>', 'kb-support' ) . '</p>' .
			'<ul>' .
				'<li>' . sprintf( 
					__( '<strong>Default Replies to Load</strong> - Applies to the front end %s Manager page for customers. The specified number of replies will be loaded by default. If more replies exist, customers can load them by clicking the relevant link. Entering <code>0</code> will load all replies by default.', 'kb-support' ),
					$ticket_singular
				) . '</li>' .
				'<li>' . sprintf(
					__( '<strong>Hide Closed %s?</strong> - With this option enabled, by default customers will not see their closed %s within the front end %s Manager page. A link will be displayed on the page should they wish to load them.', 'kb-support' ),
                    $ticket_plural,
                    strtolower( $ticket_plural ),
                    $ticket_singular
				). '</li>' .
			'</ul>'
        )
	) );

    do_action( 'kbs_settings_before_tickets_contextual_help', $screen );
	$screen->add_help_tab( array(
		'id'      => 'kbs-settings-tickets',
		'title'   => $ticket_plural,
		'content' => apply_filters( 'kbs_settings_tickets_contextual_help',
			'<p>' . sprintf( __( '<strong>%s Settings</strong>', 'kb-support' ), $ticket_singular ) . '</p>' .
			'<ul>' .
				'<li>' . sprintf( 
					__( "<strong>Sequential %s Numbers?</strong> - Enable sequential ticket numbers instead of WordPress post ID's.", 'kb-support' ),
					$ticket_singular
				) . '</li>' .
				'<li>' . sprintf( 
					__( '<strong>Sequential Starting Number</strong> - Enter the number that should be used as the first sequential %s number.', 'kb-support' ),
					strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( 
					__( '<strong>Prefix for %1$s ID\'s</strong> - Enter what you would like your %2$s ID\'s to be prefixed with.', 'kb-support' ),
					$ticket_singular,
					strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( 
					__( '<strong>Suffix for %1$s ID\'s</strong> - Enter what you would like your %2$s ID\'s to be suffixed with.', 'kb-support' ),
					$ticket_singular,
					strtolower( $ticket_singular )
				) . '</li>' .
                '<li>' . sprintf( 
					__( '<strong>Show %1$s Count?</strong> - If enabled, the current open %1$s count will be displayed next to the %2$s menu item on the main menu.', 'kb-support' ),
					$ticket_singular,
					strtolower( $ticket_plural )
				) . '</li>' .
                '<li>' . sprintf( 
					__( '<strong>Show Count on Menu Bar?</strong> - Choose whether or not to display the open %s count on the WordPress menu bar. You can choose which environments you want it displayed, or turn it off altogether. By default it will display on the website front end only.', 'kb-support' ),
					strtolower( $ticket_singular )
				) . '</li>' .
               '<li>' . sprintf( 
					__( '<strong>Enable Participants?</strong> - If enabled, participants can be added to a %1$s if requested by the customer. Participants can then contribute towards %2$s by reading and creating replies, helping towards resolution.', 'kb-support' ),
					strtolower( $ticket_singular ),
					strtolower( $ticket_plural )
				) . '</li>' .
				'<li>' . sprintf( 
					__( '<strong>Hide Closed %1$s?</strong> - By default when you view the admin %2$s screen, the <code>All</code> view includes all %2$s with all stauses. With this option enabled, closed %3$s will not be displayed unless you click the Closed view.', 'kb-support' ),
					$ticket_plural,
					strtolower( $ticket_singular ),
					strtolower( $ticket_plural )
				) . '</li>' .
			'</ul>' .
			'<p>' . __( '<strong>Submission Settings</strong>', 'kb-support' ) . '</p>' .
			'<ul>' .
				'<li>' . sprintf( 
					__( '<strong>Enforce SSL for Submissions?</strong> - If selected, the %1$s submission page must be presented securely via HTTPS. A valid SSL certificate is required.', 'kb-support' ),
					strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf(
					__( '<strong>Disable Guest Submissions?</strong> - Whether or not a guest user can submit a %1$s. If selected, the customer must be logged in.', 'kb-support' ),
					 strtolower( $ticket_singular )
				). '</li>' .
				'<li>' . __( '<strong>Show Register / Login Form?</strong> - If <strong>Disable Guest Submissions?</strong> is enabled, you can select to display the login form, registration form, or both if the customer is not logged in.', 'kb-support' ) . '</li>' .
				'<li>' . sprintf( 
					__( '<strong>Submit Label</strong> - Enter the text you would like displayed on the %1$s submission forms submit button.', 'kb-support' ),
					strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( 
					__( '<strong>Reply Label</strong> - Enter the text you would like displayed on the %1$s Reply form submit button.', 'kb-support' ),
					strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( 
					__( '<strong>Allow File Uploads</strong> - Enter the number of files a customer can upload when submitting a %1$s. Set to <code>0</code> to disable file uploads. You will need to add a File Upload field to your submission form if enabling file uploads.', 'kb-support' ),
					$ticket_singular
				) . '</li>' .
				'<li>' . sprintf( 
					__( '<strong>Allowed File Types</strong> - Enter the file extensions that a customer can upload when submitting a %1$s. Seperate each file extension with a comma. If a customer attempts to upload a file with an extension that is not listed, they will receive an error.', 'kb-support' ),
					$ticket_singular
				) . '</li>' .
			'</ul>' .
			'<p>' . __( '<strong>Reply Settings</strong>', 'kb-support' ) . '</p>' .
			'<ul>' .
			   '<li>' . sprintf( 
					__( '<strong>Re-open %1$s</strong> - By enabling this option, a customer will be able to add a reply to a %2$s that is closed. When adding the reply, the %2$s will be reopened unless the customer also checks the <strong>This %2$s can be closed</strong> option within the reply form. By default, this option is not enabled.', 'kb-support' ),
					$ticket_plural,
					strtolower( $ticket_singular )
				) . '</li>' .
                '<li>' . sprintf( 
					__( '<strong>Agents Set Reply Status</strong> - By enabling this option, agents will be able to select a status to transition the %s to when replying.', 'kb-support' ),
					strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( 
					__( '<strong>Reply whilst <code>Status</code></strong> - For each of the specified %1$s status options, you can select which status the %1$s would change to when a reply is added by a customer.', 'kb-support' ),
					strtolower( $ticket_singular )
				) . '</li>' .
			'</ul>' .
			'<p>' . __( '<strong>Agent Settings</strong>', 'kb-support' ) . '</p>' .
			'<ul>' .
				'<li>' . sprintf(
					__( '<strong>Administrators are Agents?</strong> - Select this option if users with the WordPress Administrator role should be seen as agents. If not enabled, users with the Administrator role will not be able to create or view any %1$s.', 'kb-support' ),
					strtolower( $ticket_plural )
				) . '</li>' .
				'<li>' . sprintf( 
					__( '<strong>Restritct Agent %1$s View?</strong> - Enabling this option will result in Support Workers not being able to view %2$s that are assigned to other agents. The only %2$s they will be able to view will be those that are assigned to them, or those that are not assigned to any agent.', 'kb-support' ),
					$ticket_singular,
					strtolower( $ticket_plural )
				) . '</li>' .
                '<li>' . sprintf( 
					__( '<strong>Multiple Agents per %1$s?</strong> - Enabling this option allows for multiple support workers to be assigned to a ticket. All assigned agents will be able to work on the %2$s and, if configured, will also receive all relevant notifications regarding the %2$s.', 'kb-support' ),
					$ticket_singular,
					strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( 
					__( '<strong>Display Agent Status?</strong> - If selected, customers will be able to see an indicator that shows whether the agent assigned to their %1$s is actively online or not when looking at their %1$s details.', 'kb-support' ),
					strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( 
					__( '<strong>Auto Assign new %1$s</strong> - You can automatically assign an agent to a new %2$s submitted via a submission form. Disable, or choose to auto assign to an agent with the least amount of active %3$s, or a random agent.', 'kb-support' ),
					$ticket_plural,
					strtolower( $ticket_singular ),
					strtolower( $ticket_plural )
				) . '</li>' .
				'<li>' . sprintf( 
					__( '<strong>Auto Assign on Access?</strong> - Unassigned %1$s can be automatically assigned to agents on access. Can be useful in stopping agents selectively choosing which %1$s to work on.', 'kb-support' ),
					strtolower( $ticket_plural )
				) . '</li>' .
			'</ul>' .
			'<p>' . __( '<strong>Service Levels</strong>', 'kb-support' ) . '</p>' .
			'<ul>' .
				'<li>' . sprintf(
					__( '<strong>Enable SLA Tracking?</strong> - Select this option to enable Service Level tracking on all %s.', 'kb-support' ),
					strtolower( $ticket_plural )
				) . '</li>' .
				'<li>' . sprintf(
					__( '<strong>Target Response Time</strong> - Select the time within which you are targeting an initial response to new %s.', 'kb-support' ),
					strtolower( $ticket_plural )
				) . '</li>' .
				'<li>' . __( '<strong>Warn if within</strong> - Enter the number of hours before the target response time is due to expire that a warning should be displayed if no initial response has been provided.', 'kb-support' )
				. '</li>' .
				'<li>' . sprintf(
					__( '<strong>Target Resolution Time</strong> - Select the time within which you expecting to resolve %s.', 'kb-support' ),
					strtolower( $ticket_plural )
				) . '</li>' .
				'<li>' . sprintf( __( '<strong>Warn if within</strong> - Enter the number of hours before the target resolution time is due to expire that a warning should be displayed if the %s remains open.', 'kb-support' ),
				strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf(
					__( '<strong>Define Support Hours?</strong> - Select to enable support hours. You can then enter the days and times that your business is available to work on support %s. Target response and resolution times take into consideration your working hours.', 'kb-support' ),
					strtolower( $ticket_plural )
				) . '</li>' .
			'</ul>'
        )
	) );

    do_action( 'kbs_settings_before_articles_contextual_help', $screen );
	$screen->add_help_tab( array(
		'id'      => 'kbs-settings-articles',
		'title'   => $article_plural,
		'content' => apply_filters( 'kbs_settings_articles_contextual_help',
			'<p>' . sprintf( __( '<strong>%s Settings</strong>', 'kb-support' ), $article_singular ) . '</p>' .
			'<ul>' .
				'<li>' . sprintf( 
					__( '<strong>Restrict %1$s</strong> - If selected then each newly created article, by default, will have the <strong>Restrict Access</strong> option enabled meaning that only logged in users can access. This option can be adjusted per individual %2$s.', 'kb-support' ),
					$article_plural,
					$article_singular
				) . '</li>' .
				'<li>' . sprintf( 
					__( '<strong>Show Register / Login Form?</strong> - Select to display the login form, registration form, or both if the customer is not logged in and they attempt to access a restricted %s.', 'kb-support' ),
					$article_singular
				) . '</li>' .
                '<li>' . sprintf( 
					__( '<strong>Hide Restricted %1$s</strong> - Select to hide restricted %1$s from archives if the user is logged out. They are always hidden from website search results.', 'kb-support' ),
					$article_plural
				) . '</li>' .
				'<li>' . sprintf( 
					__( '<strong>Restricted Ajax Search</strong> - If selected, restricted %1$s will be hidden from ajax search results - i.e on the %2$s submission form.', 'kb-support' ),
					$article_plural,
					strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( 
					__( '<strong>Number of Results from Ajax</strong> - Enter the number of results you want an ajax search to return - i.e on the %1$s submission form.', 'kb-support' ),
					strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( 
					__( '<strong>Search Excerpt Length</strong> - Enter the number of characters that should be displayed from a %1$s during an ajax search. Enter <code>0</code> if you do not want an excerpt to be displayed.', 'kb-support' ),
					strtolower( $ticket_singular )
				) . '</li>' .
			'</ul>' .
            '<p>' . __( '<strong>Restricted Content Notices</strong>', 'kb-support' ) . '</p>' .
			'<ul>' .
				'<li>' . sprintf(
					__( '<strong>Single %1$s</strong> - Enter the text that is displayed to a user when they attempt to access a restricted %1$s.', 'kb-support' ),
					$article_singular
				) . '</li>' .
			'</ul>'
        )
	) );

    do_action( 'kbs_settings_before_emails_contextual_help', $screen );
	$screen->add_help_tab( array(
		'id'      => 'kbs-settings-emails',
		'title'   => __( 'Emails', 'kb-support' ),
		'content' => apply_filters( 'kbs_settings_emails_contextual_help',
			'<p>' . __( '<strong>Email Settings</strong>', 'kb-support' ) . '</p>' .
			'<ul>' .
				'<li>' . __( '<strong>From Name</strong> - Enter the name you want KB Support generated emails to come from.', 'kb-support' ) . '</li>' .
				'<li>' . __( '<strong>From Email</strong> - Enter the email address KB Support generated emails should come from.', 'kb-support' ) . '</li>' .
				'<li>' . __( '<strong>Email Template</strong> - Select <code>Default Template</code> to send formatted or basic HTML emails, or select plain text only (no formatting).', 'kb-support' ) . '</li>' .
				'<li>' . __( '<strong>Logo</strong> - Upload your logo and it will appear at the top of all KB Support generated HTML emails.', 'kb-support' ) . '</li>' .
                '<li>' . __( '<strong>Attach Files</strong> - This setting determines how files are inserted into emails when using the <code>{ticket_files}</code> or <code>{reply_files}</code> email tags.', 'kb-support' ) . ' ' .
                         __( 'When enabled, the files will be attached to the email. Otherwise, the files will be listed within the content as links to view, or download, the files online', 'kb-support' ) . 
                '</li>' .
                '<li>' . sprintf( __( '<strong>Copy Company Contact</strong> - Enabling this option will copy in the primary company contact to all customer emails that are sent in relation to %s associated with the company.', 'kb-support' ), strtolower( $ticket_plural ) ) .
                '</li>' .
                '<li>' . sprintf( __( '<strong>Copy Participants</strong> - Enabling this option will ensure that all email communication relating to a %1$s is also sent to all of the participants of the %1$s. This option is only visible when <code>Enable Participants?</code> is enabled within the %2$s settings tab.', 'kb-support' ), strtolower( $ticket_singular ), strtolower( $ticket_plural ) ) .
                '</li>' .
			'</ul>' .
			'<p>' . sprintf( __( '<strong>%s Logged</strong>', 'kb-support' ), $ticket_singular ) . '<br />' .
				sprintf( __( '<em>Adjust the settings for emails that are sent to a customer when they have logged a %1$s via a submission form.</em>', 'kb-support' ), strtolower( $ticket_singular ) ) . '</p>' .
			'<ul>' .
				'<li>' . sprintf( __( '<strong>Disable this Email</strong> - Select to stop emails being sent to the customer when they have logged a %1$s.', 'kb-support' ),
				strtolower( $ticket_singular )
				) . '</li>' .
                '<li>' . sprintf( __( '<strong>No Notification Emails</strong> - Email addresses entered here will not receive the initial <strong>%s Received</strong> email when they log a %s. Note that because the WordPress email system requires at least one <strong>To</strong> address, emails will not be sent to anyone due to receive a copy of the email either. Enter one email address per line and to stop sending to an entire domain, add the domain prefixed with <code>@</code>. Example: <em>(@domain.com)</em>', 'kb-support' ),
                $ticket_singular,
				strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( __( '<strong>Email Subject</strong> - Enter the subject for the email sent to customers when a %1$s is logged by them.', 'kb-support' ),
				strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( __( '<strong>Email Heading</strong> - Enter the heading to be displayed at the top of the email content for the email sent to customers when a %1$s is logged by them.', 'kb-support' ),
				strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( __( '<strong>Content</strong> - Enter the content of the email that is sent to a customer when they have logged a %1$s. A list of email tags you can use are displayed under the textarea.', 'kb-support' ),
				strtolower( $ticket_singular )
				) . '</li>' .
			'</ul>' .
			'<p>' . __( '<strong>Reply Added</strong>', 'kb-support' ) . '<br />' .
				sprintf( __( '<em>Adjust the settings for emails that are sent to a customer when a Support Worker adds a reply to their %1$s.</em>', 'kb-support' ), strtolower( $ticket_singular ) ) . '</p>' .
			'<ul>' .
				'<li>' . sprintf( __( '<strong>Disable this Email</strong> - Select to stop emails being sent to the customer when they receive a reply to their %1$s.', 'kb-support' ),
				strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( __( '<strong>Email Subject</strong> - Enter the subject for the email sent to customers when a Support Worker has added a reply to their %1$s.', 'kb-support' ),
				strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( __( '<strong>Email Heading</strong> - Enter the heading to be displayed at the top of the email content for the email sent to customers when a reply is added to their %1$s.', 'kb-support' ),
				strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( __( '<strong>Content</strong> - Enter the content of the email that is sent to a customer when they receive a reply to their %1$s. A list of email tags you can use are displayed under the textarea.', 'kb-support' ),
				strtolower( $ticket_singular )
				) . '</li>' .
			'</ul>' .
			'<p>' . sprintf( __( '<strong>%1$s Closed</strong>', 'kb-support' ), $ticket_singular ) . '<br />' .
				sprintf( __( '<em>Adjust the settings for emails that are sent to a customer when a Support Worker closes their %1$s.</em>', 'kb-support' ), strtolower( $ticket_singular ) ) . '</p>' .
			'<ul>' .
				'<li>' . sprintf( __( '<strong>Disable this Email</strong> - Select to stop emails being sent to the customer when their %1$s is closed.', 'kb-support' ),
				strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( __( '<strong>Email Subject</strong> - Enter the subject for the email sent to customers when a Support Worker has closed their %1$s.', 'kb-support' ),
				strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( __( '<strong>Email Heading</strong> - Enter the heading to be displayed at the top of the email content for the email sent to customers when a their %1$s is closed.', 'kb-support' ),
				strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( __( '<strong>Content</strong> - Enter the content of the email that is sent to a customer when their %1$s is closed. A list of email tags you can use are displayed under the textarea.', 'kb-support' ),
				strtolower( $ticket_singular )
				) . '</li>' .
			'</ul>' .
			'<p>' . __( '<strong>Notifications</strong>', 'kb-support' ) . '<br />' .
				sprintf( __( '<em>Adjust the settings for emails that are sent to Support Workers when a new %1$s is logged or a reply is received.</em>', 'kb-support' ), strtolower( $ticket_singular ) ) . '</p>' .
			'<ul>' .
				'<li>' . sprintf( __( '<strong>Disable Notifications</strong> - Select to stop emails being sent to Support Workers when a new %1$s is logged.', 'kb-support' ),
				strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( __( '<strong>%1$s Notification Subject</strong> - Enter the subject for the email sent to Support Workers when a new %2$s is logged.', 'kb-support' ),
				$ticket_singular,
				strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( __( '<strong>%1$s Notification</strong> - Enter the content of the email that is sent to Support Workers when a %2$s is submitted from the a customer. A list of email tags you can use are displayed under the textarea.', 'kb-support' ),
				$ticket_singular,
				strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . __( '<strong>Reply Notification Subject</strong> - Enter the subject for the email sent to Support Workers a new reply is added to an existing %2$s.', 'kb-support' ) . '</li>' .
				'<li>' . sprintf( __( '<strong>%1$s Reply Notification</strong> - Enter the content of the email that is sent to Support Workers when a %2$s reply is received from the customer. A list of email tags you can use are displayed under the textarea.', 'kb-support' ),
				$ticket_singular,
				strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( __( '<strong>%1$s Notification Emails</strong> - Enter a list of email addresses (one per line) that should be notified when a new %2$s is logged by a customer. Enter <code>%3$s</code> to include the assigned agent\'s email address.', 'kb-support' ),
				$ticket_singular,
				strtolower( $ticket_singular ),
				'{agent}'
				) . '</li>' .
                '<li>' . sprintf( __( '<strong>Assignment Notices</strong> - If enabled, agents will receive email notifications when a %s is assigned to them.', 'kb-support' ),
				strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( __( '<strong>Agent Assignment Subject</strong> - Enter the subject for the email sent to Support Workers when a %s is assigned to them.', 'kb-support' ),
				strtolower( $ticket_singular )
				) . '</li>' .
				'<li>' . sprintf( __( '<strong>Agent Assigned Notification</strong> - Enter the content of the email that is sent to Support Workers when a %s is assigned to them. A list of email tags you can use are displayed under the textarea.', 'kb-support' ),
				strtolower( $ticket_singular )
				) . '</li>' .
			'</ul>'
        )
	) );

    do_action( 'kbs_settings_before_compliance_contextual_help', $screen );
	$screen->add_help_tab( array(
		'id'      => 'kbs-settings-compliance',
		'title'   => __( 'Compliance', 'kb-support' ),
		'content' => apply_filters( 'kbs_settings_compliance_contextual_help',
			'<p>' . __( '<strong>Privacy Policy</strong>', 'kb-support' ) . '<br />' .
				sprintf( __( '<em>Use these options to specify your requirements for GDPR.</em>', 'kb-support' ), $ticket_singular ) . '</p>' .
            '<ul>' .
				'<li>' . sprintf( __( '<strong>Agree to Privacy Policy?</strong> - Select this option to insert a field into your %1$s submission form that customers must select to indicate they have read and agreed to your Privacy Policy. Your <a href="%2$s">privacy policy</a> is <a href="%2$s">defined here</a>.', 'kb-support' ), strtolower( $ticket_singular ), admin_url( 'privacy.php' ) ) . '</li>' .
				'<li>' . __( '<strong>Agree to Privacy Policy Label</strong> - This is the label that will accompany the checkbox for privacy policy agreement.', 'kb-support' ) . '</li>' .
                '<li>' . __( '<strong>Agree to Privacy Policy Description</strong> - Optionally enter a description to be displayed below the <em>Agree to Privacy Policy</em> field.', 'kb-support' ) . '</li>' .
                '<li>' . sprintf( __( '<strong>%1$s</strong> - Select the action you want taken with regards to customer data that is stored within their %2$s when you action their request to anonymize or erase their data from your site.', 'kb-support' ), $ticket_plural, strtolower( $ticket_plural ) ) . '</li>' .
            '</ul>' .
            '<p>' . __( '<strong>Terms and Conditions</strong>', 'kb-support' ) . '<br />' .
				sprintf( __( '<em>You may choose to display a terms and conditions agreement field on your %1$s submission forms. You can define the settings here.</em>', 'kb-support' ), $ticket_singular ) . '</p>' .
			'<ul>' .
				'<li>' . sprintf( __( '<strong>Agree to Terms</strong> - Select this option to insert a field into your %1$s submission form that customers must select to indicate they have read and agreed to your Terms and Conditions.', 'kb-support' ), strtolower( $ticket_singular ) ) . '</li>' .
				'<li>' . __( '<strong>Agree to Terms Label</strong> - This is the label that will accompany the checkbox for terms agreement.', 'kb-support' ) . '</li>' .
                '<li>' . __( '<strong>Agree to Terms Description</strong> - Optionally enter a description to be displayed below the <em>Agree to Terms</em> field.', 'kb-support' ) . '</li>' .
				'<li>' . __( '<strong>Terms Heading</strong> - Enter a heading that will appear at the top of the Terms and Conditions pop-up window.', 'kb-support' ) . '</li>' .
				'<li>' . __( '<strong>Agreement Text</strong> - Enter your Terms and Conditions here.', 'kb-support' ) . '</li>' .
			'</ul>'
        )
	) );

    do_action( 'kbs_settings_before_styles_contextual_help', $screen );
	$screen->add_help_tab( array(
		'id'      => 'kbs-settings-styles',
		'title'   => __( 'Styles', 'kb-support' ),
		'content' => apply_filters( 'kbs_settings_styles_contextual_help',
			'<p>' . __( '<strong>Disable Styles</strong> - Select this option to stop KB Support loading its CSS style sheet. All default formatting of forms, fields and all other elements will be inherited from your currently active theme.', 'kb-support' ) . '</p>' .
            '<p>' . sprintf( __( '<strong>%s Status Colours</strong>', 'kb-support' ), $ticket_singular ) . '<br />' .
				sprintf( __( 'Choose colour codes for each individual %s status, as well as for when an agent or customer has recently replied.', 'kb-support' ), strtolower( $ticket_singular ) ) . '</p>'
        )
	) );

	if ( ! empty( $settings['extensions'] ) )	{
        do_action( 'kbs_settings_before_extensions_contextual_help', $screen );
		$screen->add_help_tab( array(
			'id'      => 'kbs-settings-extensions',
			'title'   => __( 'Extensions', 'kb-support' ),
			'content' => apply_filters( 'kbs_settings_extensions_contextual_help',
				'<p>' . __( 'The configuration settings for any KB Support extensions you have installed are controlled here.', 'kb-support' ) . '</p>'
			)
		) );
	}

	if ( ! empty( $settings['licenses'] ) )	{
        do_action( 'kbs_settings_before_licenses_contextual_help', $screen );
		$screen->add_help_tab( array(
			'id'      => 'kbs-settings-licenses',
			'title'   => __( 'Licenses', 'kb-support' ),
			'content' => apply_filters( 'kbs_settings_licenses_contextual_help',
				'<p>' . sprintf(
                    __( 'If you have any of the KB Support <a target="blank" href="%s">premium extensions</a> installed, you should enter their license keys here to ensure you receive the latest product updates.', 'kb-support' ),
                    'https://kb-support.com/extensions/'
                ) . '</p>'
            )
		) );
	}

    do_action( 'kbs_settings_before_misc_contextual_help', $screen );
	$screen->add_help_tab( array(
		'id'      => 'kbs-settings-misc',
		'title'   => __( 'Misc', 'kb-support' ),
		'content' => apply_filters( 'kbs_settings_misc_contextual_help',
			'<p>' . __( '<strong>Misc Settings</strong>', 'kb-support' ) . '</p>' .
			'<ul>' .
                '<li>' .
                    __( '<strong>Display Credit?</strong> - Enable this option to give credit for this free plugin by displaying <code>Powered by KB Support</code> on the KB Support front end pages.', 'kb-support' ) .
                '</li>' .
                '<li>' .
                    __( '<strong>Remove Rating Request?</strong> - Enabling this option will remove the KB Support request for a five star rating within the KBS admin pages.', 'kb-support' ) .
                '</li>' .
				'<li>' . sprintf(
                    __( '<strong>Remove Data on Uninstall?</strong> - Select to remove all KB Support data when the plugin is uninstalled. All %1$s, %2$s, Submission Forms, Customers and settings will be permanently deleted.', 'kb-support' ),
                    $ticket_plural,
                    $article_plural
                ) . '</li>' .
			'</ul>' .
			'<p>' . __( '<strong>Google reCAPTCHA</strong>', 'kb-support' ) . '<br />' .
				sprintf( __( '<em>If you want to use a Google reCAPTCHA within your %1$s submission form, you\'ll need to enter the settings here.</em>', 'kb-support' ), $ticket_singular ) . '</p>' .
			'<ul>' .
				'<li>' . __( '<strong>Site Key</strong> - Enter your Google reCAPTCHA site key here otherwise your reCAPTCHA field will not work.', 'kb-support' ) . '</li>' .
				'<li>' . __( '<strong>Secret</strong> - Enter your Google reCAPTCHA secret here otherwise your reCAPTCHA field will not work.', 'kb-support' ) . '</li>' .
				'<li>' . __( '<strong>reCAPTCHA Theme</strong> - Select a theme for your reCAPTCHA that fits in best with your website.', 'kb-support' ) . '</li>' .
				'<li>' . __( '<strong>reCAPTCHA Type</strong> - Choose between a reCAPTCHA image or audio.', 'kb-support' ) . '</li>' .
				'<li>' . __( '<strong>reCAPTCHA Size</strong> - Select a compact or normal sized reCAPTCHA.', 'kb-support' ) . '</li>' .
			'</ul>'
        )
	) );

	do_action( 'kbs_settings_contextual_help', $screen );

} // kbs_settings_contextual_help
add_action( 'load-kbs_ticket_page_kbs-settings', 'kbs_settings_contextual_help' );
