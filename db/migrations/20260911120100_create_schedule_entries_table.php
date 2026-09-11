<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateScheduleEntriesTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('schedule_entries', ['id' => 'id', 'signed' => false]);
        $table
            ->addColumn('user_id', 'integer', ['signed' => false])
            ->addColumn('day_of_week', 'integer', ['limit' => 1, 'signed' => false, 'comment' => '1=Monday .. 5=Friday'])
            ->addColumn('start_time', 'time')
            ->addColumn('end_time', 'time')
            ->addColumn('subject', 'string', ['limit' => 150])
            ->addColumn('room', 'string', ['limit' => 50, 'null' => true, 'default' => null])
            ->addColumn('teacher', 'string', ['limit' => 150, 'null' => true, 'default' => null])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addIndex(['user_id'])
            ->addIndex(['user_id', 'day_of_week'])
            ->create();
    }
}
