<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Local/dev only: one demo user with a full week of lessons, mirroring the
 * static prototype's sample data so the dynamic UI can be checked visually.
 */
final class DemoDataSeeder extends AbstractSeed
{
    public function run(): void
    {
        // schedule_entries.user_id has a foreign key to users, so it must be
        // truncated first or MySQL refuses to truncate the referenced table.
        $entriesTable = $this->table('schedule_entries');
        $entriesTable->truncate();

        $usersTable = $this->table('users');
        $usersTable->truncate();

        $usersTable->insert([
            'username' => 'demo',
            'email' => '[email protected]',
            'password_hash' => password_hash('demo1234', PASSWORD_DEFAULT),
        ])->saveData();

        $userId = (int) $this->getAdapter()->getConnection()->lastInsertId();

        $lessonTimes = [
            ['08:00', '08:45'],
            ['08:50', '09:35'],
            ['09:40', '10:25'],
            ['10:45', '11:30'],
            ['12:00', '12:45'],
            ['12:55', '13:40'],
            ['13:50', '14:35'],
        ];

        // day_of_week => subjects per lesson slot (null = no lesson that day)
        $subjectsByDay = [
            1 => ['Anglų', 'Matematika', 'Lietuvių', 'Technologijos', 'Istorija', 'Vokiečių', 'Vokiečių'],
            2 => ['Fizika', 'Geografija', 'Anglų', 'Matematika', 'Dailė', 'Fizinis', 'Etika'],
            3 => ['Chemija', 'Biologija', 'Matematika', 'Muzika', 'Gyvenimo įgūdžiai', 'Istorija', null],
            4 => ['Lietuvių', 'Lietuvių', 'Fizinis', 'Matematika', 'Chemija', 'Klasės valandėlė', null],
            5 => ['Anglų', 'Informatika', 'Lietuvių', 'Lietuvių', 'Geografija', 'Fizika', 'Fizinis'],
        ];

        $rows = [];
        foreach ($subjectsByDay as $day => $subjects) {
            foreach ($subjects as $i => $subject) {
                if ($subject === null) {
                    continue;
                }

                $rows[] = [
                    'user_id' => $userId,
                    'day_of_week' => $day,
                    'start_time' => $lessonTimes[$i][0],
                    'end_time' => $lessonTimes[$i][1],
                    'subject' => $subject,
                    'room' => null,
                    'teacher' => null,
                ];
            }
        }

        $entriesTable->insert($rows)->saveData();
    }
}
