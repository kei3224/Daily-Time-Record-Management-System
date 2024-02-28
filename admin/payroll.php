<?php include 'includes/session.php'; ?>
<?php
include '../timezone.php';
$range_to = date('m/d/Y');
$range_from = date('m/d/Y', strtotime('-30 day', strtotime($range_to)));
?>
<?php include 'includes/header.php'; ?>

<body class="hold-transition skin-blue sidebar-mini">
  <div class="wrapper">

    <?php include 'includes/navbar.php'; ?>
    <?php include 'includes/menubar.php'; ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
      <!-- Content Header (Page header) -->
      <section class="content-header">
        <h1>
          Reports
        </h1>
        <ol class="breadcrumb">
          <li><a href="#"><i class="fa fa-home"></i> Home</a></li>
          <li class="active">Reports</li>
        </ol>
      </section>
      <!-- Main content -->
      <section class="content">
        <?php
        if (isset($_SESSION['error'])) {
          echo "
            <div class='alert alert-danger alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
              <h4><i class='icon fa fa-warning'></i> Error!</h4>
              " . $_SESSION['error'] . "
            </div>
          ";
          unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
          echo "
            <div class='alert alert-success alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
              <h4><i class='icon fa fa-check'></i> Success!</h4>
              " . $_SESSION['success'] . "
            </div>
          ";
          unset($_SESSION['success']);
        }
        ?>
        <div class="row">
          <div class="col-xs-12">
            <div class="box">
              <div class="box-header with-border">
                <div class="pull-right">
                  <form method="POST" class="form-inline" id="payForm">
                    <div class="input-group">
                      <div class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                      </div>
                      <input type="text" class="form-control pull-right col-sm-8" id="reservation" name="date_range"
                        value="<?php echo (isset($_GET['range'])) ? $_GET['range'] : $range_from . ' - ' . $range_to; ?>">
                    </div>
                    <button type="button" class="btn btn-success btn-sm btn-flat" id="payroll"><span
                        class="glyphicon glyphicon-print"></span> Generate</button>
                    <button type="button" class="btn btn-primary btn-sm btn-flat csv-export"
                      onclick="exportTableToExcel('example1')"><span class="glyphicon glyphicon-print"></span> Export
                      CSV</button>
                    <!-- <button type="button" class="btn btn-primary btn-sm btn-flat" id="example1"><span class="glyphicon glyphicon-print"></span> Export CSV</button> -->
                  </form>
                </div>
              </div>
              <div class="box-body">
                <table id="example1" class="table table-bordered">
                  <thead>
                    <th>Employee Name</th>
                    <th>Employee ID</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Date</th>
                  </thead>
                  <tbody>
                    <?php
                    $sql = "SELECT *, SUM(amount) as total_amount FROM deductions";
                    $query = $conn->query($sql);
                    $drow = $query->fetch_assoc();
                    $deduction = $drow['total_amount'];
                    $to = date('Y-m-d');
                    $from = date('Y-m-d', strtotime('-30 day', strtotime($to)));

                    if (isset($_GET['range'])) {
                      $range = $_GET['range'];
                      $ex = explode(' - ', $range);
                      $from = date('Y-m-d', strtotime($ex[0]));
                      $to = date('Y-m-d', strtotime($ex[1]));
                    }

                    $sql = "SELECT *, SUM(num_hr) AS total_hr, attendance.employee_id AS empid FROM attendance LEFT JOIN employees ON employees.id=attendance.employee_id LEFT JOIN position ON position.id=employees.position_id WHERE date BETWEEN '$from' AND '$to' GROUP BY attendance.employee_id, attendance.date ORDER BY employees.lastname ASC, employees.firstname ASC";
                    $query = $conn->query($sql);
                    $total = 0;

                    while ($row = $query->fetch_assoc()) {
                      $empid = $row['empid'];
                      $casql = "SELECT *, SUM(amount) AS cashamount FROM cashadvance WHERE employee_id='$empid' AND date_advance BETWEEN '$from' AND '$to'";
                      $caquery = $conn->query($casql);
                      $carow = $caquery->fetch_assoc();
                      $cashadvance = $carow['cashamount'];

                      $status = ($row['status']) ? '<span class="label label-warning pull-right">ontime</span>' : '<span class="label label-danger pull-right">late</span>';

                      echo " 
                        <tr> 
                          <td>" . $row['lastname'] . ", " . $row['firstname'] . "</td> 
                          <td>" . $row['employee_id'] . "</td> 
                          <td>" . date('g:i A', strtotime($row['time_in'])) . $status . "</td>
                          <td>" . date('g:i A', strtotime($row['time_out'])) . "</td>
                          <td>" . date('m/d/Y', strtotime($row['date'])) . "</td> 
                        </tr> ";
                    }
                    ?>

                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>

    <?php include 'includes/footer.php'; ?>
  </div>
  <?php include 'includes/scripts.php'; ?>
  <script>
    $(function () {
      $('.edit').click(function (e) {
        e.preventDefault();
        $('#edit').modal('show');
        var id = $(this).data('id');
        getRow(id);
      });

      $('.delete').click(function (e) {
        e.preventDefault();
        $('#delete').modal('show');
        var id = $(this).data('id');
        getRow(id);
      });

      $("#reservation").on('change', function () {
        var range = encodeURI($(this).val());
        window.location = 'payroll.php?range=' + range;
      });

      $('#payroll').click(function (e) {
        e.preventDefault();
        $('#payForm').attr('action', 'payroll_generate.php');
        $('#payForm').submit();
      });

      // $('#payslip').click(function (e) {
      //   e.preventDefault();
      //   $('#payForm').attr('action', 'payslip_generate.php');
      //   $('#payForm').submit();
      // });

    });
    // function exportTableToXLS(tableId) {
    //   var uri = 'data:application/vnd.ms-excel;base64,' + function () {
    //     var table = document.getElementById(tableId);
    //     var output = 'sep=,\n';
    //     for (var rowIndex = 0; rowIndex < table.rows.length; rowIndex++) {
    //       var row = table.rows[rowIndex];
    //       for (var colIndex = 0; colIndex < row.cells.length; colIndex++) {
    //         var cell = row.cells[colIndex];
    //         output += cell.innerText + ',';
    //       }
    //       output += '\n';
    //     }
    //     return output;
    //   }();
    //   var link = document.createElement("a");
    //   link.href = uri;
    //   link.style = "visibility:hidden";
    //   link.download = tableId + ".xls";
    //   document.body.appendChild(link);
    //   link.click();
    //   document.body.removeChild(link);
    // }

    // // function exportTableToCSV(tableId) {
    //    var csvContent = "data:text/csv;charset=utf-8,";
    //    var table = document.getElementById(tableId);
    //    var rows = table.rows;

    //    for (var i = 0; i < rows.length; i++) {
    //      var cols = rows[i].cells;

    //      for (var j = 0; j < cols.length; j++) {
    //        csvContent += cols[j].innerText + ",";
    //      }

    //      csvContent += "\n";
    //    }

    //    var encodedUri = encodeURI(csvContent);
    //    var link = document.createElement("a");
    //    link.setAttribute("href", encodedUri);
    //    link.setAttribute("download", tableId + ".csv");
    //    document.body.appendChild(link);
    //    link.click();
    //  }

    //This s to export csv file option 1
     function exportTableToExcel(tableId, fileName) {
       var downloadLink;
       var dataType = 'application/vnd.ms-excel';
       var tableSelect = document.getElementById(tableId);
       var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');

       // Specify file name
       fileName = fileName ? fileName : 'excel_file.xls';

       // Create download link element
       downloadLink = document.createElement("a");

       document.body.appendChild(downloadLink);

       if (navigator.msSaveOrOpenBlob) {
         var blob = new Blob(['\ufeff', tableHTML], {
           type: dataType
         });
         navigator.msSaveOrOpenBlob(blob, fileName);
       } else {
         // Create a link to the file
         downloadLink.href = 'data:' + dataType + ', ' + tableHTML;

         // Setting the file name
         downloadLink.download = fileName;

         //triggering the function
         downloadLink.click();
       }
     }

    // Call the function with the ID of the table and the desired file name
    exportTableToExcel('myTable', 'Employee_Data');

    function getRow(id) {
      $.ajax({
        type: 'POST',
        url: 'position_row.php',
        data: { id: id },
        dataType: 'json',
        success: function (response) {
          $('#posid').val(response.id);
          $('#edit_title').val(response.description);
          $('#edit_rate').val(response.rate);
          $('#del_posid').val(response.id);
          $('#del_position').html(response.description);
        }
      });
    }


  </script>
  <?php include 'includes/datatable_initializer.php'; ?>
</body>

</html>