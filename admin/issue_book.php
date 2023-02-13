<?php

include '../database_connection.php';

include '../function.php';

if(!is_admin_login())
{
	header('location:../admin_login.php');
}

$error = '';

if(isset($_POST["issue_book_button"]))
{
    $formdata = array();

    if(empty($_POST["book_id"]))
    {
        $error .= '<li>ISBN Es requerido</li>';
    }
    else
    {
        $formdata['book_id'] = trim($_POST['book_id']);
    }

    if(empty($_POST["user_id"]))
    {
        $error .= '<li>ID del usuario es requerida</li>';
    }
    else
    {
        $formdata['user_id'] = trim($_POST['user_id']);
    }

    if($error == '')
    {
        //Check Book Available or Not

        $query = "
        SELECT * FROM lms_book 
        WHERE book_isbn_number = '".$formdata['book_id']."'
        ";

        $statement = $connect->prepare($query);

        $statement->execute();

        if($statement->rowCount() > 0)
        {
            foreach($statement->fetchAll() as $book_row)
            {
                //disponibilidad del libro
                if($book_row['book_status'] == 'Enable' && $book_row['book_no_of_copy'] > 0)
                {
                    //Exist el usuario

                    $query = "
                    SELECT user_id, user_status FROM lms_user 
                    WHERE user_unique_id = '".$formdata['user_id']."'
                    ";

                    $statement = $connect->prepare($query);

                    $statement->execute();

                    if($statement->rowCount() > 0)
                    {
                        foreach($statement->fetchAll() as $user_row)
                        {
                            if($user_row['user_status'] == 'Enable')
                            {
                                //Limíte de prestamos

                                $book_issue_limit = get_book_issue_limit_per_user($connect);

                                $total_book_issue = get_total_book_issue_per_user($connect, $formdata['user_id']);

                                if($total_book_issue < $book_issue_limit)
                                {
                                    $total_book_issue_day = get_total_book_issue_day($connect);

                                    $today_date = get_date_time($connect);

                                    $expected_return_date = date('Y-m-d H:i:s', strtotime($today_date. ' + '.$total_book_issue_day.' days'));

                                    $data = array(
                                        ':book_id'      =>  $formdata['book_id'],
                                        ':user_id'      =>  $formdata['user_id'],
                                        ':issue_date_time'  =>  $today_date,
                                        ':expected_return_date' => $expected_return_date,
                                        ':return_date_time' =>  '',
                                        ':book_fines'       =>  0,
                                        ':book_issue_status'    =>  'Issue'
                                    );

                                    $query = "
                                    INSERT INTO lms_issue_book 
                                    (book_id, user_id, issue_date_time, expected_return_date, return_date_time, book_fines, book_issue_status) 
                                    VALUES (:book_id, :user_id, :issue_date_time, :expected_return_date, :return_date_time, :book_fines, :book_issue_status)
                                    ";

                                    $statement = $connect->prepare($query);

                                    $statement->execute($data);

                                    $query = "
                                    UPDATE lms_book 
                                    SET book_no_of_copy = book_no_of_copy - 1, 
                                    book_updated_on = '".$today_date."' 
                                    WHERE book_isbn_number = '".$formdata['book_id']."' 
                                    ";

                                    $connect->query($query);

                                    header('location:issue_book.php?msg=add');
                                }
                                else
                                {
                                    $error .= 'Este usuario alcanzo el máximo de prestamos simultaneos';
                                }
                            }
                            else
                            {
                                $error .= '<li>Esta cuenta esta deshabilitada</li>';
                            }
                        }
                    }
                    else
                    {
                        $error .= '<li>Usuario no encontrado</li>';
                    }
                }
                else
                {
                    $error .= '<li>Libro no disponible</li>';
                }
            }
        }
        else
        {
            $error .= '<li>Libro no encontrado</li>';
        }
    }
}

if(isset($_POST["book_return_button"]))
{
    if(isset($_POST["book_return_confirmation"]))
    {
        $data = array(
            ':return_date_time'     =>  get_date_time($connect),
            ':book_issue_status'    =>  'Return',
            ':issue_book_id'        =>  $_POST['issue_book_id']
        );  

        $query = "
        UPDATE lms_issue_book 
        SET return_date_time = :return_date_time, 
        book_issue_status = :book_issue_status 
        WHERE issue_book_id = :issue_book_id
        ";

        $statement = $connect->prepare($query);

        $statement->execute($data);

        $query = "
        UPDATE lms_book 
        SET book_no_of_copy = book_no_of_copy + 1 
        WHERE book_isbn_number = '".$_POST["book_isbn_number"]."'
        ";

        $connect->query($query);

        header("location:issue_book.php?msg=return");
    }
    else
    {
        $error = 'Confirmación de recibido';
    }
}   

