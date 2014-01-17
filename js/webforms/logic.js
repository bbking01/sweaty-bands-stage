function JsWebFormsLogicRuleCheck(logic) {
    var flag = false;
    var field = $$('[name="field[' + logic["field_id"] + ']"]');
    var field_type = 'select';
    var selected = 'selected';
    if (typeof(field[0]) != 'object') {
        input = $$('[name="field[' + logic['field_id'] + '][]"]');
        field_type = 'checkbox';
        selected = 'checked';
    } else {
        if (field[0].type == 'radio') {
            field_type = 'radio';
            input = field;
            selected = 'checked';
        }
    }
    var value;
    if (field_type == 'select')
        var input = field[0].options;
    if (logic['aggregation'] == 'any' || (logic['aggregation'] == 'all' && logic['logic_condition'] == 'notequal')) {
        if (logic['logic_condition'] == 'equal') {
            for (var k in input) {
                if (typeof(input[k]) == 'object' && input[k]) {
                    if (input[k][selected]) {
                        for (var j in logic['value']) {
                            FieldIsVisible(logic["field_id"]) ? value = input[k].value : value = false;
                            if (value == logic['value'][j]) flag = true;
                        }
                    }
                }
            }
        } else {
            flag = true;
            var checked = false;
            for (var k in logic['value']) {
                for (var j in input) {
                    if (typeof(input[j]) == 'object' && input[j])
                        if (input[j][selected]) {
                            checked = true;
                            FieldIsVisible(logic["field_id"]) ? value = input[j].value : value = false;
                            if (value == logic['value'][k])
                                flag = false;
                        }
                }
            }
            if (!checked) flag = false;
        }
    } else {
        flag = true;
        for (var k in logic['value']) {
            for (var j in input) {
                if (typeof(input[j]) == 'object' && input[j])
                    FieldIsVisible(logic["field_id"]) ? value = input[j].value : value = false;
                if (!input[j][selected] && value == logic['value'][k])
                    flag = false;
            }
        }
    }
    return flag;
}

function JsWebFormsLogicTargetCheck(target, logicRules) {
    if (typeof(target) != 'object') return false;
    var flag = false;
    for (var i in logicRules) {
        if (typeof(logicRules[i]) == 'object')
            for (var j in logicRules[i]['target']) {
                if (typeof(target) == 'object')
                    if (target["id"] == logicRules[i]['target'][j]) {
                        if (JsWebFormsLogicRuleCheck(logicRules[i])) {
                            flag = true;
                            var config = logicRules[i];
                            break;
                        }
                    }

            }
    }
    var initState = "none";
    if (target["logic_visibility"] == 'visible')
        initState = "block";
    var changeState = "block";
    var display = initState;
    if (flag) {
        if (config['action'] == "hide") {
            changeState = "none";
        }
        display = changeState;
    }
    if ($(target["id"]) !== null && $(target["id"]).style !== undefined)
        $(target["id"]).style.display = display;

    if ($(target["id"] + '_row') !== null && $(target["id"] + '_row').style !== undefined)
        $(target["id"] + '_row').style.display = display;

    for (var i in logicRules) {
        if (typeof(logicRules[i]) == 'object')
            if (typeof(target) == 'object')
                if (target["id"] == 'field_' + logicRules[i]['field_id'] || FieldInFieldset(logicRules[i]['field_id'], target["id"])) {
                    for (var j in logicRules[i]['target']) {
                        var visibility;
                        if (logicRules[i]['action'] == 'show') visibility = 'hidden';
                        if (logicRules[i]['action'] == 'hide') visibility = 'visible';
                        var newTarget = {
                            'id': logicRules[i]['target'][j],
                            'logic_visibility': visibility};
                        JsWebFormsLogicTargetCheck(newTarget, logicRules);
                    }
                }
    }

    return flag;
}

function JSWebFormsLogic(targets, logicRules) {
    for (var n in logicRules) {
        var config = logicRules[n];
        if (typeof(config) == 'object') {
            var input = $$('[name="field[' + config["field_id"] + ']"]');
            var trigger_function = 'onchange';
            if (typeof(input[0]) != 'object') {
                input = $$('[name="field[' + config['field_id'] + '][]"]');
                trigger_function = 'onclick';
            } else {
                if (input[0].type == 'radio') {
                    trigger_function = 'onclick';
                }
            }
            for (var i in input) {
                if (trigger_function == 'onchange')
                    input[i].onchange = function () {
                        for (var k in targets)
                            JsWebFormsLogicTargetCheck(targets[k], logicRules);
                    }
                else
                    input[i].onclick = function () {
                        for (var k in targets)
                            JsWebFormsLogicTargetCheck(targets[k], logicRules);
                    }
            }
        }
    }
}

function FieldIsVisible(fieldId) {
    var el = $('field_' + fieldId);
    if (el !== null) {
        if (el.offsetWidth == 0 || el.offsetWidth == undefined) return false;
    } else {
        return false;
    }
    return true;
}

function FieldInFieldset(fieldId, fieldsetId) {
    if(typeof fieldsetId != 'string') return false;
    var el = $$('#fieldset_' + fieldsetId.replace('fieldset_', '') + ' #field_' + fieldId);
    if (el.length > 0) return true;
    return false;
}