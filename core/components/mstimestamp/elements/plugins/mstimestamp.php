<?php
/** @var modX $modx */
/** @var array $scriptProperties */
/* @var msTimeStamp $msTimeStamp */
switch ($modx->event->name) {
    case 'OnMODXInit':
        if ($contextKey == 'mgr') {
            $msTimeStamp = $modx->getService('mstimestamp', 'msTimeStamp', MODX_CORE_PATH . 'components/mstimestamp/model/');
        }
        break;
    case 'OnHandleRequest':
    case 'msOnManagerCustomCssJs':
    case 'OnDocFormRender':
    case 'OnDocFormSave':
        if ($msTimeStamp = $modx->getService('mstimestamp', 'msTimeStamp', MODX_CORE_PATH . 'components/mstimestamp/model/')) {
            $msTimeStamp->loadHandlerEvent($modx->event, $scriptProperties);
        }
        break;
}