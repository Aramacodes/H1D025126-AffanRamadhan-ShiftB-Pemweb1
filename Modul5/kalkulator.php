<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="style.css">
    <title>Kalkulator Sederhana</title>
</head>

<body>
    <div id="container">
        <h2>Kalkulator Sederhana</h2> <br>

        <form method="post">
            <label for="angka1">Angka pertama:</label><br>
            <input id="angka1" type="number" name="angka1" required class="inputText">
            

            <label for="angka1">Angka kedua:</label><br>
            <input id="angka2" type="number" name="angka2" required class="inputText">
            

            <label for="operator">Operator:</label><br>
            <select name="operator" id="operator">
                <option value="+">Penjumlahan (+)</option>
                <option value="-">Pengurangan (-)</option>
                <option value="*">Perkalian (*)</option>
                <option value="/">Pembagian (/)</option>
            </select>
            <br><br>
        
            <button type="submit" name="hitung">Hitung</button>
        </form>
    
    <?php
    if (isset($_POST['hitung'])) {
        $angka1 = $_POST['angka1'];
        $angka2 = $_POST['angka2'];
        $operator = $_POST['operator'];

        if ($operator == "+") {
            $hasil = $angka1 + $angka2;
        } elseif ($operator == "-") {
            $hasil = $angka1 - $angka2;
        } elseif ($operator == "*") {
            $hasil = $angka1 * $angka2;
        } elseif ($operator == "/") {
            if ($angka2 == 0) {
                echo "Tidak bisa dibagi dengan nol!";
                exit;
            }
            $hasil = $angka1 / $angka2;
        }

        echo "<h3>Hasil: $hasil</h3>";
    }
    ?>
    </div>
</body>

</html>