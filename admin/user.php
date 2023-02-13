<?php

include '../database_connection.php';

include '../function.php';

if(!is_admin_login())
{
	header('location:../admin_login.php');
}

if(isset($_GET["action"], $_GET['status'], $_GET['code']) && $_GET["action"] == 'delete')
{
	$user_id = $_GET["code"];
	$status = $_GET["status"];

	$data = array(
		':user_status'		=>	$status,
		':user_updated_on'	=>	get_date_time($connect),
		':user_id'			=>	$user_id
	);

	$query = "
	UPDATE lms_user 
    SET user_status = :user_status, 
    user_updated_on = :user_updated_on 
    WHERE user_id = :user_id
	";

	$statement = $connect->prepare($query);

	$statement->execute($data);

	header('location:user.php?msg='.strtolower($status).'');
}

$query = "
	SELECT * FROM lms_user 
    ORDER BY user_id DESC
";

$statement = $connect->prepare($query);

$statement->execute();

include '../header.php';

?>

<div class="container-fluid py-4" style="min-height: 700px;">
	<h1>Base de usuarios</h1>
	<ol class="breadcrumb mt-4 mb-4 bg-light p-2 border">
		<li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Base de usuarios</li>
    </ol>
    <?php 
 	
 	if(isset($_GET["msg"]))
 	{
 		if($_GET["msg"] == 'disable')
 		{
 			echo '<div class="alert alert-success alert-dismissible fade show" role="alert">Usuario deshabilitado <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
 		}

 		if($_GET["msg"] == 'enable')
 		{
 			echo '
 			<div class="alert alert-success alert-dismissible fade show" role="alert">Usuario habilitado <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
 			';
 		}
 	}

    ?>
    <div class="card mb-4">
    	<div class="card-header">
    		<div class="row">
    			<div class="col col-md-6">
    				<i class="fas fa-table me-1"></i> Base de usuarios
    			</div>
    			<div class="col col-md-6" align="right">
    			</div>
    		</div>
    	</div>
    	<div class="card-body">
    		<table id="datatablesSimple">
    			<thead>
    				<tr>
    					<th>Foto</th>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Contraseña</th>
                        <th>Tel</th>
                        <th>Dirección</th>
                        <th>Verificado</th>
                        <th>Estatus</th>
                        <th>Fecha de registro</th>
                        <th>Última actualización</th>
                        <th>Habilitado</th>
    				</tr>
    			</thead>
    			<tfoot>
    				<tr>
					<th>Foto</th>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Contraseña</th>
                        <th>Tel</th>
                        <th>Dirección</th>
                        <th>Verificado</th>
                        <th>Estatus</th>
                        <th>Fecha de registro</th>
                        <th>Última actualización</th>
                        <th>Habilitado</th>
    				</tr>
    			</tfoot>
    			<tbody>
    			<?php 
    			if($statement->rowCount() > 0)
    			{
    				foreach($statement->fetchAll() as $row)
    				{
    					$user_status = '';
    					if($row['user_status'] == 'Enable')
    					{
    						$user_status = '<div class="badge bg-success">Habilitado</div>';
    					}
    					else
    					{
    						$user_status = '<div class="badge bg-danger">Deshabilitado</div>';
    					}
						
						$user_verification = '';
    					if($row['user_verification_status'] == 'Yes')
    					{
    						$user_verification = '<div class="badge bg-success">Verificado</div>';
    					}
    					else
    					{
    						$user_verification = '<div class="badge bg-danger">Pendiente</div>';
    					}
    					echo '
    					<tr>
    						<td><img src="../upload/'.$row["user_profile"].'" class="img-thumbnail" width="75" /></td>
    						<td>'.$row["user_unique_id"].'</td>
    						<td>'.$row["user_name"].'</td>
    						<td>'.$row["user_email_address"].'</td>
    						<td>'.$row["user_password"].'</td>
    						<td>'.$row["user_contact_no"].'</td>
    						<td>'.$row["user_address"].'</td>
							<td>'.$user_verification.'</td>
    						<td>'.$user_status.'</td>
    						<td>'.$row["user_created_on"].'</td>
    						<td>'.$row["user_updated_on"].'</td>
    						<td><button type="button" name="delete_button" class="btn btn-danger btn-sm" onclick="delete_data(`'.$row["user_id"].'`, `'.$row["user_status"].'`)">Habilitar</td>
    					</tr>
    					';
    				}
    			}
    			else
    			{
    				echo '

    				<tr>
    					<td colspan="12" class="text-center">No hay usuarios</td>
    				</tr>
    				';
    			}
    			?>
    			</tbody>
    		</table>
    	</div>
    </div>
</div>

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
						window.location.href="user.php?action=delete&code="+code+"&status="+new_status+"";
					}
				}

</script>

<?php 

include '../footer.php';

?>