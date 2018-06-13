<?php

use Phinx\Db\Adapter\MysqlAdapter;
use think\migration\Migrator;
use think\migration\db\Column;

class CreateJobTable extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('jobs');
        $table->addColumn('queue', 'string', ['default' => ''])
            ->addColumn('payload', 'text', ['limit' => MysqlAdapter::TEXT_LONG])
            ->addColumn('attempts', 'integer', ['default' => 0, 'limit' => MysqlAdapter::INT_SMALL])
            ->addColumn('reserved', 'integer', ['default' => 0, 'limit' => MysqlAdapter::INT_SMALL])
            ->addColumn('reserved_at', 'integer', ['default' => 0, 'limit' => '10', 'null' => true])
            ->addColumn('available_at', 'integer', ['default' => 0, 'limit' => '10'])
            ->addColumn('created_at', 'integer', ['default' => 0, 'limit' => '10'])
            ->create();
    }
}
