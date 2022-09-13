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
    $listino["POSTA"] = array("25" => "Posta%5",25 => "Posta%5","19" => "Posta%3",19 => "Posta%3");
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


        //echo "<p>lorem</p>";
        $searchCache = array();
        //$json = file("./configs/SERVIZI_WEB.json");
        //print_r($json);
        echo "<br>";
        //var_dump(file_get_contents("./configs/SERVIZI_WEB.json"));
        //$json = fopen( "./configs/SERVIZI_WEB.json", 'r');

        //var_dump($json);
        $json = '{
            "tablename": "mms_slampdesk_anagrafica_servizi_attivi",
            "controllo": "false",
            "checks": {
                "F": "Fatturato 2022"
            },
            "dataPreset": {
                "enable": 1,
                "stato": 1,
                "id_cdc": "1"
            },
            "ROWSTEPS": {
                "DOMINIO": {
                    "NOME CLIENTE": {
                        "search": "true",
                        "fieldToInsert": "id_anagrafica",
                        "tableToSearch": "mms_slampdesk_anagrafica",
                        "fieldToSearch": "id",
                        "fieldToCompare": "voce"
                    },
                    "DOMINIO": {
                        "search": "true",
                        "fieldToInsert": "id_dominio",
                        "tableToSearch": "mms_slampdesk_domini",
                        "fieldToSearch": "id",
                        "fieldToCompare": "voce"
                    },
                    "DNS": {
                        "readFromCsv": "false",
                        "fieldToInsert": "dns",
                        "values": {
                            "Altro": "cliente",
                            "Register": "B039 Register",
                            "Promo": "Promo",
                            "Siteground": "Siteground"
                        }
                    },
                    "DOMINIO PREZZO": {
                        "search": "false",
                        "fieldToInsert": "prezzo"
                    },
                    "DOMINIO COSTO": {
                        "search": "false",
                        "fieldToInsert": "costo"
                    },
                    "SCADENZA DOMINIO": {
                        "search": "false",
                        "fieldToInsert": "scadenza"
                    },
                    "TIPOLOGIA": {
                        "search": "true",
                        "fieldToInsert": "id_tipologia",
                        "tableToSearch": "mms_slampdesk_opzioni",
                        "fieldToSearch": "id",
                        "fieldToCompare": "valore",
                        "valueToCompare": "Gest"
                    },
                    "LISTINO": {
                        "search": true,
                        "fieldToInsert": "id_listino",
                        "tableToSearch": "mms_slampdesk_listino",
                        "fieldToSearch": "id",
                        "fieldToCompare": "voce",
                        "valueToCompare": "mantenimento%it"
                    }
                },
                "HOSTING": {
                    "NOME CLIENTE": {
                        "search": "true",
                        "fieldToInsert": "id_anagrafica",
                        "tableToSearch": "mms_slampdesk_anagrafica",
                        "fieldToSearch": "id",
                        "fieldToCompare": "voce"
                    },
                    "DOMINIO": {
                        "search": "true",
                        "fieldToInsert": "id_dominio",
                        "tableToSearch": "mms_slampdesk_domini",
                        "fieldToSearch": "id",
                        "fieldToCompare": "voce"
                    },
                    "HOSTING": {
                        "search": "false",
                        "fieldToInsert": "prezzo"
                    },
                    "HOSTING COSTO": {
                        "search": "false",
                        "fieldToInsert": "costo"
                    },
                    "SCADENZA HOSTING": {
                        "search": "false",
                        "fieldToInsert": "scadenza"
                    },
                    "TIPOLOGIA": {
                        "search": "true",
                        "fieldToInsert": "id_tipologia",
                        "tableToSearch": "mms_slampdesk_opzioni",
                        "fieldToSearch": "id",
                        "fieldToCompare": "valore",
                        "valueToCompare": "Hosting"
                    },
                    "LISTINO": {
                        "search": true,
                        "fieldToInsert": "id_listino",
                        "tableToSearch": "mms_slampdesk_listino",
                        "fieldToSearch": "id",
                        "fieldToCompare": "voce",
                        "valueToCompare": "GetFromPrezzo"
                    }
                },
                "POSTA": {
                    "NOME CLIENTE": {
                        "search": "true",
                        "fieldToInsert": "id_anagrafica",
                        "tableToSearch": "mms_slampdesk_anagrafica",
                        "fieldToSearch": "id",
                        "fieldToCompare": "voce"
                    },
                    "DOMINIO": {
                        "search": "true",
                        "fieldToInsert": "id_dominio",
                        "tableToSearch": "mms_slampdesk_domini",
                        "fieldToSearch": "id",
                        "fieldToCompare": "voce"
                    },
                    "POSTA": {
                        "search": "false",
                        "fieldToInsert": "prezzo"
                    },
                    "POSTA COSTO": {
                        "search": "false",
                        "fieldToInsert": "costo"
                    },
                    "SCADENZA POSTA": {
                        "search": "false",
                        "fieldToInsert": "scadenza"
                    },
                    "TIPOLOGIA": {
                        "search": "true",
                        "fieldToInsert": "id_tipologia",
                        "tableToSearch": "mms_slampdesk_opzioni",
                        "fieldToSearch": "id",
                        "fieldToCompare": "valore",
                        "valueToCompare": "Posta"
                    },
                    "LISTINO": {
                        "search": true,
                        "fieldToInsert": "id_listino",
                        "tableToSearch": "mms_slampdesk_listino",
                        "fieldToSearch": "id",
                        "fieldToCompare": "voce",
                        "valueToCompare": "GetFromPrezzo"
                    }
                },
                "SMTP": {
                    "NOME CLIENTE": {
                        "search": "true",
                        "fieldToInsert": "id_anagrafica",
                        "tableToSearch": "mms_slampdesk_anagrafica",
                        "fieldToSearch": "id",
                        "fieldToCompare": "voce"
                    },
                    "DOMINIO": {
                        "search": "true",
                        "fieldToInsert": "id_dominio",
                        "tableToSearch": "mms_slampdesk_domini",
                        "fieldToSearch": "id",
                        "fieldToCompare": "voce"
                    },
                    "SMTP": {
                        "search": "false",
                        "fieldToInsert": "prezzo"
                    },
                    "SMATP COSTO": {
                        "search": "false",
                        "fieldToInsert": ""
                    },
                    "SCADENZA SMTP": {
                        "search": "false",
                        "fieldToInsert": "scadenza"
                    },
                    "TIPOLOGIA": {
                        "search": "true",
                        "fieldToInsert": "id_tipologia",
                        "tableToSearch": "mms_slampdesk_opzioni",
                        "fieldToSearch": "id",
                        "fieldToCompare": "valore",
                        "valueToCompare": "SMTP"
                    },
                    "LISTINO": {
                        "search": true,
                        "fieldToInsert": "id_listino",
                        "tableToSearch": "mms_slampdesk_listino",
                        "fieldToSearch": "id",
                        "fieldToCompare": "voce",
                        "valueToCompare": "GetFromPrezzo"
                    }
                },
                "ANTIVIRUS": {
                    "NOME CLIENTE": {
                        "search": "true",
                        "fieldToInsert": "id_anagrafica",
                        "tableToSearch": "mms_slampdesk_anagrafica",
                        "fieldToSearch": "id",
                        "fieldToCompare": "voce"
                    },
                    "DOMINIO": {
                        "search": "true",
                        "fieldToInsert": "id_dominio",
                        "tableToSearch": "mms_slampdesk_domini",
                        "fieldToSearch": "id",
                        "fieldToCompare": "voce"
                    },
                    "ANTI": {
                        "search": "false",
                        "fieldToInsert": "prezzo"
                    },
                    "ANTI COSTO": {
                        "search": "false",
                        "fieldToInsert": "costo"
                    },
                    "SCANDEZA ANTISPAM": {
                        "search": "false",
                        "fieldToInsert": "scadenza"
                    },
                    "TIPOLOGIA": {
                        "search": "true",
                        "fieldToInsert": "id_tipologia",
                        "tableToSearch": "mms_slampdesk_opzioni",
                        "fieldToSearch": "id",
                        "fieldToCompare": "valore",
                        "valueToCompare": "Anti"
                    },
                    "LISTINO": {
                        "search": true,
                        "fieldToInsert": "id_listino",
                        "tableToSearch": "mms_slampdesk_listino",
                        "fieldToSearch": "id",
                        "fieldToCompare": "voce",
                        "valueToCompare": "GetFromPrezzo"
                    }
                },
                "SSL": {
                    "NOME CLIENTE": {
                        "search": "true",
                        "fieldToInsert": "id_anagrafica",
                        "tableToSearch": "mms_slampdesk_anagrafica",
                        "fieldToSearch": "id",
                        "fieldToCompare": "voce"
                    },
                    "DOMINIO": {
                        "search": "true",
                        "fieldToInsert": "id_dominio",
                        "tableToSearch": "mms_slampdesk_domini",
                        "fieldToSearch": "id",
                        "fieldToCompare": "voce"
                    },
                    "SSL": {
                        "search": "false",
                        "fieldToInsert": "prezzo"
                    },
                    "TIPOLOGIA": {
                        "search": "true",
                        "fieldToInsert": "id_tipologia",
                        "tableToSearch": "mms_slampdesk_opzioni",
                        "fieldToSearch": "id",
                        "fieldToCompare": "valore",
                        "valueToCompare": "SSL"
                    },
                    "LISTINO": {
                        "search": true,
                        "fieldToInsert": "id_listino",
                        "tableToSearch": "mms_slampdesk_listino",
                        "fieldToSearch": "id",
                        "fieldToCompare": "voce",
                        "valueToCompare": "SSL Basic"
                    }
                },
                "PEC": {
                    "NOME CLIENTE": {
                        "search": "true",
                        "fieldToInsert": "id_anagrafica",
                        "tableToSearch": "mms_slampdesk_anagrafica",
                        "fieldToSearch": "id",
                        "fieldToCompare": "voce"
                    },
                    "DOMINIO": {
                        "search": "true",
                        "fieldToInsert": "id_dominio",
                        "tableToSearch": "mms_slampdesk_domini",
                        "fieldToSearch": "id",
                        "fieldToCompare": "voce"
                    },
                    "PEC": {
                        "search": "false",
                        "fieldToInsert": "prezzo"
                    },
                    "PEC COSTO": {
                        "search": "false",
                        "fieldToInsert": "costo"
                    },
                    "SCADENZA PEC": {
                        "search": "false",
                        "fieldToInsert": "scadenza"
                    },
                    "TIPOLOGIA": {
                        "search": "true",
                        "fieldToInsert": "id_tipologia",
                        "tableToSearch": "mms_slampdesk_opzioni",
                        "fieldToSearch": "id",
                        "fieldToCompare": "valore",
                        "valueToCompare": "PEC"
                    },
                    "LISTINO": {
                        "search": true,
                        "fieldToInsert": "id_listino",
                        "tableToSearch": "mms_slampdesk_listino",
                        "fieldToSearch": "id",
                        "fieldToCompare": "voce",
                        "valueToCompare": "GetFromPrezzo"
                    }
                },
                "COOKIEBOT": {
                    "NOME CLIENTE": {
                        "search": "true",
                        "fieldToInsert": "id_anagrafica",
                        "tableToSearch": "mms_slampdesk_anagrafica",
                        "fieldToSearch": "id",
                        "fieldToCompare": "voce"
                    },
                    "DOMINIO": {
                        "search": "true",
                        "fieldToInsert": "id_dominio",
                        "tableToSearch": "mms_slampdesk_domini",
                        "fieldToSearch": "id",
                        "fieldToCompare": "voce"
                    },
                    "COOKIEBOT": {
                        "search": "false",
                        "fieldToInsert": "prezzo"
                    },
                    "COOKIEBOT COSTO": {
                        "search": "false",
                        "fieldToInsert": "costo"
                    },
                    "TIPOLOGIA": {
                        "search": "true",
                        "fieldToInsert": "id_tipologia",
                        "tableToSearch": "mms_slampdesk_opzioni",
                        "fieldToSearch": "id",
                        "fieldToCompare": "valore",
                        "valueToCompare": "Cookiebot"
                    },
                    "LISTINO": {
                        "search": true,
                        "fieldToInsert": "id_listino",
                        "tableToSearch": "mms_slampdesk_listino",
                        "fieldToSearch": "id",
                        "fieldToCompare": "voce",
                        "valueToCompare": "Cookiebot"
                    }
                }
            }
        }';
        /*$json = fopen("./configs/SERVIZI_WEB.json", 'r');
        $json = fread($json, filesize("./configs/SERVIZI_WEB.json"));*/
        $configuration = json_decode($json);
        //var_dump($configuration);
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
            if ($configuration->controllo === "true") {
                foreach ($prechecks as $column => $checked_value) {
                    if (trim($row[$headers[$column]]) !== $checked_value) {
                        echo "<p class='error'>Linea " . ($counter + 1) . " saltata: non ha passato i controlli. Dominio: " . $row[$headers[$column]] . " Anagrafica: " . $row[$headers['NOME CLIENTE']] . "</p>";
                        $skip = true;
                    }
                    if ($skip === true) break;
                }
            }

            foreach ($cycles as $key => $values) {
                //ROWSTEPS: SERVIZIO => CAMPI
                $data = array();
                if (isset($configuration->dataPreset)) {
                    foreach($configuration->dataPreset as $k => $v){
                        $data[$k] = $v;
                    }
                }
                //la $key indica il tipo di servizio
                foreach ($values as $head => $option) {
                    //
                    //$head indica la colonna del csv
                    //echo $option;
                    $index = $headers[$head];
                    //echo "$head";
                    /*foreach ($option as $h => $v) {
                        // code...
                        echo "$h => $v ";
                    }*/

                    if ($option->search == "true") {
                        //cache 
                        //cerca nella tabella corrispondente l'id
                        $searchTable = $option->tableToSearch;

                        $condition = $option->fieldToCompare;

                        $objective = $option->fieldToSearch;
                        $dataKey = $option->fieldToInsert;

                        if(empty($option->valueToCompare))$csvValue = $row[$index];
                        else{
                            $csvValue = $option->valueToCompare;
                            if($csvValue === "GetFromPrezzo"){
                                if(isset($listino[$key][$data['prezzo']])) $csvValue = $listino[$key][$data['prezzo']];
                                else echo "<p class='error'>Search fallito, nessun listino trovato per $key, linea $counter: ".$row[$headers['DOMINIO']]." Nome Cliente: ".$row[$headers['DOMINIO']]."</p>";
                            }
                        } 
                        if (isset($searchCache[$csvValue])) {
                            //prendo il valore dalla cache
                            $data[$dataKey] = $searchCache[$csvValue];
                        } else {
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
                        }

                        /*foreach($data as $k => $v){
                            echo "$k => $V";
                        }*/
                        continue;
                    }
                    if ($option->readFromCsv === "false") {
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

                    $dataVal = str_replace('€', '', $dataVal);
                    $dataVal = str_replace(',', '.', $dataVal);
                    if (is_numeric($dataVal)) $dataval = floatval($dataVal);

                    if (!empty($dataVal)) {
                        $data[$dataKey] = $dataVal;
                    }
                    //var_dump($data);
                }
                //convertire i valori numerici in veri e propri int nell'array $data
                echo "<br>$key";
                foreach ($data as $k => $v) {
                    if (is_numeric($v)) {
                        $data[$k] = floatval($v);
                    }
                    if($k === "scadenza"){
                        $v = str_replace('/','-',$v);
                        $v = date_create($v);
                        $data[$k] = date_format($v, "Y-m-d H:i:s");
                    }
                }
                if (empty($data['prezzo']) and $key !== "DOMINIO") {
                    $data = array();
                }else{
                    if($key === "DOMINIO"){
                        //elimino il listino se prezzo non è presente
                        if(empty($data['prezzo'])){
                            unset($data['id_listino']);
                        }
                    }
                }
                //if (empty($data['prezzo']) or empty($data['costo'])) $data = array();
                echo "<br>";
                var_dump($data);
                if (!empty($data)) $ris = $db->insertInto($configtablename, $data);
                if (!empty($db->error)) {
                    echo "<p>" . $db->error['code'] . ":" . $db->error['message'] . "</p>";
                }
                if($ris === false){
                    echo "Record già presente";
                }
            }
        }

        echo "Sono arrivato alla fine!";
    }
    ?>

</body>

</html>