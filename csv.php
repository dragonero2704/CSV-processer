<?php
    class CSV{
        public $filename = null;
        public $pathToFile = null;
        public $size = null;
        public $stream = null;

        function __construct($file)
        {
            $this->filename = $file['name'];
            $this->pathToFile = $file['tmp_name'];
            $this->size = $file['size'];
            $this->stream = fopen($this->pathToFile, 'r');
        }

        public function getRows(){
            $stream = fopen($this->pathToFile, 'r');
            $rows = array();
            while($row = fgetcsv($stream, 10000, ',')){
                array_push($rows, $row);
            }
            return $rows;
        }

        public function getNextRow(){
            if($row = fgetcsv($this->stream, 10000, ',')){
                return $row;
            }else{
                return false;
            }
        }

        public function getHead(){
            $stream = fopen($this->pathToFile, 'r');
            $headers = fgetcsv($stream, 10000, ',');
            $head = array();
            $counter = 0;
            //associo il nome dell'head all'indice della colonna
            foreach($headers as $header){
                $head[$header] = $counter;
                $counter += 1;
            }
            return $head;
        }
    }
