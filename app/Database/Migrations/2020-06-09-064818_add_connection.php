<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddConnection extends Migration
{
	
	public function up()
	{
			$this->forge->addField([
					'conn_id'          => [
							'type'           => 'INT',
							'unsigned'       => TRUE,
							'auto_increment' => TRUE
					],
					'resourceid'          => [
						'type'           => 'INT'			
				],
				'userid'          => [
					'type'           => 'INT'
				
			],
					'name'       => [
							'type'           => 'VARCHAR',
							'constraint'     => '50',
					],
					
					]);
			$this->forge->addKey('conn_id', TRUE);
			$this->forge->createTable('connections');
	}

	public function down()
	{
			$this->forge->dropTable('connections');
	}
}
