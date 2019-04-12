<?php
class msTimeStampProduct extends xPDOSimpleObject {

    /**
     * Проверка изменения даты
     * @param $new
     * @return bool
     */
    public function isDirtyValidUntil($new)
    {
        $old = $this->get('valid_until');
        $old = strtotime($old);
        return $old != $new;
    }
}