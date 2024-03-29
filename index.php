<?php
require('./db.php');
require('./csv.php');

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
    if (array_reverse(explode('.', $filename))[0] == 'csv') {
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
        $ris = $db->query("SELECT * FROM mms_slampdesk_centro_di_costo WHERE voce LIKE'HOS'");
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
    $listino = array();
    $listino["HOSTING"] = array(
        140 => "Hosting_prof",
        "140" => "Hosting_prof",
        90 => "Hosting basic",
        "90" => "Hosting basic",
    );
    $listino["SMTP"] = array("15" => "SMTP%1000", 15 => "SMTP%1000");
    $listino["POSTA"] = array("25" => "Posta%5", 25 => "Posta%5", "19" => "Posta%3", 19 => "Posta%3");
    //$listino["DOMINIO"] = array("50" => "gestione%.it", 50 => "gestione%.it");

    if (!empty($csv)) {
        /*echo "<div class='tableContainer mt10'><table>";
        foreach ($csv->getRows() as $row) {
            echo "<tr>";
            foreach ($row as $element) {
                echo "<td>$element</td>";
            }
            echo "</tr>";
        }
        echo "</table></div>";*/

        $searchCache = array();
        $json = file("./configs/SERVIZI_WEB.json");
        //print_r($json);
        echo "<br>";
        //var_dump(file_get_contents("./configs/SERVIZI_WEB.json"));
        // $json = fopen( "./configs/SERVIZI_WEB.json", 'r');
        $json = file_get_contents("./configs/SERVIZI_WEB.json");

        $configuration = json_decode($json);
        // var_dump($configuration);
        $cycles = $configuration->ROWSTEPS;
        $configtablename = $configuration->tablename;
        if ($configuration->controllo == true) {
            $prechecks = $configuration->checks;
        }
        //scorro il csv riga per riga
        $headers = $csv->getHeader();
        //print_r($headers);
        $row = $csv->getNextRow(); //salto la prima linea che è l'header
        $counter = 1;

        $id_assignment = 1;

        while ($row = $csv->getNextRow()) {
            //print_r($row);
            $counter++;
            //operazione da eseguire per ogni linea
            $skip = false;
            if ($configuration->controllo == true) {
                foreach ($prechecks as $column => $checked_value) {
                    if (trim($row[$headers[$column]]) !== $checked_value) {
                        echo "<p class='error'>Linea " . ($counter + 1) . " saltata: non ha passato i controlli. Dominio: " . $row[$headers[$column]] . " Anagrafica: " . $row[$headers['NOME CLIENTE']] . "</p>";
                        $skip = true;
                    }
                    if ($skip == true) break;
                }
            }

            foreach ($cycles as $key => $values) {
                //ROWSTEPS: SERVIZIO => CAMPI
                $data = array();
                if (isset($configuration->dataPreset)) {
                    foreach ($configuration->dataPreset as $k => $v) {
                        $data[$k] = $v;
                    }
                }
                //la $key indica il tipo di servizio
                $DOMINIO = $row[$headers['DOMINIO']];
                $anagrafica = $row[$headers['NOME CLIENTE']];
                echo "<br><hr>$key $DOMINIO $anagrafica";

                $indexKey = $headers[$key];
                echo "prezzo: $row[$indexKey]";

                if ((empty($row[$indexKey]) or $row[$indexKey] == "0,00" or $row[$indexKey] == "€0,00") and $key !== "DOMINIO PREZZO") {
                    continue;
                }
                //salta se non c'è il prezzo
                foreach ($values as $head => $option) {
                    //echo "<p class='seeme'>$head</p>";
                    $index = $headers[$head];

                    if ($option->search == true) {
                        //cerca nella tabella corrispondente l'id
                        $searchTable = $option->tableToSearch;

                        $condition = $option->fieldToCompare;

                        $objective = $option->fieldToSearch;
                        $dataKey = $option->fieldToInsert;

                        if (!isset($option->valueToCompare)) {


                            $csvValue = $row[$index];
                            //echo "<p class='seeme'>$csvValue</p>";

                        } else {
                            $csvValue = $option->valueToCompare;
                            //echo "<p class='seeme'>$csvValue</p>";
                            if ($csvValue == "GetFromPrezzo") {
                                //echo "prezzo: " . $data['prezzo'];
                                if (isset($listino[$key][$data['prezzo']])) {
                                    $csvValue = $listino[$key][$data['prezzo']];
                                    //echo "<p class='seeme'>$csvValue</p>";

                                    $csvValue = addslashes($csvValue);
                                    $sql = "SELECT $objective FROM $searchTable WHERE $condition LIKE '%$csvValue%'";
                                    $ris = $db->query($sql);

                                    if (empty($db->error)) {
                                        $result = $ris->fetch_assoc();
                                        //prendo il primo risultato
                                        $dataVal = $result[$objective];
                                        $data[$dataKey] = $dataVal;
                                    } else {
                                        echo "<p>" . $db->error['code'] . ":" . $db->error['message'] . "</p>";
                                    }
                                } else {
                                    echo "<p class='error'>Search fallito, nessun listino trovato per $key, linea $counter: " . $row[$headers['DOMINIO']] . " Nome Cliente: " . $row[$headers['DOMINIO']] . "</p>";
                                }
                                continue;
                            }
                        }
                        //cerco nella cache
                        if (isset($searchCache[$csvValue])) {
                            //prendo il valore dalla cache
                            $data[$dataKey] = $searchCache[$csvValue];
                            continue;
                        }
                        //cerco il valore che mi serve
                        $csvValue = addslashes($csvValue);
                        $sql = "SELECT $objective FROM $searchTable WHERE $condition LIKE '%$csvValue%'";
                        $ris = $db->query($sql);

                        if (empty($db->error)) {
                            $result = $ris->fetch_assoc();
                            //prendo il primo risultato
                            $dataVal = $result[$objective];
                            $data[$dataKey] = $dataVal;

                            $searchCache[$csvValue] = $dataVal;
                        } else {
                            //errore
                            echo "<p class='error'>Search fallito per $key => $head Linea " . ($counter + 1) . " Dominio: " . $row[$headers['DOMINIO']] . " Anagrafica: " . $row[$headers['NOME CLIENTE']] . "</p>";
                            echo "<p>" . $db->error['code'] . ":" . $db->error['message'] . "</p>";
                        }


                        /*foreach($data as $k => $v){
                            echo "$k => $V";
                        }*/
                        continue;
                    }
                    if ($option->readFromCsv == "false") {
                        //echo "Non sono letto dal csv";
                        //dns
                        if (!empty($option->values) and is_array($option->values)) {
                            $valFromCsv = $row[$index];
                            $dataVal = $option->values[$valFromCsv];
                        }
                        $dataKey =  $option->fieldToInsert;

                        $data[$dataKey] = $dataVal;
                        continue;
                    }

                    $dataKey = $option->fieldToInsert;
                    $dataVal = $row[$index];

                    if (trim($head) == "F") {
                        if ($dataVal == "Fatturato 2022") {
                            $dataVal = 1;
                        } else {
                            $dataVal = 0;
                        }
                        $data[$dataKey] = $dataVal;
                    }


                    if (is_string($dataVal)) {
                        $dataVal = str_replace('€', '', $dataVal);
                        $dataVal = str_replace('.', '', $dataVal);
                        $dataVal = str_replace(',', '.', $dataVal);
                    }

                    if (is_numeric($dataVal)) $dataval = floatval($dataVal);


                    if (!empty($dataVal)) {
                        $data[$dataKey] = $dataVal;
                    }
                    //var_dump($data);
                }
                //controlli prima dell'insert
                //convertire i valori numerici in veri e propri int nell'array $data
                foreach ($data as $k => $v) {
                    if (is_numeric($v)) {
                        $data[$k] = floatval($v);
                    }
                    //controllo posta
                    if ($k == "scadenza") {
                        if ($key == "POSTA") {
                            $v = str_replace('/', '-', $v);
                            $arrayWithEmpty = explode(' ', $v);
                            $tmpStuff = array();
                            foreach ($arrayWithEmpty as $elementWithEmpty) {
                                if (!empty($elementWithEmpty))
                                    array_push($tmpStuff, $elementWithEmpty);
                            }
                            //var_dump($tmpStuff);
                            for ($i = 0; $i < sizeof($tmpStuff); $i++) {
                                $tmpStuff[$i] = trim($tmpStuff[$i]);
                                $tmpStuff[$i] = str_replace('.', '', $tmpStuff[$i]);
                            }
                            $data[$k] = $v = date_format(date_create($tmpStuff[1]), "Y-m-d H:i:s");
                            $data['note'] = $tmpStuff[0];
                        } else {
                            $v = str_replace('/', '-', $v);
                            $v = date_create($v);
                            $data[$k] = date_format($v, "Y-m-d H:i:s");
                        }
                    }
                }
                if (empty($data['prezzo']) and $key == "DOMINIO PREZZO") {
                    unset($data['id_listino']);
                }
                //if (empty($data['prezzo']) or empty($data['costo'])) $data = array();

                //assegnazione id e ordinamento
                $data['id'] = $id_assignment;
                $data['ordinamento'] = $id_assignment;
                $id_assignment++;
                echo "<br>";
                var_dump($data);
                if (!empty($data)) $ris = $db->insertInto($configtablename, $data);
                if (!empty($db->error)) {
                    echo "<p>" . $db->error['code'] . ":" . $db->error['message'] . "</p>";
                }
                if ($ris == false) {
                    echo "<p class='seeme'>Record già presente</p>";
                }
            }
        }

        echo "Sono arrivato alla fine!";
    }
    ?>

</body>

</html>