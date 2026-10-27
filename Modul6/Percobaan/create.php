<?php
if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    include("koneksi.php");

    $result = mysqli_query($koneksi, "INSERT INTO pengguna(username,
        password) VALUES('$username','$password')");

    header("Location:index.php");
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Create/Add</title>
    </head>
    <body>
        <h1>Memasukkan Data pada database!</h1>
        <form action="create.php" method="POST">
            <table>
                <tr>
                    <td>Username</td>
                    <td><input type="text" name="username"></td>
                </tr>
                <tr>
                    <td>Password</td>
                    <td><input type="password" name="password"></td>
                </tr>
                <tr>
                    <td><input type="submit" name="submit"></td>
                </tr>
            </table>
        </form>
    </body>
</html>