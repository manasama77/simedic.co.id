<?php
/**
 * This template is used to display the login form with [kbs_login]
 */
global $kbs_login_redirect;
if ( ! is_user_logged_in() ) : ?>
	<div id="kbs_login_form_wrap">
		<?php do_action( 'kbs_notices' ); ?>
		<form id="kbs_login_form" class="kbs_form" action="" method="post">
			<fieldset id="kbs_login_form_fields">
				<legend><?php _e( 'Log into Your Account', 'kb-support' ); ?></legend>

				<?php do_action( 'kbs_login_fields_before' ); ?>

				<div class="container kbs_login_wrapper">
					<div class="mb-3">
                        <label for="kbs-user-login"><?php _e( 'Username or Email', 'kb-support' ); ?></label>
                        <input type="text" name="kbs_user_login" class="required kbs-input" id="kbs-user-login" required>
                    </div>

					<div class="mb-3">
                        <label for="kbs-user-pass"><?php _e( 'Password', 'kb-support' ); ?></label>
                        <input type="password" name="kbs_user_pass" class="password required kbs-input" id="kbs-user-pass" required>
                    </div>

                    <?php do_action( 'kbs_login_form_fields_before_submit' ); ?>
					<input type="hidden" name="kbs_redirect" value="<?php echo esc_url( $kbs_login_redirect ); ?>"/>
					<input type="hidden" name="kbs_login_nonce" value="<?php echo wp_create_nonce( 'kbs-login-nonce' ); ?>"/>
					<input type="hidden" name="kbs_action" value="user_login"/>

					<div class="row">
                        <div class="col-md-6 mb-3">
							<input id="kbs_login_submit" type="submit" class="kbs_submit" value="<?php _e( 'Log In', 'kb-support' ); ?>"/>
						</div>
						<div class="col-md-6 mb-3">
							<a href="<?php echo wp_lostpassword_url(); ?>" title="<?php _e( 'Lost Password', 'kb-support' ); ?>">
								<?php _e( 'Lost Password?', 'kb-support' ); ?>
							</a>
						</div>
					</div><!-- .row -->
				</div><!-- .container -->

				<?php do_action( 'kbs_login_fields_after' ); ?>
			</fieldset>
		</form>
	</div>
<?php else : ?>
	<p class="kbs-logged-in"><?php _e( 'You are already logged in', 'kb-support' ); ?></p>
<?php endif; ?>
