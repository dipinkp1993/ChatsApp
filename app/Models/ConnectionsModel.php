<?php namespace App\Models;

use CodeIgniter\Model;

class ConnectionsModel extends Model{
  protected $table = 'connections';
  protected $allowedFields = ['resourceid', 'userid', 'name'];
 

}
