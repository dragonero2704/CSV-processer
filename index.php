<?php
require('./db.php');
require('./csv.php');

// controllare https://csv.thephpleague.com/ per la libreria di processing del csv
$error = "";
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
    if (array_reverse(explode('.', $filename))[0] === 'csv') {
        $csv = new CSV($_FILES['fileupload']);
    } else {
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

    <?php
    $db = new Database();

    if (!$db->isConnected()) {
        echo $db->connerror['code'] . ":" . $db->connerror['message'];
    } else {
        $ris = $db->query("SELECT * FROM mms_slampdesk_anagrafica");
        if (!empty($db->error)) {
            echo "Errore nella query";
        } else {
            if ($ris->num_rows > 0) {
                while ($row = $ris->fetch_assoc()) {
                    echo "<p>" . $row['id'] . "</p>";
                }
            }
        }
    }
    //print_r($db);



    //print_r($ris->fetch_assoc());
    ?>

    <form action="<?php echo htmlentities($_SERVER['PHP_SELF']) ?>" method="post" enctype="multipart/form-data">
        <input class="fileinput" type="file" name="fileupload">
        <input type="submit" value="Invia">
    </form>

    <?php if (!empty($error)) {
        echo "<p class='error'>$error<p>";
    } ?>

    <?php


    if (!empty($csv)) {
        echo "<div class='tableContainer mt10'><table>";
        foreach ($csv->getRows() as $row) {
            echo "<tr>";
            foreach ($row as $element) {
                echo "<td>$element</td>";
            }
            echo "</tr>";
        }
        echo "</table></div>";


        //echo "<p>lorem</p>";

        $configuration = json_decode(file_get_contents("./configs/SERVIZI_WEB.json"));
        print_r($configuration);
        $cycles = $configuration->ROWSTEPS;
        $configtablename = $configuration->tablename;
        if ($configuration->controllo === true) {
            $prechecks = $configuration->checks;
        }
        //scorro il csv riga per riga
        $headers = $csv->getHeader();
        //print_r($headers);
        $row = $csv->getNextRow(); //salto la prima linea che è l'header
        $counter = 0;

        while ($row = $csv->getNextRow()) {
            //print_r($row);
            $counter++;
            //operazione da eseguire per ogni linea
            $skip = false;
            if ($configuration->controllo === true) {
                foreach ($prechecks as $column => $checked_value) {
                    if (trim($row[$headers[$column]]) !== $checked_value) {
                        echo "<p class='error'>Linea " . ($counter + 1) . " saltata: non ha passato i controlli. Dominio: " . $row[$headers[$column]] . " Anagrafica: " . $row[$headers['NOME CLIENTE']] . "</p>";
                        $skip = true;
                    }
                    if ($skip === true) break;
                }
            }

            foreach ($cycles as $key => $values) {
                $data = array();
                //la $key indica il tipo di servizio

                foreach ($values as $head => $option) {
                    //$head indica la colonna
                    if ($options->search === true) {
                        //cerca tabella
                        $target = $options->tableToSearch;
                    } else {
                        //settare l'oggetto $data
                    }
                }

                //$db->insertInto($configtablename, $data);
            }
        }
    }
    ?>

</body>

</html>