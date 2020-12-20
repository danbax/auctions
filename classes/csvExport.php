<?php


class csvExport
{
    private $title,$columns;
    public function __construct()
    {
        $this->columns = array();
    }

    public function setTitle($titleColumns){
        $this->title = $titleColumns;
    }

    public function addColumn($column){
        array_push($this->columns,$column);
    }

    public function createCsv(){
        $data = array();
        if($this->title) {
            array_push($data, $this->title);
        }
        if($this->columns) {
            foreach($this->columns as $column){
                array_push($data, $column);

            }
        }

        $fileName = "files/Tenders-report".date('d-m-Y-H-i-s').".csv";
        $file = fopen($fileName,"w");

        fwrite($file, "\xEF\xBB\xBF");

        foreach ($data as $line) {
            fputcsv($file, $line);
        }

        fclose($file);
        return $fileName;
    }
}
