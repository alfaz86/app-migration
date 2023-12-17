<?php

namespace App\Http\Controllers;

use App\Models\DefaultModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __construct(
        // protected $db
        protected array $tables = array()
    ) {
        // $this->db = $db;
        $this->tables = DB::select('SHOW TABLES');
    }
    
    public function index()
    {
        $tables = $this->tables;
        return view('home', compact('tables'));
    }
}
