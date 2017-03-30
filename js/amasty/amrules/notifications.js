Event.observe(window, 'load', function() {
    var actionsNotifications = document.getElementById('amrules_notifications');
    var actions = document.getElementById('rule_actions_fieldset');
    var conditions = document.getElementById('rule_conditions_fieldset');
    var conditionsNotifications = actionsNotifications.cloneNode(true);
    actions.insertBefore(actionsNotifications, actions.firstChild);
    conditions.insertBefore(conditionsNotifications, conditions.firstChild);
});