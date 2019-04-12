<?php

class msTimeStamp
{
    /** @var modX $modx */
    public $modx;

    /** @var array() $config */
    public $config = array();

    /** @var array $initialized */
    public $initialized = array();

    /**
     * @param modX $modx
     * @param array $config
     */
    function __construct(modX &$modx, array $config = [])
    {
        $this->modx =& $modx;
        $corePath = MODX_CORE_PATH . 'components/mstimestamp/';
        $assetsUrl = MODX_ASSETS_URL . 'components/mstimestamp/';


        $this->config = array_merge([
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'processorsPath' => $corePath . 'processors/',
            'customPath' => $corePath . 'custom/',

            'connectorUrl' => $assetsUrl . 'connector.php',
            'assetsUrl' => $assetsUrl,
            'cssUrl' => $assetsUrl . 'css/',
            'jsUrl' => $assetsUrl . 'js/',
            'fields_stamp' => array_map('trim', explode(',', trim($this->modx->getOption('mstimestamp_fields_stamp', $this->config, 'new,favorite,popular')))),
        ], $config);

        $this->modx->addPackage('mstimestamp', $this->config['modelPath']);
        $this->modx->lexicon->load('mstimestamp:default');

    }

    /**
     * Shorthand for the call of processor
     *
     * @access public
     *
     * @param string $action Path to processor
     * @param array $data Data to be transmitted to the processor
     *
     * @return mixed The result of the processor
     */
    public function runProcessor($action = '', $data = array())
    {
        if (empty($action)) {
            return false;
        }
        #$this->modx->error->reset();
        $processorsPath = !empty($this->config['processorsPath'])
            ? $this->config['processorsPath']
            : MODX_CORE_PATH . 'components/mstimestamp/processors/';

        return $this->modx->runProcessor($action, $data, array(
            'processors_path' => $processorsPath,
        ));
    }


    /**
     * Удаление устаревшей статистики
     */
    private function removeTimeStamp()
    {
        $max_minutes = $this->modx->getOption('check_stamp_minutes', null, 240);
        if ($max_minutes == 0) {
            $max_minutes = 1;
        }

        $current_day = date('Y-m-d H:i:s', time());
        if (!$object = $this->modx->getObject('modSystemSetting', array('key' => 'mstimestamp_last_validation_date'))) {
            /* @var modSystemSetting $object */
            $object = $this->modx->newObject('modSystemSetting');
            $object->set('key', 'mstimestamp_last_validation_date');
            $object->set('xtype', 'textfield');
            $object->set('namespace', 'mstimestamp');
            $object->set('area', 'mstimestamp_main');
            $object->set('value', date('Y-m-d', strtotime($current_day)));
            $object->set('editedon', time());
            $object->save();
        }
        $last_date_remove = $object->get('value');


        // Текущая дата
        $remove = false;
        if (empty($last_date_remove)) {
            $remove = true;
        } else {
            $today = strtotime(date('Y-m-d H:i:s', strtotime($current_day)));
            $lastday = strtotime(date('Y-m-d H:i:s', strtotime('+' . $max_minutes . ' minutes', strtotime($last_date_remove))));
            if ($today >= $lastday) {
                $remove = true;
            }
        }

        if ($remove) {
            $this->removeStamp($current_day);

            // Установка новой даты обновления
            $object->set('value', date('Y-m-d H:i:s', strtotime($current_day)));
            $object->save();
        }

    }

    /**
     * Удалит записи о времени снятии и установит значения по умолчанию
     * @param $current_day
     */
    private function removeStamp($current_day)
    {

        $current_day = strtotime($current_day);
        $ids = null;
        $q = $this->modx->newQuery('msTimeStampProduct');
        $q->select('id,product_id,field');
        $q->where(array(
            'valid_until:<' => $current_day,
        ));
        if ($q->prepare() && $q->stmt->execute()) {
            while ($row = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
                $ids[] = $row;
            }
        }

        if ($ids) {
            foreach ($ids as $row) {
                $product_id = $row['product_id'];
                $field = $row['field'];
                $sql_product = "UPDATE {$this->modx->getTableName('msProductData')} SET {$field} = '0'  WHERE id = {$product_id};";
                $this->modx->exec($sql_product);

                /* @var msProduct $object */
                $object = $this->modx->newObject('msProduct');
                $object->set('id', 66);
                $object->clearCache();
                
            }
            $ids_stamp = array_column($ids, 'id');
            $sql_stamp = "DELETE FROM {$this->modx->getTableName('msTimeStampProduct')} WHERE id IN (" . implode(',', $ids_stamp) . ");";
            $this->modx->exec($sql_stamp);
        }
    }

