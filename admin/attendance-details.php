<?php
    session_start();
    require '../connection.php';
    $user = array();
    if (isset($_SESSION['username'])&&isset($_SESSION['password'])) {
        extract($_SESSION);
        $sql = "SELECT * FROM `users` WHERE `username` = '$username' AND `password` = '$password' AND `access` = 'admin'";
        $result = $conn->query($sql);
        if ($result->num_rows == 0) {
            session_destroy();
            header('Location: ../index.php');
            die();
        }
    }
    else {
      session_destroy();
      header('Location: ../index.php');
      die();
    }
    if (!isset($_GET['i']) || empty($_GET['i'])) {
      header('Location: home.php');
      die();
    }
    else {
      $uid = $conn->real_escape_string(strip_tags(base64_decode($_GET['i'])));
      $sql = "SELECT * FROM `users` WHERE `uid` = '$uid'";
      $result = $conn->query($sql);
      if (!$result->num_rows) {
        echo "<script>alert('User does not exist!'); window.location.replace('home.php');</script>";
        die();
      }
      else {
        $user = $result->fetch_assoc();
      }
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">

  <title>Attendance Details | AttendanceQR</title>

  <!-- Custom fonts for this template-->
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">

  <!-- Page level plugin CSS-->
  <link href="../vendor/datatables/dataTables.bootstrap4.css" rel="stylesheet">

  <!-- Custom styles for this template-->
  <link href="../css/sb-admin.css" rel="stylesheet">

</head>

<body id="page-top">

  <nav class="navbar navbar-expand navbar-dark bg-dark static-top">

    <a class="navbar-brand mr-1" href="home.php"><img src="../images/camera-svgrepo.svg" class="image image-responsive" height="45" width="45"> AttendanceQR</a>

    <button class="btn btn-link btn-sm text-white order-1 order-sm-0" id="sidebarToggle" href="#">
      <i class="fas fa-bars"></i>
    </button>

    <!-- Navbar Search -->
    <form class="d-none d-md-inline-block form-inline ml-auto mr-0 mr-md-3 my-2 my-md-0">
      <div class="input-group" style="visibility: hidden;">
        <input type="text" class="form-control" disabled placeholder="Search for..." aria-label="Search" aria-describedby="basic-addon2">
        <div class="input-group-append">
          <button class="btn btn-primary" type="button">
            <i class="fas fa-search"></i>
          </button>
        </div>
      </div>
    </form>

    <!-- Navbar -->
    <ul class="navbar-nav ml-auto ml-md-0">
      <li class="nav-item dropdown no-arrow">
        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <i class="fas fa-user-circle fa-fw"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
          <a class="dropdown-item" href="change-password.php">Change Password</a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">Logout</a>
        </div>
      </li>
    </ul>

  </nav>

  <div id="wrapper">

    <!-- Sidebar -->
    <ul class="sidebar navbar-nav">
      <li class="nav-item">
        <a class="nav-link" href="home.php">
          <i class="fas fa-fw fa-tachometer-alt"></i>
          <span>Dashboard</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="add-user.php">
          <i class="fas fa-fw fa-user"></i>
          <span>Add User</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="scan.php">
          <i class="fas fa-fw fa-qrcode"></i>
          <span>Scan QR</span>
        </a>
      </li>
    </ul>

    <div id="content-wrapper">

      <div class="container-fluid">

        <!-- Breadcrumbs-->
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <a href="home.php">Dashboard</a>
          </li>
          <li class="breadcrumb-item active">Attendance Details of <?php echo $user['name']; ?></li>
        </ol>
<?php
  $uid = $user['uid'];
  $sql = "SELECT * FROM `attendance` WHERE `uid` = '$uid'";
  $result = $conn->query($sql);
  $attendances = array();
  while ($row = $result->fetch_assoc()) {
    if ($row['action'] == "in") {
      $attendances[date("Y-m-d", strtotime($row['timestamp']))] = array("in-timestamp" => $row['timestamp'], "out-timestamp" => "-NA-");
    }
    else {
      $attendances[date("Y-m-d", strtotime($row['timestamp']))]['out-timestamp'] = $row['timestamp'];
    }
  }
?>
        <!-- DataTables Example -->
        <div class="card mb-3">
          <div class="card-header">
            <i class="fas fa-qrcode"></i>
            Attendance Details of <?php echo $user['name']; ?></div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-bordered text-center" id="dataTable" cellspacing="0">
                <thead>
                  <tr>
                    <th  style="vertical-align: middle;">S No.</th>
                    <th  style="vertical-align: middle;">Date</th>
                    <th  style="vertical-align: middle;">In Time</th>
                    <th  style="vertical-align: middle;">Out Time</th>
                  </tr>
                </thead>
                <tfoot>
                  <tr>
                    <th  style="vertical-align: middle;">S No.</th>
                    <th  style="vertical-align: middle;">Date</th>
                    <th  style="vertical-align: middle;">In Time</th>
                    <th  style="vertical-align: middle;">Out Time</th>
                  </tr>
                </tfoot>
                <tbody>
<?php
  $i = 0;
  foreach ($attendances as $date => $attendance) {
?>
                  <tr>
                    <td style="vertical-align: middle;"> <?php echo ++$i; ?> </td>
                    <td style="vertical-align: middle;"> <?php echo date("d F Y", strtotime($date)); ?> </td>
                    <td style="vertical-align: middle;"> <?php echo date("h:i:s A", strtotime($attendance['in-timestamp'])); ?> </td>
                    <td style="vertical-align: middle;"> <?php if ($attendance['out-timestamp'] != "-NA-") { echo date("h:i:s A", strtotime($attendance['out-timestamp'])); } else { echo $attendance['out-timestamp']; } ?> </td>
                  </tr>
<?php
  }
?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>
      <!-- /.container-fluid -->

      <!-- Sticky Footer -->
      <footer class="sticky-footer">
        <div class="container my-auto">
          <div class="copyright text-center my-auto">
            <span>Copyright © Attendance Management System 2019. Made with <i class="fas fa-heart text-danger"></i> by Interns at <a href="https://prismcode.in">PrismCode</a>.</span>
          </div>
        </div>
      </footer>

    </div>
    <!-- /.content-wrapper -->

  </div>
  <!-- /#wrapper -->

  <!-- Scroll to Top Button-->
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

  <!-- Logout Modal-->
  <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
          <button class="close" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
          <a class="btn btn-primary" href="logout.php">Logout</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap core JavaScript-->
  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

  <!-- Core plugin JavaScript-->
  <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>

  <!-- Page level plugin JavaScript-->
  <script src="../vendor/chart.js/Chart.min.js"></script>
  <script src="../vendor/datatables/jquery.dataTables.js"></script>
  <script src="../vendor/datatables/dataTables.bootstrap4.js"></script>

  <!-- Custom scripts for all pages-->
  <script src="../js/sb-admin.min.js"></script>

  <!-- Demo scripts for this page-->
  <script src="../js/demo/datatables-demo.js"></script>
  <script src="../js/demo/chart-area-demo.js"></script>

</body>

</html>
