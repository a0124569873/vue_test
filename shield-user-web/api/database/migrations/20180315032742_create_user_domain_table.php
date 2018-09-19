<?php

use think\migration\Migrator;

class CreateUserDomainTable extends Migrator
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
        $table = $this->table('user_domains');
        $table->addColumn('domain', 'string', ['comment' => '用户Domain', 'default' => '']);
        $table->addColumn('hd_domain', 'string', ['comment' => 'HD安全域名', 'default' => '']);
        $table->addColumn('status', 'integer', ['comment' => '域名状态', 'default' => 0]);
        $table->addColumn('ip_linked', 'string', ['comment' => '接入IP', 'default' => '']);
        $table->addColumn('text_code', 'string', ['comment' => 'TextCode记录值', 'default' => '']);
        $table->addColumn('veda_domain', 'string', ['comment' => 'Veda安全域名', 'default' => '']);
        $table->addColumn('port', 'integer', ['comment' => '用户域名端口', 'default' => 0]);
        $table->addColumn('user_id', 'integer', ['comment' => '用户ID', 'default' => 0]);

        $table->addColumn('create_time', 'datetime', ['comment' => '创建时间', 'default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('update_time', 'datetime', ['comment' => '更新时间', 'default' => 'CURRENT_TIMESTAMP']);
        $table->create();
    }
}
