<?php

namespace app\controllers;

use app\models\Driver;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Response;

class DriverController extends \yii\web\Controller
{
    const PAGE_SIZE = 10;//Размер страницы вывода результатов

    function beforeAction($action)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;//Вывод в JSON для всех экшенов
        return parent::beforeAction($action);
    }

    /**
     * Экшен получения списка времени прохождения, отсортированный по возрастанию времени в днях
     * @param int|null $distance искомое расстояние
     * @param int $page страница результатов
     * @param int|null $id ИД водителя
     * @return array
     */
    public function actionTravelTime($distance = null, $page = 1, $id = null)
    {
        if ($id === null) {//Если ИД водителя не передан, то выбираем всех
            $drivers = Driver::find()
                ->innerJoin('drivers_buses','id = driver_id')
                ->all();
        } else {//или выбираем конкретного водителя
            $drivers = Driver::find()
                ->where(['id' => $id])
                ->all();
        }

        //Добавляем вычисляемые поля возраста и времени прохождения
        $data = ArrayHelper::toArray($drivers, [
            'app\models\Driver' => [
                'id',
                'name',
                'birth_date' => 'birthdate', //меняем название поля так как указано в ТЗ
                'age' => function ($driver) {
                    return $driver->getAge();
                },
                'travel_time' => function ($driver) use ($distance) {
                    return $driver->getTravelTime($distance);
                },
            ],
        ]);
        if ($id === null) {//постраничная выдача и сортировка только при запросе списка водителей
            ArrayHelper::multisort($data, 'travel_time', SORT_ASC);//Сортировка по времери прохождения маршрута в днях
            $pagination = self::getPaginationData($page);
            $data = array_slice($data, $pagination['offset'], $pagination['limit']);//выборка страницы результатов
        }
        return $data;
    }

    /**
     * Экшен списка водителей, отсортированных по ФИО
     * @param int $page страница результатов
     * @return array
     */
    public function actionIndex($page = 1)
    {
        //Водители
        $drivers = Driver::find()
            ->orderBy('name')
            ->all();
        //Добавляем вычисляемое поле возраста
        $data = ArrayHelper::toArray($drivers, [
            'app\models\Driver' => [
                'id',
                'name',
                'birth_date' => 'birthdate',
                'age' => function ($driver) {
                    return $driver->getAge();
                },
            ],
        ]);
        $pagination = self::getPaginationData($page);
        $data = array_slice($data, $pagination['offset'], $pagination['limit']);//выборка страницы результатов
        return $data;
    }

    /**
     * Получения данных для пагинации по номеру страницы
     * @param int $pageNumber номер страницы
     * @return array Массив с данными для пагинации. Пример: ['limit' => 10, 'offset' => 20]
     */
    private static function getPaginationData($pageNumber)
    {
        $pageNumber = ((int) $pageNumber > 0) ? (int) $pageNumber : 1;//Приводим тип и дефолтное значение если что-то не так
        $limit = self::PAGE_SIZE;
        $offset = ($pageNumber - 1) * $limit;
        return [
            'limit' => $limit,
            'offset' => $offset,
        ];
    }

}
