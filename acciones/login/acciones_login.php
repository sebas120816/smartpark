<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (($_SERVER["REQUEST_METHOD"] == "POST")) {
    include('../../config/config.php');
    date_default_timezone_set("Europe/Madrid");
    $horaEnEspana = date("Y-m-d H:i:s");

    $correo = filter_var($_POST['emailUser'], FILTER_SANITIZE_EMAIL);
    if (filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $emailUser     = $_POST['emailUser'];
    }
    $passwordUser  = trim($_POST["passwordUser"]);

    $sqlVerificandoLogin = "SELECT id_usuario, email, password, rol FROM tbl_usuarios WHERE email = ? AND estado = 1 LIMIT 1";
    $stmtLogin = mysqli_prepare($con, $sqlVerificandoLogin);
    if (!$stmtLogin) {
        http_response_code(500);
        die("Error preparando login SMARTPARK: " . mysqli_error($con) . ". Verifica que importaste BD/smartpark.sql y que exista la tabla tbl_usuarios.");
    }
    mysqli_stmt_bind_param($stmtLogin, "s", $emailUser);
    mysqli_stmt_execute($stmtLogin);
    $resultLogin = mysqli_stmt_get_result($stmtLogin);

    if (mysqli_num_rows($resultLogin) == 1) {
        while ($rowData  = mysqli_fetch_assoc($resultLogin)) {
            $passwordBD = $rowData['password'];
            if (password_verify($passwordUser, $passwordBD)) {
                session_start(); //Creando la sesion ya que los datos son validos
                $_SESSION['IdUser']     = $rowData['id_usuario'];
                $_SESSION['emailUser']     = $rowData['email'];
                $_SESSION['rol'] = $rowData['rol'];

                $Update = "UPDATE tbl_usuarios SET ultima_sesion=? WHERE id_usuario=?";
                $stmtUpdate = mysqli_prepare($con, $Update);
                mysqli_stmt_bind_param($stmtUpdate, "si", $horaEnEspana, $rowData['id_usuario']);
                mysqli_stmt_execute($stmtUpdate);

                header("location:../../dashboard/?welcome=1");
            } else {
                header("location:../../?errorLogin=1");
            }
        }
    } else {
        header("location:../../?errorU=1");
    }
}
