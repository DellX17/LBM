<?php

include '../database_connection.php';

include '../function.php';

if(!is_admin_login())
{
	header('location:../admin_login.php');
}

$message = '';

$error = '';

if(isset($_POST['add_category']))
{
	$formdata = array();

	if(empty($_POST['category_name']))
	{
		$error .= '<li>El nombre es requerido</li>';
	}
	else
	{
		//No se si ya lo mensione pero uso trim para los espacios en blanco
		$formdata['category_name'] = trim($_POST['category_name']);
	
	}

	if($error == '')
	{
		$query = "
		SELECT * FROM lms_category 
        WHERE category_name = '".$formdata['category_name']."'
		";

		$statement = $connect->prepare($query);

		$statement->execute();

		if($statement->rowCount() > 0)
		{
			$error = '<li>Esta categoría ya existe!</li>';
		}
		else
		{
			$data = array(
				':category_name'			=>	$formdata['category_name'],
				':category_status'			=>	'Enable',
				':category_created_on'		=>	get_date_time($connect)
			);

			$query = "
			INSERT INTO lms_category 
            (category_name, category_status, category_created_on) 
            VALUES (:category_name, :category_status, :category_created_on)
			";

			$statement = $connect->prepare($query);

			$statement->execute($data);

			header('location:category.php?msg=add');
		}
	}
}

if(isset($_POST["edit_category"]))
{
	$formdata = array();

	if(empty($_POST["category_name"]))
	{
		$error .= '<li>El nombre es requerido</li>';
	}
	else
	{
		$formdata['category_name'] = $_POST['category_name'];
	}

	if($error == '')
	{
		$category_id = convert_data($_POST['category_id'], 'decrypt');

		$query = "
		SELECT * FROM lms_category 
        WHERE category_name = '".$formdata['category_name']."' 
        AND category_id != '".$category_id."'
		";

		$statement = $connect->prepare($query);

		$statement->execute();

		if($statement->rowCount() > 0)
		{
			$error = '<li>Ese nombre ya esta en uso</li>';
		}
		else
		{
			$data = array(
				':category_name'		=>	$formdata['category_name'],
				':category_updated_on'	=>	get_date_time($connect),
				':category_id'			=>	$category_id
			);

			$query = "
			UPDATE lms_category 
            SET category_name = :category_name, 
            category_updated_on = :category_updated_on  
            WHERE category_id = :category_id
			";

			$statement = $connect->prepare($query);

			$statement->execute($data);

			header('location:category.php?msg=edit');
		}
	}
}

if(isset($_GET["action"], $_GET["code"], $_GET["status"]) && $_GET["action"] == 'delete')
{
	$category_id = $_GET["code"];
	$status = $_GET["status"];
	$data = array(
		':category_status'			=>	$status,
		':category_updated_on'		=>	get_date_time($connect),
		':category_id'				=>	$category_id
	);
	$query = "
	UPDATE lms_category 
    SET category_status = :category_status, 
    category_updated_on = :category_updated_on 
    WHERE category_id = :category_id
	";

	$statement = $connect->prepare($query);

	$statement->execute($data);

	header('location:category.php?msg='.strtolower($status).'');
}


$query = "
SELECT * FROM lms_category 
    ORDER BY category_name ASC
";

$statement = $connect->prepare($query);

$statement->execute();

include '../header.php';

?>

