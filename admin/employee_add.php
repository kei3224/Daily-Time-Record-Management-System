<?php
	include 'includes/session.php';

	if(isset($_POST['add'])){
		$firstname = $_POST['firstname'];
		$lastname = $_POST['lastname'];
		$address = $_POST['address'];
		$birthdate = $_POST['birthdate'];
		$contact = $_POST['contact'];
		$gender = $_POST['gender'];
		$position = $_POST['position'];
		$schedule = $_POST['schedule'];
		$filename = $_FILES['photo']['name'];
		$employee_id = generateEmployeeID($lastname, $hireDate, 3);

		// Check if a photo has been provided
		if(empty($filename)){
			$filename = '';
		}
		else{
			move_uploaded_file($_FILES['photo']['tmp_name'], '../images/'.$filename);    
		}

		
		//Inserting the employee to the employees table
		$sql = "INSERT INTO employees (employee_id, firstname, lastname, address, birthdate, contact_info, gender, position_id, schedule_id, photo, created_on) VALUES ('$employee_id', '$firstname', '$lastname', '$address', '$birthdate', '$contact', '$gender', '$position', '$schedule', '$filename', NOW())";
		if($conn->query($sql)){
			$_SESSION['success'] = 'Employee added successfully';
		}
		else{
			$_SESSION['error'] = $conn->error;
		}
	}
	else{
		$_SESSION['error'] = 'Fill up add form first';
	}

	header('location: employee.php');
	function generateEmployeeID($lastname, $hireDate, $incrementalNumber) {
		// Extract the last two digit of the employment year
		$year = substr($hireDate,2,2);
	
		// Extract the month and date with leading zeros
		$month = date("m", strtotime($hireDate));
		$day = date('d', strtotime($hireDate));
	
		// Format the incremental number with the leading zeros
		$formattedIncrementalNumber = sprintf("%03d", $incrementalNumber);
	
		// Create the employee ID based on the given specification
		$employeeID = "091" . strtoupper(substr($lastname,0,1)).$year.$month.$day.$formattedIncrementalNumber;
	
		return $employeeID;
	}
?>