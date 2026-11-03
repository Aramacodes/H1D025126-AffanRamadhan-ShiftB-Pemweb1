<?php
session_start();
$nama = $_POST['nama'];
$umur = $_POST['umur'];
$email = $_POST['email'];


$_SESSION['name'] = $nama;
$_SESSION['umur'] = $umur;
$_SESSION['email'] = $email;
header("Location:home.php");