<div class="container-fluid py-4" style="min-height: 700px;">
	<h1>Administrador de categorías</h1>
	<?php 

	if(isset($_GET['action']))
	{
		if($_GET['action'] == 'add')
		{
	?>

	<ol class="breadcrumb mt-4 mb-4 bg-light p-2 border">
		<li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
		<li class="breadcrumb-item"><a href="category.php">Administrador de categorías</a></li>
		<li class="breadcrumb-item active">Nueva</li>
	</ol>
	<div class="row">
		<div class="col-md-6">
			<?php 

			if($error != '')
			{
				echo '<div class="alert alert-danger alert-dismissible fade show" role="alert"><ul class="list-unstyled">'.$error.'</ul> <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
			}

			?>
			<div class="card mb-4">
				<div class="card-header">
					<i class="fas fa-user-plus"></i> Nueva categoría
                </div>
                <div class="card-body">

                	<form method="POST">

                		<div class="mb-3">
                			<label class="form-label">Nombre</label>
                			<input type="text" name="category_name" id="category_name" class="form-control" />
                		</div>

                		<div class="mt-4 mb-0">
                			<input type="submit" name="add_category" value="Agregar" class="btn btn-success" />
                		</div>

                	</form>

                </div>
            </div>
		</div>
	</div>


	<?php 
		}
		else if($_GET["action"] == 'edit')
		{
			$category_id = convert_data($_GET["code"],'decrypt');

			if($category_id > 0)
			{
				$query = "
				SELECT * FROM lms_category 
                WHERE category_id = '$category_id'
				";

				$category_result = $connect->query($query);

				foreach($category_result as $category_row)
				{
				?>
	
	<ol class="breadcrumb mt-4 mb-4 bg-light p-2 border">
		<li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
		<li class="breadcrumb-item"><a href="category.php">Administrador de categorías</a></li>
		<li class="breadcrumb-item active">Editar</li>
	</ol>
	<div class="row">
		<div class="col-md-6">
			<div class="card mb-4">
				<div class="card-header">
					<i class="fas fa-user-edit"></i> Editar detalles
				</div>
				<div class="card-body">

					<form method="post">

						<div class="mb-3">
							<label class="form-label">Nombre</label>
							<input type="text" name="category_name" id="category_name" class="form-control" value="<?php echo $category_row['category_name']; ?>" />
						</div>

						<div class="mt-4 mb-0">
							<input type="hidden" name="category_id" value="<?php echo $_GET['code']; ?>" />
							<input type="submit" name="edit_category" class="btn btn-primary" value="Editar" />
						</div>

					</form>

				</div>
			</div>

		</div>
	</div>

				<?php 
				}
			}
		}
	}
	else
	{	

	?>
	<ol class="breadcrumb mt-4 mb-4 bg-light p-2 border">
		<li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
		<li class="breadcrumb-item active">Administrador de categorías</li>
	</ol>

	<?php 

	if(isset($_GET['msg']))
	{
		if($_GET['msg'] == 'add')
		{
			echo '<div class="alert alert-success alert-dismissible fade show" role="alert">Nueva categoría añadida<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
		}

		if($_GET["msg"] == 'edit')
		{
			echo '<div class="alert alert-success alert-dismissible fade show" role="alert">Datos actualizados <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
		}
		if($_GET["msg"] == 'disable')
		{
			echo '<div class="alert alert-success alert-dismissible fade show" role="alert">Categoria deshabilitada! <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
		}

		if($_GET['msg'] == 'enable')
		{
			echo '<div class="alert alert-success alert-dismissible fade show" role="alert">Categoria habilitada! <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
		}
	}	

	?>

	<div class="card mb-4">
		<div class="card-header">
			<div class="row">
				<div class="col col-md-6">
					<i class="fas fa-table me-1"></i> Administrar Categorías
				</div>
				<div class="col col-md-6" align="right">
					<a href="category.php?action=add" class="btn btn-success btn-sm">Nueva</a>
				</div>
			</div>
		</div>
		<div class="card-body">

		
			<table id="datatablesSimple">
				<thead>
					<tr>
						<th>Nombre</th>
						<th>Estado</th>
						<th>Fecha de creación</th>
						<th>Ultima actualización</th>
						<th>Modificar registro</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th>Nombre</th>
						<th>Estado</th>
						<th>Fecha de creación</th>
						<th>Ultima actualización</th>
						<th>Modificar registro</th>
					</tr>
				</tfoot>
				<tbody>
				<?php 

				if($statement->rowCount() > 0)
				{
					foreach($statement->fetchAll() as $row)
					{
						$category_status = '';
						if($row['category_status'] == 'Enable')
						{
							$category_status = '<div class="badge bg-success">Habilitado</div>';
						}
						else
						{
							$category_status = '<div class="badge bg-danger">Deshabilitado</div>';
						}

						echo '
						<tr>
							<td>'.$row["category_name"].'</td>
							<td>'.$category_status.'</td>
							<td>'.$row["category_created_on"].'</td>
							<td>'.$row["category_updated_on"].'</td>
							<td class="text-right">
								<a href="category.php?action=edit&code='.convert_data($row["category_id"]).'" class="btn btn-sm btn-primary">Editar</a>
								<button name="delete_button" class="btn btn-danger btn-sm" onclick="delete_data(`'.$row["category_id"].'`, `'.$row["category_status"].'`)">Habilitado</button>
							</td>
						</tr>
						';
					}
				}
				else
				{
					echo '
					<tr>
						<td colspan="4" class="text-center">Data en blanco</td>
					</tr>
					';
				}

				?>
				</tbody>
			</table>

			<script>

				function delete_data(code, status)
				{
					var new_status = 'Enable';

					if(status == 'Enable')
					{
						new_status = 'Disable';
					}

					if(confirm("Confirmar cambio?"))
					{
						window.location.href="category.php?action=delete&code="+code+"&status="+new_status+"";
					}
				}

			</script>

		</div>
	</div>
	<?php 
	}
	?>

</div>

<?php 

include '../footer.php';

?>