$query = "
	SELECT * FROM lms_issue_book 
    ORDER BY issue_book_id DESC
";

$statement = $connect->prepare($query);

$statement->execute();

include '../header.php';

?>
<div class="container-fluid py-4" style="min-height: 700px;">
	<h1>Administrador de prestamos</h1>
    <?php 

    if(isset($_GET["action"]))
    {
        if($_GET["action"] == 'add')
        {
    ?>
    <ol class="breadcrumb mt-4 mb-4 bg-light p-2 border">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="issue_book.php">Administrador de prestamos</a></li>
        <li class="breadcrumb-item active">Nuevo prestamo</li>
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
                    <i class="fas fa-user-plus"></i> Nuevo prestamo
                </div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label"> ISBN </label>
                            <input type="text" name="book_id" id="book_id" class="form-control" />
                            <span id="book_isbn_result"></span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"> ID del usuario</label>
                            <input type="text" name="user_id" id="user_id" class="form-control" />
                            <span id="user_unique_id_result"></span>
                        </div>
                        <div class="mt-4 mb-0">
                            <input type="submit" name="issue_book_button" class="btn btn-success" value="Prestar" />
                        </div>  
                    </form>
                    <script>
                    var book_id = document.getElementById('book_id');

                    book_id.onkeyup = function()
                    {
                        if(this.value.length > 2)
                        {
                            var form_data = new FormData();

                            form_data.append('action', 'search_book_isbn');

                            form_data.append('request', this.value);

                            fetch('action.php', {
                                method:"POST",
                                body:form_data
                            }).then(function(response){
                                return response.json();
                            }).then(function(responseData){
                                var html = '<div class="list-group" style="position:absolute; width:93%">';

                                if(responseData.length > 0)
                                {
                                    for(var count = 0; count < responseData.length; count++)
                                    {
                                        html += '<a href="#" class="list-group-item list-group-item-action"><span onclick="get_text(this)">'+responseData[count].isbn_no+'</span> - <span class="text-muted">'+responseData[count].book_name+'</span></a>';
                                    }
                                }
                                else
                                {
                                    html += '<a href="#" class="list-group-item list-group-item-action">No se encontro ningún libro</a>';
                                }

                                html += '</div>';

                                document.getElementById('book_isbn_result').innerHTML = html;
                            });
                        }
                        else
                        {
                            document.getElementById('book_isbn_result').innerHTML = '';
                        }
                    }

                    function get_text(event)
                    {
                        document.getElementById('book_isbn_result').innerHTML = '';

                        document.getElementById('book_id').value = event.textContent;
                    }

                    var user_id = document.getElementById('user_id');

                    user_id.onkeyup = function(){
                        if(this.value.length > 2)
                        {   
                            var form_data = new FormData();

                            form_data.append('action', 'search_user_id');

                            form_data.append('request', this.value);

                            fetch('action.php', {
                                method:"POST",
                                body:form_data
                            }).then(function(response){
                                return response.json();
                            }).then(function(responseData){
                                var html = '<div class="list-group" style="position:absolute;width:93%">';

                                if(responseData.length > 0)
                                {
                                    for(var count = 0; count < responseData.length; count++)
                                    {
                                        html += '<a href="#" class="list-group-item list-group-item-action"><span onclick="get_text1(this)">'+responseData[count].user_unique_id+'</span> - <span class="text-muted">'+responseData[count].user_name+'</span></a>';
                                    }
                                }
                                else
                                {
                                    html += '<a href="#" class="list-group-item list-group-item-action">No se encontro dicho usuario!</a>';
                                }
                                html += '</div>';

                                document.getElementById('user_unique_id_result').innerHTML = html;
                            });
                        }
                        else
                        {
                            document.getElementById('user_unique_id_result').innerHTML = '';
                        }
                    }

                    function get_text1(event)
                    {
                        document.getElementById('user_unique_id_result').innerHTML = '';

                        document.getElementById('user_id').value = event.textContent;
                    }

                    </script>
                </div>
            </div>
        </div>
    </div>
    <?php 
        }
        else if($_GET["action"] == 'view')
        {
            $issue_book_id = convert_data($_GET["code"], 'decrypt');

            if($issue_book_id > 0)
            {
                $query = "
                SELECT * FROM lms_issue_book 
                WHERE issue_book_id = '$issue_book_id'
                ";

                $result = $connect->query($query);

                foreach($result as $row)
                {
                    $query = "
                    SELECT * FROM lms_book 
                    WHERE book_isbn_number = '".$row["book_id"]."'
                    ";

                    $book_result = $connect->query($query);

                    $query = "
                    SELECT * FROM lms_user 
                    WHERE user_unique_id = '".$row["user_id"]."'
                    ";

                    $user_result = $connect->query($query);

                    echo '
                    <ol class="breadcrumb mt-4 mb-4 bg-light p-2 border">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="issue_book.php">Administrador de prestamos</a></li>
                        <li class="breadcrumb-item active">Detalles del prestamo</li>
                    </ol>
                    ';

                    if($error != '')
                    {
                        echo '<div class="alert alert-danger">'.$error.'</div>';
                    }

                    foreach($book_result as $book_data)
                    {
                        echo '
                        <h2>Detalles del libro</h2>
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%"> ISBN </th>
                                <td width="70%">'.$book_data["book_isbn_number"].'</td>
                            </tr>
                            <tr>
                                <th width="30%">Titulo del libro</th>
                                <td width="70%">'.$book_data["book_name"].'</td>
                            </tr>
                            <tr>
                                <th width="30%">Autor</th>
                                <td width="70%">'.$book_data["book_author"].'</td>
                            </tr>
                        </table>
                        <br />
                        ';
                    }

                    foreach($user_result as $user_data)
                    {
                        echo '
                        <h2>Detalles del usuario</h2>
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%"> ID del usuario</th>
                                <td width="70%">'.$user_data["user_unique_id"].'</td>
                            </tr>
                            <tr>
                                <th width="30%">Nombre</th>
                                <td width="70%">'.$user_data["user_name"].'</td>
                            </tr>
                            <tr>
                                <th width="30%">Dirección</th>
                                <td width="70%">'.$user_data["user_address"].'</td>
                            </tr>
                            <tr>
                                <th width="30%">Tel</th>
                                <td width="70%">'.$user_data["user_contact_no"].'</td>
                            </tr>
                            <tr>
                                <th width="30%"> Email </th>
                                <td width="70%">'.$user_data["user_email_address"].'</td>
                            </tr>
                            <tr>
                                <th width="30%">Imagen</th>
                                <td width="70%"><img src="'.base_url().'upload/' . $user_data["user_profile"].'" class="img-thumbnail" width="100" /></td>
                            </tr>
                        </table>
                        <br />
                        ';
                    }

                    $status = $row["book_issue_status"];

                    $form_item = '';

                    if($status == "Issue")
                    {
                        $status = '<span class="badge bg-warning">Prestado</span>';

                        $form_item = '
                        <label><input type="checkbox" name="book_return_confirmation" value="Yes" /> Confirmo que he recivido el libro</label>
                        <br />
                        <div class="mt-4 mb-4">
                            <input type="submit" name="book_return_button" value="Cerrar prestamo" class="btn btn-primary" />
                        </div>
                        ';
                    }

                    if($status == 'Not Return')
                    {
                        $status = '<span class="badge bg-danger">Vencido</span>';

                        $form_item = '
                        <label><input type="checkbox" name="book_return_confirmation" value="Yes" /> Confirmo que he recivido el libro</label><br />
                        <div class="mt-4 mb-4">
                            <input type="submit" name="book_return_button" value="Cerrar prestamo" class="btn btn-primary" />
                        </div>
                        ';
                    }

                    if($status == 'Return')
                    {
                        $status = '<span class="badge bg-primary">Regresado</span>';
                    }

                    echo '
                    <h2>Detalles del prestamo</h2>
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Fecha del prestamo</th>
                            <td width="70%">'.$row["issue_date_time"].'</td>
                        </tr>
                        <tr>
                            <th width="30%">Fecha esperada de regreso para prestamo</th>
                            <td width="70%">'.$row["expected_return_date"].'</td>
                        </tr>
                        <tr>
                            <th width="30%">Fecha de regreso</th>
                            <td width="70%">'.$row["return_date_time"].'</td>
                        </tr>
                        <tr>
                            <th width="30%">Estado del libro</th>
                            <td width="70%">'.$status.'</td>
                        </tr>
                        <tr>
                            <th width="30%">Total de multas</th>
                            <td width="70%">$ '.' '.$row["book_fines"].'</td>
                        </tr>
                    </table>
                    <form method="POST">
                        <input type="hidden" name="issue_book_id" value="'.$issue_book_id.'" />
                        <input type="hidden" name="book_isbn_number" value="'.$row["book_id"].'" />
                        '.$form_item.'
                    </form>
                    <br />
                    ';

                }
            }
        }
    }
    else
    {
    ?>
	<ol class="breadcrumb mt-4 mb-4 bg-light p-2 border">
		<li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Administrador de prestamos</li>
    </ol>

    <?php 
    if(isset($_GET['msg']))
    {
        if($_GET['msg'] == 'add')
        {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">Nuevo prestamo registrado<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
        }

        if($_GET["msg"] == 'return')
        {
            echo '
            <div class="alert alert-success alert-dismissible fade show" role="alert">Prestamo cerrado <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
            ';
        }
    }
    ?>

    <div class="card mb-4">
    	<div class="card-header">
    		<div class="row">
    			<div class="col col-md-6">
    				<i class="fas fa-table me-1"></i> Administrador de prestamos
                </div>
                <div class="col col-md-6" align="right">
                    <a href="issue_book.php?action=add" class="btn btn-success btn-sm">Nuevo</a>
                </div>
            </div>
        </div>
        <div class="card-body">
        	<table id="datatablesSimple">
        		<thead>
        			<tr>
        				<th> ISBN</th>
                        <th>ID del usuario</th>
                        <th>Fecha del prestamo</th>
                        <th>Fecha que se regresó</th>
                        <th>Multas</th>
                        <th>Estado</th>
                        <th>Detalles</th>
        			</tr>
        		</thead>
        		<tfoot>
        			<tr>
                    <th> ISBN</th>
                        <th>ID del usuario</th>
                        <th>Fecha del prestamo</th>
                        <th>Fecha que se regresó</th>
                        <th>Multas</th>
                        <th>Estado</th>
                        <th>Detalles</th>
        			</tr>
        		</tfoot>
        		<tbody>
        		<?php
        		if($statement->rowCount() > 0)
        		{
        			$one_day_fine = get_one_day_fines($connect);

        			set_timezone($connect);

        			foreach($statement->fetchAll() as $row)
        			{
        				$status = $row["book_issue_status"];

        				$book_fines = $row["book_fines"];

        				if($row["book_issue_status"] == "Issue")
        				{
        					$current_date_time = new DateTime(get_date_time($connect));
        					$expected_return_date = new DateTime($row["expected_return_date"]);

        					if($current_date_time > $expected_return_date)
        					{
        						$interval = $current_date_time->diff($expected_return_date);

        						$total_day = $interval->d;

        						$book_fines = $total_day * $one_day_fine;

        						$status = 'Not Return';

        						$query = "
        						UPDATE lms_issue_book 
													SET book_fines = '".$book_fines."', 
													book_issue_status = '".$status."' 
													WHERE issue_book_id = '".$row["issue_book_id"]."'
        						";

        						$connect->query($query);
        					}
        				}

        				if($status == 'Issue')
        				{
        					$status = '<span class="badge bg-warning">Activo</span>';
        				}

        				if($status == 'Not Return')
        				{
        					$status = '<span class="badge bg-danger">Vencido</span>';
        				}

        				if($status == 'Return')
        				{
        					$status = '<span class="badge bg-primary">Entregado</span>';
        				}

        				echo '
        				<tr>
        					<td>'.$row["book_id"].'</td>
        					<td>'.$row["user_id"].'</td>
        					<td>'.$row["issue_date_time"].'</td>
        					<td>'.$row["return_date_time"].'</td>
        					<td>$'.$book_fines.'</td>
        					<td>'.$status.'</td>
        					<td>
                                <a href="issue_book.php?action=view&code='.convert_data($row["issue_book_id"]).'" class="btn btn-info btn-sm">Detalles</a>
                            </td>
        				</tr>
        				';
        			}
        		}
        		else
        		{
        			echo '
        			<tr>
        				<td colspan="7" class="text-center">Data en blanco</td>
        			</tr>
        			';
        		}
        		?>
        		</tbody>
        	</table>
        </div>
    </div>
    <?php 
    }
    ?>
</div>

<?php 

include '../footer.php';

?>