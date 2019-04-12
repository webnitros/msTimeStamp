<?php

/**
 * The home manager controller for msTimeStamp.
 *
 */
class msTimeStampHomeManagerController extends modExtraManagerController
{
    /** @var msTimeStamp $msTimeStamp */
    public $msTimeStamp;


    /**
     *
     */
    public function initialize()
    {
        $this->msTimeStamp = $this->modx->getService('msTimeStamp', 'msTimeStamp', MODX_CORE_PATH . 'components/mstimestamp/model/');
        parent::initialize();
    }


    /**
     * @return array
     */
    public function getLanguageTopics()
    {
        return ['mstimestamp:manager','mstimestamp:default'];
    }


    /**
     * @return bool
     */
    public function checkPermissions()
    {
        return true;
    }


    /**
     * @return null|string
     */
    public function getPageTitle()
    {
        return $this->modx->lexicon('mstimestamp');
    }


    /**
     * @return void
     */
    public function loadCustomCssJs()
    {
        $this->addCss($this->msTimeStamp->config['cssUrl'] . 'mgr/main.css');
        $this->addJavascript($this->msTimeStamp->config['jsUrl'] . 'mgr/mstimestamp.js');
        $this->addJavascript($this->msTimeStamp->config['jsUrl'] . 'mgr/misc/utils.js');
        $this->addJavascript($this->msTimeStamp->config['jsUrl'] . 'mgr/misc/combo.js');
        $this->addJavascript($this->msTimeStamp->config['jsUrl'] . 'mgr/misc/default.grid.js');
        $this->addJavascript($this->msTimeStamp->config['jsUrl'] . 'mgr/misc/default.window.js');
        $this->addJavascript($this->msTimeStamp->config['jsUrl'] . 'mgr/widgets/items/grid.js');
        $this->addJavascript($this->msTimeStamp->config['jsUrl'] . 'mgr/widgets/items/windows.js');
        $this->addJavascript($this->msTimeStamp->config['jsUrl'] . 'mgr/widgets/home.panel.js');
        $this->addJavascript($this->msTimeStamp->config['jsUrl'] . 'mgr/sections/home.js');

        $this->addHtml('<script type="text/javascript">
        msTimeStamp.config = ' . json_encode($this->msTimeStamp->config) . ';
        msTimeStamp.config.connector_url = "' . $this->msTimeStamp->config['connectorUrl'] . '";
        Ext.onReady(function() {MODx.load({ xtype: "mstimestamp-page-home"});});
        </script>');
    }


    /**
     * @return string
     */
    public function getTemplateFile()
    {
        $this->content .= '<div id="mstimestamp-panel-home-div"></div>';

        return '';
    }
}