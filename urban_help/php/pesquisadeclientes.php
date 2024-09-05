<!DOCTYPE html>
<html>
<head>
    <title>Informações dos clientes disponíveis</title>
    <link rel="stylesheet" href="estiloPesquisa">
    <style>
        .button-container {
            background-color: white;
            padding: 10px;
            border: 1px solid #ccc;
            width: 100px;
            text-align: center;
        }

        .button-container a {
            text-decoration: none;
            color: #000;
        }
    </style>
</head>
<body>
<h1>Informações dos clientes disponíveis</h1><br><br>

<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "urban";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

$sql = "SELECT * FROM cliente";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>Nome</th><th>Email</th></tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['nome'] . "</td>";
        echo "<td><a href='mailto:" . $row['email'] . "'>" . $row['email'] . "</a></td>";
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo "Nenhum resultado encontrado.";
}

mysqli_close($conn);
?>


<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>

<div class="button-container">
    <a href="index.html">Voltar</a>
</div>

</body>
</html>
