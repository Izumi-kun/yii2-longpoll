/**
 * LongPoll client script.
 *
 * @license https://opensource.org/licenses/BSD-3-Clause
 * @author Viktor Khokhryakov <viktor.khokhryakov@gmail.com>
 */
window.longpoll = (function ($) {
    var pub = {
        start: function () {
            $.each(polls, function (id, poll) {
                poll.start();
            });
        },
        stop: function () {
            $.each(polls, function (id, poll) {
                poll.stop();
            });
        },
        register: function (options) {
            polls.push(new Poll(options));
        },
        /**
         * Stop and remove all registered polls
         */
        clear: function () {
            pub.stop();
            polls = [];
        }
    };

    var polls = [];

    var defaultPollSettings = {
        type: "GET",
        url: undefined,
        params: undefined,
        callback: undefined,
        pollInterval: 500,
        pollErrorInterval: 5000
    };

    function Poll(options) {
        var settings = $.extend({}, defaultPollSettings, options || {});
        var params = settings.params;
        var timeoutId = null;
        var xhr = null;
        this.start = function () {
            if (xhr === null && timeoutId === null) {
                doLoop();
            }
        };
        this.stop = function () {
            if (xhr !== null) {
                xhr.abort();
            }
            if (timeoutId !== null) {
                clearTimeout(timeoutId);
                timeoutId = null;
            }
        };
        function doLoop() {
            xhr = $.ajax({
                type : settings.type,
                url: settings.url,
                data: params,
                dataType: "json"
            }).done(function(response){
                var triggered = false;
                $.each(response.params, function (id, value) {
                    if (params[id] != value) {
                        triggered = true;
                        return false;
                    }
                });
                params = response.params;
                if (triggered && typeof settings.callback === "function") {
                    settings.callback(response.data);
                }
                timeoutId = setTimeout(doLoop, settings.pollInterval);
            }).fail(function(e){
                if (e.status) {
                    timeoutId = setTimeout(doLoop, settings.pollErrorInterval);
                }
            }).always(function(){
                xhr = null;
            });
        }
    }

    return pub;
})(window.jQuery);
