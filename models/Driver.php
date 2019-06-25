<?php

namespace app\models;

use DateTime;
use Yii;

/**
 * This is the model class for table "drivers".
 *
 * @property int $id
 * @property string $name
 * @property string $birthdate
 */
class Driver extends \yii\db\ActiveRecord
{
    const HOURS_IN_DRIVEDAY = 8;//Число часов в день, которые может работать водитель

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'drivers';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'birthdate'], 'required'],
            [['birthdate'], 'safe'],
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
            'name' => 'Имя',
            'birthdate' => 'Дата рождения',
        ];
    }

    /**
     * Связь с моделью Автобус
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getBuses()
    {
        return $this->hasMany(Bus::class, ['id' => 'bus_id'])
            ->viaTable('drivers_buses', ['driver_id' => 'id']);
    }

    /**
     * Подсчет возраста
     * @return int
     * @throws \Exception
     */
    public function getAge()
    {
        try {
            $birthdate = new DateTime($this->birthdate);
        } catch (\Exception $e) {
            return 0;
        }
        $now = new DateTime();
        $interval = $now->diff($birthdate);
        return $interval->y;
    }

    /**
     * Связь с самой быстрой моделью автобуса для водителя
     * @return Driver|array|\yii\db\ActiveRecord|null
     * @throws \yii\base\InvalidConfigException
     */
    public function getFastestBus()
    {
        return $this->hasMany(Bus::class, ['id' => 'bus_id'])
            ->viaTable('drivers_buses', ['driver_id' => 'id'])
            ->orderBy(['avg_speed' => SORT_DESC])
            ->one();
    }

    /**
     * Подсчет времени прохождения дистанции
     * @param $distance
     * @return float|null
     * @throws \yii\base\InvalidConfigException
     */
    public function getTravelTime($distance)
    {
        $fastestBus = $this->getFastestBus();//Выбираем самый быстрый автобус
        if (empty($fastestBus)) {
            return null;//Нет автобусов - нет времени
        }

        $maxSpeed = $fastestBus->avg_speed;//Скорость
        $days = ceil($distance / ($maxSpeed * self::HOURS_IN_DRIVEDAY)); //Дистанцию делим на скорость,
        // затем делим на количество часов в день, которое водитель может ехать и округляем до большего

        return $days;
    }
}
