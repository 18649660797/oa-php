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
    edy.rendererHelp = {
        createLink: function(href, text) {
            return "<a href='{0}'>{1}</a>".replace("{0}", href || "").replace("{1}", text || "");
        }
    };
    w.edy = edy;
} (window, jQuery));