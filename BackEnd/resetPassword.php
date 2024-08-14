<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DataBase.php';


    $rootFolder = basename($_SERVER['DOCUMENT_ROOT']);
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . str_replace('/pages', '', dirname($_SERVER['SCRIPT_NAME']));

	use \Firebase\JWT\JWT;
	use \Firebase\JWT\Key;

	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	
	
	// Check if the token parameter is present in the URL
	if(isset($_GET['token'])) {
		$token = $_GET['token'];


		$db = new DataBase();
		$sql_select = "SELECT * FROM tbl_reset_password_tokens WHERE token = ? ORDER BY id DESC LIMIT 1 ";
		$db->select($sql_select,[$token]);

		// decode JWT
		$secret_key = 'In3Sg/jhwLg2BsRzQ961/A==';

		try {
		    $jwt_decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
		    // print_r($jwt_decoded);

			// Compare the timestamp with the current time
			if ($jwt_decoded->exp <  time()) {
				echo "The timestamp is in the past and expired.";
			}


		} catch (Exception $e) {
		    echo 'Error decoding JWT: ' . $e->getMessage();
		}


		// Optionally, you may want to validate the token here
		// For example, check if the token is properly formatted and not expired

		// Proceed with reset password logic...
	} else {
		// Token parameter is not present in the URL
		// Handle the case when the token is missing
		echo "Token not found in URL.";
		// Redirect or display an error message as appropriate
	}


?>

<?php ob_start(); ?>


<?php $styles = ob_get_clean(); ?>


<?php ob_start(); ?>
<body class="ltr main-body leftmenu error-1">
<?php $custombody = ob_get_clean(); ?>

<?php ob_start(); ?>
<div class="page main-signin-wrapper">
<?php $custompage = ob_get_clean(); ?>

			<!-- Row -->
			<div class="row signpages text-center">
				<div class="col-md-12">
					<div class="card">
						<div class="row row-sm">
							<div class="col-lg-6 col-xl-5 d-none d-lg-block text-center bg-primary details">
								<div class="mt-5 pt-5 p-2 pos-absolute">
									<img src="<?php echo $baseUrl; ?>/assets/img/brand/logo-light-icon.png" class="header-brand-img mb-4" alt="logo">
									<img src="<?php echo $baseUrl; ?>/assets/img/brand/logo-light-text.png" class="header-brand-img mb-4" alt="logo">
									<div class="clearfix"></div>
									<img src="<?php echo $baseUrl; ?>/assets/img/svgs/user.svg" class="ht-100 mb-0" alt="user">
									<h5 class="mt-4 text-white">Reset password</h5>
									<span class="tx-white-6 tx-13 mb-5 mt-xl-0">Signup to create, discover and connect with the global community</span>
								</div>
							</div>
							<div class="col-lg-6 col-xl-7 col-xs-12 col-sm-12 login_form ">
								<div class="main-container container-fluid">
									<div class="row row-sm">
										<div class="card-body mt-2 mb-2">
											<img src="<?php echo $baseUrl; ?>/assets/img/brand/logo-light.png" class="d-lg-none header-brand-img text-start float-start mb-4 error-logo-light" alt="logo">
											<img src="<?php echo $baseUrl; ?>/assets/img/brand/logo.png" class=" d-lg-none header-brand-img text-start float-start mb-4 error-logo" alt="logo">
											<div class="clearfix"></div>
											<h5 class="text-start mb-2">Create New Password</h5>
											<p class="mb-4 text-muted tx-13 ms-0 text-start">It's free to signup and only takes a minute.</p>
											<form id="reset-password" action="#" method="post">
												<input type="hidden" id="token" name="token" value='<?=$token?>'>
												<div class="form-group text-start">
													<label>New Password</label>
													<input class="form-control" id="cust-password" name="cust-password" placeholder="Enter your new password" type="password">
												</div>
												<div class="form-group text-start">
													<label>Confirm Password</label>
													<input class="form-control" id="conf-cust-password" name="conf-cust-password" placeholder="Confirm your password" type="password">
												</div>
												<button type="submit" class="btn btn-main-primary btn-block text-white">Create Account</button>
											</form>
											<div class="text-start mt-5 ms-0">
												<p class="mb-0">Do you remembered your password? <a href="/">Sign In</a></p>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- End Row -->

<?php $content = ob_get_clean(); ?>

<?php ob_start(); ?>


<?php $scripts = ob_get_clean(); ?>

<?php include 'layouts/custom-base.php'; ?>


<script>
	// Add an event listener to the form submission
	$(document).on("submit", "#reset-password", function(event) {
		event.preventDefault(); // Prevent the default form submission
		
		// Get the username and password from the form
		const token = $("#token").val();
		const password = $("#cust-password").val();
		const confirmedPassword = $("#conf-cust-password").val();
		
		// Send the login credentials to your API using AJAX
		$.ajax({
			url: "https://cp.educhecks.com/api/user/", // Replace with your API endpoint
			type: "POST",
			contentType: "application/json",
			dataType: "json",
			data: JSON.stringify({ "action": "resetPassword", "token": token, "conf-cust-password": confirmedPassword, "cust-password": password }),
			success: function(response) {
				// Successful response from the API
				if (response.token) {
					// Token received, handle accordingly (e.g., store in localStorage, redirect)
					console.log("Login successful. Token:", response.token);
					localStorage.setItem('jwtToken', response.token);
					// Redirect to dashboard or any other page
					window.location.href = "/";
				} else {
					// Handle login error (e.g., display error message)
					console.error("Login failed:", response.error);
					toastr.error('Files expiration limit updated successfully!', 'Notification', { timeOut: 2000 });
				}
			},
			error: function(xhr, status, error) {
				// Handle AJAX error (e.g., display error message)
				console.error("AJAX error:", error);
			}
		});
	});
</script>
