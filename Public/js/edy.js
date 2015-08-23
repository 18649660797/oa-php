(function(w, $) {
    function edy() {

    }
    edy.alert = function(msg) {
        return w.BUI && w.BUI.Message && w.BUI.Message.Alert && w.BUI.Message.Alert(msg) || alert(msg);
    };
    edy.ajaxHelp = {
        handleAjax: function(data) {
            if (!data || data.error) {
                edy.alert(data.error);
                return false;
            }
            return true;
        }
    };
    w.edy = edy;
} (window, jQuery));