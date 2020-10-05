<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Compilador</title>
</head>
<body>
    <form action="src/uploadHandler.php" method="post" enctype="multipart/form-data">
        <label for="algoritmo">Envie o algoritmo a ser compilado:</label>
        <br>
        <input type="file" id="algoritmo" name="file">
        <br>
        <button type="submit">Enviar</button>
        <button type="reset">Limpar</button>
    </form>
</body>
</html>