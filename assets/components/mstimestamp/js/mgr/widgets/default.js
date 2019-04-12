/**
 * Created by Andrey Stepanenko.
 * User: webnitros
 * Date: 08.01.2019
 * Time: 1:39
 */
Ext.ComponentMgr.onAvailable('minishop2-product-tab', function () {
    this.on('afterrender', function () {
        // Установка класса
        for (var i = 0; i < miniShop2.mstimestamp_fields.length; i++) {
            var field = miniShop2.mstimestamp_fields[i]
            var fieldminishop = Ext.getCmp('modx-resource-' + field)
            if (fieldminishop) { fieldminishop.on('check', validUntilEnabledDisable) }
        }
    })

    function validUntilEnabledDisable (el) {
        var fieldKey = el.fieldKey
        var valid_until = Ext.getCmp('modx-timestamp-date-valid_until_' + fieldKey)
        var $el = Ext.get(valid_until.container.dom.parentElement)
        if (el.checked) {
            valid_until.onEnable()
            valid_until.disabled = false
            $el.removeClass('mstimestamp_disabled')
            $el.addClass('mstimestamp_enabled')
        } else {
            valid_until.onDisable()
            valid_until.disabled = true
            valid_until.setValue('')
            $el.addClass('mstimestamp_disabled')
            $el.removeClass('mstimestamp_enabled')
        }
    }
    this.on('beforerender', function () {
        var leftRegion = this.items.items[0].items.items[2].items.items[0].items.items[0]
        var rightRegion = this.items.items[0].items.items[2].items.items[0].items.items[1]
        var record = Ext.getCmp('modx-panel-resource').record
        insertField(leftRegion)
        insertField(rightRegion)

        function insertField (items) {

            var newName, newValue, newValueStamp, disabled = ''
            for (var i = 0; i < items.items.items.length; i++) {
                var field = items.items.items[i]

                var fieldKey = field.fieldKey
                if (miniShop2.mstimestamp_fields.indexOf(fieldKey) !== -1) {

                    disabled = true
                    newValueStamp = record[fieldKey] || false
                    if (newValueStamp) {
                        disabled = false
                    }
                    newName = 'valid_until_' + fieldKey
                    newValue = record['valid_until_' + fieldKey] || ''

                    items.items.insert(i, 'modx-timestamp-date-' + newName, new Ext.form.DateField({
                        id: 'modx-timestamp-date-' + newName,
                        format: 'd.m.Y',
                        name: newName,
                        hideLabel: false,
                        fieldLabel: _('mstimestamp_sign_label'),
                        description: _('mstimestamp_sign_desc'),
                        xtype: 'xdatetime',
                        inputValue: 1,
                        value: newValue,
                        disabled: disabled,
                        listeners: {
                            afterrender: function ($this) {
                                var $el = Ext.get($this.container.dom.offsetParent)
                                $el.addClass('mstimestamp_container')

                                if ($this.disabled) {
                                    $el.addClass('mstimestamp_disabled')
                                } else {
                                    $el.addClass('mstimestamp_enabled')
                                }

                            }
                        }
                    }))
                }
            }
        }
    })
})



