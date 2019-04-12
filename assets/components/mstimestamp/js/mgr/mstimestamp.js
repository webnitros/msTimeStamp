var msTimeStamp = function (config) {
    config = config || {};
    msTimeStamp.superclass.constructor.call(this, config);
};
Ext.extend(msTimeStamp, Ext.Component, {
    page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}, view: {}, utils: {}
});
Ext.reg('mstimestamp', msTimeStamp);

msTimeStamp = new msTimeStamp();