<?php

namespace app\components;

class CustomResponse extends \yii\web\Response
{
    protected function prepare()
    {
        if (is_object($this->data) || is_array($this->data)) {
            $this->format = self::FORMAT_JSON;
        }
        return parent::prepare();
    }
}