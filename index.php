<?php

include 'database_connection.php';
include 'function.php';

if(is_user_login())
{
	header('location:issue_book_details.php');
}

include 'header.php';



?>

<div class="p-5 mb-4 bg-dark bg-gradient rounded-3 text-center text-white">

	<div class="container-fluid py-5 center">

		<h1 class="display-5 fw-bold text-align-center">Sistema Prestateca</h1>
		<h5 class="disaply-5 ">Por Aarón Aguilar</h5>

	</div>

</div>

<div class="row align-items-md-stretch">

	<div class="col-md-6">

		<div class="h-100 p-5 text-white bg-dark rounded-3">

			<h2>Admin Login</h2>
			<p></p>
			<a href="admin_login.php" class="btn btn-outline-light">Admin Login</a>

		</div>

	</div>

	<div class="col-md-6">

		<div class="h-100 p-5 bg-light border rounded-3">

			<h2>Usuario Login</h2>

			<p></p>

			<a href="user_login.php" class="btn btn-outline-secondary">Usuario Login</a>

			<a href="user_registration.php" class="btn btn-outline-primary">Crear usuario</a>

		</div>

	</div>

</div>

<?php

include 'footer.php';

?>