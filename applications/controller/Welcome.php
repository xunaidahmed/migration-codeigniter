<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->library('migrations');
    }

    public function index()
    {
        $data = [];

        $this->load->view('welcome',$data);
    }

    public function migration( $param = null )
    {
        $collation = $this->db->select('values')->get_where( TABLE_SETTINGS, array(
            'key' => 'migrations'
        ), 1)->row_array();

        $collation = ($collation ? $collation['values'] + 1 : 1);

        $ultimate = $this->migrations->compileQuries($collation);

        echo sizeof($ultimate);
    }

    public function execute_migrate()
    {
        $this->load->library('migrations');

        $collation = $this->db->select('values')->get_where( TABLE_SETTINGS, array(
            'key' => 'migrations'
        ), 1)->row_array();


        $collation = ($collation ? $collation['values'] + 1 : 1);
        
        if( $collation )
            $this->migrations->runMigrate($collation);
    }
}