    /**
     * Обработчик для событий
     * @param modSystemEvent $event
     * @param array $scriptProperties
     */
    public function loadHandlerEvent(modSystemEvent $event, $scriptProperties = array())
    {
        /*
         * Загруказка карточки товара для добавление дополнительных полей
         * Сохранение карточки товара
         * Вход на страницу: OnHandleRequest
         *
         * */
        extract($scriptProperties);
        switch ($event->name) {
            case 'OnHandleRequest':
                $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
                if (!$isAjax) {
                    $this->removeTimeStamp();
                }
                break;
            case 'OnDocFormSave':

                /* @var msProduct $resource */
                if ($resource->get('class_key') == 'msProduct' and count($this->config['fields_stamp']) > 0) {

                    $data = array();
                    // Получаем список полей
                    foreach ($this->config['fields_stamp'] as $field) {
                        $value = $resource->get('valid_until_' . $field);
                        if (!empty($value)) {
                            $data[$field] = $resource->get('valid_until_' . $field);
                        }
                    }

                    // Получаем поля у которых уже назначено время
                    $product_id = $resource->get("id");
                    $ids = null;
                    $q = $this->modx->newQuery('msTimeStampProduct');
                    $q->select('id,field,valid_until');
                    $q->where(array(
                        'product_id' => $product_id,
                    ));
                    if ($q->prepare() && $q->stmt->execute()) {
                        while ($row = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
                            $field = $row['field'];
                            $ids[$field] = $row['id'];
                            $valid_until = $row['valid_until'];
                            if (!empty($valid_until) and empty($data[$field])) {
                                $data[$field] = date('d.m.Y', $valid_until);
                            }
                        }
                    }

                    if (count($data)) {
                        // Если была добавлена дата в массиве
                        foreach ($data as $field => $valid_until) {

                            // проверяем установку значений
                            $isValue = (boolean)$resource->get($field);
                            if ($isValue and !empty($valid_until)) {

                                /* @var msTimeStampProduct $TimeStampProduct */
                                $criteria = array(
                                    'product_id' => $product_id,
                                    'field' => $field,
                                );

                                if (!$TimeStampProduct = $this->modx->getObject('msTimeStampProduct', $criteria)) {
                                    $TimeStampProduct = $this->modx->newObject('msTimeStampProduct');
                                    $TimeStampProduct->fromArray($criteria);
                                } else {
                                    unset($ids[$field]);
                                }

                                $valid_until = strtotime($valid_until);
                                if ($TimeStampProduct->isDirtyValidUntil($valid_until)) {
                                    $TimeStampProduct->set('valid_until', $valid_until);
                                    $TimeStampProduct->save();
                                }
                            }
                        }

                        // Если снято значение то удаляем временную метку
                        if (count($ids) > 0) {
                            foreach ($ids as $id) {
                                if ($object = $this->modx->getObject('msTimeStampProduct', $id)) {
                                    $object->remove();
                                }
                            }
                        }
                    }
                }
                break;
            case 'OnDocFormRender':
                /* @var msProduct $resource */

                if ($resource->get('class_key') == 'msProduct' and count($this->config['fields_stamp']) > 0) {
                    $data = array();
                    foreach ($this->config['fields_stamp'] as $field) {
                        $data[$field] = '';
                    }
                    $product_id = $resource->get('id');
                    if (!empty($product_id)) {
                        $q = $this->modx->newQuery('msTimeStampProduct');
                        $q->select('field,valid_until');
                        $q->where(array(
                            'product_id' => $product_id,
                        ));
                        if ($q->prepare() && $q->stmt->execute()) {
                            while ($row = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
                                $field = $row['field'];
                                if (isset($data[$field])) {
                                    $data[$field] = $row['valid_until'];
                                }

                            }
                        }
                    }

                    // Установка значений
                    foreach ($data as $field => $date) {
                        $value = '';
                        if (!empty($date)) {
                            $value = date('Y-m-d', $date);
                        }
                        $valueResource = $resource->get($field);
                        if (!empty($valueResource)) {
                            $resource->set('valid_until_' . $field, $value);
                        }
                    }
                }

                break;
            case 'msOnManagerCustomCssJs':
                if (count($this->config['fields_stamp']) > 0) {
                    $this->modx->controller->addLastJavascript($this->config['jsUrl'] . 'mgr/widgets/default.js');
                    $this->modx->controller->addCss($this->config['cssUrl'] . 'mgr/default.css');

                    $this->modx->controller->addHtml('
                    <script type="text/javascript">
                    // <![CDATA[
                    miniShop2.mstimestamp_fields = ' . json_encode($this->config['fields_stamp']) . ';
                    // ]]>
                    </script>');
                }
                break;
        }

    }

}