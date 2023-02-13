<?php

include '../database_connection.php';

include '../function.php';

//Para que no sean listillos y quieran forzar la entrada haha
if(!is_admin_login())
{
	header('location:../admin_login.php');
}


include '../header.php';

?>

<div class="container-fluid py-4">
	<h1 class="mb-5">Dashboard</h1>
	<div class="row">
		<div class="col-xl-3 col-md-6">
			<div class="card bg-secondary text-white mb-4">
				<div class="card-body">
					<h1 class="text-center"><?php echo Count_total_issue_book_number($connect); ?></h1>
					<h5 class="text-center">Historial de prestamos</h5>
				</div>
			</div>
		</div>
		<div class="col-xl-3 col-md-6">
			<div class="card bg-secondary text-white mb-4">
				<div class="card-body">
					<h1 class="text-center"><?php echo Count_total_returned_book_number($connect); ?></h1>
					<h5 class="text-center">Libros regresados</h5>
				</div>
			</div>
		</div>
		<div class="col-xl-3 col-md-6">
			<div class="card bg-secondary text-white mb-4">
				<div class="card-body">
					<h1 class="text-center"><?php echo Count_total_not_returned_book_number($connect); ?></h1>
					<h5 class="text-center">Libros no regresados</h5>
				</div>
			</div>
		</div>
		<div class="col-xl-3 col-md-6">
			<div class="card bg-secondary text-white mb-4">
				<div class="card-body">
					<h1 class="text-center">$ <?php echo Count_total_fines_received($connect); ?></h1>
					<h5 class="text-center">Multas</h5>
				</div>
			</div>
		</div>
		<div class="col-xl-3 col-md-6">
			<div class="card bg-secondary text-white mb-4">
				<div class="card-body">
					<h1 class="text-center"><?php echo Count_total_book_number($connect); ?></h1>
					<h5 class="text-center">Total de libros</h5>
				</div>
			</div>
		</div>
		<div class="col-xl-3 col-md-6">
			<div class="card bg-secondary text-white mb-4">
				<div class="card-body">
					<h1 class="text-center"><?php echo Count_total_author_number($connect); ?></h1>
					<h5 class="text-center">Total de autores</h5>
				</div>
			</div>
		</div>
		<div class="col-xl-3 col-md-6">
			<div class="card bg-secondary text-white mb-4">
				<div class="card-body">
					<h1 class="text-center"><?php echo Count_total_category_number($connect); ?></h1>
					<h5 class="text-center">Total de categorías</h5>
				</div>
			</div>
		</div>
		<div class="col-xl-3 col-md-6">
			<div class="card bg-secondary text-white mb-4">
				<div class="card-body">
					<h1 class="text-center"><?php echo Count_total_location_rack_number($connect); ?></h1>
					<h5 class="text-center">Total de anaqueles</h5>
				</div>
			</div>
		</div>
	</div>
</div>

<?php

include '../footer.php';

?>