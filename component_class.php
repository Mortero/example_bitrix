<?php

use Bitrix\Main\Engine\ActionFilter,
    Bitrix\Main\Engine\Contract\Controllerable,
    Bitrix\Main\Loader,
    ExampleModule\Entity\ExampleORM;

/**
 * Класс-пример
 * Для упрощения чтения и структуры языковые фразы НЕ вынесены в отдельный файл
 */
class ExampleComponent extends CBitrixComponent implements Controllerable
{
    protected $limit = 10;

    public function onPrepareComponentParams($arParams)
    {
        $this->arParams = $arParams;

        if (!empty((int)$arParams['LIMIT'])) {
            $this->limit = (int)$arParams['LIMIT'];
        }

        return $arParams;
    }

    public function configureActions()
    {
        return [
            'ajaxExample' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
                    new ActionFilter\Csrf(),
                ],
            ],
        ];
    }

    public function ajaxExampleAction(): void
    {
        $userID = getCurrentUserID();
        if (!$userID) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Для выполнения этого действия, пожалуйста, авторизуйтесь',
            ]);
        } else {
            $hlRecID = $this->addHlRecord($userID);
    
            if (!empty($hlRecID)) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Ваш запрос был успешно добавлен',
                ]);
            } else {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Произошла ошибка при выполнении запроса. Пожалуйста, повторите позднее',
                ]);
            }
        }
        
        die();
    }

    private function getRecords(int $userID, string $extraParam = ''): array
    {
        $arRecords = [];

        $arFilter = [
            'USER_ID' => $userID,
            'ACTIVE' => 'Y',
        ];

        if (!empty(trim($extraParam))) {
            $arFilter['PARAM_VALUE'] = trim($extraParam);
        }

        $arRes = ExampleORM::getList([
            'filter' => $arFilter,
            'limit' => $this->limit,
            'select' => ['ID', 'NAME', 'DESCRIPTION'],
        ])->fetchAll();

        if (!empty($arRes)) {
            foreach ($arRes as $record) {
                if (empty($record['DESCRIPTION'])) {
                    $record['DESCRIPTION'] = 'Описание временно недоступно';
                }
    
                $arRecords[$record['ID']] = $record;
            }
        }

        return $arRecords;
    }

    private function getCurrentUserID(): int
    {
        $userID = 0;

        global $USER;
        if ($USER->IsAuthorized()) {
            $userID = $USER->GetID();
        }

        return $userID;
    }

    public function executeComponent()
    {
        $this->arResult['ITEMS'] = $this->getRecords();

        $this->setResultCacheKeys(['ITEMS']);

        return $this->includeComponentTemplate();
    }

    /**
     * Ради примера метод приведён здесь, но должен располагаться в модуле
     */
    private function addHlRecord(int $userID) {
        $recordID = null;

        try {
            $timestamp = new DateTime();
            $result = ExampleORM::add([
                'USER_ID' => $userID,
                'NAME' => 'Запрос пользователя от ' . $timestamp->Format('d.m.Y H:i:s'),
            ]);

            if ($result->isSuccess()) {
                $recordID = $result->getId();
            }
        } catch (Exception $e) {
            AjaxRequestLogger::add(['USER_ID' => $userID, 'EXCEPTION' => $e->GetMessage()]);
        }

        return $recordID;
    }
}