<?php
    $mahasiswa = [
        [
            "nama" => "Romi",
            "asal" => "Bogor",
            "umur" => 22
        ],
        [
            "nama" => "Anto",
            "asal" => "Depok",
            "umur" => 23
        ],
        [
            "nama" => "Putri",
            "asal" => "Bangka",
            "umur" => 21
        ]
    ];

    foreach ($mahasiswa as $mhs) {
        echo "Nama: " . $mhs["nama"] . "<br>";
        echo "Asal: " . $mhs["asal"] . "<br>";
        echo "Umur: " . $mhs["umur"] . "<br><br>";
    }
?>