<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "buses".
 *
 * @property int $id
 * @property string $name
 * @property int $avg_speed
 */
class Bus extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'buses';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'avg_speed'], 'required'],
            [['avg_speed'], 'integer'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Наименование',
            'avg_speed' => 'Средняя скорость',
        ];
    }

    /**
     * Связь с моделью Водитель
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getDrivers()
    {
        return $this->hasMany(Driver::class, ['id' => 'driver_id'])
            ->viaTable('drivers_buses', ['bus_id' => 'id']);
    }
}
