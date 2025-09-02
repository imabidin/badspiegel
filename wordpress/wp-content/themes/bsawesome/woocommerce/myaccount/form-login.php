<?php

/**
 * Login Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-login.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.9.0
 */

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

do_action('woocommerce_before_customer_login_form'); ?>

<form id="modern-login-form" class="modern-login-wrapper" method="post" novalidate>
	<div class="row g-3" id="username_email_validation">
		<div class="col-12" id="username_email_check_wrapper">
			<label class="form-label" for="username_email_check"><?php esc_html_e('Email address', 'woocommerce'); ?> <span class="required" aria-hidden="true">*</span><span class="visually-hidden"><?php esc_html_e('Required', 'woocommerce'); ?></span></label>
			<div class="position-relative">
				<input type="email" class="form-control" id="username_email_check" name="username_email_check" autocomplete="email" value="" placeholder="" required aria-required="true" />
				<button type="button" class="fade btn btn-link btn-sm link-body-emphasis position-absolute end-0 top-50 translate-middle-y" id="change-btn"><?php esc_html_e('Edit', 'woocommerce'); ?></button>
			</div>
			<div class="form-text"></div>
		</div>
		<div class="col-12 collapse" id="username_email_valid">
			<label class="form-label" for="password"><?php esc_html_e('Password', 'woocommerce'); ?> <span class="required" aria-hidden="true">*</span><span class="visually-hidden"><?php esc_html_e('Required', 'woocommerce'); ?></span></label>
			<div class="position-relative">
				<input class="form-control" type="password" name="password" id="password" autocomplete="current-password" required aria-required="true" />
				<button type="button" class="fade btn btn-link btn-sm link-body-emphasis position-absolute end-0 top-50 translate-middle-y" id="show-password-btn" aria-label="Passwort anzeigen">Passwort anzeigen</button>
			</div>
			<div class="form-text">Willkommen zurück! Bitte geben Sie Ihr Passwort ein.</div>
			<div class="mt-3"><a href="<?php echo esc_url(wp_lostpassword_url()); ?>"><?php esc_html_e('Lost your password?', 'woocommerce'); ?></a></div>
		</div>
		<div class="col-12 collapse" id="username_email_invalid">

			<div class="card mb-3">
			<div class="card-body">
				<h5 class="card-title mb-3">Vorteile als Mitglied</h5>
				
				<ul class="list-unstyled mb-last-0">
				<li class="d-flex align-items-start mb-3">
					<i class="fa-light fa-sharp fa-rocket fa-fw me-2 mt-1 text-primary"></i>
					<div>
					<strong class="fw-medium">Blitzschnell zur Kasse</strong>
					<small class="d-block text-muted">Adressen und Zahlungsdaten sicher für den nächsten Einkauf speichern.</small>
					</div>
				</li>
				
				<li class="d-flex align-items-start mb-3">
					<i class="fa-light fa-sharp fa-heart fa-fw me-2 mt-1 text-primary"></i>
					<div>
					<strong class="fw-medium">Wunschliste erstellen</strong>
					<small class="d-block text-muted">Produkte merken und später einfach wiederfinden.</small>
					</div>
				</li>
				
				<li class="d-flex align-items-start">
					<i class="fa-light fa-sharp fa-star fa-fw me-2 mt-1 text-primary"></i>
					<div>
					<strong class="fw-medium">Exklusive Angebote</strong>
					<small class="d-block text-muted">Zugang zu speziellen Rabatten nur für Mitglieder erhalten.</small>
					</div>
				</li>
				</ul>
				
			</div>
			</div>
			<p class="mb-0 small text-muted">
				Wenn Sie ein Konto erstellen, stimmen Sie unseren <a href="/agb/" rel="nofollow noopener">Nutzungsbedingungen</a> zu. Sie erfahren in unserer <a href="/datenschutz/" rel="nofollow noopener" target="_blank">Datenschutzerklärung</a>, wie wir Ihre Daten verarbeiten.
			</p>

		</div>
		<div class="col-12">
			<button type="button" class="btn btn-dark col-12 col-sm-auto" id="continue-btn"><?php esc_html_e('Next', 'woocommerce'); ?></button>
		</div>
	</div>
</form>

<?php do_action('woocommerce_after_customer_login_form'); ?>