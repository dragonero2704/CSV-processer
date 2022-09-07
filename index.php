<?php
require('./db.php');
require('./csv.php');
// controllare https://csv.thephpleague.com/ per la libreria di processing del csv

if (!empty($_FILES['fileupload'])) {
    try {
        // Undefined | Multiple Files | $_FILES Corruption Attack
        // If this request falls under any of them, treat it invalid.
        // Check $_FILES['upload']['error'] value.
        switch ($_FILES['fileupload']['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new RuntimeException('No file sent.');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new RuntimeException('Exceeded filesize limit.');
            default:
                throw new RuntimeException('Unknown errors.');
        }
    } catch (RuntimeException $e) {
        echo $e->getMessage();
    }

    //upload andato a buon fine
    //controllo che il file sia effetivamente un csv
    $filename = $_FILES['fileupload']['name'];
    if(array_reverse(explode('.', $filename))[0] === 'csv'){
        $csv = new CSV($_FILES['fileupload']);
    }else{
        $error = "Not a CSV file";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSV processer</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h1 class="title">CSV processer</h1>
    <p class="subtitle">dragonero2704</p>

    <h3 class="mt10 subtitle">Caricare un file CSV</h3>
    <form action="<?php echo htmlentities($_SERVER['PHP_SELF']) ?>" method="post" enctype="multipart/form-data">
        <input class="fileinput" type="file" name="fileupload">
        <input type="submit" value="Invia" name="submit">
    </form>
    <?php
    if(!empty($csv)){
        echo "<div class='tableContainer mt10'><table>";
        foreach ($csv->getRows() as $row) {
            echo "<tr>";
            foreach($row as $element){
                echo"<td>$element</td>";
            }
            echo "</tr>";
        }
        echo "</table></div>";

    }

    // $db = new Database();



    ?>

</body>

</html>