<?php 
include '../database_connection.php';

include '../function.php';
$param1 = $_GET['param1'];

$query = "SELECT * FROM `lms`.`lms_issue_book` WHERE `issue_book_id` IS NOT NULL $param1
ORDER BY issue_book_id DESC
";

		$query;
        $statement = $connect->prepare($query);
        $statement->execute();
		
		echo $query;
		?>
        	<table id="datatablesSimple" class="table table-bordered table-dark" >
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