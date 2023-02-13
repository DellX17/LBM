<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

include 'database_connection.php';

include 'function.php';

if(is_user_login())
{
	header('location:issue_book_details.php');
}

$message = '';

$success = '';

if(isset($_POST["register_button"]))
{
	$formdata = array();

	if(empty($_POST["user_email_address"]))
	{
		$message .= '<li>El correo es requerido</li>';
	}
	else
	{
		if(!filter_var($_POST["user_email_address"], FILTER_VALIDATE_EMAIL))
		{
			$message .= '<li>Correo invalido</li>';
		}
		else
		{
			$formdata['user_email_address'] = trim($_POST['user_email_address']);
		}
	}

	if(empty($_POST["user_password"]))
	{
		$message .= '<li>Una contraseña es requerida</li>';
	}
	else
	{
		$formdata['user_password'] = trim($_POST['user_password']);
	}

	if(empty($_POST['user_name']))
	{
		$message .= '<li>Un nombre de usuario es requerido</li>';
	}
	else
	{
		$formdata['user_name'] = trim($_POST['user_name']);
	}

	if(empty($_POST['user_address']))
	{
		$message .= '<li>Es requerida una dirección</li>';
	}
	else
	{
		$formdata['user_address'] = trim($_POST['user_address']);
	}

	if(empty($_POST['user_contact_no']))
	{
		$message .= '<li>Es requerido un número</li>';
	}
	else
	{
		$formdata['user_contact_no'] = trim($_POST['user_contact_no']);
	}

	if(!empty($_FILES['user_profile']['name']))
	{
		$img_name = $_FILES['user_profile']['name'];
		$img_type = $_FILES['user_profile']['type'];
		$tmp_name = $_FILES['user_profile']['tmp_name'];
		$fileinfo = @getimagesize($tmp_name);
		$width = $fileinfo[0];
		$height = $fileinfo[1];

		$image_size = $_FILES['user_profile']['size'];

		$img_explode = explode(".", $img_name);

		$img_ext = strtolower(end($img_explode));

		$extensions = ["jpeg", "png", "jpg"];

		if(in_array($img_ext, $extensions))
		{
			if($image_size <= 2000000)
			{
				if(true)
				{
					$new_img_name = time() . '-' . rand() . '.' . $img_ext;
					if(move_uploaded_file($tmp_name, "upload/".$new_img_name))
					{
						$formdata['user_profile'] = $new_img_name;
					}
				}
				else
				{
					$message .= '<li>Dimensiones exedentes a 225 X 225</li>';
				}
			}
			else
			{
				$message .= '<li>El máximo tamaño de imagen son 2mb</li>';
			}
		}
		else
		{
			$message .= '<li>Formato no valido</li>';
		}
	}
	else
	{
		$message .= '<li>Se requiere una imagen de usuario</li>';
	}

	if($message == '')
	{
		$data = array(
			':user_email_address'		=>	$formdata['user_email_address']
		);

		$query = "
		SELECT * FROM lms_user 
        WHERE user_email_address = :user_email_address
		";

		$statement = $connect->prepare($query);

		$statement->execute($data);

		if($statement->rowCount() > 0)
		{
			$message = '<li>Correo en uso</li>';
		}
		else
		{
			$user_verificaton_code = md5(uniqid());

			$user_unique_id = 'U' . rand(10000000,99999999);

			$data = array(
				':user_name'			=>	$formdata['user_name'],
				':user_address'			=>	$formdata['user_address'],
				':user_contact_no'		=>	$formdata['user_contact_no'],
				':user_profile'			=>	$formdata['user_profile'],
				':user_email_address'	=>	$formdata['user_email_address'],
				':user_password'		=>	$formdata['user_password'],
				':user_verificaton_code'=>	$user_verificaton_code,
				':user_verification_status'	=>	'No',
				':user_unique_id'		=>	$user_unique_id,
				':user_status'			=>	'Enable',
				':user_created_on'		=>	get_date_time($connect)
			);

			$query = "
			INSERT INTO lms_user 
            (user_name, user_address, user_contact_no, user_profile, user_email_address, user_password, user_verificaton_code, user_verification_status, user_unique_id, user_status, user_created_on) 
            VALUES (:user_name, :user_address, :user_contact_no, :user_profile, :user_email_address, :user_password, :user_verificaton_code, :user_verification_status, :user_unique_id, :user_status, :user_created_on)
			";

			$statement = $connect->prepare($query);

			$statement->execute($data);

			//Ya no me da tiempo de hacer la validación por correo así que se hace desde un dbmanager

			/*require 'vendor/autoload.php';

			$mail = new PHPMailer(true);

			$mail->isSMTP();

			$mail->Host = 'smtp.gmail.com';  

			$mail->SMTPAuth = true;

			$mail->Username = 'danieldellamas@gmail.com';  

			$mail->Password = 'xxx'; 

			$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

			$mail->Port = 80;

			$mail->setFrom('danieldellamas@gmail.com', 'Seggs');

			$mail->addAddress($formdata['user_email_address'], $formdata['user_name']);

			$mail->isHTML(true);

			$mail->Subject = 'Registration Verification';

			$mail->Body = '
			 <p>Thank you for registering your Unique ID is <b>'.$user_unique_id.'

                <p>This is a verification email, please click the link to verify your email address.</p>
                <p><a href="'.base_url().'verify.php?code='.$user_verificaton_code.'">Click to Verify</a></p>
                <p>Thank you...</p>
			';

			$mail->send();*/
			$success = 'Se ha creado tu usuario con la ID: ' . $formdata['user_unique_id'] . ', esa sera tu identificación para realizar prestamos! puedes regresar y hacer login';
		}

	}
}

include 'header.php';

?>


<div class="d-flex align-items-center justify-content-center mt-5 mb-5" style="min-height:700px;">
	<div class="col-md-6">
		<?php 

		if($message != '')
		{
			echo '<div class="alert alert-danger"><ul>'.$message.'</ul></div>';
		}

		if($success != '')
		{
			echo '<div class="alert alert-success">'.$success.'</div>';
		}

		?>
		<div class="card">
			<div class="card-header">Nuevo registro de usuario</div>
			<div class="card-body">
				<form method="POST" enctype="multipart/form-data">
					<div class="mb-3">
						<label class="form-label">Email</label>
						<input type="text" name="user_email_address" id="user_email_address" class="form-control" />
					</div>
					<div class="mb-3">
						<label class="form-label">Contraseña</label>
						<input type="password" name="user_password" id="user_password" class="form-control" />
					</div>
					<div class="mb-3">
						<label class="form-label">Nombre de usuario</label>
                        <input type="text" name="user_name" class="form-control" id="user_name" value="" />
                    </div>
					<div class="mb-3">
						<label class="form-label">Tel</label>
						<input type="text" name="user_contact_no" id="user_contact_no" class="form-control" />
					</div>
					<div class="mb-3">
						<label class="form-label">Dirección</label>
						<textarea name="user_address" id="user_address" class="form-control"></textarea>
					</div>
					<div class="mb-3">
						<label class="form-label">Foto</label><br />
						<input type="file" name="user_profile" id="user_profile" />
						<br />
						<span class="text-muted">Solo se admite .jpg y .png</span>
					</div>
					<div class="text-center mt-4 mb-2">
						<input type="submit" name="register_button" class="btn btn-primary" value="Register" />
					</div>
				</form>
			</div>
		</div>
	</div>
</div>


<?php 


include 'footer.php';

?>