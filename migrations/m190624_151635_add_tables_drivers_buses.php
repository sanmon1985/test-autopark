<?php

use yii\db\Migration;
use yii\db\Schema;

/**
 * Class m190624_151635_add_table_drivers
 */
class m190624_151635_add_tables_drivers_buses extends Migration
{
    public function safeUp()
    {
        $driversCount = 50;
        $busCount = 40;
        //Таблица водителей
        $this->createTable('drivers', [
            'id' => Schema::TYPE_PK,
            'name' => Schema::TYPE_STRING . ' NOT NULL',
            'birthdate' => Schema::TYPE_DATE . ' NOT NULL',
        ]);
        $this->createIndex('drivers_name_index', 'drivers', 'name');

        //Таблица автобусов
        $this->createTable('buses', [
            'id' => Schema::TYPE_PK,
            'name' => Schema::TYPE_STRING . ' NOT NULL',
            'avg_speed' => Schema::TYPE_INTEGER . ' NOT NULL',
        ]);

        //Таблица связей водитель-автобус
        $this->createTable('drivers_buses', [
            'driver_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'bus_id' => Schema::TYPE_INTEGER . ' NOT NULL',
        ]);
        $this->addPrimaryKey('drivers_buses_pk', 'drivers_buses', ['driver_id', 'bus_id']);

        $faker = \Faker\Factory::create('ru_RU');
        //Генерируем водителей
        for ($i = 0; $i < $driversCount; $i++) {
            $driverData = [
                'name' => "{$faker->lastName} {$faker->firstNameMale} {$faker->middleNameMale}",//ФИО
                'birthdate' => $faker->date('Y-m-d', '2000-01-01'),//Дата рождения (до 200года)
            ];
            $this->insert('drivers', $driverData);
        }
        //Генерируем автобусы
        for ($i = 0; $i < $busCount; $i++) {
            $busData = [
                //Формат названия <Марка> NNN - <год>
                'name' => $faker->randomElement([
                        'ЛиАЗ',
                        'ПАЗ',
                        'Volvo',
                        'Hynday',
                        'Scania',
                        'MAN',
                        'Mercedes-Benz'
                    ]) . " " . $faker->numberBetween(100, 999) . " - " . $faker->year,
                'avg_speed' => $faker->numberBetween(50, 100),
            ];
            $this->insert('buses', $busData);
        }
        //Добавляем связи водитель-автобус
        for ($i = 0; $i < 100; $i++) {
            $relationData = [
                'driver_id' => $faker->numberBetween(1, $driversCount),
                'bus_id' => $faker->numberBetween(1, $busCount),
            ];
            $this->upsert('drivers_buses', $relationData, false);//upsert вместо insert для игнорирования дубликатов
        }

    }

    public function safeDown()
    {
        $this->dropTable('drivers');
        $this->dropTable('buses');
        $this->dropTable('drivers_buses');
    }
}
