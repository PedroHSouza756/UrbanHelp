<?php
if (isset($_POST['submit'])) {
    $tipo = $_POST['escolha'];
    
    if ($tipo == 'CPF') {
        header("Location: index.html");
        exit;
    } elseif ($tipo == 'CNPJ') {
        header("Location: indexfuncionario.html");
        exit;
    }
}
?>